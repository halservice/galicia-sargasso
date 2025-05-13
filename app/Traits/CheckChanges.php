<?php

namespace App\Traits;

trait CheckChanges
{
    protected function checkChanges(string $response): int
    {
        if (preg_match('/Number of changes made:\s*(\d+)/i', $response, $matches)) {
            $number = (int)trim($matches[1]);
            return $number;
        }
        return -1;
    }
}
