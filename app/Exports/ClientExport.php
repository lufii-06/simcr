<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Client::with('user');
        if (!empty($this->filters['start_date'])) $query->whereDate('created_at', '>=', $this->filters['start_date']);
        if (!empty($this->filters['end_date'])) $query->whereDate('created_at', '<=', $this->filters['end_date']);
        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            "NO", 
            "NAMA PERUSAHAAN", 
            "KONTAK UTAMA", 
            "EMAIL", 
            "NOHP", 
            "ALAMAT", 
            "TANGGAL GABUNG"
        ];
    }

    public function map($client): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $client->company_name,
            $client->main_contact,
            $client->user->email,
            $client->phone,
            $client->address,
            $client->created_at->format('d/m/Y')
        ];
    }
}
