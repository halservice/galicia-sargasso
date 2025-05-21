<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            Log::error("Exception while calling OpenAI: {$e->getMessage()}", [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException("Errore durante l'elaborazione della richiesta. Riprova più tardi.");
        }

    }


}
