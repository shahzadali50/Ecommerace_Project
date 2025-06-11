<?php

namespace App\Models;

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Wishlist;
use App\Models\PurchaseProduct;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'brand_id',
        'category_id',
        'thumnail_img',
        'gallary_img',
        'stock',
        'status',
        'purchase_price',
        'sale_price',
        'discount',
        'final_price',
        'feature',
        'barcode',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class, 'product_id');
    }
    public function wishlists()
{
    return $this->hasMany(Wishlist::class);
}


}
