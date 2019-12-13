<?php

use App\Models\UserAddress;
use Illuminate\Database\Seeder;

class UserAddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = \App\Models\User::query()->pluck('id')->toArray();

        if (empty($userIds)) {
            return;
        }

        /* @var \Faker\Generator $faker */
        $faker = app(Faker\Generator::class);

        $userAddresses = factory(UserAddress::class)
            ->times(count($userIds) * 3)
            ->make()
            ->each(function (UserAddress $userAddress) use ($faker, $userIds) {
                $userAddress->user_id = $faker->randomElement($userIds);
                $userAddress->save();
        });

        UserAddress::query()->first()->update(['user_id' => 1]);
    }
}
