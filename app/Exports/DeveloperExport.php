<?php

namespace App\Exports;

use App\Models\Developer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DeveloperExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Developer::with(['user', 'specialization']);
        if (!empty($this->filters['start_date'])) $query->whereDate('created_at', '>=', $this->filters['start_date']);
        if (!empty($this->filters['end_date'])) $query->whereDate('created_at', '<=', $this->filters['end_date']);
        if (!empty($this->filters['specialization_id'])) {
            $query->where('specialization_id', $this->filters['specialization_id']);
        }
        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            "NO", 
            "NAMA DEVELOPER", 
            "SPESIALISASI", 
            "EMAIL", 
            "NOHP", 
            "ALAMAT", 
            "TANGGAL GABUNG"
        ];
    }

    public function map($dev): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $dev->user->name,
            $dev->specialization->name ?? 'N/A',
            $dev->user->email,
            $dev->phone,
            $dev->address,
            $dev->created_at->format('d/m/Y')
        ];
    }
}
