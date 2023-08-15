<?php

namespace App\Http\Controllers;

use App\Exports\AmazonSellerExport;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\GoogleSeller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Exports\GoogleSellerExport;
use App\Imports\AmazonSellerImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GoogleSellerImport;
use App\Models\AmazonSeller;

class SellerController extends Controller
{
    // register a new user method
    public function getGoogleSeller(Request $request)
    {
        $sellers = GoogleSeller::get();
        return response()->json([
            'data' => $sellers
        ]);
    }

    // login a user method
    public function updateGoogleSeller(Request $request)
    {
        $id = $request->id;
        $data = $request->all();
        Log::info(json_encode($data));
        $seller = GoogleSeller::where('id', $id)->first();
        if (!$seller) {
            return response()->json([
                'message' => 'Could not find Data',
            ], 201);
        }
        $seller->update($data);
        return response()->json([
            'data' => $seller,
        ], 201);
    }

    public function exportGoogleSeller()
    {
        return Excel::download(new GoogleSellerExport, 'google_seller.xlsx');
    }

    public function importGoogleSeller(Request $request)
    {
        Excel::import(new GoogleSellerImport, request()->file('file')->store('temp'));
        return response()->json([
            'message' => 'Successfully imported!',
        ], 200);
    }


        // register a new user method
        public function getAmazonSeller(Request $request)
        {
            $sellers = AmazonSeller::get();
            return response()->json([
                'data' => $sellers
            ]);
        }
    
        // login a user method
        public function updateAmazonSeller(Request $request)
        {
            $id = $request->id;
            $data = $request->all();
            Log::info(json_encode($data));
            $seller = AmazonSeller::where('id', $id)->first();
            if (!$seller) {
                return response()->json([
                    'message' => 'Could not find Data',
                ], 201);
            }
            $seller->update($data);
            return response()->json([
                'data' => $seller,
            ], 201);
        }
    
        public function exportAmazonSeller()
        {
            return Excel::download(new AmazonSellerExport, 'amazon_seller.xlsx');
        }
    
        public function importAmazonSeller(Request $request)
        {
            Excel::import(new AmazonSellerImport, request()->file('file')->store('temp'));
            return response()->json([
                'message' => 'Successfully imported!',
            ], 200);
        }
}
