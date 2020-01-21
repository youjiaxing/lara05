<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use App\Models\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductRating implements ShouldQueue
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
     * @param OrderReviewed $event
     *
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        $order = $event->order;

        if ($order->review_status == Order::REVIEW_STATUS_PENDING) {
            return;
        }

        $sql = <<<EOF
UPDATE products
SET
    rating = (
        SELECT AVG(review_rating)
        FROM order_items
        WHERE
            product_id = products.id
            AND
            reviewed_at IS NOT NULL
    ),
    updated_at = :now
WHERE
    id IN (
        SELECT product_id FROM order_items WHERE order_id = :order_id AND reviewed_at IS NOT NULL
    )
EOF;
        if (\DB::update($sql, ['order_id' => $order->id, 'now' => now()]) == 0) {
            // throw new \Exception("订单 {$order->id} 更新相关商品评分失败: 无匹配项");
        }
    }
}
