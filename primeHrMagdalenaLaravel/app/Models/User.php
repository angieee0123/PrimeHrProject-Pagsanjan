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

    /**
     * Whether this user has been granted the given role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
    }

    /**
     * Whether this user has been granted any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->roles ?? [])) > 0;
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
            $this->roles ?? []
        );

        return array_values(array_unique(array_filter($routes)));
    }
}
