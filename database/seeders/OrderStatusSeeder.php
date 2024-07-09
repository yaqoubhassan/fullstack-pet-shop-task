<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['open', 'pending_payment', 'paid', 'shipped', 'cancelled'];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(
                [
                    'title' => $status
                ],
                [
                    'uuid' => (string) Str::uuid()
                ]
            );
        }
    }
}
