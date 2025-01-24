<?php

namespace App\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class LLama
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

                $response = Http::withToken(config('services.mindinabox.api_key'))
                ->post("https://astro-llama.internal.mindinabox.io/v1/chat/completions", [
                    'model' => 'meta-llama/Llama-3.1-8B-Instruct',
                    'messages' => $this->messages,
                ])
                ->json();

//            /** @var Response $response */
//            $response = Http::mindinabox()->post('chat/completions', [
//                'messages' => $this->messages,
//            ]);

//            dd($response->json());
//            if ($response->failed()) {
//                return '';
//            }


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
