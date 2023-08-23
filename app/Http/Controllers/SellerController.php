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
use App\Mail\AmazonMail as MailAmazonMail;
use App\Mail\GoogleMail as MailGoogleMail;
use App\Models\AmazonMail;
use App\Models\AmazonSeller;
use App\Models\GoogleMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

    public function getTopAmazon(Request $request)
    {
        $data = $this->getAmazonTopDiscount();
        return response()->json([
            'product' => $data
        ], 200);
    }

    private function getAmazonTopDiscount()
    {
        $query = "SELECT 
        ar.total_price, 
        ar.item_price, 
        product.sku, 
        product.price, 
        product.`title`, 
        ar.offer_link, 
        ROUND((
          100 - 100 * ar.total_price / product.price
        ), 2) AS discount 
      FROM 
        (
          SELECT 
            amazon_results.* 
          FROM 
            amazon_results 
            INNER JOIN (
              SELECT 
                MAX(id) AS id 
              FROM 
                amazon_results 
              GROUP BY 
                product_id
            ) last_updates ON last_updates.id = amazon_results.id
        ) ar 
        LEFT JOIN product ON ar.product_id = product.id 
        WHERE ar.total_price <> 0
      ORDER BY 
        discount DESC limit 50";

        $data = DB::select($query);
        return $data;
    }
    public function sendAmazonMail(Request $request)
    {
        $id = $request->get('id');
        $seller = AmazonSeller::find($id);
        $mailHistory = new AmazonMail();
        $mailHistory->seller_id = $id;
        $mailHistory->status = 'sent';
        $mailHistory->save();
        $data = $this->getAmazonTopDiscount();

        Mail::to($seller->email)
        ->send(new MailAmazonMail($data));

        return response()->json([
            'message' => 'message'
        ]);
    }

    public function getAmazonMail(Request $request)
    {
        $mail = AmazonMail::with(['seller'])->orderBy('id', 'DESC')->get();

        return response()->json([
            'mail' => $mail
        ]);
    }

    public function getTopGoogle(Request $request)
    {
        $data = $this->getGoogleTopDiscount();
        return response()->json([
            'product' => $data
        ], 200);
    }

    private function getGoogleTopDiscount()
    {
        $query = "SELECT 
        ar.total_price, 
        ar.item_price, 
        product.sku, 
        product.price, 
        product.`title`, 
        ar.offer_link, 
        ROUND((
          100 - 100 * ar.total_price / product.price
        ), 2) AS discount 
      FROM 
        (
          SELECT 
            google_results.* 
          FROM 
          google_results 
            INNER JOIN (
              SELECT 
                MAX(id) AS id 
              FROM 
              google_results 
              GROUP BY 
                product_id
            ) last_updates ON last_updates.id = google_results.id
        ) ar 
        LEFT JOIN product ON ar.product_id = product.id 
        WHERE ar.total_price <> 0
      ORDER BY 
        discount DESC limit 50";

        $data = DB::select($query);
        return $data;
    }
    public function sendGoogleMail(Request $request)
    {
        $id = $request->get('id');
        $seller = GoogleSeller::find($id);
        $mailHistory = new GoogleMail();
        $mailHistory->seller_id = $id;
        $mailHistory->status = 'sent';
        $mailHistory->save();
        $data = $this->getGoogleTopDiscount();

        Mail::to($seller->email)
        ->send(new MailGoogleMail($data));

        return response()->json([
            'message' => 'message'
        ]);
    }

    public function getGoogleMail(Request $request)
    {
        $mail = GoogleMail::with(['seller'])->orderBy('id', 'DESC')->get();

        return response()->json([
            'mail' => $mail
        ]);
    }

}
