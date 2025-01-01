<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserController extends Controller {

    public function changeRole(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'role' => 'required|in:user,admin,driver',
        ]);

        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update the role
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
