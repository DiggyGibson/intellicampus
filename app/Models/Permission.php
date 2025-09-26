<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',           // Permission display name (e.g., "View Students")
        'slug',           // Permission identifier (e.g., "students.view")
        'module',         // Module this permission belongs to (e.g., "students")
        'description',    // Detailed description of what this permission allows
        'is_system',      // Whether this is a system permission (cannot be deleted)
        'metadata'        // JSON field for additional permission data
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
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
        'is_system' => false,
        'metadata' => '{}',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from module and name if not provided
        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = self::generateSlug($permission->module, $permission->name);
            }
            
            // Extract module from slug if not provided
            if (empty($permission->module) && !empty($permission->slug)) {
                $parts = explode('.', $permission->slug);
                if (count($parts) > 1) {
                    $permission->module = $parts[0];
                }
            }
        });

        // Prevent deletion of system permissions
        static::deleting(function ($permission) {
            if ($permission->is_system) {
                return false;
            }
        });
    }

    /**
     * Generate a slug from module and name.
     *
     * @param string $module
     * @param string $name
     * @return string
     */
    public static function generateSlug($module, $name)
    {
        $module = Str::slug($module, '_');
        $action = Str::slug($name, '_');
        
        // Remove common prefixes from action
        $action = preg_replace('/^(view|create|update|delete|manage|access)_/', '', $action);
        
        // Determine the action verb
        if (stripos($name, 'view') !== false || stripos($name, 'read') !== false) {
            $verb = 'view';
        } elseif (stripos($name, 'create') !== false || stripos($name, 'add') !== false) {
            $verb = 'create';
        } elseif (stripos($name, 'update') !== false || stripos($name, 'edit') !== false) {
            $verb = 'update';
        } elseif (stripos($name, 'delete') !== false || stripos($name, 'remove') !== false) {
            $verb = 'delete';
        } elseif (stripos($name, 'manage') !== false || stripos($name, 'admin') !== false) {
            $verb = 'manage';
        } else {
            $verb = $action;
        }
        
        return "{$module}.{$verb}";
    }

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withPivot('granted_at', 'granted_by')
            ->withTimestamps();
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'permission_user')
            ->withPivot('granted_at', 'granted_by', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Check if this permission belongs to a specific module.
     *
     * @param string $module
     * @return bool
     */
    public function belongsToModule($module)
    {
        return $this->module === $module;
    }

    /**
     * Get all permissions for a specific module.
     *
     * @param string $module
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function forModule($module)
    {
        return static::where('module', $module)->get();
    }

    /**
     * Get all unique modules.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getModules()
    {
        return static::distinct('module')->pluck('module');
    }

    /**
     * Create multiple permissions for a module.
     *
     * @param string $module
     * @param array $actions
     * @return \Illuminate\Support\Collection
     */
    public static function createForModule($module, $actions = ['view', 'create', 'update', 'delete'])
    {
        $permissions = collect();
        
        foreach ($actions as $action) {
            $name = ucfirst($action) . ' ' . ucfirst($module);
            $permission = static::firstOrCreate(
                ['slug' => "{$module}.{$action}"],
                [
                    'name' => $name,
                    'module' => $module,
                    'description' => "Allow user to {$action} {$module}",
                    'is_system' => true
                ]
            );
            $permissions->push($permission);
        }
        
        return $permissions;
    }

    /**
     * Get the count of roles with this permission.
     *
     * @return int
     */
    public function getRoleCount()
    {
        return $this->roles()->count();
    }

    /**
     * Get the count of users with this permission (direct assignment).
     *
     * @return int
     */
    public function getDirectUserCount()
    {
        return $this->users()->count();
    }

    /**
     * Get total user count (through roles and direct assignment).
     *
     * @return int
     */
    public function getTotalUserCount()
    {
        $roleUsers = User::whereHas('roles.permissions', function ($query) {
            $query->where('permissions.id', $this->id);
        })->pluck('id');
        
        $directUsers = $this->users()->pluck('users.id');
        
        return $roleUsers->merge($directUsers)->unique()->count();
    }

    /**
     * Scope a query to only include system permissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to filter by module.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $module
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope a query to order by module and name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('module')->orderBy('name');
    }
}