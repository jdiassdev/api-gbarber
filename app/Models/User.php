<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\UserRoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'birthday',
        'phone',
        'cpf',
        'about',
        'specialties',
        'score',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'cpf',
    ];

    protected function casts(): array
    {
        return [
            'birthday'  => 'date',
            'password'  => 'hashed',
            'is_active' => 'boolean',
            'score'     => 'float',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }

    public function barberBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'barber_id');
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBarber(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::BARBER->value);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::ADMIN->value);
    }
}
