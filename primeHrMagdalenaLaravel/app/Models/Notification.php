<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'related_id',
        'related_type',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function getTimeAgoAttribute()
    {
        $diff = $this->created_at->diffInMinutes(now());
        
        if ($diff < 1) return 'Just now';
        if ($diff < 60) return $diff . ' minute' . ($diff > 1 ? 's' : '') . ' ago';
        
        $hours = floor($diff / 60);
        if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        
        $days = floor($hours / 24);
        if ($days < 7) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        
        return $this->created_at->format('M d, Y');
    }
}
