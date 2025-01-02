<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function storeCount(){
        try{
            $storeCount = Store::count();
            return response()->json([
                'message' => 'Store count retrieved successfully.',
                'store_count'=>$storeCount
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message'=>'Something went wrong: '.$e->getMessage()
            ],500);
        }
    }
    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $perPage = 10;
        $search = $request->input('search');

        $storesQuery = Store::withCount('products');

        if ($search) {
            $storesQuery->search($search);
        }

        $stores = $storesQuery->skip(($page - 1) * $perPage)
                              ->take($perPage)
                              ->get();

        $stores->transform(function ($store) {
            $store->logo = $store->logo ? url("storage/{$store->logo}") : null;
            $store->product_count = $store->products_count;
            unset($store->products_count);
            return $store;
        });

        $totalStores = $search ?$storesQuery->count():Store::count();

        return response()->json([
            'current_page' => $page,
            'total_pages' => ceil($totalStores / $perPage),
            'stores' => $stores,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:stores|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store = Store::create($validated);


        $store->logo = $store->logo ? url("storage/{$store->logo}") : null;

        return response()->json(['message' => 'Store created successfully', 'store' => $store], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $store = Store::withCount('products')->find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $store->logo = $store->logo ? url("storage/{$store->logo}") : null;

        $page = $request->query('page', 1);
        $perPage = 10;

        $products = $store->products()->with('images')
                          ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'logo' => $store->logo,
                'product_count' => $store->products_count,
            ],
            'products' => $products
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        if ($request->hasFile('logo')) {

            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }


            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store->update($validated);


        $store->logo = $store->logo ? url("storage/{$store->logo}") : null;

        return response()->json(['message' => 'Store updated successfully', 'store' => $store], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        if ($store->logo) {
            Storage::disk('public')->delete($store->logo);
        }

        $store->delete();

        return response()->json(['message' => 'Store deleted successfully'], 200);
    }
}
