<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAiSetting extends Model
{
    public const PROVIDERS = ['groq', 'openai', 'anthropic'];

    protected $fillable = [
        'user_id',
        'provider',
        'api_key',
        'model',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Last 4 characters of the stored key, for display — never expose the full key
     * back to the client once saved.
     */
    public function maskedKey(): ?string
    {
        if (!$this->api_key) {
            return null;
        }

        $len = strlen($this->api_key);
        return str_repeat('•', min($len, 20)) . substr($this->api_key, -4);
    }
}
