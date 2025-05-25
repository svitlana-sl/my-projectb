<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitterProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'default_hourly_rate',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'default_hourly_rate' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user that owns the sitter profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 