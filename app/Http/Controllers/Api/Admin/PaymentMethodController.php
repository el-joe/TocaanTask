<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\CreatePaymentMethodRequest;
use App\Http\Requests\Api\Admin\UpdatePaymentMethodRequest;
use App\Http\Resources\Api\Admin\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = PaymentMethod::orderByDesc('created_at')->get();

        return apiResourceCollection(PaymentMethodResource::class, $paymentMethods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePaymentMethodRequest $request){
        $extraRequestValidationRules = [];
        foreach ($request->required_fields as $key) {
            $extraRequestValidationRules['configuration.' . $key] = 'required|string';
        }
        $request->validate($extraRequestValidationRules);

        $paymentMethod = PaymentMethod::create($request->all());

        return apiResource(PaymentMethodResource::class, $paymentMethod,200,"Payment method created successfully");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $extraRequestValidationRules = [];
        foreach ($request->required_fields as $key) {
            $extraRequestValidationRules['configuration.' . $key] = 'required|string';
        }

        $request->validate($extraRequestValidationRules);

        $paymentMethod->update($request->all());

        return apiResource(PaymentMethodResource::class, $paymentMethod, 200,"Payment method updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->delete();

        return response()->json([
            "message" => "Payment method deleted successfully"
        ], Response::HTTP_NO_CONTENT);
    }
}
