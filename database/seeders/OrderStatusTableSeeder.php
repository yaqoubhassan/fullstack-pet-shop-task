<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\OrderStatus;

class OrderStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['open', 'pending_payment', 'paid', 'shipped', 'cancelled'];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate([
                'uuid' => (string) Str::uuid(),
                'title' => $status
            ]);
        }
    }
}
