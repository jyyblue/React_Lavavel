<?php

namespace App\Imports;

use App\Models\AmazonSeller;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class AmazonSellerImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $amazon_id = $row[0];
        $name = $row[1];
        $email = $row[2];
        $sales_agent_name = $row[3];
        $sales_agent_email = $row[4];
        $piva = $row[5];
        if($name == 'Name' 
        && $email == 'Email' 
        && $sales_agent_name == 'Sales Agent Name' 
        && $sales_agent_email == 'Sales Agent Email') {
            return null;
        }
        $seller = AmazonSeller::where('amazon_id', $amazon_id)->first();
        if($seller) {
            // if($email) {
                $seller->update([
                    'email' => $email,
                    'sales_agent_name' => $sales_agent_name,
                    'sales_agent_email' => $sales_agent_email,
                    'piva' => $piva,
                ]);
            // }
            return null;
        }else{
            return new AmazonSeller([
                'amazon_id' => $row[0],
                'name' => $row[1],
                'email' => $row[2],
                'sales_agent_name' => $row[3],
                'sales_agent_email' => $row[4],
                'piva' => $row[5],
            ]);
        }
    }
}
