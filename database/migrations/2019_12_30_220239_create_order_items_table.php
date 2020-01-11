<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->unsignedBigInteger('order_id')->comment('订单id');
            $table->unsignedBigInteger('product_sku_id')->comment('产品sku id');
            $table->unsignedBigInteger('product_id')->comment('产品id, 冗余字段');

            // 基本数据
            $table->unsignedInteger('quantity')->comment('数量');
//            $table->decimal('unit_price',10,2)->comment('原始单价, 优惠前');
            $table->decimal('amount',10,2)->comment('总价, 优惠前');
            $table->decimal('paid_amount',10,2)->comment('实际应支付价, 优惠后');

            // 退款相关
            $table->string('refund_status')->default(\App\Models\OrderItem::REFUND_STATUS_PENDING)->comment('退款状态');
            $table->unsignedInteger('refund_quantity')->default(0)->comment('退款数量');
            $table->decimal('refund_amount',10,2)->default(0)->comment('退款金额');

            // 评价相关
            $table->unsignedInteger('review_rating')->nullable()->comment('评价分数, 1~5');
            $table->string('review_content', 1000)->default('')->comment("评价内容");

            $table->json('extra')->nullable();
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
        Schema::dropIfExists('order_items');
    }
}
