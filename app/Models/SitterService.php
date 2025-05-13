<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SitterService extends Model
{
    /** @use HasFactory<\Database\Factories\SitterServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'hourly_rate',
        'service_type_id',
        'sitter_id'
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function sitter()
    {
        return $this->belongsTo(User::class, 'sitter_id');
    }
}
