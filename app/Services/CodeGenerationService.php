<?php

namespace App\Services;

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\UserSetting;
use App\Models\GeneratedFormalModel;

class CodeGenerationService
{
    public function generateCode(string $text, bool $checkPrompt, array &$conversationThread, UserSetting $settings, bool $startFromCode): string
    {
        $coder = match ($settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT(),
        };

        $model = $settings->llm_code;

        $systemMessage = $this->systemMessage($settings, $startFromCode, $checkPrompt);

        if ($checkPrompt) {
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

            return $coder->send($conversationThread, $model);
        }

        $message = $coder->systemMessage($systemMessage, $text);

        return $coder->send($message, $model);
    }

    private function systemMessage(UserSetting $settings, bool $startFromCode, bool $checkPrompt): string
    {
        $language = $settings->programming_language;

        if (!$startFromCode) {
            return "You are an expert programmer.
            Generate clean and secure code based on user requirements and given formal model, using the following programming language: $language.
            - The formal model is a guideline, but the code should be as simple and direct as possible.
            - DO NOT introduce state variables, flags, or additional logic unless required.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.";
        }

        if ($checkPrompt) {
            return "You are an expert programmer.
            Generate clean and secure code based on user requirements, using the following programming language $language.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.

            Handling unclear requests:
            If the user request is ambiguous or lacks necessary details, DO NOT generate ANY code section. Instead, ask for clarification by specifying what additional information is needed.
            When asking for clarification:
            - Use the same language as the user request.
            - Focus on practical aspects needed to implement the functionality
            - Avoid technical questions unless necessary. Keep your questions simple and relevant to the core functionality.
            - Your clarification requests should be based on general knowledge and should not assume the user has programming expertise.
            - Do not ask for details that can reasonably be assumed.
            - Only if the user explicitly ask for a code in a language different than the programming language in the prompt $language. Explain you only use $language unless changed in the settings page.
            Format your clarification request as follows:
            - Always start with '**Requesting new info**:'
            - [List the missing details]
            - Always end with '**Please, use the new revised prompt and modify it.**'
            If clarification is needed, provide a sample revised prompt for the user to follow. Format it as follows:
            - Start with 'Start sample prompt'
            - Include a modified prompt of the user request that incorporates all the missing details. Make sure to NOT modify the data already given by the user. If the user has to add something use [] to encapsulate the placeholders.
            - End with 'End sample prompt'
            This way, the user can easily adjust their request based on your suggestions.";
        }

        return "You are an expert programmer.
            Generate clean and secure code based on user requirements, using the following programming language $language.
            - If a requirement can be implemented with a direct function, prefer that approach.
            - You should only write the requested function(s), without a `main` function (unless explicitly required in the prompt)  or test cases.
            - You must provide the code within appropriate code blocks, with no explanations.
            - Format your response using markdown.";
    }
}
