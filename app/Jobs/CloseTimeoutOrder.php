<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloseTimeoutOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->status != Order::ORDER_STATUS_CREATED) {
            Log::channel('queue')->info("订单已付款, 无需自动关闭.", $this->order->attributesToArray());
            return;
        }

        $this->order->load('orderItems.productSku');
        DB::transaction(function () {
            // 关闭订单
            $this->order->closeOrder("pay timeout")->save();

            // 恢复库存
            $orderItems = $this->order->orderItems;
            foreach ($orderItems as $orderItem) {
                /* @var OrderItem $orderItem */
                $orderItem->productSku->addStock($orderItem->quantity);
            }
        });

        Log::channel('queue')->info("订单超时自动关闭", $this->order->attributesToArray());
    }
}
