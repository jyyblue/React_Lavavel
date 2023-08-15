<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\GoogleSeller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SellerController extends Controller
{
    // register a new user method
    public function getGoogleSeller(Request $request) {
        $sellers = GoogleSeller::get();
        return response()->json([
          'data' => $sellers
        ]);
    }

    // login a user method
    public function updateGoogleSeller(Request $request) {
        $id = $request->id;
        $data = $request->all();
        Log::info(json_encode($data));
        $seller = GoogleSeller::where('id', $id)->first();
        if(!$seller) {
            return response()->json([
                'message' => 'Could not find Data',
            ], 201);
        }
        $seller->update($data);
        return response()->json([
            'data' => $seller,
        ], 201);
    }

    // logout a user method
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Logged out successfully!',
        ])->withCookie($cookie);
    }

    // get the authenticated user method
    public function user(Request $request) {
        return new UserResource($request->user());
    }
}
