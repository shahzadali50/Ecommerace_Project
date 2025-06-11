<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrandTranslation extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'lang', 'brand_id', 'user_id'];

    public function brand(){
    	return $this->belongsTo(Brand::class);
    }

}
