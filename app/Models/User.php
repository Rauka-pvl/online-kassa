<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login',
        'name',
        'email',
        'password',
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
        ];
    }

    // Добавить в app/Models/User.php
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function registeredAppointments()
    {
        return $this->hasMany(Appointment::class, 'registrar_id');
    }

    public function isDoctor(): bool
    {
        return $this->role == 4;
    }

    public function isAdmin(): bool
    {
        return $this->role == 1;
    }

    public function isRegistrar(): bool
    {
        return $this->role == 3;
    }

    public function getRoleNameAttribute(): string
    {
        $roles = [
            1 => 'Администратор',
            2 => 'Бухгалтер',
            3 => 'Регистратор',
            4 => 'Врач'
        ];

        return $roles[$this->role] ?? 'Неизвестно';
    }
}
