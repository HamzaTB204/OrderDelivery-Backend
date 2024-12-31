<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
           
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProduct = CartProduct::where('cart_id', $cart->id)->get();
            if ($cartProduct->isEmpty()) {
                return response()->json(['message' => 'You did not add any product to your cart'], 404);
            }
            $allProducts=[];
            $totalPrice=0;
            
            foreach ($cartProduct as $product) {
                $totalPrice += $product->price * $product->quantity;
                $productDetails = Product::with('store', 'images')->find($product->product_id);
                $store = $productDetails->store; 
                if ($store) {
                    $store->logo = $store->logo ? url("storage/{$store->logo}") : null; 
                    if (!isset($stores[$store->id])) {
                        $stores[$store->id] = $store;
                    }
                }
                $allProducts[] = [
                    'product' => $productDetails,
                    'quantity' => $product->quantity,
                    
                ];
            }
    
            return response()->json([
                'Cart Product' => $allProducts ,
                'Total Price'=> $totalPrice,
            ]);
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
        if (!$user) {
            return response()->json(['Failed' => false, 'message' => 'User  not authenticated.'], 401);
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = Product::find( $request->product_id);
        try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProductExists = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->exists();
            $productQuantity = $product->quantity;
            if ($request->quantity < $productQuantity) {
                if ($cartProductExists) {
                    return response()->json(['success' => false, 'message' => 'Product is already in your cart.'], 409);
                }
                $totalPrice = $product->price * $request->quantity;
                CartProduct::create([
                    'cart_id'=> $cart->id,
                    'product_id'=> $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $totalPrice,
                ]);
                return response()->json(['success' => ' Added to your cart '], 200);
            }
            else {
                return response()->json(['success' => false, 'message' => 'There is not enough product'], 200);
            }
            
        } catch (\Exception $e) {

            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $user = auth()->user();
        // if (!$user) {
        //     return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        // }
        // try {
        //     $product = Product::with('store', 'images')->find( $id );
        //     $cart = Cart::where('user_id', $user->id)->first();
        //     $cartProduct = CartProduct::where('cart_id', $cart->id)->where('product_id',$id)->first();
        //     return response()->json([
        //         'product'=> $product,
        //         'price'=>$cartProduct->price,
        //         'Quantity'=>$cartProduct->quantity,
        //     ], );

        // }catch (\Exception $e) {

        //     return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        $product = Product::find( $request->product_id);
        try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProductExists = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->first();
            $productQuantity = $product->quantity;
            if ($request->quantity > $productQuantity){
                return response()->json(['success' => false, 'message' => 'There is not enough product'], 400);
            }
            $totalPrice = $product->price * $request->quantity;
            $cartProductExists->update([
                'quantity' => $request->quantity,
                'price' => $totalPrice,
            ]);
            return response()->json(['success' => true, 'message' => 'quantity updated successfully.'], 200);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProduct = CartProduct::where('cart_id', $cart->id)->where('product_id' ,$id)->first();
            $deleted=$cartProduct->delete();
            if ($deleted) {
                return response()->json(['message' => ' Deleted Done '], 200);
            } else {
                return response()->json(['message' => 'Not Deleted'], 500);
            }
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    public function add_cart_To_order(){
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }
        $cart=Cart::where('user_id', $user->id)->first();
        $cartProduct = CartProduct::where('cart_id', $cart->id)->get();
        if ($cartProduct->isEmpty()){
            return response()->json(['success'=> false, 'message'=> 'there is no product in your cart']);
        }
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
            foreach ($cartProduct as $product){
                $product2 = Product::find($product->product_id);
                $isUpdated = $product2->Quantity($product->quantity);
                $product2->increment('orders_count', 1);
                if ($isUpdated){
                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $product->product_id,
                        'quantity' => $product->quantity,
                        'price' => $product->price,
                    ]);
                    CartProduct::where('cart_id', $cart->id)->where('product_id' ,$product->product_id)->first()->delete();
                }
            }
            return response()->json(['success'=> true,'Done'=> 'Your order has been added successfully '], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }
}
