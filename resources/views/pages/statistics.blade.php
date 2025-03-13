<?php

use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use App\Models\UserSetting;
use App\Enums\ProgrammingLanguage;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use IcehouseVentures\LaravelChartjs\Builder;

new class extends Component {
    use \App\Traits\checkChanges;
    use \App\Traits\checkFailedTest;

    public int $currentYear = 0;
    public int $totalCount = 0;
    public float $mean = 0;
    public int $failedProcess = 0;

    public array $iterationsCount = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    public array $correctProcess = [0 => 0, 1 => 0];

    public function mount()
    {
        $this->currentYear = now()->year;

        $this->totalCount = GeneratedValidatedCode::select('id','created_at')
            ->whereYear('created_at', $this->currentYear)
            ->get()
            ->count();

        $process = GeneratedValidatedCode::select('validation_process')
            ->latest()
            ->get();

        foreach ($process as $message){
            $assistantMessages = array_filter($message->validation_process, function ($item){
                return isset($item['role']) && $item['role']==='assistant';
            });
            $iterationCount = count($assistantMessages);

            $assistantMessage = end($assistantMessages)['content'];

            $numberChanges = $this->checkChanges($assistantMessage);
            if($numberChanges != -1){
                $lastIteration = $numberChanges;
            } else {
                $lastIteration = $this->checkFailedTest($assistantMessage);
            }
            // per valutare percentuale di risultati non corretti alla fine del processo, se corretto modifico il num iterations per trovare ris corretto
            if ($lastIteration === 0){
                $this->correctProcess[0]++;
                $this->iterationsCount[$iterationCount]++;
            }else{
                $this->correctProcess[1]++;
            }
        }

        $this->failedProcess = ($this->correctProcess[1]/$this->totalCount)*100;

        $totalTest = 0;
        $total = 0;
        foreach ($this->iterationsCount as $iteration => $iterationNumber) {
            $total += $iteration * $iterationNumber;
            $totalTest += $iterationNumber;
        }
        $this->mean = round($total / $totalTest,2);

    }

    #[Computed]
    public function monthlyChart()
    {
        $validatedCodes = GeneratedValidatedCode::select('id','created_at')
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
                    "label" => "Distribution of projects over months",
                    "data" => array_values($monthArr),
                    "fill" => false,
                ],
            ])
            ->options([
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
            ]);
    }

    #[Computed]
    public function llmCodeChart()
    {
        $labels = array_map(fn ($case) => $case->value, ProgrammingLanguage::cases());
        $data = GeneratedValidatedCode::with(['generator' => function ($query) {
            if ($query->getModel() instanceof GeneratedCode) {
                $query->with('formalModel'); // Carichiamo solo se è un GeneratedCode
            }
            if ($query->getModel() instanceof GeneratedFormalModel) {
                $query->with('generatedCode'); // Carichiamo solo se è un GeneratedFormalModel
            }
        }])
            ->get()
            ->map(function ($item) {
                if ($item->generator instanceof GeneratedFormalModel) {
                    $item->programming_language = $item->generator->generatedCode->programming_language->value;
                } else {
                    $item->programming_language = $item->generator->programming_language->value;
                }
                return $item;
            });
        $programmingLanguages = $data->pluck('programming_language')->toArray();
        $counts = array_count_values($programmingLanguages);

        $chartData = [];
        foreach ($labels as $label) {
            $chartData[] = $counts[$label] ?? 0;
        }

        return Chartjs::build()
            ->name('codeLanguage')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    'data' => $chartData
                ]
            ])
            ->options([]);
    }

    #[Computed]
    public function iterationChart()
    {
        $labels = ['1 iteration','2 iterations','3 iterations','4 iterations','5 iterations'];
        $data = [];

        foreach (range(1, 5) as $key) {
            $data[] = $this->iterationsCount[$key] ?? 0;
        }

        return Chartjs::build()
            ->name('iterationToCorrect')
            ->type('bar')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    'data' => $data,
                ]
            ])
            ->options([
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
            ]);
    }

}
?>

<x-card title="Statistics"
        subtitle="Here you can find key insights about the Galicia project."
        shadow
        separator>


    <div class="flex flex-col items-center justify-center">
        <div class="font-bold text-2xl text-secondary flex justify-center">
            <h1>Galicia worked on&nbsp;</h1>
        <span x-data="{ count: 0, target: {{ $this->totalCount }} }"
              x-init="let interval = setInterval(() => { if(count < target) count++; else clearInterval(interval); }, 20)"
              x-text="count"
              class="text-primary">
        </span>
            <h1>&nbsp;test cases in {{ $this->currentYear }}</h1>
        </div>
    </div>
    <p class="text-center">The following chart shows the number of tests conducted each month in {{ $this->currentYear }}.</p>
    <div class="w-3/4 mx-auto flex justify-center mb-6">
            <x-chartjs-component :chart="$this->monthlyChart" />
    </div>

    <div class="font-bold text-xl text-secondary text-center">
        Programming languages used in validation tests
    </div>
    <p class="text-center">The percentage of tests conducted for each programming language.</p>
    <div class="w-1/3 mx-auto flex justify-center mb-6">
        <x-chartjs-component :chart="$this->llmCodeChart" />
    </div>

    <div class="font-bold text-lg text-secondary text-center">
        Iterations required to reach a correct result
    </div>
    <p class="text-center">This chart shows how many attempts were needed before obtaining a correct solution.</p>
    <div class="w-3/4 mx-auto flex justify-center mb-6">
        <x-chartjs-component :chart="$this->iterationChart" />
    </div>

    <div class="mt-6 text-center">
        <div class="font-bold text-lg text-secondary">
            Additional insights on validation tests
        </div>
        <p>On average, a test required <span class="text-primary font-bold">{{ $this->mean }}</span> iterations to reach a correct result.</p>
        <p>A total of <span class="text-primary font-bold">{{ $this->failedProcess }}%</span> of tests did not produce a valid result and reached the maximum allowed iterations.</p>
    </div>

</x-card>
