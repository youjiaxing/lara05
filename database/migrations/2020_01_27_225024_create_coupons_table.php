<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique()->comment('优惠码');
            $table->string('title')->comment('优惠券名');
            $table->unsignedInteger('total')->comment('总量');
            $table->unsignedInteger('used')->default(0)->comment('已使用数量');
            $table->string('type')->comment("类型: fixed, percent");
            $table->decimal('value')->comment('fixed时表示优惠金额, percent时表示优惠百分比');
            $table->decimal('cond_min_amount')->default(0)->comment('要求订单最低的金额');
            $table->decimal('max_discount_amount')->nullable()->comment('最大优惠金额, 用于百分比优惠时限制额度');
            $table->timestamp('not_before')->nullable()->comment('生效时间');
            $table->timestamp('not_after')->nullable()->comment('失效时间');
            $table->boolean('enabled')->default(true)->comment('是否启用');
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
        Schema::dropIfExists('coupons');
    }
}
