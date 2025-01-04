<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserController extends Controller {

    public function userCount()
    {
        try {
            $userCount = User::where('role', 'user')->count();
            $driverCount = User::where('role', 'driver')->count();

            return response()->json([
                'user_count' => $userCount,
                'driver_count' => $driverCount,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function driverOrders()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not authenticated.'], 401);
        }
        if ($user->role !== 'driver') {
            return response()->json(['success' => false, 'message' => 'Access denied, Drivers only.'], 403);
        }

        try {
            $orders = Order::where('driver_id', $user->id)->get();
            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No orders assigned to you.'], 404);
            }

            $allOrders = [];
            foreach ($orders as $order) {
                $productsDetails = [];
                $orderProducts = OrderProduct::where('order_id', $order->id)->get();
                foreach ($orderProducts as $orderProduct) {
                    $productDetail = Product::with('store', 'images')->find($orderProduct->product_id);
                    $store = $productDetail->store;

                    if ($store) {
                        $store->logo = $store->logo ? url("storage/{$store->logo}") : null;
                    }

                    $productsDetails[] = [
                        'order details' => $orderProduct,
                        'product details' => $productDetail,
                    ];
                }

                $allOrders[] = [
                    'order status' => $order->status,
                    'driver_id' => $order->driver_id,
                    'products' => $productsDetails,
                ];
            }

            return response()->json($allOrders, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Something wrong happened: ' . $e->getMessage()], 500);
        }
    }
    public function changeRole(Request $request, $id)
    {

        $request->validate([
            'role' => 'required|in:user,admin,driver',
        ]);


        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user,
        ]);
    }

    public function index(){
        $users=User::all();
        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture ? url("storage/{$user->profile_picture}") : null,
                'location' => $user->location,
                'locale' => $user->locale,
                'role'=> $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });
        return response()->json($users);
    }

    public function updateProfile( Request $request ) {
        $user = $request->user();

        $request->validate( [
            'first_name'      => 'string|required',
            'last_name'       => 'string|required',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:2048|nullable',
            'location'        => 'string|required',
        ] );


        if ( $request->hasFile( 'profile_picture' ) ) {
            $path                  = $request->file( 'profile_picture' )
                                             ->store('profile_pictures','public');
            $user->profile_picture = $path;
        }

        $user->update( $request->only( [
            'first_name',
            'last_name',
            'location',
        ] ) );
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'profile_picture' => $user->profile_picture ? url("storage/{$user->profile_picture}") : null,
            'location' => $user->location,
            'locale' => $user->locale,
            'role'=>$user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
        return response()->json(['user'=> $userData]);
    }


    public function changeLocale( Request $request ) {
        $locale=$request->get('locale');
        if ( in_array( $locale, [ 'en', 'ar' ] ) ) {
            App::setLocale( $locale );

            $request->user()->locale=$locale;
            $user=$request->user();
            $user->update( $request->only([
                'locale',
            ]));

            return response()->json( [
                'message' => 'Locale changed successfully',
                'locale'  => $locale,
            ] );
        }

        return response()->json( [ 'error' => 'Invalid locale' ], 400 );
    }


}
