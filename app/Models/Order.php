<?php

namespace App\Models;

use App\Models\SaleProduct;
use App\Models\OrderTranslation;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'name', 'phone_number',
        'total_price', 'discount', 'subtotal_price', 'status', 'payment_method', 'user_id',
        'email', 'address', 'city', 'state', 'postal_code', 'country', 'order_notes','order_id','payment_intent_id','stripe_session_id','payment_status','stripe_customer_id'
    ];

    public function saleProducts()
    {
        return $this->hasMany(SaleProduct::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order_translations()
    {
        return $this->hasMany(OrderTranslation::class);
    }


}
