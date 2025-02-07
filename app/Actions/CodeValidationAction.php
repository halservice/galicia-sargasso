<?php

namespace App\Actions;

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Settings\CodeGeneratorSettings;
use App\Traits\ExtractCodeTrait;
use Illuminate\Http\Client\ConnectionException;

class CodeValidationAction
{
    use ExtractCodeTrait;

    public function __construct(
        private readonly CodeGeneratorSettings $settings,
//        private readonly ResetGeneratorsAction $resetGeneratorsAction,
    )
    {
        //
    }

    /**
     * @throws ConnectionException
     */
    public function __invoke(GeneratedCode $code, GeneratedFormalModel $formalModel): GeneratedValidatedCode
    {
        $iterations = $this->settings->iteration;

        $coder = match ($this->settings->llm_code) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

//        ($this->resetGeneratorsAction)();

        $systemMessage = "Your job is to validate a source code given the formal model. You must refine the code following the specification of the formal model. First of all you have to generate the new validated code '### Validated code:'. After you should briefly summon the changes you have done in '### Changes Made:'. Lastly you must print '### Number of changes made:' and specify an integer that could be 0 if there are no changes.";
        $userMessage = "Validate this code {$code->generated_code} following the formal model {$formalModel->generated_formal_model}";

        $currentCode = $code->generated_code;
        $message = $coder->systemMessage($systemMessage, $userMessage);
        $messages = $message;

        $flag = false;
        for ($i = 1; $i <= $iterations && $flag === false; $i++) {
//            $this->req = "Validating the code... Iteration: $i/$iterations";
//            $this->stream(to: 'req', content: $this->req);

            $response = $coder->send($message);

            $messages[] = [
                'role' => 'assistant',
                'content' => $response,
            ];

            $flag = $this->checkChanges($response);
            $currentCode = $this->extractCodeFromResponse($response);

            if (!$flag && $i + 1 <= $iterations) {
                $messages[] = [
                    'role' => 'user',
                    'content' => "Here is the updated code after iteration $i: $currentCode. Please, validate the code again following the formal model {$formalModel->generated_formal_model}."
                ];
                $message = [
                    [
                        'role' => 'system',
                        'content' => $systemMessage,
                    ], [
                        'role' => 'user',
                        'content' => end($messages)['content'],
                    ]
                ];

            }

        }

        $checkSystemMessage = "Your task is to verify whether a set of test cases, generated from a formal model, are correctly handled in the code. While execution is not possible, analyze the logic to determine whether they are likely to pass or fail.";
        $checkUserMessage = "Here is the code $currentCode and here are the tests {$formalModel->test_case}.";
        $checkTest = $coder->systemMessage($checkSystemMessage, $checkUserMessage);
        $checkTest = $coder->send($checkTest);

        return GeneratedValidatedCode::log(
            $this->settings->startFromGeneratedCode() ? $formalModel : $code,
            $checkTest,
            $messages,
            $systemMessage,
            $currentCode,
        );
    }

    protected function checkChanges(string $response): bool
    {
        if (preg_match('/Number of changes made:\s*(\d+)/i', $response, $matches)) {
            $number = (int)trim($matches[1]);
            if ($number === 0) {
                return true;
            }
        }

        return false;
    }
}
