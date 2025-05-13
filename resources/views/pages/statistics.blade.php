<?php

use App\Actions\CalculateStatsAction;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Services\ChartBuilder;
use App\Settings\CodeGeneratorSettings;
use App\Models\UserSetting;
use App\Enums\ProgrammingLanguage;
use App\Traits\CheckChanges;
use App\Traits\CheckFailedTest;
use App\Traits\CodeGotBetterTrait;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use IcehouseVentures\LaravelChartjs\Builder;

// @phpstan-ignore-next-line
new class extends Component {
    use CheckChanges;
    use CheckFailedTest;
    use CodeGotBetterTrait;

    public int $currentYear = 0;
    public int $totalCount = 0;
    public float $iterationMean = 0;
    public int $failedProcess = 0;

    public int $betterCode = 0;
    public int $rightAtFirst = 0;
    public int $wrongProcess = 0;

    public array $iterationsCount = [];

    protected ChartBuilder $chartBuilder;

    public function mount(): void
    {
        $this->chartBuilder = new ChartBuilder();

        // Get current year.
        $this->currentYear = now()->year;
        // Get all the validated codes in the current year.
        $process = GeneratedValidatedCode::whereYear('created_at', $this->currentYear)
            ->select('validation_process')
            ->latest()
            ->get();
        // Get total test cases count.
        $this->totalCount = $process->count();

        $stats = app(CalculateStatsAction::class)->getStats($process);
        $this->wrongProcess = $stats['wrongProcess'];
        $this->iterationsCount = $stats['iterationsCount'];
        $this->betterCode = $stats['betterCode'];
        $this->rightAtFirst = $stats['rightAtFirst'];
        $this->iterationMean = $stats['iterationMean'];

        // Get the % of test that didn't reach a correct result after all the iterations.
        $this->failedProcess = $this->totalCount > 0
            ? round(($this->wrongProcess / $this->totalCount) * 100, 2)
            : 0;

    }

    #[Computed]
    public function monthlyChart(): Builder
    {
        return $this->chartBuilder->getMonthlyChart($this->currentYear);
    }

    #[Computed]
    public function llmCodeChart(): Builder
    {
        return $this->chartBuilder->getLlmCodeChart();
    }

    #[Computed]
    public function iterationChart(): Builder
    {
        return $this->chartBuilder->getIterationChart($this->iterationsCount);
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
    <p class="text-center">The following chart shows the number of tests conducted each month
        in {{ $this->currentYear }}.</p>
    <div class="w-3/4 mx-auto flex justify-center mb-6">
        <x-chartjs-component :chart="$this->monthlyChart"/>
    </div>

    <div class="mt-6 text-center">
        <div class="font-bold text-2xl text-secondary">
            Main insights on Galicia's process
        </div>
        <p><span class="text-secondary italic">Galicia</span>'s process refined <span
                class="text-primary font-bold">{{ $this->betterCode }}</span> codes. These did not pass all tests on the
            first iteration, but were successfully validated before the final iteration.</p>
        <p>A total of <span class="text-primary font-bold">{{ $this->rightAtFirst }}</span> cases were generated
            correctly and passed all the tests on the first iteration.</p>
        <p>On average, a test case required <span class="text-primary font-bold">{{ $this->iterationMean }}</span> iterations to
            reach a correct result.</p>
        <p>A total of <span class="text-primary font-bold">{{ $this->failedProcess }}%</span> of cases did not produce a
            valid result and reached the maximum allowed iterations.</p>
    </div>

    <div class="font-bold text-xl text-secondary text-center mt-3">
        Programming languages used in validation tests
    </div>
    <p class="text-center">The percentage of tests conducted for each programming language.</p>
    <div class="w-1/3 mx-auto flex justify-center mb-6">
        <x-chartjs-component :chart="$this->llmCodeChart"/>
    </div>

    <div class="font-bold text-lg text-secondary text-center">
        Iterations required to reach a correct result
    </div>
    <p class="text-center">This chart shows how many attempts were needed before obtaining a correct solution.</p>
    <div class="w-3/4 mx-auto flex justify-center mb-6">
        <x-chartjs-component :chart="$this->iterationChart"/>
    </div>


</x-card>
