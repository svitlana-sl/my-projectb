<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

trait HasFileUpload
{
    /**
     * Upload file and create thumbnail
     */
    public function uploadFile(
        UploadedFile $file, 
        string $directory, 
        string $fileField, 
        string $thumbField = null, 
        int $thumbWidth = 300, 
        int $thumbHeight = 300
    ): array {
        // Validate file
        $this->validateFile($file);
        
        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);
        
        // Create directory structure
        $fullDirectory = $directory . '/' . $this->getModelIdentifier();
        
        // Store original file
        $filePath = $file->storeAs($fullDirectory, $filename, 'public');
        
        $result = [$fileField => $filePath];
        
        // Create thumbnail if requested
        if ($thumbField && $this->isImage($file)) {
            $thumbPath = $this->createThumbnail(
                storage_path('app/public/' . $filePath),
                $fullDirectory,
                $filename,
                $thumbWidth,
                $thumbHeight
            );
            $result[$thumbField] = $thumbPath;
        }
        
        return $result;
    }
    
    /**
     * Delete files from storage
     */
    public function deleteFiles(array $filePaths): void
    {
        foreach ($filePaths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
    
    /**
     * Get file URL
     */
    public function getFileUrl(string $path = null): string
    {
        if (!$path) {
            return $this->getDefaultImage();
        }
        
        return Storage::disk('public')->url($path);
    }
    
    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        $config = config('image.validation');
        $maxSize = $config['max_file_size'];
        $allowedTypes = $config['allowed_mime_types'];
        
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1024 / 1024;
            throw new \InvalidArgumentException("File size must be less than {$maxSizeMB}MB");
        }
        
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $allowedFormats = implode(', ', ['JPEG', 'PNG', 'GIF', 'WebP', 'AVIF']);
            throw new \InvalidArgumentException("File must be an image ({$allowedFormats})");
        }
    }
    
    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }
    
    /**
     * Get model identifier for directory structure
     */
    protected function getModelIdentifier(): string
    {
        return class_basename($this) . '-' . ($this->id ?? 'new');
    }
    
    /**
     * Check if file is an image
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }
    
    /**
     * Create thumbnail
     */
    protected function createThumbnail(
        string $originalPath, 
        string $directory, 
        string $filename, 
        int $width, 
        int $height
    ): string {
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $directory . '/' . $thumbFilename;
        $fullThumbPath = storage_path('app/public/' . $thumbPath);
        
        // Create directory if not exists
        $thumbDir = dirname($fullThumbPath);
        if (!file_exists($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        
        // Create and save thumbnail
        try {
            $image = Image::read($originalPath);
            $image->cover($width, $height);
            
            // For AVIF files, save as WebP for better browser compatibility
            if (str_ends_with(strtolower($filename), '.avif')) {
                $thumbFilename = str_replace('.avif', '.webp', $thumbFilename);
                $thumbPath = $directory . '/' . $thumbFilename;
                $fullThumbPath = storage_path('app/public/' . $thumbPath);
                $image->toWebp(85);
            }
            
            $image->save($fullThumbPath);
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
            // Create a copy of original as fallback
            copy($originalPath, $fullThumbPath);
        }
        
        return $thumbPath;
    }
    
    /**
     * Get default image URL
     */
    public function getDefaultImage(): string
    {
        return asset('images/default-avatar.svg');
    }
} 