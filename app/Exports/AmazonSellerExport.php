<?php

namespace App\Exports;

use App\Models\AmazonSeller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AmazonSellerExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AmazonSeller::all();
    }
    
    public function headings(): array
    {
        return [
            'Name',
            'Email',
        ];
    }

    public function map($movie): array
    {
        return [
            $movie->name,
            $movie->email,
        ];
    }
}
