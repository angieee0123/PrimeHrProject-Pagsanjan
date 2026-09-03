<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * One notification for one recipient.
 *
 * This model owns the notification *vocabulary* as well as the rows — the
 * audiences, the categories and how each category is drawn — because the bell,
 * the polled feed and the history page all render the same notification and
 * must not each keep their own copy of what a `pass_slip` looks like. That is
 * the same rule `EmployeeSupportingDocument` follows for the wizard's twelve
 * documents.
 */
class Notification extends Model
{
    /**
     * Which bell a notification belongs in.
     *
     * A notification is written for one *person*, but a person has one bell per
     * area they can enter — an HR officer who is also an employee sees work
     * items on /admin and their own leave decisions on /employee. The audience
     * is what keeps the two lists apart. 'system' is the broadcast case and
     * shows in every bell.
     */
    public const AUDIENCES = ['admin', 'employee', 'mayor', 'system'];

    /**
     * Category => how it is presented.
     *
     * `label` names the category in the history page's filter and on the card,
     * `icon` is the SVG body drawn inside the badge, `hue` is the gradient it
     * is filled with. These hues stay fixed rather than following the active
     * palette, the same rule the categorical chart colours keep: the colour
     * exists to tell leave from travel at a glance, and re-tinting it toward
     * the theme is how that stops working.
     */
    public const CATEGORIES = [
        'leave_request' => [
            'label' => 'Leave',
            'hue'   => ['#15803d', '#22c55e'],
            'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        ],
        'monetization' => [
            'label' => 'Monetization',
            'hue'   => ['#065f46', '#10b981'],
            'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M9.6 7v10"/><path d="M9.6 8.2h3a2.2 2.2 0 0 1 0 4.4h-3"/><path d="M8.4 14.6h5"/>',
        ],
        'travel_order' => [
            'label' => 'Travel Order',
            'hue'   => ['#a16207', '#eab308'],
            'icon'  => '<path d="M17.8 19.2 16 11l3.5-3.5a2.1 2.1 0 0 0-3-3L13 8 4.8 6.2a1 1 0 0 0-.9 1.7l5.1 3.3-2.3 2.3-2.2-.4a1 1 0 0 0-.9 1.6l2.3 2.3 2.3 2.3a1 1 0 0 0 1.6-.9l-.4-2.2 2.3-2.3 3.3 5.1a1 1 0 0 0 1.7-.9z"/>',
        ],
        'pass_slip' => [
            'label' => 'Pass Slip',
            'hue'   => ['#0f766e', '#14b8a6'],
            'icon'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 4v4"/><path d="M16 4v4"/>',
        ],
        'attendance' => [
            'label' => 'Attendance',
            'hue'   => ['#b91c1c', '#ef4444'],
            'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 7 12 12 15 14"/>',
        ],
        'payroll' => [
            'label' => 'Payroll',
            'hue'   => ['#0369a1', '#0ea5e9'],
            'icon'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        ],
        'training' => [
            'label' => 'Training',
            'hue'   => ['#7c3aed', '#a78bfa'],
            'icon'  => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>',
        ],
        'account' => [
            'label' => 'Account',
            'hue'   => ['#4338ca', '#818cf8'],
            'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        ],
        'request' => [
            'label' => 'Request',
            'hue'   => ['#ea580c', '#fb923c'],
            'icon'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        ],
        'system' => [
            'label' => 'System',
            'hue'   => ['#334155', '#64748b'],
            'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        ],
    ];

    protected $fillable = [
        'user_id',
        'type',
        'audience',
        'title',
        'message',
        'link',
        'related_id',
        'related_type',
        'dedupe_key',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /** @var array<string, bool> column name => present on this connection */
    protected static array $columnCache = [];

    /**
     * Columns added after the table shipped are dropped rather than written on
     * an install that has not run those migrations. Losing the bell there is a
     * nuisance; losing the leave approval the bell was announcing is not.
     */
    protected static function booted()
    {
        static::saving(function (self $notification) {
            foreach (['audience', 'dedupe_key'] as $column) {
                if (! static::hasNotificationColumn($column)) {
                    unset($notification->attributes[$column]);
                }
            }
        });
    }

    public static function hasNotificationColumn(string $column): bool
    {
        if (! array_key_exists($column, static::$columnCache)) {
            try {
                static::$columnCache[$column] = Schema::hasColumn('notifications', $column);
            } catch (\Throwable $e) {
                static::$columnCache[$column] = false;
            }
        }

        return static::$columnCache[$column];
    }

    /** Test seam: schema presence is memoised for the life of the process. */
    public static function flushColumnCache(): void
    {
        static::$columnCache = [];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    /**
     * The other half of markAsRead(). "I will deal with this later" is a real
     * thing to want from a list of work items, and without it the only route
     * back to an unread badge is never to have opened the notification.
     */
    public function markAsUnread(): void
    {
        if (! $this->is_read) {
            return;
        }

        $this->update(['is_read' => false, 'read_at' => null]);
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
        return $query->newestFirst()->limit($limit);
    }

    /**
     * Newest first, ties broken by id.
     *
     * Several notifications for one event are written in the same second — a
     * travel order decision reaches the filer and every accepted companion at
     * once — and without the id tiebreak the database is free to return them in
     * any order, so the same list reshuffles between two polls.
     */
    public function scopeNewestFirst($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function scopeForAdmin($query)
    {
        if (! static::hasNotificationColumn('audience')) {
            return $query;
        }

        return $query->whereIn('audience', ['admin', 'system']);
    }

    /**
     * Employee-facing notifications only. 'system' is included on every side:
     * it marks broadcast announcements, not area-specific traffic.
     */
    public function scopeForEmployee($query)
    {
        if (! static::hasNotificationColumn('audience')) {
            return $query;
        }

        return $query->whereIn('audience', ['employee', 'system']);
    }

    /**
     * The mayor's oversight bell. Deliberately not a superset of the admin
     * one: the mayor's area is read-only, so an item queued for HR to act on
     * does not belong in a list that implies it is the mayor's to act on.
     */
    public function scopeForMayor($query)
    {
        if (! static::hasNotificationColumn('audience')) {
            return $query;
        }

        return $query->whereIn('audience', ['mayor', 'system']);
    }

    /**
     * Pick the audience scope by name. The panels, the polled feed that
     * refreshes them and the history page must select the same rows, so the
     * choice lives here rather than being re-spelled in each caller.
     */
    public function scopeForAudience($query, ?string $audience)
    {
        return match ($audience) {
            'admin' => $query->forAdmin(),
            'mayor' => $query->forMayor(),
            default => $query->forEmployee(),
        };
    }

    /**
     * Unread count for one person's one bell.
     *
     * The bell badge, the sidebar row's badge and the history page's header all
     * state this number, and a rail saying 3 beside a bell saying 5 is worse
     * than neither showing one. Memoised per request because those three sit on
     * the same page and would otherwise be three identical queries.
     *
     * @var array<string, int>
     */
    protected static array $unreadMemo = [];

    public static function unreadCountFor($userId, string $audience): int
    {
        if (! $userId) {
            return 0;
        }

        $key = $userId . ':' . $audience;

        if (! array_key_exists($key, static::$unreadMemo)) {
            try {
                static::$unreadMemo[$key] = static::where('user_id', $userId)
                    ->forAudience($audience)
                    ->unread()
                    ->count();
            } catch (\Throwable $e) {
                // A missing table must not take down every page's sidebar.
                static::$unreadMemo[$key] = 0;
            }
        }

        return static::$unreadMemo[$key];
    }

    /** Drop the per-request memo — after a write, and between tests. */
    public static function flushUnreadMemo(): void
    {
        static::$unreadMemo = [];
    }

    /** Presentation for this row's category, falling back to 'system'. */
    public function category(): array
    {
        return self::CATEGORIES[$this->type] ?? self::CATEGORIES['system'];
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category()['label'];
    }

    public function getTimeAgoAttribute()
    {
        // (int) cast: Carbon 3 returns fractional minutes, which otherwise
        // render as "43.078455566667 minutes ago".
        $diff = (int) $this->created_at->diffInMinutes(now());

        if ($diff < 1) return 'Just now';
        if ($diff < 60) return $diff . ' minute' . ($diff > 1 ? 's' : '') . ' ago';

        $hours = (int) floor($diff / 60);
        if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';

        $days = (int) floor($hours / 24);
        if ($days < 7) return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';

        return $this->created_at->format('M d, Y');
    }
}
