<?php

use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* @var \Illuminate\Support\Collection $products */
        $products = factory(\App\Models\Product::class, 300)
            ->create();

        $products->each(function (\App\Models\Product $product) {
            /* @var \Illuminate\Support\Collection $skus */
            $skus = factory(\App\Models\ProductSku::class, mt_rand(1, 5))->create(['product_id' => $product->id]);
            $product->price_max = $skus->max(function ($sku) {
                return $sku->price;
            });
            $product->price_min = $skus->min(function ($sku) {
                return $sku->price;
            });
            $product->save();
        });
    }
}
