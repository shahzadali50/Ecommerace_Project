<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\BrandTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'user_id',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

public function brand_translations(){
    return $this->hasMany(BrandTranslation::class);
}
protected static function boot()
{
    parent::boot();

    static::deleting(function ($brand) {
        // Delete all related products
        foreach ($brand->products as $product) {
            $product->delete();
        }

        // Delete all related brand translations
        foreach ($brand->brand_translations as $translation) {
            $translation->delete();
        }
    });
}

}
