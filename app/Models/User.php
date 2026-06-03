<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
        'avatar',              // ST-10: caminho relativo em storage/app/public/avatars/
        'onboarding_completo', // ST-15: wizard de primeiro acesso
        'tutorial_completo',   // ST-15: placeholder Shepherd.js (pós-TCC)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'password'            => 'hashed',
        'active'              => 'boolean',
        'onboarding_completo' => 'boolean',
        'tutorial_completo'   => 'boolean',
    ];

    public function profissional()
    {
        return $this->hasOne(Profissional::class);
    }

    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function nivelAcesso(): ?int
    {
        return $this->roles()->min('nivel');
    }
}
