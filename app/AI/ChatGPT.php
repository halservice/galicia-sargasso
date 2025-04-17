<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPT
{
    public function systemMessage(string $sys_message, string $usr_message): array
    {
        return [
            [
            'role' => 'system',
            'content' => $sys_message,
            ],[
                'role' => 'user',
                'content' => $usr_message,
            ]
        ];

    }

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function send(array $message, string $model): string
    {

        try{
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(60)
                ->post("https://api.openai.com/v1/chat/completions", [
                    'model' => $model,
                    'messages' => $message,
                ])
                ->json();

            return $response['choices'][0]['message']['content']
                ?? throw new \Exception("Unexpected response from the AI service.");

        } catch (\Throwable $e) {
            Log::error("Exception while calling OpenAI: " . $e->getMessage());
            throw new \Exception("An error occurred while processing your request. Please try again later.");
        }

    }

}
