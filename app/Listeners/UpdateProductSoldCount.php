<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateProductSoldCount implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param OrderPaid $event
     *
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // 更新各 Sku 销量
        $order = $event->order;
        $order->load('orderItems.productSku');
        $productIds = [];
        foreach ($order->orderItems as $orderItem) {
            /* @var OrderItem $orderItem */
            $productSku = $orderItem->productSku;
            // $productSkuId = $orderItem->product_sku_id;
            $productId = $orderItem->product_id;
            $productIds[$productId] = $productId;

            $soldCount = OrderItem::query()->where('product_sku_id', $productSku->id)
                ->whereHas('order', function (Builder $query) {
                    $query->whereNotNull('paid_at');
                })->sum('quantity');

            $productSku->update(['sold_count' => $soldCount]);
        }

        // 更新各主商品 Product 销量
        foreach ($productIds as $productId) {
            $soldCount = ProductSku::query()->where('product_id', $productId)
                ->sum('sold_count');

            Product::query()
                ->where('id', $productId)
                ->update(['sold_count' => $soldCount]);
        }

        Log::debug("已更新订单对应商品销量", $order->toArray());
    }
}
