<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    // Only use created_at, not updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'owner_id',
        'sitter_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the owner user.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the sitter user.
     */
    public function sitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sitter_id');
    }
} 