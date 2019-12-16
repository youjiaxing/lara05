<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ProductSku;
use Faker\Generator as Faker;

$factory->define(ProductSku::class, function (Faker $faker) {
    return [
        'price' => $faker->randomFloat(2, 0.01, 1000),
        'stock' => $faker->numberBetween(0, 20),
        'title' => $faker->word,
        'description' => $faker->sentence,
    ];
});
