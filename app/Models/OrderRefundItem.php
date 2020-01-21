<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderRefundItems
 * @package App\Models
 *
 * @property ProductSku $productSku
 * @property int        $quantity  退款数量
 * @property float      $amount    退款金额
 * @property OrderItem  $orderItem 订单子项
 */
class OrderRefundItem extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
