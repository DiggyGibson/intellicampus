<?php

// app/Models/OrganizationalPermission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationalPermission extends Model
{
    protected $fillable = [
        'user_id', 'scope_type', 'scope_id', 'permission_key',
        'access_level', 'granted_by', 'granted_at', 'expires_at',
        'is_active', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get who granted the permission
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Get the scoped entity (polymorphic)
     */
    public function scopedEntity()
    {
        $modelClass = $this->getScopeModelClass();
        return $modelClass ? (new $modelClass)->find($this->scope_id) : null;
    }

    /**
     * Get the model class for the scope type
     */
    protected function getScopeModelClass(): ?string
    {
        $scopeModels = [
            'college' => College::class,
            'school' => School::class,
            'department' => Department::class,
            'division' => Division::class,
            'program' => AcademicProgram::class,
        ];

        return $scopeModels[$this->scope_type] ?? null;
    }

    /**
     * Scope for active permissions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now());
                     });
    }

    /**
     * Check if permission allows specific action
     */
    public function allows(string $action): bool
    {
        $levels = [
            'view' => ['view', 'create', 'edit', 'delete', 'manage'],
            'create' => ['create', 'edit', 'delete', 'manage'],
            'edit' => ['edit', 'delete', 'manage'],
            'delete' => ['delete', 'manage'],
            'manage' => ['manage'],
        ];

        return in_array($this->access_level, $levels[$action] ?? []);
    }
}