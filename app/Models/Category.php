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
        'parent_id',
    ];
     // Parent category
     public function parent()
     {
         return $this->belongsTo(Category::class, 'parent_id');
     }

     // Child categories
     public function children()
     {
         return $this->hasMany(Category::class, 'parent_id')->with('children');
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
                foreach ($category->products as $product) {
                    // $product->purchaseProducts()->delete();
                    $product->delete();
                }

            } catch (\Throwable $e) {
                Log::error('Error in Category deleting event: ' . $e->getMessage());
                throw $e; // important: rethrow to stop deletion if something fails
            }
        });
    }

}
