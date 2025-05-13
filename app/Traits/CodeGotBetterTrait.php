<?php

namespace App\Traits;

trait CodeGotBetterTrait
{
    use CheckChanges;
    use CheckFailedTest;
    protected function gotABetterCode(array $AssistantMessages): int
    {
        // This method receive all the assistant messages generated in the validation code.
        $firstWrong = false;
        $firstMessage = reset($AssistantMessages)['content'];

        // First it checks if the first message is correct or not.
        // If it's correct in the first step then the code can't improve.
        // If it's wrong in the first step then the code improved only if it's correct in the last step.
        $numberChanges = $this->checkChanges($firstMessage);
        if ($numberChanges != -1 && $numberChanges != 0) {
            $firstWrong = true;
        } elseif ($numberChanges === -1){
            $numberChanges = $this->checkFailedTest($firstMessage);
            if ($numberChanges != -1 && $numberChanges != 0) {
                $firstWrong = true;
            }
        }

        if (!$firstWrong && $numberChanges != -1) {
            return 0;
        }

        $lastMessage = end($AssistantMessages)['content'];
        if($this->checkChanges($lastMessage) === 0 || $this->checkFailedTest($lastMessage) === 0 ) {
            return 1;
        }

        return -1;
    }

}
