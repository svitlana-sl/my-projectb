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
        
        // Store original file
        $filePath = $file->storeAs($fullDirectory, $filename, $disk);
        
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
        
        // For DigitalOcean Spaces, use CDN if available
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
     * Create thumbnail (optimized for cloud storage)
     */
    protected function createThumbnail(
        UploadedFile $originalFile,
        string $directory, 
        string $filename, 
        int $width, 
        int $height,
        string $disk
    ): string {
        $thumbFilename = 'thumb_' . $filename;
        $thumbPath = $directory . '/' . $thumbFilename;
        
        try {
            // Read the original file
            $image = Image::read($originalFile->path());
            $image->cover($width, $height);
            
            // For AVIF files, save as WebP for better browser compatibility
            if (str_ends_with(strtolower($filename), '.avif')) {
                $thumbFilename = str_replace('.avif', '.webp', $thumbFilename);
                $thumbPath = $directory . '/' . $thumbFilename;
                $image->toWebp(85);
            }
            
            // Save to temporary location first
            $tempPath = sys_get_temp_dir() . '/' . $thumbFilename;
            $image->save($tempPath);
            
            // Upload to storage
            $content = file_get_contents($tempPath);
            Storage::disk($disk)->put($thumbPath, $content);
            
            // Clean up temp file
            unlink($tempPath);
            
        } catch (\Exception $e) {
            \Log::error('Thumbnail creation failed: ' . $e->getMessage());
            
            // Fallback: use original file as thumbnail by copying it
            $originalContent = file_get_contents($originalFile->path());
            Storage::disk($disk)->put($thumbPath, $originalContent);
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
            
            // Check if temp file exists
            if (!Storage::disk($disk)->exists($tempFilePath)) {
                \Log::warning("Temp file not found: {$tempFilePath}");
                return false;
            }
            
            // Create UploadedFile from temp file
            $uploadedFile = $this->createUploadedFileFromTemp($tempFilePath, $disk);
            if (!$uploadedFile) {
                return false;
            }
            
            // Determine upload directory and thumbnail size based on field
            [$directory, $thumbWidth, $thumbHeight] = $this->getUploadConfig($fileField);
            
            // Upload file
            $filePaths = $this->uploadFile(
                $uploadedFile,
                $directory,
                $fileField,
                $thumbField,
                $thumbWidth,
                $thumbHeight
            );
            
            // Update model
            $this->update($filePaths);
            
            // Clean up temp file
            Storage::disk($disk)->delete($tempFilePath);
            
            // Clean up local temp file if created
            if (isset($this->tempLocalPath) && file_exists($this->tempLocalPath)) {
                unlink($this->tempLocalPath);
                unset($this->tempLocalPath);
            }
            
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
            
            if ($disk === 'do_spaces') {
                // For cloud storage, download to temp file
                $fileContent = Storage::disk($disk)->get($tempFilePath);
                $this->tempLocalPath = sys_get_temp_dir() . '/' . $originalName;
                file_put_contents($this->tempLocalPath, $fileContent);
                
                return new UploadedFile(
                    $this->tempLocalPath,
                    $originalName,
                    Storage::disk($disk)->mimeType($tempFilePath),
                    null,
                    true
                );
            } else {
                // For local storage
                $fullPath = Storage::disk($disk)->path($tempFilePath);
                return new UploadedFile(
                    $fullPath,
                    $originalName,
                    Storage::disk($disk)->mimeType($tempFilePath),
                    null,
                    true
                );
            }
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
        return asset('images/default-avatar.svg');
    }
} 