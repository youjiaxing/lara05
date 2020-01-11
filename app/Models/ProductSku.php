<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductSku
 * @package App\Models
 * @property integer $product_id
 * @property Product $product
 * @property int $stock
 * @property double $price
 * @property string $title
 * @property string $description
 */
class ProductSku extends Model
{
    protected $fillable = [
        'product_id',
        'price',
        'stock',
        'title',
        'description',
        'sold_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 扣除库存
     *
     * @param int $dec
     *
     * @return bool 成功返回true, 失败返回false
     */
    public function decreaseStock($dec)
    {
        if ($dec < 0) {
            throw new InternalException("减库存不可小于0");
        }

        $affectCount = $this->newQuery()
            ->where('id', $this->id)
            ->where('stock', '>=', $dec)
            ->limit(1)
            ->decrement('stock', $dec);
        return $affectCount == 0 ? false : true;
    }

    /**
     * 增加库存
     * @param $inc
     *
     * @return bool 成功返回true, 失败返回false
     */
    public function addStock($inc)
    {
        if ($inc < 0) {
            throw new InternalException("加库存不可小于0");
        }

        return $this->increment('stock', $inc) > 0;
    }
}
