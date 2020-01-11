<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderItem
 * @package App\Models *
 *
 * @property Order       $order
 * @property Product     $product
 * @property ProductSku  $productSku
 * @property int         $product_id
 * @property int         $user_id
 * @property int         $product_sku_id
 * @property int         $quantity
 * @property double      $amount      总价, 优惠前
 * @property-read double $unit_price  单价
 * @property double      $paid_amount 总价, 优惠后
 * @property string      $refund_status
 * @property int         $refund_quantity
 */
class OrderItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';    // 无退款
//    const REFUND_STATUS_APPLIED = 'applied';    // 已申请
    const REFUND_STATUS_REFUND_PART = 'refund_part';    // 部分退款
    const REFUND_STATUS_REFUND_ALL = 'refund_all';  // 完全退款

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => "无退款",
        self::REFUND_STATUS_REFUND_PART => '已部分退款',
        self::REFUND_STATUS_REFUND_ALL => '已全部退款',
    ];

    protected $fillable = [
        'user_id',
        'order_id',
        'product_sku_id',
        'product_id',
        'quantity',
        'amount',
        'paid_amount',
        'refund_status',
        'refund_quantity',

    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'extra' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    /**
     * @return float
     */
    public function getUnitPriceAttribute()
    {
        return $this->amount / $this->quantity;
    }
}
