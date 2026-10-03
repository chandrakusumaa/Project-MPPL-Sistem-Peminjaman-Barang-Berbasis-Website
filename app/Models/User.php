<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pending_email',
        'notification_preferences',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Organizations the user belongs to.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['id', 'role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Organizations created by this user.
     */
    public function createdOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    /**
     * Get the user's role in a specific organization.
     */
    public function roleIn(Organization $organization): ?Role
    {
        $membership = $this->organizations()
            ->where('organization_id', $organization->id)
            ->first();

        return $membership?->pivot?->role;
    }

    /**
     * Check if the user is a member of an organization.
     */
    public function isMemberOf(Organization $organization): bool
    {
        return $this->organizations()
            ->where('organization_id', $organization->id)
            ->exists();
    }

    /**
     * Check if user has specific role(s) in an organization.
     *
     * @param  Role|array<Role|string>|string  $roles
     */
    public function hasRoleIn(Organization $organization, Role|array|string $roles): bool
    {
        $currentRole = $this->roleIn($organization);

        if (! $currentRole) {
            return false;
        }

        if (is_array($roles)) {
            $allowedValues = array_map(
                fn ($r) => $r instanceof Role ? $r->value : $r,
                $roles
            );

            return in_array($currentRole->value, $allowedValues, true);
        }

        $expectedValue = $roles instanceof Role ? $roles->value : $roles;

        return $currentRole->value === $expectedValue;
    }

    /**
     * Check if user is an admin of the organization.
     */
    public function isAdminOf(Organization $organization): bool
    {
        return $this->hasRoleIn($organization, Role::ADMIN);
    }

    /**
     * Check if user is a staff of the organization.
     */
    public function isStaffOf(Organization $organization): bool
    {
        return $this->hasRoleIn($organization, Role::STAFF);
    }

    /**
     * Check if user is a staff or admin in the organization (can manage).
     */
    public function canManage(Organization $organization): bool
    {
        return $this->hasRoleIn($organization, [Role::ADMIN, Role::STAFF]);
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Notification categories whose email can be toggled by the user.
     */
    public const NOTIFICATION_CATEGORIES = ['membership', 'borrowing', 'damage'];

    /**
     * Categories whose email is transactional and can never be disabled.
     */
    public const MANDATORY_EMAIL_CATEGORIES = ['damage'];

    /**
     * Check if user wants email notifications for a specific category.
     */
    public function wantsEmailFor(string $category): bool
    {
        if (in_array($category, self::MANDATORY_EMAIL_CATEGORIES, true)) {
            return true;
        }

        $prefs = $this->notification_preferences;

        if (! is_array($prefs)) {
            return true;
        }

        // If the key is not set (or malformed), default to true
        return filter_var($prefs[$category] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    /**
     * Build a complete, well-formed preference array (all categories present as booleans,
     * mandatory categories forced to true).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, bool>
     */
    public static function normalizeNotificationPreferences(array $input): array
    {
        $normalized = [];

        foreach (self::NOTIFICATION_CATEGORIES as $category) {
            $normalized[$category] = in_array($category, self::MANDATORY_EMAIL_CATEGORIES, true)
                ? true
                : (bool) ($input[$category] ?? true);
        }

        return $normalized;
    }
}
