<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(['key' => 'shipping_free_threshold'], ['value' => '500']);
        Setting::firstOrCreate(['key' => 'shipping_fee'], ['value' => '50']);
    }
}
