<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class NeoFeederSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hubungkan ke http://103.59.95.161:3003/ws/live2.php
        Setting::setValue(
            'neo_feeder_url', 
            'http://103.59.95.161:3003/ws/live2.php', 
            false, 
            'Neo Feeder API URL'
        );

        Setting::setValue(
            'neo_feeder_username', 
            'BudiPrasetyo', 
            false, 
            'Neo Feeder Username'
        );

        Setting::setValue(
            'neo_feeder_password', 
            'jayahangtuah2023!', 
            true, 
            'Neo Feeder Password (encrypted)'
        );
    }
}
