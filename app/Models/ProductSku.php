<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
