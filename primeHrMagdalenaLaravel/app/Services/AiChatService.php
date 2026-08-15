<?php

namespace App\Services;

use App\Exceptions\AiRateLimitException;
use App\Models\SystemAiSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single entry point for calling out to an LLM for chatbot responses.
 * Resolution order: the calling user's own provider/key (Settings →
 * AI/Chatbot), then the org-wide default (also Settings → AI/Chatbot, admin-
 * managed, stored in system_ai_settings), then .env's GROQ_API_KEY as a
 * last-resort fallback if that row was never configured.
 */
class AiChatService
{
    /**
     * Why the most recent call in this request returned null.
     *
     * Every caller degrades to a deterministic answer when chat() returns null,
     * and they all used to describe that the same way: "the provider is
     * unavailable or not configured (Settings → AI/Chatbot)". That sentence was
     * wrong in the case that actually happens. A configured key that the
     * provider *rejects* fails identically to no key at all, so a live 401 —
     * a revoked or mistyped key — was reported to every user, on every
     * unmatched question, as though nobody had ever set the assistant up. The
     * admin reading it goes to Settings, sees a key sitting there, and has no
     * reason to suspect the key itself.
     *
     * Recorded rather than thrown because a failed narration is not a failed
     * answer: the row data is still good, and the caller's job is to render it
     * without prose, not to abort.
     */
    private static ?string $lastFailure = null;

    public const FAILURE_UNCONFIGURED = 'unconfigured';
    public const FAILURE_REJECTED = 'rejected';
    public const FAILURE_UNAVAILABLE = 'unavailable';

    /**
     * The reason the last chat()/complete() returned null, or null if the last
     * call succeeded. Valid only within the current request.
     */
    public static function lastFailure(): ?string
    {
        return self::$lastFailure;
    }

    /**
     * Single-prompt completion. Kept for callers that have no conversation
     * context to pass along.
     */
    public static function complete(?User $user, string $prompt, float $temperature = 0.5, int $maxTokens = 450): ?string
    {
        return self::chat($user, '', [['role' => 'user', 'content' => $prompt]], $temperature, $maxTokens);
    }

    /**
     * Multi-turn completion with an optional system prompt. The AI Assistant
     * services use this so prior turns stay in context ("show John's leave" →
     * "now generate a report") instead of being flattened into one string.
     *
     * @param array<int, array{role: string, content: string}> $messages
     */
    public static function chat(?User $user, string $system, array $messages, float $temperature = 0.3, int $maxTokens = 900): ?string
    {
        self::$lastFailure = null;

        $config = self::resolveConfig($user);

        if (!$config['api_key']) {
            self::$lastFailure = self::FAILURE_UNCONFIGURED;
            return null;
        }

        $messages = self::normalizeMessages($messages);

        if (empty($messages)) {
            self::$lastFailure = self::FAILURE_UNAVAILABLE;
            return null;
        }

        return match ($config['provider']) {
            'openai' => self::callOpenAiCompatible($config, 'https://api.openai.com/v1/chat/completions', $system, $messages, $temperature, $maxTokens),
            'anthropic' => self::callAnthropic($config, $system, $messages, $temperature, $maxTokens),
            default => self::callOpenAiCompatible($config, 'https://api.groq.com/openai/v1/chat/completions', $system, $messages, $temperature, $maxTokens),
        };
    }

    /**
     * A 401/403 means the key itself is bad — revoked, mistyped, or belonging
     * to another provider. That is an operator problem with an exact fix, and
     * it is worth distinguishing from a provider outage, which resolves itself.
     */
    private static function noteHttpFailure(int $status): void
    {
        self::$lastFailure = in_array($status, [401, 403], true)
            ? self::FAILURE_REJECTED
            : self::FAILURE_UNAVAILABLE;
    }

    public static function defaultModel(string $provider): string
    {
        return match ($provider) {
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-sonnet-5',
            default => 'llama-3.3-70b-versatile',
        };
    }

