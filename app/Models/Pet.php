<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasFileUpload;

class Pet extends Model
{
    use HasFactory;
    use HasFileUpload;

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Clean up files when pet is deleted
        static::deleting(function ($pet) {
            $pet->deleteOldPhoto();
        });
    }

    protected $fillable = [
        'owner_id',
        'name',
        'breed',
        'age',
        'weight',
        'photo_url',
        'photo_path',
        'photo_thumb_path',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'age' => 'integer',
        ];
    }

    /**
     * Get the owner of the pet.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    
    /**
     * Get photo URL (prioritize uploaded file over URL)
     */
    public function getPhotoUrlAttribute(): string
    {
        // If we have a thumb, use it
        if (!empty($this->photo_thumb_path)) {
            return $this->getFileUrl($this->photo_thumb_path);
        }
        
        // If we have original file, use it  
        if (!empty($this->photo_path)) {
            return $this->getFileUrl($this->photo_path);
        }
        
        // If we have external URL, use it
        if (!empty($this->attributes['photo_url'])) {
            return $this->attributes['photo_url'];
        }
        
        // Return default image
        return $this->getDefaultImage();
    }
    
    /**
     * Get photo URL for display (separate method to avoid conflicts)
     */
    public function getDisplayPhotoUrl(): string
    {
        return $this->getPhotoUrlAttribute();
    }
    
    /**
     * Handle photo upload
     */
    public function uploadPhoto(\Illuminate\Http\UploadedFile $file): void
    {
        // Delete old files
        $this->deleteOldPhoto();
        
        // Upload new files
        $filePaths = $this->uploadFile(
            $file,
            'pets',
            'photo_path',
            'photo_thumb_path',
            400,
            400
        );
        
        $this->update($filePaths);
    }
    
    /**
     * Delete old photo files
     */
    public function deleteOldPhoto(): void
    {
        $this->deleteFiles([
            $this->photo_path,
            $this->photo_thumb_path
        ]);
    }
    
    /**
     * Get default image for pets
     */
    public function getDefaultImage(): string
    {
        return asset('images/default-pet.svg');
    }
}
