<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',           // Role display name (e.g., "Super Administrator")
        'slug',           // URL-friendly name (e.g., "super-administrator")
        'description',    // Detailed description of the role
        'is_system',      // Whether this is a system-defined role (cannot be deleted)
        'is_active',      // Whether the role is currently active
        'priority',       // Role priority for hierarchy (lower number = higher priority)
        'metadata'        // JSON field for additional role data
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'is_system' => false,
        'priority' => 999,
        'metadata' => '{}',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });

        // Update slug when name changes
        static::updating(function ($role) {
            if ($role->isDirty('name') && !$role->isDirty('slug')) {
                $role->slug = Str::slug($role->name);
            }
        });

        // Prevent deletion of system roles
        static::deleting(function ($role) {
            if ($role->is_system) {
                return false;
            }
        });
    }

    /**
     * Get the users that have this role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot('assigned_at', 'assigned_by', 'expires_at', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withPivot('granted_at', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Assign a permission to this role.
     *
     * @param Permission|string|int $permission
     * @return void
     */
    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        } elseif (is_numeric($permission)) {
            $permission = Permission::findOrFail($permission);
        }

        if (!$this->hasPermissionTo($permission)) {
            $this->permissions()->attach($permission->id, [
                'granted_at' => now(),
                'granted_by' => auth()->id()
            ]);
        }
    }

    /**
     * Remove a permission from this role.
     *
     * @param Permission|string|int $permission
     * @return void
     */
    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        } elseif (is_numeric($permission)) {
            $permission = Permission::find($permission);
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Check if the role has a specific permission.
     *
     * @param Permission|string $permission
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions()->where('permission_id', $permission->id)->exists();
    }

    /**
     * Sync permissions for this role.
     *
     * @param array $permissions Array of permission IDs or slugs
     * @return void
     */
    public function syncPermissions($permissions)
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            if (is_numeric($permission)) {
                return $permission;
            }
            return Permission::where('slug', $permission)->first()?->id;
        })->filter()->toArray();

        $syncData = [];
        foreach ($permissionIds as $permissionId) {
            $syncData[$permissionId] = [
                'granted_at' => now(),
                'granted_by' => auth()->id()
            ];
        }

        $this->permissions()->sync($syncData);
    }

    /**
     * Get all permissions for this role (including inherited).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        return $this->permissions;
    }

    /**
     * Check if this role is a super admin role.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->slug === 'super-administrator';
    }

    /**
     * Check if this role is higher priority than another role.
     *
     * @param Role $role
     * @return bool
     */
    public function hasHigherPriorityThan(Role $role)
    {
        return $this->priority < $role->priority;
    }

    /**
     * Get the count of users with this role.
     *
     * @return int
     */
    public function getUserCount()
    {
        return $this->users()->count();
    }

    /**
     * Get the count of active users with this role.
     *
     * @return int
     */
    public function getActiveUserCount()
    {
        return $this->users()->where('status', 'active')->count();
    }

    /**
     * Scope a query to only include active roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include system roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to order by priority.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }
}