<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
            $product = Product::findOrFail($productId);

            $images = $product->images()->get()->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => url("storage/{$image->path}")
                ];
            });

            if ($images->isEmpty()) {
                return response()->json([
                    'message' => 'No images found for this product.',
                ], 404);
            }

            return response()->json([
                'message' => 'Images retrieved successfully.',
                'images' => $images
            ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $productId)
    {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $product = Product::findOrFail($productId);

            $path = $request->file('image')->store('gallery', 'public');

            $image = $product->images()->create([
                'path' => $path,
            ]);

            return response()->json([
                'message' => 'Image added successfully',
                'id' => $image->id,
                'url' => url("storage/$image->path"),
            ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($product_id, $image_id)
    {
            $image = Image::where('product_id', $product_id)
                          ->where('id', $image_id)
                          ->first();

            if (!$image) {
                return response()->json(['message' => 'Image not found'], 404);
            }

            $imageData = [
                'id' => $image->id,
                'product_id' => $image->product_id,
                'url' => url("storage/{$image->path}"),
                'created_at' => $image->created_at,
                'updated_at' => $image->updated_at,
            ];
            return response()->json(['image' => $imageData], 200);
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function delete($productId, $imageId)
    {
            $product = Product::findOrFail($productId);

            $image = $product->images()->where('id', $imageId)->first();

            if (!$image) {
                return response()->json([
                    'message' => 'Image not found for this product.'
                ], 404);
            }

            if (Storage::exists("public/{$image->path}")) {
                Storage::delete("public/{$image->path}");
            }

            $image->delete();

            return response()->json([
                'message' => 'Image deleted successfully.'
            ], 200);
    }
}
