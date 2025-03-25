<?php

namespace App\Traits;

trait ExtractCodeTrait
{
    public function extractCodeFromResponse(string $response): string
    {
        if (preg_match('/```(?:\w+)?\s*(.+?)```/s', $response, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
