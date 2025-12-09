<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\CreateOrderRequest;
use App\Http\Resources\Api\Admin\OrderResource;
use App\Mail\OrderPaymentMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::orderByDesc('created_at')
        ->when($request->status, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->with('user', 'orderItems.product')
        ->paginate(50);
        return apiResourceCollection(OrderResource::class, $orders);
    }

    function store(CreateOrderRequest $request)
    {
        $order = Order::create($request->only(['user_id']));

        foreach ($request->items as $item) {
            $product = Product::select('id', 'price')->findOrFail($item['product_id']);
            $order->orderItems()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $product->price,
            ]);
        }

        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'), 200, "Order created successfully");
    }

    function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . OrderStatusEnum::valuesAsString(),
        ]);

        if($order->cannotUpdate()){
            return response()->json(['message' => 'Only pending orders can be updated.'], 400);
        }
        $order->update($request->only(['status']));
        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'), 200, "Order updated successfully");
    }

    function destroy(Order $order)
    {
        if($order->cannotUpdate()){
            return response()->json(['message' => 'Only pending orders can be deleted.'], 400);
        }

        $order->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    function addItem(Request $request, Order $order)
    {
        if($order->cannotUpdate()){
            return response()->json(['message' => 'Cannot add items to a non-pending order.'], 400);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::select('id', 'price')->findOrFail($request->product_id);

        $item = $order->orderItems()->where('product_id', $request->product_id)->first();

        if($item){
            $item->qty += $request->qty;
            $item->save();
        }else{
            $order->orderItems()->create([
                'product_id' => $request->product_id,
                'qty' => $request->qty,
                'price' => $product->price,
            ]);
        }

        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'), 200, "Item added to order successfully");
    }

    function updateItem(Request $request, Order $order, $itemId)
    {
        if($order->cannotUpdate()){
            return response()->json(['message' => 'Cannot update items in a non-pending order.'], 400);
        }

        $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        $item = $order->orderItems()->findOrFail($itemId);
        $item->update([
            'qty' => $request->qty,
        ]);

        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'), 200, "Order item updated successfully");
    }

    function removeItem(Request $request, Order $order, $itemId)
    {
        if($order->cannotUpdate()){
            return response()->json(['message' => 'Cannot remove items from a non-pending order.'], 400);
        }

        $item = $order->orderItems()->findOrFail($itemId);
        $item->delete();

        return apiResource(OrderResource::class, $order->load('user', 'orderItems.product'), 200, "Order item removed successfully");
    }
}
