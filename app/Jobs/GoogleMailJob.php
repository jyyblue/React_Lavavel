<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use App\Models\GoogleResults;
use Illuminate\Support\Facades\Mail;
use App\Models\GoogleMail;
use App\Mail\GoogleMail as MailGoogleMail;
use Illuminate\Support\Facades\DB;

class GoogleMailJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $seller;
    private $discount;
    private $email;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->seller = $data['seller'];
        $this->discount = $data['discount'];
        $this->email = $data['email'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }
        //
        try {
            sleep(3);

            $mailHistory = new GoogleMail();
            $mailHistory->seller_id = $this->seller->id;
            $mailHistory->status = 'sent';
            $mailHistory->save();
            $sellerName = $this->seller->name;
    
            $data = $this->getGoogleTopDiscount($sellerName, $this->discount);
    
            if(count($data) > 0) {
              $mailData = [
                'seller' => $this->seller,
                'data' => $data,
              ];
              Mail::to($this->email)
              ->send(new MailGoogleMail($mailData));
            }
          } catch (\Exception $e) {
            Log::info('Error: handle' . $e->getMessage());
        }
    }

    private function getGoogleTopDiscount($sellerName, $discount)
    {
        $where = "";
        if($discount == 1) {
            $where = " AND ROUND((
                100 - 100 * ar.total_price / product.price
              ), 2) > 20 ";
        }
        $query = "SELECT 
        ar.total_price, 
        ar.item_price, 
        ar.seller,
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
              MAX(call_group_id) AS call_group_id 
            FROM 
            google_results 
          ) last_updates ON last_updates.call_group_id = google_results.call_group_id
        ) ar 
        LEFT JOIN product ON ar.product_id = product.id 
        WHERE ar.total_price <> 0 ". $where. "
        AND ar.seller ='".$sellerName."'
      ORDER BY 
        discount DESC limit 50";

        $data = DB::select($query);
        return $data;
    }
}
