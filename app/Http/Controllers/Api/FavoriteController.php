<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\FavoriteProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        ($user);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
            $favorite = Favorite::where('user_id', $user->id)->first();
            $favoriteProduct = FavoriteProduct::where('favorite_id', $favorite->id)->get();
            if (!$favoriteProduct) {
                return response()->json(['message' => 'You did not add any product to your favorite'], 404);
            }
            $allproduct=[];
            foreach ($favoriteProduct as $product) {
                $favoritDetails = Product::with('store', 'images')->find($product->product_id);
                $store = $favoritDetails->store;
                if ($store) {
                    $store->logo = $store->logo ? url("storage/{$store->logo}") : null;
                    if (!isset($stores[$store->id])) {
                        $stores[$store->id] = $store;
                    }
                }
                $allproduct[] = $favoritDetails;

            }
            return response()->json(['Favorite Product' => $allproduct]);
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happened ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        ($user);
        if (!$user) {
            return response()->json(['Failed' => false, 'message' => 'User  not authenticated.'], 401);
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);
        try {
            $favorite = Favorite::where('user_id', $user->id)->first();
            $favoriteProductExists = FavoriteProduct::where('favorite_id', $favorite->id)->where('product_id', $request->product_id)->exists();
            if ($favoriteProductExists) {
                return response()->json(['success' => false, 'message' => 'Product is already in your favorites.'], 409);
            }
            FavoriteProduct::create([
                'favorite_id'=> $favorite->id,
                'product_id'=> $request->product_id,
            ]);
            return response()->json(['success' => ' Added to your favorite '], 200);
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $id for product id not for order
        $user = auth()->user();
        ($user);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
            return response()->json(['product'=> Product::find($id)]);
        }catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favorite $favorite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        ($user);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
            $favorite = Favorite::where('user_id', $user->id)->first();
            $favoriteProduct = FavoriteProduct::where('favorite_id', $favorite->id)->where('product_id' ,$id)->first();
            $deleted=$favoriteProduct->delete();
            if ($deleted) {
                return response()->json(['message' => ' Deleted Done '], 200);
            } else {
                return response()->json(['message' => 'Not Deleted'], 500);
            }
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }
}
