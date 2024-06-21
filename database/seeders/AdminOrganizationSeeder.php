<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('organizations')->insert([
            'name' => 'E-Health',
            'code' => '1',
            'type' => 'new',
            'address' => 'Kuala Lumpur',
            'latitude' => '3.05603241859056',
            'longitude' => '101.69845483959776',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
