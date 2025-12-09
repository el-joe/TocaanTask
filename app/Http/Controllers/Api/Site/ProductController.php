<?php

namespace App\Http\Controllers\Api\Site;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Admin\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    function index(Request $request) {
        $products = Product::active()
        ->when($request->has('q'), function ($query) use ($request) {
            $query->whereAny(['name','description'], 'like', '%' . $request->q . '%');
        })
        ->paginate(12);

        return apiResourceCollection(ProductResource::class, $products);
    }
}
