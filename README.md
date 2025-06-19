# 🐾 Pet Care Platform

Laravel-based platform for connecting pet owners with reliable pet sitters, featuring comprehensive user management, pet profiles, and service request handling.

## 🏗️ Architecture

### Backend
- **Laravel 11** with Filament Admin Panel
- **MySQL** database with optimized relationships
- **DigitalOcean Spaces** for file storage with CDN support
- **Sanctum** for API authentication
- **Swagger** for API documentation

## 🚀 Key Features

### User Management
- Multi-role system (Owner, Sitter, Both, Admin)
- Profile management with avatar uploads
- Location-based services
- Jetstream authentication

### Pet Management
- Comprehensive pet profiles with photos
- Breed, age, weight tracking
- Photo galleries with automatic thumbnails

### Service Requests
- Booking system for pet sitting services
- Status tracking (Pending, Accepted, Rejected, Completed)
- Date range validation

### **📁 Simplified File Upload System (Variant 2)**

#### ✨ Key Advantages:
- **🔄 Clean Architecture**: Uses existing API endpoints
- **🛠️ Minimal Code**: No duplicate functionality
- **🔒 Consistent Security**: Same validation as Filament admin
- **📁 DigitalOcean Spaces**: Compatible with existing file structure
- **⚡ Easy Maintenance**: Single endpoint per resource

#### 🏗️ Implementation Details:

**Backend Integration:**
```php
// Enhanced existing endpoints to accept file uploads
PUT /api/users/{id}    // + avatar_file field
PUT /api/pets/{id}     // + photo_file field

// Uses same validation and storage logic as Filament
// Automatic thumbnail generation
// Old file cleanup on update
```

**Key Technical Features:**
- **Method Spoofing**: POST + `_method=PUT` for multipart compatibility
- **Unified Validation**: Single `getFileValidationRules()` method
- **Smart Thumbnails**: Automatic resize with Intervention Image
- **CDN Support**: DigitalOcean Spaces with optional CDN
- **Error Handling**: Comprehensive error catching and logging

## 🛠️ Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ (for asset compilation)
- MySQL 8.0+

### Backend Setup
```bash
# Clone and install
git clone <repository>
cd my-project
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Storage setup
php artisan storage:link

# Compile assets
npm install
npm run build

# Start development server
php artisan serve
```

### DigitalOcean Spaces Configuration
```bash
# Add to .env
FILESYSTEM_DISK=do_spaces
DO_SPACES_KEY=your_access_key
DO_SPACES_SECRET=your_secret_key
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your_bucket_name
```

## 📖 API Documentation

### File Upload Endpoints

#### Upload User Avatar
```http
POST /api/users/{id}
Content-Type: multipart/form-data

{
  "_method": "PUT",
  "avatar_file": <file>,
  "name": "Updated Name"  // optional other fields
}
```

#### Upload Pet Photo
```http
POST /api/pets/{id}
Content-Type: multipart/form-data

{
  "_method": "PUT", 
  "photo_file": <file>,
  "name": "Updated Pet Name"  // optional other fields
}
```

### Validation Rules
- **File Types**: JPEG, PNG, GIF, WebP, AVIF
- **Max Size**: 10MB (configurable)
- **Thumbnails**: Automatic generation (200x200 for avatars, 400x400 for pets)

## 🧪 Testing

```bash
# Backend tests
php artisan test

# Test upload system
php artisan system:test-upload

# Fix file permissions (DigitalOcean Spaces)
php artisan storage:fix-permissions
```

## 🔧 Maintenance

### File Cleanup
```bash
# Clean orphaned files
php artisan storage:clean-orphan-files

# Clean temporary files
php artisan storage:clean-temp-files --days=1
```

### Performance
- **CDN Integration**: DigitalOcean Spaces CDN for global file delivery
- **Optimized Thumbnails**: WebP conversion for AVIF files
- **Database Indexing**: Optimized queries for file relationships

## 🎯 Best Practices

### Security
- File validation on both frontend and backend
- MIME type verification
- Size limitations
- XSS protection for file URLs

### Performance
- Lazy loading for images
- Progressive enhancement
- Error boundary components
- Optimistic UI updates

### Maintenance
- Regular file cleanup
- Monitoring storage usage
- Performance logging
- Error tracking

## 📋 Development Notes

### Code Quality
- **DRY Principles**: Shared logic and reusable components
- **SOLID Architecture**: Trait-based file handling
- **Error Handling**: Comprehensive exception catching
- **Configuration**: Environment-based settings

### Deployment
- Environment-specific configurations
- Asset optimization
- File storage migration tools
- Performance monitoring

---

Built with ❤️ for pet owners and sitters everywhere! 🐕🐱
