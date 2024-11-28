<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores=Store::all();
        return response()->json($stores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $store = Store::create($validated);
        return response()->json(['message' => 'Store created successfully', 'store' => $store], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::with('products')->find($id);

        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        return response()->json($store, 200);
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
        ]);
        $store->update($validated);
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

        $store->delete();
        return response()->json(['message' => 'Store deleted successfully'], 200);
    }
}
