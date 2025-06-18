<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;

trait HasFileUpload
{
    /**
     * Get the storage disk for uploads
     */
    protected function getUploadDisk(): string
    {
        return config('image.storage.disk', config('filesystems.default', 'public'));
    }

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
        
        $disk = $this->getUploadDisk();
        
        // Store original file with public visibility
        $filePath = $file->storeAs($fullDirectory, $filename, [
            'disk' => $disk,
            'visibility' => 'public'
        ]);
        
        $result = [$fileField => $filePath];
        
        // Create thumbnail if requested
        if ($thumbField && $this->isImage($file)) {
            $thumbPath = $this->createThumbnail(
                $file,
                $fullDirectory,
                $filename,
                $thumbWidth,
                $thumbHeight,
                $disk
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
        $disk = $this->getUploadDisk();
        
        foreach ($filePaths as $path) {
            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
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
        
        $disk = $this->getUploadDisk();
        
        // Use CDN for DigitalOcean Spaces if enabled
        if ($disk === 'do_spaces' && config('image.digital_ocean.cdn_enabled', false)) {
            $cdnEndpoint = config('image.digital_ocean.cdn_endpoint');
            if ($cdnEndpoint) {
                return rtrim($cdnEndpoint, '/') . '/' . ltrim($path, '/');
            }
        }
        
        return Storage::disk($disk)->url($path);
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
    protected function createThumbnail(UploadedFile $originalFile, string $directory, string $filename, int $width, int $height, string $disk): string
    {
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $directory . '/' . $thumbFilename;
        
        try {
            $image = Image::read($originalFile->path())->cover($width, $height);
            
            // Convert AVIF to WebP for better compatibility
            if (str_ends_with(strtolower($filename), '.avif')) {
                $thumbFilename = str_replace('.avif', '.webp', $thumbFilename);
                $thumbPath = $directory . '/' . $thumbFilename;
                $image->toWebp(85);
            }
            
            $tempPath = sys_get_temp_dir() . '/' . $thumbFilename;
            $image->save($tempPath);
            
            Storage::disk($disk)->put($thumbPath, file_get_contents($tempPath), 'public');
            unlink($tempPath);
            
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
            // Fallback: copy original file
            Storage::disk($disk)->put($thumbPath, file_get_contents($originalFile->path()), 'public');
        }
        
        return $thumbPath;
    }
    
    /**
     * Handle Filament file upload (simplified method)
     */
    public function handleFilamentUpload(string $tempFilePath, string $fileField, string $thumbField = null): bool
    {
        try {
            $disk = $this->getUploadDisk();
            
            if (!Storage::disk($disk)->exists($tempFilePath)) {
                return false;
            }
            
            $uploadedFile = $this->createUploadedFileFromTemp($tempFilePath, $disk);
            if (!$uploadedFile) {
                return false;
            }
            
            [$directory, $thumbWidth, $thumbHeight] = $this->getUploadConfig($fileField);
            
            $filePaths = $this->uploadFile($uploadedFile, $directory, $fileField, $thumbField, $thumbWidth, $thumbHeight);
            $this->update($filePaths);
            
            Storage::disk($disk)->delete($tempFilePath);
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("File upload failed: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Create UploadedFile from temp storage
     */
    private function createUploadedFileFromTemp(string $tempFilePath, string $disk): ?UploadedFile
    {
        try {
            $originalName = basename($tempFilePath);
            $mimeType = Storage::disk($disk)->mimeType($tempFilePath);
            
            if ($disk === 'do_spaces') {
                $fileContent = Storage::disk($disk)->get($tempFilePath);
                $tempLocalPath = sys_get_temp_dir() . '/' . $originalName;
                file_put_contents($tempLocalPath, $fileContent);
                
                return new UploadedFile($tempLocalPath, $originalName, $mimeType, null, true);
            }
            
            $fullPath = Storage::disk($disk)->path($tempFilePath);
            return new UploadedFile($fullPath, $originalName, $mimeType, null, true);
            
        } catch (\Exception $e) {
            \Log::error("Failed to create UploadedFile: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Get upload configuration based on field type
     */
    private function getUploadConfig(string $fileField): array
    {
        switch ($fileField) {
            case 'avatar_path':
                return ['avatars', 200, 200];
            case 'photo_path':
                return ['pets', 400, 400];
            default:
                return ['uploads', 300, 300];
        }
    }
    
    /**
     * Get default image URL
     */
    public function getDefaultImage(): string
    {
        $modelType = class_basename($this);
        
        if ($modelType === 'User') {
            return asset('images/default-avatar.svg');
        } elseif ($modelType === 'Pet') {
            return asset('images/default-pet.svg');
        }
        
        return asset('images/default-avatar.svg');
    }
} 