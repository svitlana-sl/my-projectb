<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;  // імпорт

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Примусове встановлення кореневого URL з .env → APP_URL
        URL::forceRootUrl(config('app.url'));

        // Всі згенеровані посилання (asset(), route(), url() тощо) — через HTTPS
        URL::forceScheme('https');
    }
}
