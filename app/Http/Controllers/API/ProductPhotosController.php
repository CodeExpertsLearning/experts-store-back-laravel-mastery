<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ProductPhotosStoreRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductPhotosController extends Controller
{
    public function index(Product $product)
    {
        return response()->json(['data' => $product->photos]);
    }

    public function store(Product $product, ProductPhotosStoreRequest $request)
    {
        $files = $request->photos;

        $photos = [];

        foreach ($files as $file) {
            $photos[] = ['photo' => $file->store('products', 'public')];
        }

        $product->photos()->createMany($photos);
    }

    public function destroy(Product $product, $photo)
    {
        $productPhoto = $product->photos()->find($photo);

        if(Storage::disk('public')->exists($productPhoto->photo))
            Storage::disk('public')->delete($productPhoto->photo);

        $productPhoto->delete();

        return response()->json([], 204);
    }
}
