<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserController extends Controller {

    public function updateProfile( Request $request ) {
        $user = $request->user();

        $request->validate( [
            'first_name'      => 'string|required',
            'last_name'       => 'string|required',
            'profile_picture' => 'file|nullable',
            'location'        => 'string|required',
        ] );


        if ( $request->hasFile( 'profile_picture' ) ) {
            $path                  = $request->file( 'profile_picture' )
                                             ->store( 'profile_pictures' );
            $user->profile_picture = $path;
        }

        $user->update( $request->only( [
            'first_name',
            'last_name',
            'location',
        ] ) );
        return response()->json( $user );
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
