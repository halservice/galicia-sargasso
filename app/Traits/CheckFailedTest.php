<?php

namespace App\Traits;

trait CheckFailedTest
{
    protected function checkFailedTest(string $response): int
    {
        if (preg_match('/### Number of test failed:\s*(\d+)/i', $response, $matches)) {
            return  (int)trim($matches[1]);
        }
        return -1;
    }
}
