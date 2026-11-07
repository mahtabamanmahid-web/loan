<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    public const SUPER_ADMIN = 'super_admin';

    protected $fillable = ['name', 'slug', 'description'];

    /** Cached permission slugs for this request. */
    protected ?array $permissionSlugs = null;

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(AdminPermission::class, 'admin_role_permission');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        $this->permissionSlugs ??= $this->permissions()->pluck('slug')->all();

        return in_array($slug, $this->permissionSlugs, true);
    }
}
