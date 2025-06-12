<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Pet;

class TestUploadSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:test-upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the file upload system components';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing File Upload System');
        $this->info('==============================');
        $this->newLine();

        $errors = 0;

        // Test 1: Check if models load
        $this->info('1️⃣ Testing Model Loading...');
        try {
            $user = new User();
            $pet = new Pet();
            $this->line('✅ Models loaded successfully');
        } catch (\Exception $e) {
            $this->error('❌ Error loading models: ' . $e->getMessage());
            $errors++;
        }
        $this->newLine();

        // Test 2: Check default images
        $this->info('2️⃣ Testing Default Images...');
        try {
            $userDefaultImage = $user->getDefaultImage();
            $petDefaultImage = $pet->getDefaultImage();
            $this->line('✅ User default image: ' . $userDefaultImage);
            $this->line('✅ Pet default image: ' . $petDefaultImage);
        } catch (\Exception $e) {
            $this->error('❌ Error getting default images: ' . $e->getMessage());
            $errors++;
        }
        $this->newLine();

        // Test 3: Check storage directories
        $this->info('3️⃣ Testing Storage Directories...');
        $directories = ['avatars', 'pets', 'temp'];
        
        foreach ($directories as $dir) {
            if (Storage::disk('public')->exists($dir)) {
                $this->line('✅ Directory exists: ' . $dir);
            } else {
                $this->error('❌ Directory missing: ' . $dir);
                $errors++;
            }
        }
        $this->newLine();

        // Test 4: Check Intervention Image
        $this->info('4️⃣ Testing Intervention Image...');
        try {
            if (class_exists('\Intervention\Image\Laravel\Facades\Image')) {
                $this->line('✅ Intervention Image loaded');
            } else {
                $this->error('❌ Intervention Image not found');
                $errors++;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error checking Intervention Image: ' . $e->getMessage());
            $errors++;
        }
        $this->newLine();

        // Test 5: Check storage link
        $this->info('5️⃣ Testing Storage Link...');
        $linkPath = public_path('storage');
        if (is_link($linkPath)) {
            $this->line('✅ Storage link exists');
            $this->line('   Target: ' . readlink($linkPath));
        } else {
            $this->error('❌ Storage link missing');
            $errors++;
        }
        $this->newLine();

        // Test 6: Check configuration
        $this->info('6️⃣ Testing Configuration...');
        $configExists = file_exists(config_path('image.php'));
        if ($configExists) {
            $this->line('✅ Image configuration file exists');
            $config = config('image');
            $this->line('   Max file size: ' . ($config['validation']['max_file_size'] / 1024 / 1024) . 'MB');
            $this->line('   Avatar thumbnail: ' . $config['thumbnails']['avatar']['width'] . 'x' . $config['thumbnails']['avatar']['height']);
        } else {
            $this->error('❌ Image configuration file missing');
            $errors++;
        }
        $this->newLine();

        // Test 7: Test database connectivity
        $this->info('7️⃣ Testing Database...');
        try {
            $userCount = User::count();
            $petCount = Pet::count();
            $this->line('✅ Database connection working');
            $this->line('   Users: ' . $userCount);
            $this->line('   Pets: ' . $petCount);
        } catch (\Exception $e) {
            $this->error('❌ Database error: ' . $e->getMessage());
            $errors++;
        }
        $this->newLine();

        // Summary
        if ($errors === 0) {
            $this->info('🎉 All tests passed! System is ready.');
        } else {
            $this->error('⚠️ ' . $errors . ' test(s) failed. Please check the errors above.');
        }
        
        $this->newLine();
        $this->info('📋 Next steps:');
        $this->line('1. Access your Filament admin panel');
        $this->line('2. Try creating/editing a user with avatar upload');
        $this->line('3. Try creating/editing a pet with photo upload');
        $this->line('4. Run: php artisan files:clean-orphans --dry-run');
        
        return $errors === 0 ? 0 : 1;
    }
}
