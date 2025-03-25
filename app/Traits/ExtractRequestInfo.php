<?php

namespace App\Traits;

trait ExtractRequestInfo
{
    public function extractRequestNewInfo(string $response): string
    {
        if (preg_match('/Requesting new info\s*(.*)/s', $response, $matches)) {
            return "**".trim($matches[0]);
        }

        return "Error in generating the code.\nPlease try again.";
    }
}
