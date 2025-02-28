<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use IcehouseVentures\LaravelChartjs\Builder;

new class extends Component {

    public int $currentYear = 0;
    public int $totalCount = 0;

    public function mount()
    {
        $this->currentYear = now()->year;

        $this->totalCount = GeneratedValidatedCode::select('id')
            ->whereYear('created_at', $this->currentYear)
            ->get()
            ->count();

    }

    #[Computed]
    public function monthlyChart()
    {
        $validatedCodes = GeneratedValidatedCode::select('id')
            ->whereYear('created_at', $this->currentYear)
            ->get()
            ->groupBy(function ($date){
                return \Carbon\Carbon::parse($date->created_at)->format('m');
                });

        $validatedCodesMonthly = [];
        $monthArr = [];

        foreach ($validatedCodes as $key => $value){
            $validatedCodesMonthly[(int)$key] = count($value);
        }

        for($i=1; $i<=12; $i++){
            if(!empty($validatedCodesMonthly[$i])){
                $monthArr[$i] = $validatedCodesMonthly[$i];
                }else{
                $monthArr[$i] = 0;
            }
        }

        $labelsName = collect(range(1, 12))->map(function ($month){
            return \Carbon\Carbon::create()->month($month)->format('F');
            })->toArray();

        return Chartjs::build()
            ->name('lineChartTest')
            ->type('line')
            ->size(['width' => 200, 'height' => 100])
            ->labels($labelsName)
            ->datasets([
                [
                    "label" => "Numbers of completed process in " . $this->currentYear,
                    'backgroundColor' => "rgba(38, 185, 154, 0.31)",
                    'borderColor' => "rgba(38, 185, 154, 0.7)",
                    "pointBorderColor" => "rgba(38, 185, 154, 0.7)",
                    "pointBackgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    "data" => array_values($monthArr),
                    "fill" => false,
                ],
            ])
            ->options([]);
    }

    #[Computed]
    public function llmCodeChart()
    {

    }

    #[Computed]
    public function llmFormalChart()
    {

    }

    #[Computed]
    public function llmValidationChart()
    {

    }
}
?>

<x-card title="Statistic"
        subtitle="Here you can find statistics about the Galicia project."
        shadow
        separator>

    <div class="font-bold text-2xl text-secondary flex items-center">
        <h1>Galicia worked on&nbsp;</h1>
    <span x-data="{ count: 0, target: {{ $this->totalCount }} }"
          x-init="let interval = setInterval(() => { if(count < target) count++; else clearInterval(interval); }, 20)"
          x-text="count"
          class="text-primary">
    </span>
        <h1>&nbsp;projects in {{ $this->currentYear }}</h1>
    </div>

    <div class="w-3/4">
        <x-chartjs-component :chart="$this->monthlyChart" />
    </div>

{{--    <div class="flex justify-between gap-4">--}}
{{--        <div class="w-1/3">--}}
{{--            <x-chartjs-component :chart="$this->llmCodeChart" />--}}
{{--        </div>--}}
{{--        <div class="w-1/3">--}}
{{--            <x-chartjs-component :chart="$this->llmFormalChart" />--}}
{{--        </div>--}}
{{--        <div class="w-1/3">--}}
{{--            <x-chartjs-component :chart="$this->llmValidationChart" />--}}
{{--        </div>--}}
{{--    </div>--}}

</x-card>
