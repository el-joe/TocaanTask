<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Mail\OrderPaymentMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    use HasFactory;

    // boot method to send email on update status
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            if ($order->wasChanged('status') && $order->status == OrderStatusEnum::CONFIRMED) {
                Mail::to($order->user->email)->send(new OrderPaymentMail($order->refresh()));
            }
        });
    }
    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Accessors
    function getSubTotalAttribute()
    {
        $subTotal = $this->orderItems->sum(fn($item) => $item->total_item_price);
        return number_format($subTotal, 2, '.', '');
    }

    function getGrandTotalAttribute()
    {
        // Assuming no additional fees or taxes for simplicity
        return $this->sub_total;
    }

    function getSlugAttribute()
    {
        return base64_encode(json_encode(['order_id' => $this->id, 'user_id' => $this->user_id]));
    }

    function cannotUpdate()
    {
        return $this->status != OrderStatusEnum::PENDING || $this->payments()->where('status', PaymentStatusEnum::SUCCESS)->exists();
    }
}
