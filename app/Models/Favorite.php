<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    use HasFactory;

    // Disable auto-incrementing since we use composite primary key
    public $incrementing = false;
    
    // Disable updated_at since we only have created_at
    public $timestamps = false;
    
    protected $primaryKey = ['owner_id', 'sitter_id'];

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