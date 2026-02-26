<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = [
            'key' => 'late_letters',
            'value' => 3,
        ];

        Setting::updateOrCreate(
            ['key' => $setting['key']],
            ['value' => $setting['value']]
        );
    }
}
