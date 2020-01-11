<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class Product
 * @package App\Models
 * @property boolean     $is_sale
 * @property-read string $image_url 商品图
 * @property string      $title
 */
class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'price_min',
        'price_max',
        'is_sale',
        'sold_count',
        'review_count',
        'rating',
    ];

    protected $casts = [
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'is_sale' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function getImageUrlAttribute()
    {
        if (preg_match('~^http(s)?://~', $this->attributes['image'])
        ) {
            return $this->attributes['image'];
        } else {
            return Storage::disk('public')->url($this->attributes['image']);
        }
    }
}
