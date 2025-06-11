<?php

namespace App\Models;

use App\Models\Order;

    use Illuminate\Database\Eloquent\Model;


class OrderTranslation extends Model
{

    protected $fillable = [
        'lang', 'name','address', 'city', 'state',
        'country', 'order_notes', 'payment_method', 'status', 'user_id', 'order_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }





}
