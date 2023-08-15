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
        $name = $row[0];
        $email = $row[1];
        if($name == 'Name' && $email == 'Email') {
            return null;
        }
        $seller = AmazonSeller::where('name', $name)->first();
        if($seller) {
            if($email) {
                $seller->update([
                    'email' => $email,
                ]);
            }
            return null;
        }else{
            return new AmazonSeller([
                'name' => $row[0],
                'email' => $row[1],
            ]);
        }
    }
}
