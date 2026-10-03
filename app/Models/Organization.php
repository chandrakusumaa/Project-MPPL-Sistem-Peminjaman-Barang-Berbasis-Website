<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'logo',
        'max_borrow_days',
        'late_fine_per_day',
        'archived_at',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'max_borrow_days' => 'integer',
            'late_fine_per_day' => 'integer',
        ];
    }

    /**
     * User who created the organization.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Users belonging to this organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['id', 'role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Admins of this organization.
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', Role::ADMIN->value);
    }

    /**
     * Staff members of this organization.
     */
    public function staff(): BelongsToMany
    {
        return $this->users()->wherePivot('role', Role::STAFF->value);
    }

    /**
     * Regular members of this organization.
     */
    public function regularMembers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', Role::MEMBER->value);
    }

    /**
     * Check if the organization is archived.
     */
    public function isArchived(): bool
    {
        return ! is_null($this->archived_at);
    }

    /**
     * Total active admins count in this organization.
     */
    public function adminCount(): int
    {
        return $this->admins()->count();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include active (not archived) organizations.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }
}
