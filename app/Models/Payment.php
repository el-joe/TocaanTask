<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'payment_method_id',
        'order_id',
        'transaction_id',
        'status',
        'pay_details',
        'callback_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => PaymentStatusEnum::class,
        'pay_details' => 'array',
        'callback_details' => 'array',
    ];

    // Relationships

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
