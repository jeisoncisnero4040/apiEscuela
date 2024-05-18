<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.4
     */
    public function run()
    {
        DB::table('rols')->insert([
            ['description' => 'Admin'],
            ['description' => 'Teacher'],
            ['description' => 'Estudent'],

        ]);
    }
}
