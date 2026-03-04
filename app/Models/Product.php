<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
    ];


    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn($value, $attributes) =>
            $value ?? Str::slug($attributes['name'])
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
