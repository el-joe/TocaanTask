<?php

namespace App\Http\Resources\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'amount'=> $this->amount,
            'payment_method' => $this->paymentMethod->name,
            'order_id' => $this->order_id,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status->value,
            'pay_details' => $this->pay_details,
            'callback_details' => $this->callback_details,
        ];
    }
}
