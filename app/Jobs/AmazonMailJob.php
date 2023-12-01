<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use App\Models\AmazonResults;
use Illuminate\Support\Facades\Mail;
use App\Models\AmazonMail;
use App\Mail\AmazonMail as MailAmazonMail;
use Illuminate\Support\Facades\DB;

class AmazonMailJob implements ShouldQueue
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
            $seller_id = $this->seller->id;
            $amazon_id = $this->seller->amazon_id;
            $mailHistory = new AmazonMail();
            $mailHistory->seller_id = $seller_id;
            $mailHistory->status = 'sent';
            $mailHistory->save();
            $data = $this->getAmazonTopDiscount($amazon_id, $this->discount);
            if(count($data) > 0) {
              // send mail
              $mailData = [
                  'seller' => $this->seller,
                  'data' => $data,
              ];
              Mail::to($this->email)
              ->send(new MailAmazonMail($mailData));
            }

          } catch (\Exception $e) {
            Log::info('Error: handle' . $e->getMessage());
        }
    }

    public function getAmazonTopDiscount($amazon_id, $discount)
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
        ar.seller_name,
        product.sku, 
        product.price, 
        product.`title`, 
        ar.offer_link, 
        amazon_seller.name as seller,
        ar.call_group_id,
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
                MAX(call_group_id) AS call_group_id 
              FROM 
                amazon_results 
            ) last_updates ON last_updates.call_group_id = amazon_results.call_group_id
        ) ar 
        LEFT JOIN product ON ar.product_id = product.id 
        LEFT JOIN amazon_seller ON amazon_seller.amazon_id = ar.seller 
        WHERE ar.total_price <> 0".$where.
        " AND ar.seller ='".$amazon_id."'".
      " ORDER BY 
        discount DESC limit 50";

        $data = DB::select($query);
        return $data;
    }
}
