<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;



class OrderController extends Controller
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
        
        if ($user->role === 'admin') {
            $orders = Order::all();
        } 
        else {
            $orders = Order::where('user_id', $user->id)->get();
        }
        try {
            
            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders found.'], 404);
            }
            $allOrders=[];
            foreach($orders as $order){
                if ($order->status !== 'canceled'){
                    $orderProduct = OrderProduct::where('order_id', $order->id)->first();
                    $productDetails = Product::with('store', 'images')->find($orderProduct->product_id);
                    $store = $productDetails->store; 
                    if ($store) {
                        $store->logo = $store->logo ? url("storage/{$store->logo}") : null; 
                        if (!isset($stores[$store->id])) {
                            $stores[$store->id] = $store;
                        }
                    }
                    $allOrders[] = [
                        'order status' =>$order->status,
                        'order details' => $orderProduct,
                        'product details' => $productDetails
                    ];  
                }
            }
            return $allOrders;
            return response()->json(['data' => $ordersWithProducts ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['Failed' => false, 'message' => 'User  not authenticated.'], 401);
        }
        if (!($user->location)) {
            return response()->json(['Failed' => false, 'message' => 'Not allowed please enter your location'], 401);
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        if ($product) {
            $isUpdated = $product->Quantity($request->quantity);
            if ($isUpdated) {
                try {
                    $order = Order::create([
                        'user_id' => $user->id,
                        'status' => 'pending',
                    ]);

                    $totalPrice = $product->price * $request->quantity;

                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $request->product_id,
                        'quantity' => $request->quantity,
                        'price' => $totalPrice,
                    ]);

                    $product->increment('orders_count', 1);

                    return response()->json(['success' => 'Your order has been added successfully'], 200);
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'There is not enough product'], 200);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'The product doesn\'t exist'], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        ($user);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
            $order = Order::where('user_id', $user->id)->where('id', $id)->first();
            if (!$order) {
                return response()->json([ 'message' => 'Order not found.'], 404);
            }
            if ($order->status === 'canceled'){
                return response()->json([ 'message' => 'the order canceled'], 404);
            }
            $orderDetails=OrderProduct::where('order_id',$id)->first();
            $productDetails = Product::with('store', 'images')->find($orderDetails->product_id);
                    $store = $productDetails->store; 
                    if ($store) {
                        $store->logo = $store->logo ? url("storage/{$store->logo}") : null; 
                        if (!isset($stores[$store->id])) {
                            $stores[$store->id] = $store;
                        }
                    }
                    $allOrders[] = [
                        'order status' =>$order->status,
                        'order details' => $orderDetails,
                        'product details' => $productDetails
                    ];  
            return response()->json([ 'order details' => $allOrders ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
        }

    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = Order::where('user_id', $user->id)->where('id', $id)->firstOrFail();
            $product = Product::findOrFail($request->product_id);
            $orderProduct = OrderProduct::where('order_id', $order->id)->where('product_id', $product->id)->firstOrFail();
            $isUpdated = $product->updateQuantity($orderProduct->quantity , $request->quantity );
            if (!$isUpdated) {
                return response()->json(['success' => false, 'message' => 'There is not enough product'], 400);
            }
            $totalPrice = $product->price * $request->quantity;
            $orderProduct->update([
                'quantity' => $request->quantity,
                'price' => $totalPrice,
            ]);
            return response()->json(['success' => true, 'message' => 'Order updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $orderProducts = OrderProduct::where('order_id', $order->id)->get();

        try {
            foreach ($orderProducts as $orderProduct) {
                $product = Product::findOrFail($orderProduct->product_id);

                $product->decrement('orders_count', 1);

                $orderProduct->delete();
            }

            $deleted = $order->delete();
            if ($deleted) {
                return response()->json(['message' => 'Deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'Order deletion failed'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
    public function cancelOrder(string $orderId ){
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
        $order = Order::where('id', $orderId)->where('user_id', $user->id)->first();
        $order->updateStatus('canceled');
        $orderProducts = OrderProduct::where('order_id', $order->id)->get();
        foreach ($orderProducts as $orderProduct) {
            $product = Product::findOrFail($orderProduct->product_id);
            $product->updateQuantity($orderProduct->quantity,0);
            $product->decrement('orders_count', 1);
        }
        return response()->json([
            'message' => 'Order canceled successfully',
        ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

}
