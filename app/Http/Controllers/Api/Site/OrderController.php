<?php

namespace App\Http\Controllers\Api\Site;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Site\CreateOrderRequest;
use App\Http\Resources\Api\Admin\OrderResource;
use App\Http\Resources\Api\Admin\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    function index(Request $request) {
        $orders = auth('customer')->user()->orders()->with('orderItems.product')
        ->orderByDesc('created_at')
        ->when($request->has('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->paginate(10);

        return apiResourceCollection(OrderResource::class, $orders);
    }

    function store(CreateOrderRequest $request)
    {
        $order = auth('customer')->user()->orders()->create([
            'status' => OrderStatusEnum::PENDING,
        ]);

        foreach ($request->items as $item) {
            $product = Product::select('id', 'price','active')->findOrFail($item['product_id']);
            $order->orderItems()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $product->price,
            ]);
        }

        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'),200, "Order created successfully, please wait for admin confirmation to complete payment.");
    }

    function payments()
    {
        $payments = Payment::whereHas('order', function ($query) {
            $query->where('user_id', auth('customer')->id());
        })
        ->with('order')
        ->orderByDesc('created_at')
        ->paginate(10);

        return apiResourceCollection(PaymentResource::class, $payments);
    }
}
