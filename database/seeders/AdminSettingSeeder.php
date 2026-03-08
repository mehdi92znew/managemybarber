<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\AdminSetting::set('default_trial_days', 14, 'integer');
        \App\Models\AdminSetting::set('registration_enabled', true, 'boolean');
        \App\Models\AdminSetting::set('platform_fee_percent', 5, 'integer');
        \App\Models\AdminSetting::set('support_email', 'admin@managemybarber.fr', 'string');
    }
}
