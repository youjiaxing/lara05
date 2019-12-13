<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\User::class, 100)->create()
            ->each(function ($user) {
                if ($user->id == 1) {
                    $user->fill([
                        'name' => 'yjx',
                        'email' => '287009007@qq.com',
                        'password' => bcrypt('123456789')
                    ]);
                    $user->save();
                }
            });
    }
}
