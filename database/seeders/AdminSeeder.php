<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as Faker;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        User::firstOrCreate([
            'email' => 'admin@buckhill.co.uk'
        ], [
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email_verified_at' => now(),
            'password' => Hash::make('secret123'),
            'phone_number' => $faker->phoneNumber(),
            'address' => $faker->address(),
            'avatar' => $faker->uuid(),
            'is_admin' => true,
            'is_marketing' => $faker->boolean(),
            'remember_token' => Str::random(10),
        ]);
    }
}
