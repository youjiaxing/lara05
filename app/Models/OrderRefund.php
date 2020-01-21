<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class OrderRefund
 * @package App\Models
 *
 * @property Collection<OrderRefundItem>|OrderRefundItem[] $orderRefundItems
 * @property Order                                         $order
 * @property-read string                                   $status_map    退款状态 - 说明
 * @property string                                        $status        退款状态
 * @property float                                         $amount        总退款金额
 * @property string                                        $reject_reason 拒绝理由
 *
 * @method Builder active() 正在需处理/处理中的退款申请单
 */
class OrderRefund extends Model
{
    const STATUS_CREATED = 'created';
    const STATUS_RETURN = 'return';     // 等待退货
    const STATUS_SUCCESS = 'success';
    const STATUS_REJECT = 'reject';     // 失败

    //TODO
    //微信退款会导致 "退款中" 的状态, 因为它的退款是通过异步回调来处理的.
    public static $statusMap = [
        self::STATUS_CREATED => "等待处理",
        self::STATUS_RETURN => "退货中",
        self::STATUS_SUCCESS => "完成",
        self::STATUS_REJECT => "拒绝",
    ];

    const TYPE_REFUND = 'refund';   // 仅退款
    const TYPE_RETURN = 'return';   // 退款退货

    protected $guarded = [];

    protected $casts = [
        'extra' => 'array',
    ];

    protected $dates = [
        'refunded_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderRefundItems()
    {
        return $this->hasMany(OrderRefundItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return string
     */
    public function getStatusMapAttribute()
    {
        return static::$statusMap[$this->status];
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereIn('status', [self::STATUS_CREATED, self::STATUS_RETURN]);
    }
}
