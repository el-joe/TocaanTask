<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'eljoe1717@gmail.com',
        ],[
            'name' => 'Youssef',
            'email' => 'eljoe1717@gmail.com',
            'phone' => '+201558099183',
            'password' => '123456'
        ]);
        User::factory()->count(10)->create();
    }
}
