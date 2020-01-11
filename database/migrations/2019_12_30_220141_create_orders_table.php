<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('no')->unique()->comment("订单编号");
            $table->unsignedBigInteger('user_id')->index()->comment('用户id');

            // 订单基本状态
            $table->string('status')->default(\App\Models\Order::ORDER_STATUS_CREATED)->comment("订单状态: ");
            $table->decimal('amount',10,2)->comment("总价, 优惠前");
            $table->decimal('paid_amount',10,2)->comment("实际需支付价, 优惠后");
            $table->json('address')->comment("收货地址");
            $table->string('remark', 1000)->comment("备注");

            // 支付相关
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->string('payment_method')->nullable()->comment('支付方式');
            $table->string('payment_no')->nullable()->comment('支付平台订单号');

            // 物流
            $table->string('express_status')->default(\App\Models\Order::EXPRESS_STATUS_PENDING)->comment('物流状态');
            $table->json('express_data')->nullable()->comment('物流数据: 公司, 订单号, 最新消息');

            // 退款相关
            $table->string('refund_status')->default(\App\Models\Order::REFUND_STATUS_PENDING)->comment("退款状态");
            $table->string('refund_no')->nullable()->comment("退款编号, 此处假设多次退款仍使用同一条退款编号");
            $table->decimal('refund_amount',10,2)->default(0)->comment('退款金额, 注意不得超过 paid_amount');

            // 评价相关
            $table->timestamp('reviewed_at')->nullable()->comment('订单评价时间');

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
        Schema::dropIfExists('orders');
    }
}
