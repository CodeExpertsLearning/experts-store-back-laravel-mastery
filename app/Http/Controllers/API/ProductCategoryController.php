<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    //api/products/{product}/categories
    public function index(Product $product)
    {
        return response()->json([
            'data' => $product->categories
        ]);
    }
}
