<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
            'role' => UserRole::class,
        ];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCashier(): bool
    {
        return $this->role === UserRole::Cashier;
    }

    /** الحساب الأساسي للإدارة — لا يُحذف ولا يُزال دوره */
    public function isProtectedAdmin(): bool
    {
        return $this->isAdmin();
    }

    public function roleLabel(): string
    {
        return $this->role?->label() ?? 'كاشير';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function registerFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $data = ['failed_login_attempts' => $attempts];

        if ($attempts >= 5) {
            $data['locked_until'] = now()->addMinutes(15);
            $data['failed_login_attempts'] = 0;
        }

        $this->forceFill($data)->save();
    }

    public function clearLoginAttempts(): void
    {
        if ($this->failed_login_attempts > 0 || $this->locked_until) {
            $this->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ])->save();
        }
    }

    public function homeRoute(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'pos.index';
    }

    public static function adminCount(): int
    {
        return static::query()->where('role', UserRole::Admin)->count();
    }
}
