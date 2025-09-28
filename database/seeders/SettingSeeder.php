<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['label' => 'presentase'],
            ['data' => 2] // default 0%
        );

        // kalau mau langsung isi default untuk setting lain juga
        // Setting::updateOrCreate(['label' => 'pajak'], ['data' => 0]);
        // Setting::updateOrCreate(['label' => 'profit'], ['data' => 0]);
        // Setting::updateOrCreate(['label' => 'diskon'], ['data' => 0]);
    }
}
