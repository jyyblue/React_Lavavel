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
use App\Models\AmazonAPICallHistory;
use App\Models\AmazonMail;
use App\Models\AmazonResults;
use App\Models\AmazonSeller;
use App\Models\GoogleMail;
use App\Models\GoogleScrapeHistory;
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
        return response()->json([
            'data' => $setting,
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

    public function getAmazonScrapeHistory() {
        $data = AmazonAPICallHistory::select(DB::raw('SUM(sz_success) as sz_success, SUM(sz_fail) as sz_fail, MAX(call_time) as call_time, SUM(sz_empty_asin) as sz_empty_asin'))
        ->groupBy('call_group_id')->orderBy('call_group_id', 'DESC')->get();
        return response()->json([
            'data' => $data,
        ]);
    }

    public function getGoogleScrapeHistory() {
        $data = GoogleScrapeHistory::select(DB::raw('SUM(sz_success) as sz_success, SUM(sz_fail) as sz_fail, MAX(call_time) as call_time, SUM(sz_zero) as sz_zero'))
        ->groupBy('call_group_id')->orderBy('call_group_id', 'DESC')->get();
        return response()->json([
            'data' => $data,
        ]);
    }
}
