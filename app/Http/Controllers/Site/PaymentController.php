<?php

namespace App\Http\Controllers\Site;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\PaymentService as ServicesPaymentService;
use Illuminate\Http\Request;
use PaymentService;

class PaymentController extends Controller
{
    function show($slug)
    {
        $details = json_decode(base64_decode($slug), true);
        $order = Order::findOrFail($details['order_id']);
        $payment_methods = PaymentMethod::active()->select('id','name')->get();
        return view('payment.show', get_defined_vars());
    }

    function payNow(Request $request,$order)
    {
        $request->validate([
            'payment_method' => 'required|exists:payment_methods,id',
        ]);

        $order = Order::findOrFail($order);

        $paymentMethod = PaymentMethod::findOrFail($request->payment_method);

        $paymentClass = '\\App\\Payments\\' . $paymentMethod->class;

        // check if class exists
        if (!class_exists($paymentClass)) {
            throw new \Exception("Payment class {$paymentClass} not found.");
        }

        $paymentService = new ServicesPaymentService(new $paymentClass());

        $payResponse = $paymentService->pay([
            'order_id' => $order->id,
            'amount' => $order->grand_total,
            'user_id' => $order->user_id,
            'payment_method_id' => $paymentMethod->id,
        ]);

        $order->payments()->create([
            'payment_method_id' => $paymentMethod->id,
            'amount' => $order->grand_total,
            'transaction_id' => $payResponse['transaction_id'],
            'status' => PaymentStatusEnum::PENDING,
            'pay_details' => $payResponse,
        ]);

        return redirect()->to($payResponse['redirect_url']);
    }

    function callback(Request $request)
    {
        $transactionId = $request->query('q');

        $payment = Payment::where('transaction_id', $transactionId)->firstOrFail();
        // For demonstration, assuming we know the payment method class
        // In real scenario, you might want to store payment method info with transaction
        $paymentClass = '\\App\\Payments\\' . $payment->paymentMethod?->class;

        if (!class_exists($paymentClass)) {
            throw new \Exception("Payment class {$paymentClass} not found.");
        }

        $paymentService = new ServicesPaymentService(new $paymentClass());
        $callbackResponse = $paymentService->callback($transactionId);

        $payment->update([
            'callback_details' => $callbackResponse,
        ]);

        if ($callbackResponse['status'] === 'success') {
            $payment->update(['status' => PaymentStatusEnum::SUCCESS]);
            $payment->order->update(['status' => OrderStatusEnum::CONFIRMED]);
        } else {
            $payment->update(['status' => PaymentStatusEnum::FAILED]);
        }

        return redirect()->route('payment.callbackStatus', ['status' => $callbackResponse['status']]);
    }

    function callbackStatus($status)
    {
        return view('payment.callback', get_defined_vars());
    }
}
