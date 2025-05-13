<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use Carbon\Carbon;
use IcehouseVentures\LaravelChartjs\Builder;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

class ChartBuilder
{
    public function getMonthlyChart(int $currentYear): Builder
    {
        // Get the validated code and group them by month.
        $validatedCodes = GeneratedValidatedCode::select('id', 'created_at')
            ->whereYear('created_at', $currentYear)
            ->get()
            ->groupBy(function ($date) {
                return \Carbon\Carbon::parse($date->created_at)->format('m');
            });

        // Create an array with the count of the monthly validated codes.
        $validatedCodesMonthly = [];
        $monthArr = [];
        foreach ($validatedCodes as $key => $value) {
            $validatedCodesMonthly[(int)$key] = count($value);
        }
        for ($i = 1; $i <= 12; $i++) {
            if (!empty($validatedCodesMonthly[$i])) {
                $monthArr[$i] = $validatedCodesMonthly[$i];
            } else {
                $monthArr[$i] = 0;
            }
        }

        // Create label with the names of the months.
        $labelsName = collect(range(1, 12))->map(function ($month) {
            return Carbon::create()->month($month)->format('F');
        })->toArray();

        // Create chart.
        return Chartjs::build()
            ->name('lineChartTest')
            ->type('line')
            ->size(['width' => 200, 'height' => 100])
            ->labels($labelsName)
            ->datasets([
                [
                    "label" => "Distribution of test cases over months",
                    "data" => array_values($monthArr),
                    "fill" => false,
                ],
            ])
            ->options($this->chartOptions());

    }

    public function getLlmCodeChart(): Builder
    {
        // Get all Programming Language cases.
        $labels = array_map(fn($case) => $case->value, ProgrammingLanguage::cases());

        $data = GeneratedValidatedCode::with(['generator' => function ($query) {
            if ($query->getModel() instanceof GeneratedCode) {
                $query->with('formalModel');
            }
            if ($query->getModel() instanceof GeneratedFormalModel) {
                $query->with('generatedCode');
            }
        }])
            ->whereYear('created_at', now()->year)
            ->get()
            ->map(function ($item) {
                if ($item->generator instanceof GeneratedFormalModel) {
                    return $item->generator->generatedCode->programming_language->name ?? null;
                }
                if ($item->generator instanceof GeneratedCode) {
                    return $item->generator->programming_language->name ?? null;
                }

                return null;
            })
        ->toArray();
        $counts = array_count_values($data);

        // Count the usage of each programming language.
        $chartData = [];
        foreach ($labels as $label) {
            $chartData[] = $counts[$label] ?? 0;
        }

        // Create Chart.
        return Chartjs::build()
            ->name('codeLanguage')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    'data' => $chartData
                ]
            ]);
    }

    public function getIterationChart(array $iterationsCount): Builder
    {
        // The max number of iterations possible is five.
        $labels = ['1 iteration', '2 iterations', '3 iterations', '4 iterations', '5 iterations'];

        // Associate the iteration number with the iteration count.
        $data = [];
        foreach (range(1, 5) as $key) {
            $data[] = $iterationsCount[$key] ?? 0;
        }

        // Create Chart.
        return Chartjs::build()
            ->name('iterationToCorrect')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    "label" => "Distribution of test cases over iterations",
                    'data' => $data,
                ]
            ])
            ->options($this->chartOptions());
    }

    private function chartOptions(): array
    {
        return
            [
                'scales' => [
                    'y' => [
                        'grid' => [
                            'color' => 'rgba(169, 169, 169, 0.47)',
                        ],
                    ],
                    'x' => [
                        'grid' => [
                            'color' => 'rgba(169, 169, 169, 0.47)',
                        ],
                    ],
                ],
            ];
    }
}
