<?php

namespace App\Http\Requests\Api\Site;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ];
    }

    // check if products are active
    public function withValidator($validator){
        $validator->after(function ($validator) {
            foreach ($this->items as $item) {
                $product = Product::find($item['product_id']);
                if (!$product || !$product->active) {
                    $validator->errors()->add('items', 'One or more products are inactive or do not exist.');
                    break;
                }
            }
        });
    }
}
