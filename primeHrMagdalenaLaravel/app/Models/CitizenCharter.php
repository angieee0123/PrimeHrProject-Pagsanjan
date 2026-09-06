<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One imported Citizen's Charter document.
 *
 * Single-active semantics: exactly one row carries `is_active = true` at a
 * time. A replacement is stored inactive first and only activated once its
 * text extracts cleanly, so a failed import never leaves the chatbot with
 * nothing to answer from; the row it replaces (and its file) is then removed,
 * so storage holds exactly the charter the chatbot reads. The charter is
 * public municipal information, so these rows carry no employee_id and are
 * never passed through AiAccessPolicy.
 */
class CitizenCharter extends Model
{
    protected $fillable = [
        'original_name', 'stored_path', 'file_type', 'file_size', 'content_hash',
        'content', 'page_count', 'status', 'extractor', 'error',
        'is_active', 'uploaded_by', 'extracted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'extracted_at' => 'datetime',
    ];

    /**
     * The charter the chatbot answers from: the active row with usable text.
     */
    public static function current(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('status', 'extracted')
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->latest('id')
            ->first();
    }

    /**
     * The active row regardless of extraction state — what Settings shows.
     */
    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->latest('id')->first();
    }
}
