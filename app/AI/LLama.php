<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LLama
{

    public function systemMessage(string $sys_message, string $usr_message): array
    {
        return [
            [
                'role' => 'system',
                'content' => $sys_message,
            ], [
                'role' => 'user',
                'content' => $usr_message,
            ]
        ];

    }


    /**
     * @throws ConnectionException
     */
    public function send(array $message, string $model): string
    {
        try {
            $response = Http::withToken(config('services.mindinabox.api_key'))
                ->timeout(60)
                ->post("https://astro-llama.internal.mindinabox.io/v1/chat/completions", [
                    'model' => 'meta-llama/Llama-3.1-8B-Instruct',
                    'messages' => $message,
                ])
                ->json();

            return $response['choices'][0]['message']['content'];

        } catch (\Exception $e) {
            ds('Error in ChatGPT:', $e->getMessage());
            throw $e;
        }

    }


}
