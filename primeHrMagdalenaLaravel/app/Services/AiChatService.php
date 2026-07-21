<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single entry point for calling out to an LLM for chatbot responses.
 * Resolves the calling user's own provider/API key (Settings → AI/Chatbot)
 * when configured, otherwise falls back to the system-wide GROQ_API_KEY.
 */
class AiChatService
{
    public static function complete(?User $user, string $prompt, float $temperature = 0.5, int $maxTokens = 450): ?string
    {
        $config = self::resolveConfig($user);

        if (!$config['api_key']) {
            return null;
        }

        return match ($config['provider']) {
            'openai' => self::callOpenAiCompatible($config, 'https://api.openai.com/v1/chat/completions', $prompt, $temperature, $maxTokens),
            'anthropic' => self::callAnthropic($config, $prompt, $temperature, $maxTokens),
            default => self::callOpenAiCompatible($config, 'https://api.groq.com/openai/v1/chat/completions', $prompt, $temperature, $maxTokens),
        };
    }

    public static function defaultModel(string $provider): string
    {
        return match ($provider) {
            'openai' => 'gpt-4o-mini',
            'anthropic' => 'claude-sonnet-5',
            default => 'llama-3.3-70b-versatile',
        };
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

        return [
            'provider' => 'groq',
            'api_key' => config('services.groq.api_key') ?: env('GROQ_API_KEY'),
            'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
        ];
    }

    private static function callOpenAiCompatible(array $config, string $endpoint, string $prompt, float $temperature, int $maxTokens): ?string
    {
        try {
            $response = Http::timeout(20)->withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'model' => $config['model'],
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error("AI chat provider ({$config['provider']}) error: " . $response->status() . ' ' . $response->body());
        } catch (\Throwable $e) {
            Log::error("AI chat provider ({$config['provider']}) exception: " . $e->getMessage());
        }

        return null;
    }

    private static function callAnthropic(array $config, string $prompt, float $temperature, int $maxTokens): ?string
    {
        try {
            $response = Http::timeout(20)->withHeaders([
                'x-api-key' => $config['api_key'],
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $config['model'],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            Log::error('AI chat provider (anthropic) error: ' . $response->status() . ' ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('AI chat provider (anthropic) exception: ' . $e->getMessage());
        }

        return null;
    }
}
