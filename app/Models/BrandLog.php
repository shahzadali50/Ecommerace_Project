<?php

namespace App\Models;

use App\Models\User;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;

class BrandLog extends Model
{
    protected $fillable = [
        'note',
        'brand_name',
        'brand_id',
        'user_id',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function user()
{
    return $this->belongsTo(User::class);
}
}
