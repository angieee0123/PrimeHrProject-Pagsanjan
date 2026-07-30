<?php

namespace App\Services;

/**
 * Feature 7: AI Memory.
 *
 * Turns a follow-up that only makes sense in context into a standalone
 * question, so downstream services never have to reason about pronouns:
 *
 *   User: "Show John's leave records."
 *   User: "Now generate a report."
 *        → "Generate a report of John's leave records."
 *
 * Rewriting happens only when the message actually looks like a follow-up;
 * a self-contained question is passed through untouched so we do not spend a
 * model round-trip (or risk distorting it) for nothing.
 */
class ConversationMemoryService
{
    /** Words that signal the message leans on earlier turns. */
    private const ANAPHORA = '/\b(that|those|these|it|its|them|they|their|him|her|his|the\s+same|same|again|instead|also|too|this\s+one|the\s+report|the\s+chart|the\s+graph|the\s+list|the\s+file|the\s+employee|the\s+department)\b/i';

    /** A message this short is almost always a follow-up. */
    private const SHORT_TURN_WORDS = 6;

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function resolve(string $message, array $history): string
    {
        if (empty($history) || !$this->looksLikeFollowUp($message)) {
            return $message;
        }

        $rewritten = $this->rewriteWithModel($message, $history);

        return $rewritten ?: $this->rewriteHeuristically($message, $history);
    }

    private function looksLikeFollowUp(string $message): bool
    {
        $words = str_word_count($message);

        return $words <= self::SHORT_TURN_WORDS || (bool) preg_match(self::ANAPHORA, $message);
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    private function rewriteWithModel(string $message, array $history): ?string
    {
        if (!AiChatService::isConfigured()) {
            return null;
        }

        $transcript = collect(array_slice($history, -6))
            ->map(fn (array $turn) => ($turn['role'] === 'assistant' ? 'Assistant: ' : 'User: ')
                . mb_substr($turn['content'], 0, 400))
            ->implode("\n");

        $system = <<<'PROMPT'
Rewrite the user's latest message into a single self-contained question by
substituting in the specific subject from the conversation.

Rules:
- Output ONLY the rewritten question. No preamble, no quotes, no explanation.
- Keep the user's intent and verb exactly; only resolve what "it"/"that"/
  "the report" refers to.
- If the message is already self-contained, output it unchanged.
- Never invent a name, department, or date that does not appear above.
PROMPT;

        $answer = AiChatService::chat(
            null,
            $system,
            [['role' => 'user', 'content' => "Conversation so far:\n{$transcript}\n\nLatest message: {$message}"]],
            0.0,
            160
        );

        if (!$answer) {
            return null;
        }

        $answer = trim(strip_tags($answer), " \t\n\r\"'");

        // Guard against the model returning commentary instead of a question.
        if ($answer === '' || mb_strlen($answer) > 400 || str_contains(strtolower($answer), 'i cannot')) {
            return null;
        }

        return $answer;
    }

    /**
     * No model available: staple the previous user turn on as context. Crude,
     * but it keeps the subject in play rather than losing it entirely.
     *
     * @param array<int, array{role: string, content: string}> $history
     */
    private function rewriteHeuristically(string $message, array $history): string
    {
        $lastUserTurn = collect($history)
            ->filter(fn (array $turn) => $turn['role'] === 'user')
            ->last();

        if (!$lastUserTurn) {
            return $message;
        }

        return $message . ' (in reference to: ' . mb_substr($lastUserTurn['content'], 0, 200) . ')';
    }

    /**
     * Employee names mentioned earlier, newest first. Lets a service re-scope
     * to the person under discussion without re-asking.
     *
     * @param array<int, array{role: string, content: string}> $history
     * @return array<int, string>
     */
    public function recentSubjects(array $history): array
    {
        $names = [];

        foreach (array_reverse($history) as $turn) {
            if (preg_match_all('/\b([A-Z][a-z]{2,}(?:\s+[A-Z][a-z]{2,})+)\b/', $turn['content'] ?? '', $m)) {
                foreach ($m[1] as $candidate) {
                    $names[] = $candidate;
                }
            }
        }

        return array_values(array_unique($names));
    }
}
