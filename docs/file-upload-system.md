# 📁 File Upload System Documentation

## 🎯 Overview

Система завантаження файлів для користувачів та домашніх тварин з автоматичним створенням мініатюр, валідацією та очищенням.

## 🏗️ Architecture

### Database Structure
```sql
-- Users table
ALTER TABLE users ADD COLUMN avatar_path VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN avatar_thumb_path VARCHAR(255) NULL;

-- Pets table  
ALTER TABLE pets ADD COLUMN photo_path VARCHAR(255) NULL;
ALTER TABLE pets ADD COLUMN photo_thumb_path VARCHAR(255) NULL;
```

### File Organization
```
storage/app/public/
├── avatars/
│   └── User-{id}/
│       ├── {uuid}.jpg        # Original file
│       └── thumb_{uuid}.jpg  # Thumbnail (200x200)
└── pets/
    └── Pet-{id}/
        ├── {uuid}.jpg        # Original file
        └── thumb_{uuid}.jpg  # Thumbnail (400x400)
```

## 🔧 Components

### 1. HasFileUpload Trait
Основний трейт для обробки файлів:
- **Валідація**: розмір (5MB), типи (JPEG, PNG, GIF, WebP, AVIF)
- **Збереження**: унікальні імена, організовані папки
- **Мініатюри**: автоматичне створення через Intervention Image
- **Очищення**: видалення старих файлів

### 2. Model Methods

#### User Model
```php
$user->uploadAvatar($uploadedFile);    // Upload avatar
$user->deleteOldAvatar();              // Clean old files
$user->avatar_url;                     // Get current avatar URL
```

#### Pet Model
```php
$pet->uploadPhoto($uploadedFile);      // Upload photo
$pet->deleteOldPhoto();                // Clean old files
$pet->photo_url;                       // Get current photo URL
```

### 3. Filament Integration

#### Form Components
```php
Forms\Components\FileUpload::make('avatar_file')
    ->label('Avatar')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('1:1')
    ->maxSize(5120)
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'])
```

#### Custom Pages
- **CreateUser/CreatePet**: Handle file upload during creation
- **EditUser/EditPet**: Handle file upload during updates

## 🚀 Usage Examples

### Upload Avatar in Filament
1. User selects file in form
2. Filament saves to `temp/` directory
3. Custom page moves file to `avatars/User-{id}/`
4. Intervention Image creates thumbnail
5. Database paths updated
6. Temp file cleaned

### Programmatic Upload
```php
$user = User::find(1);
$file = $request->file('avatar');
$user->uploadAvatar($file);
```

## 🧹 Maintenance

### Clean Orphan Files
```bash
# Preview what will be deleted
php artisan files:clean-orphans --dry-run

# Actually clean files
php artisan files:clean-orphans
```

### Automatic Cleanup
Files are automatically cleaned daily at 2 AM via Laravel Scheduler:
- Orphaned files (no database reference)
- Temp files older than 24 hours
- Empty directories

## 🔒 Security Features

1. **File Validation**: Size and type restrictions
2. **Unique Names**: UUID-based filenames prevent conflicts
3. **Organized Storage**: Files outside public directory
4. **Access Control**: Served through Laravel routes
5. **Auto Cleanup**: Prevents storage bloat

## 📊 Performance Benefits

1. **Thumbnails**: Fast loading in lists (15KB vs 500KB)
2. **Originals**: High quality for detail views
3. **Caching**: Laravel storage caching
4. **Lazy Loading**: Load only when needed

## 🛠️ Configuration

### Storage Disk
Files use `public` disk by default, configured in `config/filesystems.php`:
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### Image Processing
Powered by Intervention Image v3:
- **Drivers**: GD or ImageMagick
- **Formats**: JPEG, PNG, GIF, WebP, AVIF
- **Operations**: Resize, crop, optimize

## 🚨 Troubleshooting

### Storage Link Missing
```bash
php artisan storage:link
```

### Permission Issues
```bash
chmod -R 755 storage/app/public
```

### Large File Uploads
Update `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## 🆕 AVIF Support

### What is AVIF?
AVIF (AV1 Image File Format) - сучасний формат зображень з:
- **90% менший розмір** порівняно з JPEG при тій же якості
- **Підтримка HDR** та широкої кольорової гами
- **Прогресивне завантаження**
- **Підтримка анімації**

### Our Implementation
- ✅ **Upload**: Повна підтримка завантаження AVIF файлів
- ✅ **Validation**: Автоматична перевірка MIME типу
- ✅ **Thumbnails**: Конвертація мініатюр у WebP для сумісності
- ✅ **Fallback**: Автоматичний fallback для старих браузерів

### Browser Support
- ✅ Chrome 85+ (2020)
- ✅ Firefox 93+ (2021) 
- ✅ Safari 16+ (2022)
- ❌ IE (не підтримується)

## 📈 Future Enhancements

- [ ] Multiple file uploads
- [ ] Image compression optimization  
- [ ] Cloud storage (S3) integration
- [ ] Progressive AVIF loading
- [ ] Image watermarks
- [ ] EXIF data handling 