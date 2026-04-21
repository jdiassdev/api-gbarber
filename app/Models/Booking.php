<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Booking extends Model
{
    use HasUlids;

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
            ->where('role', 'barber');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    //scopes
    public function scopeUpcoming($query)
    {
        return $query->whereDate('booking_date', '>=', now()->format('Y-m-d'));
    }

    public function scopeForBarber($query, string $barberId)
    {
        return $query->where('barber_id', $barberId);
    }


    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para reservas de um cliente específico
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
