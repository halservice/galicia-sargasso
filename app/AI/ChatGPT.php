<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ChatGPT
{
    protected array $messages = [];

    public function systemMessage(string $message): static
    {
        $this->messages[] = [
            'role' => 'system',
            'content' => $message,
        ];

        return $this;
    }

    /**
     * @throws ConnectionException
     */
    public function send(string $message): string
    {
        try {
            $this->messages[] = [
                'role' => 'user',
                'content' => $message,
            ];

            ds('Sending request with messages:', $this->messages);


            $response = Http::withToken(config('services.openai.api_key'))
                ->post("https://api.openai.com/v1/chat/completions", [
                    'model' => "gpt-4",
                    'messages' => $this->messages,
                ])
                ->json();

            $responseContent = $response['choices'][0]['message']['content'];
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $responseContent,
            ];

            return $responseContent;

        } catch (\Exception $e) {
            ds('Error in ChatGPT:', $e->getMessage());
            throw $e;
        }
    }

}
