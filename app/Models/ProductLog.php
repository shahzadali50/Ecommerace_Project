<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProductLog extends Model
{
    protected $fillable = [
        'note',
        'product_name',
        'user_id',
        'product_id',
    ];


    public function user()
{
    return $this->belongsTo(User::class);
}

}
