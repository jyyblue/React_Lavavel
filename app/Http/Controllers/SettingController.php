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
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    // register a new user method
    public function getSetting(Request $request)
    {
        $setting = Setting::get();
        $google = Setting::where('category', 'cron')->whereIn('name', ['google_week1', 'google_week2'])
        ->get()->pluck('value')->toArray();
        $tmp = array();

        $amazon = Setting::where('category', 'cron')->whereIn('name', ['amazon_week1', 'amazon_week2'])
        ->get()->pluck('value')->toArray();

        $dayofweek = Carbon::now()->dayOfWeek;

        $googletype = gettype($google);
        $runAmazon = in_array($dayofweek, $amazon);
        return response()->json([
            'data' => $setting,
            'google' => $google,
            'amazon' => $amazon,
            'dayofweek' => $dayofweek,
            'runAmazon' => $runAmazon,
            '$googletype' => $googletype
        ]);
    }

    public function updateSetting(Request $request) {
        $id = $request->get('id');
        $value =$request->get('value');
        Setting::find($id)->update([
            'value' => $value,
        ]);
        return response()->json([
            'message' => 'Updated Successfully!',
        ]);
    }
}
