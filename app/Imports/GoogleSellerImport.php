<?php

namespace App\Imports;

use App\Models\GoogleSeller;
use Maatwebsite\Excel\Concerns\ToModel;

class GoogleSellerImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $name = $row[0];
        $email = $row[1];
        $sales_agent_name = $row[2];
        $sales_agent_email = $row[3];
        if($name == 'Name' 
        && $email == 'Email'
        && $sales_agent_name == 'Sales Agent Name' 
        && $sales_agent_email == 'Sales Agent Email'
        ) {
            return null;
        }
        $seller = GoogleSeller::where('name', $name)->first();
        if($seller) {
            // if($email) {
                $seller->update([
                    'email' => $email,
                    'sales_agent_name' => $sales_agent_name,
                    'sales_agent_email' => $sales_agent_email,
                ]);
            // }
            return null;
        }else{
            return new GoogleSeller([
                'name' => $row[0],
                'email' => $row[1],
                'sales_agent_name' => $row[2],
                'sales_agent_email' => $row[3],
            ]);
        }
    }
}
