<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ordered by id after created_at: a question and its answer are written in
     * the same second, and `created_at` alone leaves their order undefined —
     * which replays a thread with the assistant appearing to speak first.
     */
    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
