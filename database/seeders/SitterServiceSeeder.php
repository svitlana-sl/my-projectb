<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SitterService;

class SitterServiceSeeder extends Seeder
{
    public function run(): void
    {
        SitterService::factory(20)->create();
    }
}
