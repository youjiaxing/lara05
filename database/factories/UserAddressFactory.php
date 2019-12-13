<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserAddress;
use Faker\Generator as Faker;

$factory->define(UserAddress::class, function (Faker $faker) {
    return [
        'contact_name' => $faker->name,
        'contact_phone' => $faker->phoneNumber,
        'zip' => (int)$faker->postcode,
        'province' => $faker->state,
        'city' => $faker->city,
        'district' => $faker->area,
        'address' => $faker->buildingNumber . "号",
        'last_used_at' => $faker->dateTimeBetween('-1 years', 'now'),
    ];
});
