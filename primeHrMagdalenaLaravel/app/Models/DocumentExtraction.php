<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Extracted text for one uploaded file, so the assistant can search document
 * contents and not just filenames.
 */
class DocumentExtraction extends Model
{
    protected $fillable = [
        'source_type', 'source_id', 'employee_id',
        'file_path', 'file_type', 'file_size', 'file_hash',
        'content', 'metadata', 'status', 'extractor', 'error', 'extracted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'extracted_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Full-text where available, LIKE otherwise — the test database is SQLite,
     * which has neither MATCH…AGAINST nor this index.
     */
    public function scopeMatching($query, string $terms)
    {
        $terms = trim($terms);

        if ($terms === '') {
            return $query;
        }

        if ($query->getConnection()->getDriverName() === 'mysql') {
            return $query->whereRaw('MATCH(content) AGAINST (? IN BOOLEAN MODE)', [$terms]);
        }

        return $query->where('content', 'like', '%' . $terms . '%');
    }
}
