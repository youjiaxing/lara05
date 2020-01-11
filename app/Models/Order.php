<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Order
 * @package App\Models
 * @property Collection<OrderItem> $orderItems        子订单
 * @property Carbon                $created_at        订单创建日期
 * @property string                $status            订单前向状态
 * @property-read string           $status_map        订单前向状态 - 翻译
 * @property string                $refund_status     退货状态
 * @property-read string           $refund_status_map 退货状态 - 解释
 * @property string                $express_status    物流状态
 * @property array                 $extra
 * @property int                   $no                订单流水号
 * @property double                $amount            总价, 优惠前
 * @property double                $paid_amount       实际需支付价, 优惠后
 * @property string                $payment_no        第三方支付订单流水号
 * @property string                $payment_method    支付方式
 * @property Carbon                $paid_at           支付时间
 * @property User                  $user
 * @property string                $full_address      完整地址
 * @property array                 $address           = [
 *     'contact_name' => '',
 *     'contact_phone'=>'',
 *     'zip'=>'',
 *     'province'=>'',
 *     'city'=>'',
 *     'district'=>'',
 *     'address'=>''
 * ]
 * @property string                $remark            订单备注
 * @property int                   $user_id           用户id
 */
class Order extends Model
{
    // 订单前向状态
    const ORDER_STATUS_CREATED = 'created'; // 已创建, 待支付
    const ORDER_STATUS_PAID = 'paid';   // 已支付
    const ORDER_STATUS_DELIVERED = 'delivered'; // 已发货
    const ORDER_STATUS_RECEIVED = 'received';   // 已收货
    const ORDER_STATUS_CLOSED = 'closed';   // 已关闭

    public static $orderStatusMap = [
        self::ORDER_STATUS_CREATED => "未付款",
        self::ORDER_STATUS_PAID => '已支付',
        self::ORDER_STATUS_DELIVERED => '已发货',
        self::ORDER_STATUS_RECEIVED => '已收货',
        self::ORDER_STATUS_CLOSED => '已关闭',
    ];

    // 退款状态
    const REFUND_STATUS_PENDING = 'pending';    // 无退款
    const REFUND_STATUS_APPLIED = 'applied';    // 已申请
    const REFUND_STATUS_REFUND_PART = 'refund_part';    // 已部分退款
    const REFUND_STATUS_REFUND_ALL = 'refund_all';  // 已全额退款

    public static $refundStatusMap = [
        self::REFUND_STATUS_PENDING => "无退款",
        self::REFUND_STATUS_APPLIED => '已申请',
        self::REFUND_STATUS_REFUND_PART => '已部分退款',
        self::REFUND_STATUS_REFUND_ALL => '已全额退款',
    ];

    // 物流状态
    const EXPRESS_STATUS_PENDING = 'pending';   // 无物流
    const EXPRESS_STATUS_DELIVERED = 'delivered';   // 运输途中
    const EXPRESS_STATUS_RECEIVED = 'received'; // 已送达

    public static $expressStatusMap = [
        self::EXPRESS_STATUS_PENDING => '无物流',
        self::EXPRESS_STATUS_DELIVERED => '运输途中',
        self::EXPRESS_STATUS_RECEIVED => '已送达',
    ];

    protected $fillable = [
        'no',
        'user_id',
        'status',
        'amount',
        'paid_amount',
        'address',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'express_status',
        'express_data',
        'refund_status',
        'refund_no',
        'refund_amount',
        'reviewed_at',
        'extra',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'address' => 'array',
        'extra' => 'array',
    ];

    protected $dates = [
        'paid_at',
        'reviewed_at',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param string $closeReason
     *
     * @return $this
     */
    public function closeOrder($closeReason = "")
    {
        $this->status = static::ORDER_STATUS_CLOSED;
        $extra = $this->extra;
        $extra['close_reason'] = $closeReason;
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 订单状态
     * @return string
     */
    public function getStatusMapAttribute()
    {
        return static::$orderStatusMap[$this->attributes['status']];
    }

    /**
     * 退款状态
     * @return mixed
     */
    public function getRefundStatusMapAttribute()
    {
        return static::$refundStatusMap[$this->attributes['refund_status']];
    }

    /**
     * 是否已付款
     * @return bool
     */
    public function isPaid()
    {
        return !is_null($this->paid_at);
    }

    public function getFullAddressAttribute()
    {
        if (empty($this->address)) {
            return "";
        }

        return $this->address['province'] . $this->address['city'] . $this->address['district'] . $this->address['address'];
    }
}
