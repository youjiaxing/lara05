<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('no');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount')->comment("退款金额");
            $table->string('status')->default(\App\Models\OrderRefund::STATUS_CREATED)->comment("退款状态");
            $table->string('type')->comment("类型: 仅退款、退货");
            $table->timestamp('refunded_at')->nullable()->comment("退款时间");
            $table->string('refund_no')->nullable()->comment("退款单号, 可直接使用 no 字段的值");
            $table->string('user_remark')->default("")->comment("用户填写的备注");
            $table->string('reject_reason')->nullable()->comment("失败的理由");
            $table->json('extra')->nullable()->comment("扩展字段");
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
        Schema::dropIfExists('order_refunds');
    }
}
