<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton row: the org-wide fallback AI provider/key used for any user who
 * hasn't configured their own (see UserAiSetting). Editable from
 * Settings → AI/Chatbot by admins, so no server/.env access is needed to
 * change it — .env's GROQ_API_KEY is only consulted if this row is empty.
 */
class SystemAiSetting extends Model
{
    protected $fillable = [
        'provider',
        'api_key',
        'model',
        'theme',
        'custom_theme_primary',
        'theme_secondary',
        'theme_accent',
        'theme_muted',
    ];

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function maskedKey(): ?string
    {
        if (!$this->api_key) {
            return null;
        }

        $len = strlen($this->api_key);
        return str_repeat('•', min($len, 20)) . substr($this->api_key, -4);
    }
}
