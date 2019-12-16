<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('price_min', 10, 2)->default(0);
            $table->decimal('price_max', 10, 2)->default(0);
            $table->string('title')->comment("商品标题");
            $table->text('description')->comment("商品描述");
            $table->string('image')->nullable()->comment("主图");
            $table->float('rating')->default(0)->comment("评分");
            $table->boolean('is_sale')->default(true)->comment("是否销售中");
            $table->unsignedInteger('review_count')->default(0)->comment("评价人数");
            $table->unsignedInteger('sold_count')->default(0)->comment("销量");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
