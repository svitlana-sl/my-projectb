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

    // Define composite primary key
    protected $primaryKey = ['owner_id', 'sitter_id'];
    public $incrementing = false;
    protected $keyType = 'array';

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
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'owner_id,sitter_id';
    }

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey()
    {
        return $this->owner_id . ',' . $this->sitter_id;
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