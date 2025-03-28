<?php

namespace App\Traits;

trait ExtractSamplePromptTrait
{
    public function extractSamplePrompt(string $response): string
    {
        if (preg_match('/Start sample prompt\s*(.*?)\s*(?=End sample prompt|\z)/s', $response, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }
}
