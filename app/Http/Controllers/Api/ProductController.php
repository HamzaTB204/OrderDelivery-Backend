<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function productCount()
    {
        try {
            $count = Product::count();
            return response()->json([
                'message' => 'Product count retrieved successfully.',
                'product_count' => $count,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $storeId = $request->query('store_id');
            $search = $request->query('search');
            $page = $request->query('page', 1);
            $perPage = 10;

            $productsQuery = Product::query();

            if ($storeId) {
                $productsQuery->where('store_id', $storeId);
            }

            if ($search) {
                $productsQuery->search($search);
            }

            $products = $productsQuery->with('store', 'images')->paginate($perPage, ['*'], 'page', $page);

            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found'], 404);
            }

            $products->getCollection()->transform(function ($product) use ($storeId) {
                if (!$storeId) {
                    $product->store_name = $product->store->name ?? null;
                    $product->store_id = $product->store->id ?? null;
                }

                unset($product->store);
                return $product;
            });

            return response()->json([
                'message' => 'Products retrieved successfully.',
                'products' => $products,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $fields = $request-> validate([
                'en_name' => "required|max:30",
                'ar_name' => "required|max:30",
                'en_description' => "required|max:255",
                'ar_description' => "required|max:255",
                'quantity' => "required",
                'price' => "required",
                'store_id' => "required|exists:stores,id",
            ]);

            $product=Product::create($fields);

            return response()->json([
                'message' =>  "Product Added Successfully",
                'product' => $product
            ]);
        }catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happened ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $product = Product::with('store', 'images')->find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $product->store_name = $product->store->name ?? null;
            $product->store_id = $product->store->id ?? null;

            unset($product->store);

            return response()->json([
                'message' => 'Product retrieved successfully.',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::where('id', $id)->first();
            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            $fields = $request-> validate([
                'en_name' => "nullable|max:30",
                'ar_name' => "nullable|max:30",
                'en_description' => "nullable|max:255",
                'ar_description' => "nullable|max:255",
                'quantity' => "nullable",
                'price' => "nullable",
                'store_id' => "exists:stores,id",
            ]);
            $product->fill(array_filter($fields));
            $product->save();
            return response()->json([
                'message' => 'updated Done',
                'product' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        try {
            if (!$product) {
                return response()->json(['message' => 'The product Does not Exist'], 404);
            }

            $deleted = $product->delete();

            if ($deleted) {
                return response()->json(['message' => ' Deleted Done '], 200);
            } else {
                return response()->json(['message' => 'Not Deleted'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happened ' . $e->getMessage()], 500);
        }
    }

    public function getMostOrderedProducts()
    {
        try {
            $products = Product::with(['store', 'images'])
                               ->orderBy('orders_count', 'desc')
                               ->take(10)
                               ->get();

            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found'], 404);
            }

            $products->transform(function ($product) {
                $product->images = $product->images->map(function ($image) {
                    return url("storage/{$image->path}");
                });
                $product->store_name = $product->store->name ?? null;
                $product->store_id = $product->store->id ?? null;
                unset($product->store);
                return $product;
            });

            return response()->json([
                'message' => 'Most ordered products retrieved successfully.',
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function getLatestProducts()
    {
        try {
            $products = Product::with(['store', 'images'])
                               ->orderBy('created_at', 'desc')
                               ->take(10)
                               ->get();

            if ($products->isEmpty()) {
                return response()->json(['message' => 'No products found'], 404);
            }

            $products->transform(function ($product) {
                $product->images = $product->images->map(function ($image) {
                    return url("storage/{$image->path}");
                });
                $product->store_name = $product->store->name ?? null;
                $product->store_id = $product->store->id ?? null;
                unset($product->store);
                return $product;
            });

            return response()->json([
                'message' => 'Latest products retrieved successfully.',
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }


}
