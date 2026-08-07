<?php

namespace App\Http\Controllers;

use App\Services\AiFileResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Serves the files the AI Assistant surfaces in chat.
 *
 * Files are not linked straight to /storage: that URL is world-readable to
 * anyone who has it, which is the wrong answer for payslips and government ID
 * scans. Every card the assistant renders points here instead, and every hit
 * re-reads the database row and re-checks AiAccessPolicy before a byte is sent
 * — so a link shared out of the chat is still useless to someone who may not
 * see that employee.
 */
class AiFileController extends Controller
{
    public function __construct(private AiFileResolver $files)
    {
    }

    /**
     * GET /ai-assistant/file/{source}/{ref}
     */
    public function show(Request $request, string $source, string $ref)
    {
        $user = Auth::user();
        $result = $this->files->resolve($user, $source, $ref);

        $this->audit($user, $source, $ref, $result);

        if (!$result['success']) {
            abort($result['status'] ?? 404, $result['error'] ?? 'File unavailable.');
        }

        // Images and PDFs open in place so the chat can show them; everything
        // else downloads.
        $disposition = ($result['inline'] && !$request->boolean('download')) ? 'inline' : 'attachment';

        return Storage::disk($result['disk'])->response(
            $result['path'],
            $result['file_name'],
            ['Content-Type' => $result['mime'], 'X-Content-Type-Options' => 'nosniff'],
            $disposition
        );
    }

    /**
     * File access lands in the same audit channel as the questions that
     * produced the link — a denied fetch is exactly the event worth having.
     *
     * @param array<string, mixed> $result
     */
    private function audit($user, string $source, string $ref, array $result): void
    {
        Log::channel('ai_audit')->info('assistant.file', [
            'user_id' => $user?->id,
            'username' => $user?->username ?? $user?->email,
            'source' => $source,
            'ref' => $ref,
            'outcome' => $result['success'] ? 'served' : 'denied',
            'reason' => $result['error'] ?? null,
        ]);
    }
}
