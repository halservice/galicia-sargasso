<?php

namespace App\Traits;

trait ExtractTestResultsTrait
{
    public function extractTestResult(string $response): string
    {
        if (preg_match('/### Test cases:\s*(.*?)\s*(?=###|\z)/s', $response, $matches)) {
            return trim($matches[1]);
        }

        return trim($response);
    }
}
