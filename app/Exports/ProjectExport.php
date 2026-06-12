<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProjectExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Project::with(['client', 'status']);
        
        if (!empty($this->filters['client_id'])) $query->where('client_id', $this->filters['client_id']);
        if (!empty($this->filters['status_id'])) $query->where('project_status_id', $this->filters['status_id']);
        if (!empty($this->filters['start_date'])) $query->whereDate('start_date', '>=', $this->filters['start_date']);
        if (!empty($this->filters['end_date'])) $query->whereDate('end_date', '<=', $this->filters['end_date']);
        if (!empty($this->filters['keyword'])) {
            $keyword = $this->filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('code', 'like', "%{$keyword}%")
                  ->orWhereHas('client', function ($cq) use ($keyword) {
                      $cq->where('company_name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            "NO", 
            "KODE PROJECT", 
            "NAMA PROJECT", 
            "KLIEN", 
            "STATUS", 
            "TANGGAL MULAI", 
            "TANGGAL SELESAI"
        ];
    }

    public function map($project): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $project->code,
            $project->name,
            $project->client->company_name,
            $project->status->name,
            $project->start_date ? date('d/m/Y', strtotime($project->start_date)) : '-',
            $project->end_date ? date('d/m/Y', strtotime($project->end_date)) : '-'
        ];
    }
}
