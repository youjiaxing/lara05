<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    $time = $faker->dateTimeThisYear;
    $image = $faker->randomElement([
        "https://cdn.learnku.com/uploads/images/201806/01/5320/7kG1HekGK6.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/1B3n0ATKrn.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/r3BNRe4zXG.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/C0bVuKB2nt.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/82Wf2sg8gM.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/nIvBAQO5Pj.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/XrtIwzrxj7.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/uYEHCJ1oRp.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/2JMRaFwRpo.jpg",
        "https://cdn.learnku.com/uploads/images/201806/01/5320/pa7DrV43Mw.jpg",
    ]);

    return [
        'price_max' => $faker->randomFloat(2, 100, 200),
        'price_min' => $faker->randomFloat(2, 1, 100 - 0.01),
        'title' => $faker->words(5, true),
        'description' => $faker->sentence,
        'is_sale' => true,
        'review_count' => $faker->numberBetween(0, 20),
        'sold_count' => $faker->numberBetween(0, 50),
        'rating' => $faker->randomFloat(2, 0, 5),
        'image' => $image,
        'created_at' => $time,
        'updated_at' => $time,
    ];
});
