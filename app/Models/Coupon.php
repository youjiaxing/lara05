<?php

namespace App\Models;

use App\Exceptions\InvalidCouponException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Coupons
 * @package App\Models
 * @property string      code                优惠码
 * @property string      title               优惠券名
 * @property-read string desc                优惠券说明
 * @property int         used                已使用数量
 * @property int         total               总量
 * @property string      type                类型: fixed, percent
 * @property-read string type_str            类型-中文
 * @property float       value               fixed时表示优惠金额, percent时表示优惠百分比
 * @property float       cond_min_amount     要求订单最低的金额
 * @property float       max_discount_amount 最大优惠金额, 用于百分比优惠时限制额度
 * @property Carbon      not_before          生效时间
 * @property Carbon      not_after           失效时间
 * @property boolean     enabled             是否启用
 */
class Coupon extends Model
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $TYPE_MAP = [
        self::TYPE_FIXED => '固定金额',
        self::TYPE_PERCENT => '百分比',
    ];

    protected $guarded = [];

    protected $dates = [
        'not_before',
        'not_after',
    ];

    protected $casts = [
        'enabled' => 'bool',
    ];

    protected $appends = [
        'type_str',
        'desc',
    ];

    protected static function boot()
    {
        parent::boot();

        // static::creating(function ($user) {
        //     if (empty($user->code)) {
        //         $user->code = static::genCode();
        //     }
        // });
    }

    /**
     * 自动生成优惠码
     * @return string
     */
    public static function genCode()
    {
        do {
            $code = strtoupper(Str::random(16));
        } while (Coupon::query()->where('code', $code)->exists());
        return $code;
    }

    /**
     * @return string
     */
    public function getTypeStrAttribute()
    {
        return static::$TYPE_MAP[$this->type] ?? $this->type;
    }

    public function getDescAttribute()
    {
        $str = '';
        if ($this->cond_min_amount > 0) {
            $str .= sprintf('满%.2f元,', $this->cond_min_amount);
        }
        $str .= '优惠';
        if ($this->type == self::TYPE_FIXED) {
            $str .= $this->value . '元';
        } elseif ($this->type == self::TYPE_PERCENT) {
            $str .= $this->value . '%';
            if ($this->max_discount_amount > 0) {
                $str .= ', 最多优惠' . $this->max_discount_amount . '元';
            }
        } else {
            $str = '无效优惠配置';
        }
        return $str;
    }

    /**
     * 增加优惠券使用量+1
     * @return bool
     */
    public function use()
    {
        $affected = $this->newQuery()
            ->where('id', $this->id)
            ->where('used', '<', $this->total)
            ->increment('used');
        return $affected ? true : false;
    }
}
