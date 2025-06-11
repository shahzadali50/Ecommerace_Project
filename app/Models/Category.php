<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Product;
use App\Models\CategoryTranslation;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'user_id',
    ];

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function category_translations(){
    	return $this->hasMany(CategoryTranslation::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            try {
                foreach ($category->brands as $brand) {
                    foreach ($brand->brand_translations as $translation) {
                        $translation->delete();
                    }

                    foreach ($brand->products as $product) {
                        $product->purchaseProducts()->delete();
                        $product->delete();
                    }

                    if ($brand->image && Storage::disk('public')->exists($brand->image)) {
                        Storage::disk('public')->delete($brand->image);
                    }

                    $brand->delete();
                }

                foreach ($category->category_translations as $translation) {
                    $translation->delete();
                }

            } catch (\Throwable $e) {
                Log::error('Error in Category deleting event: ' . $e->getMessage());
                throw $e; // important: rethrow to stop deletion if something fails
            }
        });
    }

}
