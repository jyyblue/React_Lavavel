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
