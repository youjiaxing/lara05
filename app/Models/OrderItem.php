<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderItem
 * @package App\Models *
 *
 * @property Order       $order
 * @property Product     $product
 * @property ProductSku  $productSku
 * @property User        $user
 * @property int         $product_id
 * @property int         $user_id
 * @property int         $product_sku_id
 * @property int         $quantity
 * @property double      $amount          总价, 优惠前
 * @property-read double $unit_price      单价
 * @property double      $paid_amount     总价, 优惠后
 * @property-read double $discount_amount 优惠的减免金额
 * @property string      $refund_status
 * @property int         $refund_quantity
 * @property float       $refund_amount   已退款金额
 * @property int         $review_rating   评分
 * @property string      $review_content  评论内容
 * @property Carbon      $reviewed_at     评价时间
 */
class OrderItem extends Model
{
    const REFUND_STATUS_PENDING = 'pending';    // 无退款
    const REFUND_STATUS_APPLIED = 'applied';    // 已申请
    const REFUND_STATUS_REFUND_PART = 'refund_part';    // 部分退款
    const REFUND_STATUS_REFUND_ALL = 'refund_all';  // 完全退款

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => "无退款",
        self::REFUND_STATUS_APPLIED => "已申请",
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
        'refund_amount',
        'review_rating',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'extra' => 'array'
    ];

    protected $dates = [
        'reviewed_at',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return float
     */
    public function getUnitPriceAttribute()
    {
        return $this->amount / $this->quantity;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->status == self::REFUND_STATUS_REFUND_ALL;
    }

    /**
     * 当前有效的数量
     * @return int
     */
    public function validQuantity()
    {
        return $this->quantity - $this->refund_quantity;
    }

    /**
     * 检测当前的退款状态
     *
     * @return string
     */
    public function detectRefundStatus()
    {
        if ($this->paid_amount == $this->refund_amount) {
            return self::REFUND_STATUS_REFUND_ALL;
        } elseif ($this->refund_amount == 0) {
            return self::REFUND_STATUS_APPLIED;
        } else {
            return self::REFUND_STATUS_REFUND_PART;
        }
    }

    /**
     * 商品价格是否改变(原因: 优惠等)
     * @return bool
     */
    public function isAmountChanged()
    {
        return $this->amount != $this->paid_amount;
    }

    /**
     * 减免的优惠金额
     * @return float
     */
    public function getDiscountAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }
}
