<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

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
     */
    public function send(array $message): string
    {
        try{
            $response = Http::withToken(config('services.openai.api_key'))
                ->post("https://api.openai.com/v1/chat/completions", [
                    'model' => "gpt-4o-mini",
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
