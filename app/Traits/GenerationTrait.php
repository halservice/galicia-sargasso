<?php

namespace App\Traits;

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use Illuminate\Http\Client\ConnectionException;

trait GenerationTrait
{
    /**
     * @throws ConnectionException
     */
    protected function chat(array $params): array
    {
        $checkPrompt = $params['checkPrompt'];
        $startFromCode = $params['startFromCode'];
        $text = $params['text'];
        $settings = $params['settings'];
        $conversationThread = $params['conversationThread'] ?? [];
        $model = $params['model'];
        $systemMessage = $params['systemMessage'];

        // Create a new chat
        $coder = match ($settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT(),
        };

        if ($checkPrompt) {
            // If the request is to check the prompt then:
            // 1. If it's the first request then the $conversationThread is empty, first upload the $systemMessage
            // 2. Then always upload the new user request
            if (empty($conversationThread)) {
                $conversationThread[] = [
                    'role' => 'system',
                    'content' => $systemMessage
                ];
            }
            $conversationThread[] = [
                'role' => 'user',
                'content' => $text
            ];
            $response = $coder->send($conversationThread, $model);
        }else{
            $message = $coder->systemMessage($systemMessage, $text);
            $response = $coder->send($message, $model);
        }

        return [
            'response' => $response,
            'conversationThread' => $conversationThread
        ];
    }
}
