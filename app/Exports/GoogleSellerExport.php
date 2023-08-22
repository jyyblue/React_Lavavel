<?php

namespace App\Exports;

use App\Models\GoogleSeller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GoogleSellerExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return GoogleSeller::all();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Sales Agent Name',
            'Sales Agent Email',
        ];
    }

    public function map($seller): array
    {
        return [
            $seller->name,
            $seller->email,
            $seller->sales_agent_name,
            $seller->sales_agent_email,
        ];
    }
}
