<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cart
 * @package App\Models
 * @property-read bool $is_sale
 */
class Cart extends Model
{
    protected $fillable = [
        'amount',
        'user_id',
        'product_sku_id',
    ];

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIsSaleAttribute()
    {
        return $this->productSku->product->is_sale;
    }

    public function hasStock()
    {
        return $this->productSku->stock > 0;
    }
}
