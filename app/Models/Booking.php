<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\BookingStatusEnum;
use App\Enum\UserRoleEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory, HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'barber_id',
        'service_id',
        'booking_date',
        'booking_time',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'barber_id')
            ->where('role', UserRoleEnum::BARBER->value);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('booking_date', '>=', now()->format('Y-m-d'));
    }

    public function scopeForBarber(Builder $query, string $barberId): Builder
    {
        return $query->where('barber_id', $barberId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [BookingStatusEnum::CANCELED->value]);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
