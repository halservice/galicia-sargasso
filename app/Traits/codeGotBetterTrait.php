<?php

namespace App\Traits;

trait codeGotBetterTrait
{
    use \App\Traits\checkChanges;
    use \App\Traits\checkFailedTest;
    protected function gotABetterCode(array $AssistantMessages): int
    {
        $firstWrong = false;
        $firstMessage = reset($AssistantMessages)['content'];
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
