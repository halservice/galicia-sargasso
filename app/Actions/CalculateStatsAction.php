<?php

namespace App\Actions;

use App\Traits\CheckChanges;
use App\Traits\CheckFailedTest;
use App\Traits\CodeGotBetterTrait;
use Illuminate\Support\Collection;

class CalculateStatsAction
{
    use CheckFailedTest;
    use CheckChanges;
    use CodeGotBetterTrait;


    protected int $correctProcess = 0;
    protected int $wrongProcess = 0;
    public array $iterationsCount = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    protected int $betterCode = 0;
    protected int $rightAtFirst = 0;

    public function getStats(Collection $validations): array
    {
        foreach ($validations as $validation) {
            // The stats focus on the assistant response.
//            $assistantMessages = array_filter($validation->validation_process, fn($msg) => $msg['role'] === 'assistant');
            $assistantMessages = array_filter($validation->validation_process, function ($item) {
                return isset($item['role']) && $item['role'] === 'assistant';
            });
            // Count how many iterations where actually done.
            $iterationCount = count($assistantMessages);

            // Get the last message of the assistant.
            $assistantMessage = end($assistantMessages)['content'];
            // Obtain the number of failed test in the last iteration.
            $lastIteration = $this->checkFailedTest($assistantMessage);

            // If the number of failed test in the last iteration is 0 then the final result is a correct code.
            // Meaning there is a +1 right process & we must update the count for the iteration.
            // If the number of failed test in the last iteration is not 0 then the final result is not corret.
            // Meaning there is an increase of wrong process.
            if ($lastIteration === 0) {
                $this->correctProcess++;
                $this->iterationsCount[$iterationCount]++;
            } elseif ($lastIteration != -1) {
                $this->wrongProcess++;
            }

            // Depending on the code improvement the count for a 'betterCode' or 'rightAtFirst' values increases.
            $result = $this->gotABetterCode($assistantMessages);
            if ($result === 1) {
                $this->betterCode++;
            } elseif ($result === 0) {
                $this->rightAtFirst++;
            }
        }

        // Only keeping into consideration the successful validation, get the mean of the number of iterations required to obtain a correct result.
        $totalTest = 0;
        $total = 0;
        foreach ($this->iterationsCount as $iteration => $iterationNumber) {
            $total += $iteration * $iterationNumber;
            $totalTest += $iterationNumber;
        }
        $mean = $totalTest > 0 ? round($total / $totalTest, 2) : 0;

        return [
            "rightAtFirst" => $this->rightAtFirst,
            "betterCode" => $this->betterCode,
            "iterationsCount" => $this->iterationsCount,
            "wrongProcess" => $this->wrongProcess,
            "iterationMean" => $mean,
        ];
    }


}
