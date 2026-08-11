<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'employee_id', 'username', 'roles', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    public const ROLES = ['employee', 'hr', 'admin', 'mayor'];

    /**
     * Accounts start out Inactive (the users.status column defaults to it) and
     * stay unusable until an admin activates them from the personnel page.
     */
    public function isActive(): bool
    {
        return $this->status === 'Active';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
        ];
    }

    /**
     * Get the employee associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function notificationPreference()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function aiSetting()
    {
        return $this->hasOne(UserAiSetting::class);
    }

    /**
     * Whether this user wants to receive the given notification category.
     * Opt-out model: a user with no preference row yet (the common case,
     * since this predates the preferences feature) wants everything.
     */
    public function wantsNotification(string $key): bool
    {
        $pref = $this->notificationPreference;

        return $pref === null || (bool) $pref->{$key};
    }

    /**
     * Whether this user has been granted the given role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->normalizedRoles(), true);
    }

    /**
     * Whether this user has been granted any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->normalizedRoles())) > 0;
    }

    /**
     * Roles from the current JSON column with a safe fallback to legacy `role`.
     *
     * @return array<int, string>
     */
    public function normalizedRoles(): array
    {
        $roles = $this->getAttribute('roles');

        if (is_string($roles)) {
            $decoded = json_decode($roles, true);
            $roles = is_array($decoded) ? $decoded : [];
        }

        if (is_array($roles) && count($roles) > 0) {
            return array_values(array_unique(array_filter(
                $roles,
                fn ($value) => is_string($value) && $value !== ''
            )));
        }

        $legacyRole = $this->getAttribute('role');
        if (is_string($legacyRole) && $legacyRole !== '') {
            return [$legacyRole];
        }

        return [];
    }

    public function primaryRole(): ?string
    {
        return $this->normalizedRoles()[0] ?? null;
    }

    /**
     * The dashboard route name for a given role.
     */
    public static function dashboardRouteForRole(string $role): ?string
    {
        return match ($role) {
            'admin', 'hr' => 'admin.dashboard',
            'mayor' => 'mayor.dashboard',
            'employee' => 'employee.dashboard',
            default => null,
        };
    }

    /**
     * Distinct dashboard route names reachable by this user's roles.
     *
     * @return array<int, string>
     */
    public function dashboardRoutes(): array
    {
        $routes = array_map(
            fn (string $role) => static::dashboardRouteForRole($role),
            $this->normalizedRoles()
        );

        return array_values(array_unique(array_filter($routes)));
    }
}