    /**
     * Whether any provider is configured. Lets callers degrade gracefully
     * instead of surfacing a null answer as an error.
     */
    public static function isConfigured(?User $user = null): bool
    {
        return (bool) self::resolveConfig($user)['api_key'];
    }

    /**
     * Drop blanks, collapse consecutive same-role turns, and guarantee the
     * sequence starts with a user turn — Anthropic rejects anything else, and
     * the OpenAI-compatible providers behave better for it too.
     *
     * @param array<int, array{role: string, content: string}> $messages
     * @return array<int, array{role: string, content: string}>
     */
    private static function normalizeMessages(array $messages): array
    {
        $clean = [];

        foreach ($messages as $message) {
            $role = ($message['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim((string) ($message['content'] ?? ''));

            if ($content === '') {
                continue;
            }

            $last = end($clean);
            if ($last && $last['role'] === $role) {
                $clean[count($clean) - 1]['content'] .= "\n\n" . $content;
                continue;
            }

            $clean[] = ['role' => $role, 'content' => $content];
        }

        while (!empty($clean) && $clean[0]['role'] !== 'user') {
            array_shift($clean);
        }

        return array_values($clean);
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     */
    private static function callOpenAiCompatible(array $config, string $endpoint, string $system, array $messages, float $temperature, int $maxTokens): ?string
    {
        if ($system !== '') {
            array_unshift($messages, ['role' => 'system', 'content' => $system]);
        }

        $response = null;

        try {
            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'model' => $config['model'],
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);
        } catch (\Throwable $e) {
            Log::error("AI chat provider ({$config['provider']}) exception: " . $e->getMessage());
            self::$lastFailure = self::FAILURE_UNAVAILABLE;
            return null;
        }

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        if ($response->status() === 429) {
            Log::warning("AI chat provider ({$config['provider']}) rate limited: " . $response->body());
            throw new AiRateLimitException($config['provider'], $response->body());
        }

        Log::error("AI chat provider ({$config['provider']}) error: " . $response->status() . ' ' . $response->body());
        self::noteHttpFailure($response->status());

        return null;
    }

    /**
     * @param array<int, array{role: string, content: string}> $messages
     */
    private static function callAnthropic(array $config, string $system, array $messages, float $temperature, int $maxTokens): ?string
    {
        $payload = [
            'model' => $config['model'],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => $messages,
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        $response = null;

        try {
            $response = Http::timeout(30)->withHeaders([
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', $payload);
        } catch (\Throwable $e) {
            Log::error('AI chat provider (anthropic) exception: ' . $e->getMessage());
            self::$lastFailure = self::FAILURE_UNAVAILABLE;
            return null;
        }

        if ($response->successful()) {
            return $response->json('content.0.text');
        }

        if ($response->status() === 429) {
            Log::warning('AI chat provider (anthropic) rate limited: ' . $response->body());
            throw new AiRateLimitException('anthropic', $response->body());
        }

        Log::error('AI chat provider (anthropic) error: ' . $response->status() . ' ' . $response->body());
        self::noteHttpFailure($response->status());

        return null;
    }

    private static function resolveConfig(?User $user): array
    {
        $setting = $user?->aiSetting;

        if ($setting && $setting->provider && $setting->api_key) {
            return [
                'provider' => $setting->provider,
                'api_key' => $setting->api_key,
                'model' => $setting->model ?: self::defaultModel($setting->provider),
            ];
        }

        $system = SystemAiSetting::current();
        if ($system->provider && $system->api_key) {
            return [
                'provider' => $system->provider,
                'api_key' => $system->api_key,
                'model' => $system->model ?: self::defaultModel($system->provider),
            ];
        }

        return [
            'provider' => 'groq',
            'api_key' => config('services.groq.api_key') ?: env('GROQ_API_KEY'),
            'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
        ];
    }
}
