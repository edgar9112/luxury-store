<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'BIENVENIDA10'],
            [
                'type' => CouponType::Percentage,
                'value' => 10,
                'min_order_cents' => 0,
                'starts_at' => null,
                'expires_at' => null,
                'max_uses' => null,
                'is_active' => true,
            ]
        );
    }
}
