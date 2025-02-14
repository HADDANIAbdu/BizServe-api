<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'users';

    // Implement the JWTSubject methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Define a relationship with the Role model
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Define many-to-many relationship with the Permission model (through roles)
    public function permissions()
    {
        return $this->hasManyThrough(Permission::class, Role::class);
    }

    // Check if the user has a specific role by name or array of names
    public function hasRole($roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        // Use Laravel collection method `contains` for a more optimized check
        return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
    }

    // Check if the user has a specific permission by name
    public function hasPermission(string $permission): bool
    {
        // Directly check if any role's permissions contain the given permission name
        return $this->roles->flatMap(function ($role) {
            return $role->permissions->pluck('name');
        })->contains($permission);
    }

    // Assign a role to the user without detaching existing roles
    public function assignRole($roleId): void
    {
        $role = Role::findOrFail($roleId);
        $this->roles()->syncWithoutDetaching($role->id);
    }
}
