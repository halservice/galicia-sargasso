<?php

namespace App\Actions;

use App\AI\ChatGPT;
use App\AI\LLama;
use App\Enums\LLM;
use App\Models\GeneratedCode;
use App\Models\GeneratedFormalModel;
use App\Models\GeneratedValidatedCode;
use App\Models\UserSetting;
use App\SystemMessages\ValidationMessages;
use App\Traits\CheckFailedTest;
use App\Traits\ExtractTestResultsTrait;
use App\Traits\ExtractValidatedCodeTrait;
use Illuminate\Http\Client\ConnectionException;

class CodeValidationAction
{
    use ExtractValidatedCodeTrait;
    use CheckFailedTest;
    use ExtractTestResultsTrait;

    public function __construct()
    {
        //
    }

    /**
     * @throws ConnectionException
     */
    public function __invoke(GeneratedCode $code, GeneratedFormalModel $formalModel, UserSetting $settings, bool $startFromCode): GeneratedValidatedCode
    {
        $iterations = $settings->iteration;
        $model = $settings->llm_validation;
        $currentCode = $code->generated_code;

        $coder = match ($settings->llm_validation) {
            LLM::Llama->value => new LLama(),
            default => new ChatGPT()
        };

        // Upload starting user input.
        if($startFromCode){
            $request = $code->requirement;
        }else{
            $request = $formalModel->requirement;
        }

        // First step: create the test cases to use for validating the formal model.
        // They must be generated with the generated code and the formal model.
        $validationMessages = new ValidationMessages();
        $message = $validationMessages->testCaseMessages($request, $formalModel->generated_formal_model,$currentCode);

        $testCases = $coder->send($message, $model);

        // Second step: validate the code with the given test cases.
        $systemMessage = $validationMessages->systemMessage($code->programming_language->value);
        $userMessage = "Verify this code:\n {$currentCode}.\n
                        Following the test cases:\n {$testCases}";

        $message = $coder->systemMessage($systemMessage, $userMessage);

        $messages = $message;
        $flag = false;
        for ($i = 1; $i <= $iterations && !$flag; $i++) {

            $response = $coder->send($message, $model);
            $messages[] = [
                'role' => 'assistant',
                'content' => $response,
            ];

            $flag = $this->checkFailed($response);
            $currentCode = $this->extractValidatedCodeFromResponse($response);
            if (!$flag && $i + 1 <= $iterations) {
                $messages[] = [
                    'role' => 'user',
                    'content' => "This is iteration $i. Verify this code:\n$currentCode.\n
                                    Following the test cases:\n$testCases."
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

        $testResults = $this->extractTestResult(end($messages)['content']);

        // Save the new generated code
        return GeneratedValidatedCode::log(
            $settings->startFromGeneratedCode() ? $formalModel : $code,
            $testCases,
            $testResults,
            $messages,
            $systemMessage,
            $currentCode,
        );
    }

    protected function checkFailed(string $response): bool
    {
        $number = $this->checkFailedTest($response);
        if($number === 0){
            return true;
        }

        return  false;
    }

}
