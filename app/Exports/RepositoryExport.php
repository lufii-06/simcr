<?php

namespace App\Exports;

use App\Models\Repository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RepositoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Repository::with('project');
        
        if (!empty($this->filters['project_id'])) $query->where('project_id', $this->filters['project_id']);
        if (!empty($this->filters['visibility'])) $query->where('is_public', $this->filters['visibility'] == 'public');
        if (!empty($this->filters['status'])) $query->where('is_active', $this->filters['status'] == 'active');
        if (!empty($this->filters['keyword'])) {
            $keyword = $this->filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhereHas('project', function ($pq) use ($keyword) {
                      $pq->where('name', 'like', "%{$keyword}%");
                  });
            });
        }
        
        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            "NO", 
            "NAMA REPOSITORY", 
            "PROJECT", 
            "VISIBILITAS", 
            "URL CLONE (HTTP)", 
            "STATUS"
        ];
    }

    public function map($repo): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $repo->name,
            $repo->project->name,
            $repo->is_public ? 'Public' : 'Private',
            config('app.url') . '/git/' . $repo->name . '.git',
            $repo->is_active ? 'Active' : 'Archived'
        ];
    }
}
