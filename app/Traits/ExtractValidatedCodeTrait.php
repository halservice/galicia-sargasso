<?php

namespace App\Traits;

trait ExtractValidatedCodeTrait
{
    public function extractValidatedCodeFromResponse(string $response): string
    {
        if (preg_match('/### Validated code:\s*```(?:\w+)?\s*(.+?)```/s', $response, $matches)) {
            return trim($matches[1]);
        }

        return trim($response);
    }
}
