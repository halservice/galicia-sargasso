<?php

namespace App\Exports;

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Traits\DataPreparation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ValidatedCodesExport implements FromCollection, WithHeadings, WithMapping
{
    use DataPreparation;
    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return GeneratedValidatedCode::with(['generator' => function ($query) {
            $query->when($query->getModel() instanceof GeneratedCode, fn($q) => $q->with('formalModel'))
                ->when($query->getModel() instanceof GeneratedFormalModel, fn($q) => $q->with('generatedCode'));
        }])
            ->where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn($item) => $this->prepareData($item));

    }

    public function map($row): array
    {
        return $this->getMap($row);
    }

    public function headings(): array
    {
        return $this->getHeadings();
    }
}
