<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
