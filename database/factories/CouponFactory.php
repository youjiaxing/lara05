<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Coupon;
use Faker\Generator as Faker;

$factory->define(Coupon::class, function (Faker $faker) {
    $type = $faker->randomElement(array_keys(Coupon::$TYPE_MAP));
    return [
        'code' => strtoupper(\Illuminate\Support\Str::random(16)),
        'title' => $faker->sentence(3),
        'total' => $faker->numberBetween(5,1000),
        'used' => 0,
        'type' => $type,
        'value' => $type == Coupon::TYPE_FIXED ? $faker->randomFloat(2, 0.01, 1000) : $faker->numberBetween(1,99),
        'cond_min_amount' => $faker->numberBetween(0, 100),
        'max_discount_amount' => $type == Coupon::TYPE_FIXED ? 0 : $faker->numberBetween(100, 1000),
        'not_before' => lcg_value() < 0.5 ? \Carbon\Carbon::now() : $faker->dateTimeThisMonth,
        'enabled' => true,
    ];
});
