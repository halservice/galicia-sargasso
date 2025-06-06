<?php

namespace App\SystemMessages;

class ValidationMessages
{
    public function testCaseMessages(string $request, string $formalModel, string $generatedCode): array
    {
        $systemMessage = "Given a user request, the corresponding formal model and the corresponding program source code, generate a set of test cases that the code should pass if written correctly.
            Ensure coverage of edge cases, threshold values and security issues.
                Format the output ensuring:
                    1. No introductory phrases (e.g., 'Certainly,' 'Sure,' etc.). The response must not indicate that it is generated by an AI chatbot.
                    2. Each test case is clearly separated by an empty line.
                    3. Write in markdown format.";

        $userMessage = "Given the following request:'{$request}'\n
            And the formal model:\n{$formalModel}\n
            and the code:\n{$generatedCode}\n
            Could you generate a few test cases that the code should pass if written correctly?";

        return [
                [
                    'role' => 'system',
                    'content' => $systemMessage,
                ], [
                    'role' => 'user',
                    'content' => $userMessage,
                ]
            ];
    }
    public function systemMessage(string $programmingLanguage): string
    {
        return "Your job is to verify if the given source code complies with the given test cases.
         If the code does not pass all test cases, you must refine it to ensure it meets all the test cases requirements.
         **Process**:
         - Run the given test cases on the original code.
         - If a test passes, leave the code unchanged.
         - If a test fails, modify the code to make it pass while ensuring previously passed tests remain valid.
        **Rules:**
        - If the code passes all test cases but can be improved, apply minimal, non-intrusive refinements to make the code more efficient or readable.
        - Do not add initialization instructions in the source code that modify the value of input parameters, even if these instructions are part of the formal model.
        - Only fix mistakes in the requested function(s)—DO NOT add a `main`, additional functions, or any execution logic.
        - The function(s) must remain standalone. Assume they will be tested in an external environment.
        - If a test case is not applicable to the given code, discard it and specify why- DO NOT modify the code if the test case is not applicable.
        **Output format:**
        1. '### Test cases:' A brief explanation of each test result, specifying whether it passed or failed **on the original code**. If a test case is discarded, explain why. Leave an empty line after every test.
        2. '### Number of test failed:' An integer indicating how many test cases **failed in the original code** (0 if all tests passed without modifications).
        3. '### Validated code:' The refined code after resolving any failed tests, or the original code if all tests passed immediately. You must provide the code within appropriate code blocks and with the programming language {$programmingLanguage}. DO NOT add any comment in this section.
        4. '### Changes Made:' Listing any fixes or improvements in the code (or stating 'No changes needed.').";
    }
}
