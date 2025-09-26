<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',          // User who performed the action
        'activity_type',    // Type of activity (login, logout, create, update, delete, etc.)
        'description',      // Human-readable description of the activity
        'model_type',       // The model class that was affected (e.g., App\Models\Student)
        'model_id',         // The ID of the model that was affected
        'changes',          // JSON of what changed (for updates)
        'ip_address',       // IP address of the user
        'user_agent',       // Browser/device information
        'session_id',       // Session ID for tracking
        'metadata',         // Additional metadata as JSON
        'performed_at'      // When the activity was performed
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
        'performed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($log) {
            // Set performed_at if not provided
            if (!$log->performed_at) {
                $log->performed_at = now();
            }
            
            // Set IP address if not provided and available
            if (!$log->ip_address && request()) {
                $log->ip_address = request()->ip();
            }
            
            // Set user agent if not provided and available
            if (!$log->user_agent && request()) {
                $log->user_agent = request()->userAgent();
            }
            
            // Set session ID if not provided and available
            if (!$log->session_id && request()) {
                $log->session_id = session()->getId();
            }
        });
    }

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected by the activity.
     */
    public function model()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }
        return null;
    }

    /**
     * Log a user activity.
     *
     * @param string $type
     * @param string $description
     * @param Model|null $model
     * @param array|null $changes
     * @param array|null $metadata
     * @return static
     */
    public static function log($type, $description, $model = null, $changes = null, $metadata = null)
    {
        $data = [
            'user_id' => auth()->id(),
            'activity_type' => $type,
            'description' => $description,
            'changes' => $changes,
            'metadata' => $metadata,
        ];
        
        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->getKey();
        }
        
        return static::create($data);
    }

    /**
     * Log a login activity.
     *
     * @param User $user
     * @param array|null $metadata
     * @return static
     */
    public static function logLogin($user, $metadata = null)
    {
        return static::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
            'description' => "User {$user->name} logged in",
            'metadata' => array_merge($metadata ?? [], [
                'login_method' => request()->input('login_method', 'standard'),
                'remember_me' => request()->boolean('remember'),
            ])
        ]);
    }

    /**
     * Log a logout activity.
     *
     * @param User $user
     * @return static
     */
    public static function logLogout($user)
    {
        return static::create([
            'user_id' => $user->id,
            'activity_type' => 'logout',
            'description' => "User {$user->name} logged out"
        ]);
    }

    /**
     * Log a failed login attempt.
     *
     * @param string $email
     * @param array|null $metadata
     * @return static
     */
    public static function logFailedLogin($email, $metadata = null)
    {
        return static::create([
            'user_id' => null,
            'activity_type' => 'failed_login',
            'description' => "Failed login attempt for email: {$email}",
            'metadata' => array_merge($metadata ?? [], [
                'email' => $email,
                'reason' => 'Invalid credentials'
            ])
        ]);
    }

    /**
     * Log a password reset activity.
     *
     * @param User $user
     * @param string $type 'requested' or 'completed'
     * @return static
     */
    public static function logPasswordReset($user, $type = 'requested')
    {
        $description = $type === 'completed' 
            ? "User {$user->name} completed password reset"
            : "User {$user->name} requested password reset";
            
        return static::create([
            'user_id' => $user->id,
            'activity_type' => "password_reset_{$type}",
            'description' => $description
        ]);
    }

    /**
     * Log a model creation activity.
     *
     * @param Model $model
     * @param string|null $description
     * @return static
     */
    public static function logCreation($model, $description = null)
    {
        $modelName = class_basename($model);
        $description = $description ?? "Created new {$modelName}";
        
        return static::log('create', $description, $model, $model->toArray());
    }

    /**
     * Log a model update activity.
     *
     * @param Model $model
     * @param array $changes
     * @param string|null $description
     * @return static
     */
    public static function logUpdate($model, $changes, $description = null)
    {
        $modelName = class_basename($model);
        $description = $description ?? "Updated {$modelName}";
        
        return static::log('update', $description, $model, $changes);
    }

    /**
     * Log a model deletion activity.
     *
     * @param Model $model
     * @param string|null $description
     * @return static
     */
    public static function logDeletion($model, $description = null)
    {
        $modelName = class_basename($model);
        $description = $description ?? "Deleted {$modelName}";
        
        return static::log('delete', $description, $model, $model->toArray());
    }

    /**
     * Log a permission change activity.
     *
     * @param User $user
     * @param string $action
     * @param array $permissions
     * @return static
     */
    public static function logPermissionChange($user, $action, $permissions)
    {
        return static::log(
            'permission_change',
            "{$action} permissions for user {$user->name}",
            $user,
            ['action' => $action, 'permissions' => $permissions]
        );
    }

    /**
     * Log a role change activity.
     *
     * @param User $user
     * @param string $action
     * @param array $roles
     * @return static
     */
    public static function logRoleChange($user, $action, $roles)
    {
        return static::log(
            'role_change',
            "{$action} roles for user {$user->name}",
            $user,
            ['action' => $action, 'roles' => $roles]
        );
    }

    /**
     * Scope a query to only include activities by a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include activities of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to only include activities for a specific model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $modelType
     * @param int|null $modelId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForModel($query, $modelType, $modelId = null)
    {
        $query->where('model_type', $modelType);
        
        if ($modelId !== null) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope a query to only include recent activities.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Get activity type label.
     *
     * @return string
     */
    public function getActivityTypeLabel()
    {
        $labels = [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'failed_login' => 'Failed Login',
            'password_reset_requested' => 'Password Reset Requested',
            'password_reset_completed' => 'Password Reset Completed',
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'permission_change' => 'Permission Changed',
            'role_change' => 'Role Changed',
            'roles_updated' => 'Roles Updated',
            'status_change' => 'Status Changed',
            'bulk_action' => 'Bulk Action',
            'export' => 'Data Exported',
            'import' => 'Data Imported',
            'view' => 'Viewed',
        ];
        
        return $labels[$this->activity_type] ?? ucfirst(str_replace('_', ' ', $this->activity_type));
    }

    /**
     * Get activity type color for UI.
     *
     * @return string
     */
    public function getActivityTypeColor()
    {
        $colors = [
            'login' => 'success',
            'logout' => 'info',
            'failed_login' => 'danger',
            'password_reset_requested' => 'warning',
            'password_reset_completed' => 'success',
            'create' => 'success',
            'update' => 'info',
            'delete' => 'danger',
            'permission_change' => 'warning',
            'role_change' => 'warning',
            'roles_updated' => 'warning',
            'status_change' => 'info',
            'bulk_action' => 'primary',
            'export' => 'primary',
            'import' => 'primary',
            'view' => 'secondary',
        ];
        
        return $colors[$this->activity_type] ?? 'secondary';
    }

    /**
     * Get activity type badge class for Bootstrap.
     * This is the MISSING METHOD that was causing the error.
     * Used in views to display colored badges.
     *
     * @return string
     */
    public function getTypeBadgeClass()
    {
        // Map activity types to Bootstrap badge classes
        // This method is called from the view with $activity->getTypeBadgeClass()
        $badgeClasses = [
            'login' => 'success',              // Green badge
            'logout' => 'info',                 // Light blue badge
            'failed_login' => 'danger',         // Red badge
            'password_reset_requested' => 'warning',     // Yellow badge
            'password_reset_completed' => 'success',     // Green badge
            'password_reset' => 'warning',      // Generic password reset - Yellow
            'create' => 'success',              // Green badge
            'update' => 'info',                 // Light blue badge
            'delete' => 'danger',               // Red badge
            'permission_change' => 'warning',   // Yellow badge
            'role_change' => 'warning',         // Yellow badge
            'roles_updated' => 'warning',       // Yellow badge
            'status_change' => 'info',          // Light blue badge
            'bulk_action' => 'primary',         // Blue badge
            'export' => 'primary',              // Blue badge
            'import' => 'primary',              // Blue badge
            'view' => 'secondary',              // Gray badge
        ];
        
        // Check if the model uses 'type' field instead of 'activity_type'
        // Some views might use a 'type' field directly
        $activityType = $this->activity_type ?? $this->type ?? null;
        
        // Return the badge class or default to 'secondary' (gray)
        return $badgeClasses[$activityType] ?? 'secondary';
    }

    /**
     * Get the type attribute - for views that use $activity->type
     * This is an accessor that maps to activity_type
     *
     * @return string|null
     */
    public function getTypeAttribute()
    {
        return $this->activity_type;
    }

    /**
     * Check if this is a critical activity that should be highlighted
     *
     * @return bool
     */
    public function isCritical()
    {
        $criticalTypes = [
            'failed_login',
            'delete',
            'permission_change',
            'role_change',
            'roles_updated',
            'password_reset_completed',
            'status_change'
        ];
        
        return in_array($this->activity_type, $criticalTypes);
    }

    /**
     * Get a formatted description with user context
     *
     * @return string
     */
    public function getFormattedDescription()
    {
        if ($this->user) {
            return "[{$this->user->name}] {$this->description}";
        }
        
        return $this->description;
    }

    /**
     * Get the icon class for the activity type
     *
     * @return string
     */
    public function getIconClass()
    {
        $icons = [
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'failed_login' => 'fas fa-exclamation-triangle',
            'password_reset_requested' => 'fas fa-key',
            'password_reset_completed' => 'fas fa-check-circle',
            'create' => 'fas fa-plus-circle',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'permission_change' => 'fas fa-shield-alt',
            'role_change' => 'fas fa-user-tag',
            'roles_updated' => 'fas fa-users-cog',
            'status_change' => 'fas fa-toggle-on',
            'bulk_action' => 'fas fa-tasks',
            'export' => 'fas fa-download',
            'import' => 'fas fa-upload',
            'view' => 'fas fa-eye',
        ];
        
        return $icons[$this->activity_type] ?? 'fas fa-circle';
    }

    /**
     * Format the performed_at timestamp for display
     *
     * @return string
     */
    public function getFormattedDate()
    {
        if (!$this->performed_at) {
            return 'Unknown';
        }
        
        // If less than 24 hours ago, show relative time
        if ($this->performed_at->isToday()) {
            return $this->performed_at->format('Today at g:i A');
        } elseif ($this->performed_at->isYesterday()) {
            return $this->performed_at->format('Yesterday at g:i A');
        } elseif ($this->performed_at->gt(now()->subDays(7))) {
            return $this->performed_at->diffForHumans();
        } else {
            return $this->performed_at->format('M d, Y g:i A');
        }
    }
}