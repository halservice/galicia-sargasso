<?php

namespace App\Http\Controllers;

use App\AI\ChatGPT;
use App\AI\LLama;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class GenerateController extends Controller
{
//    /**
//     * @throws ConnectionException
//     */
//    public function __invoke(Request $request)
//    {
//        $validated = $request->validate([
//            'user_input' => 'required|string',
//            'programming_language' => 'required|string',
//            'llm_code' => 'required|string',
//        ]);
//
//        $code = match ($validated['llm_code']) {
//            'llm_code' => $this->llama($validated),
//            default => $this->chatGPT($validated)
//        };
//
//        return response()->json(['message' => $code]);
//    }
//
//    /**
//     * @param array<string, mixed> $validated
//     * @throws ConnectionException
//     */
//    protected function llama(array $validated): string
//    {
//        $chat = new LLama();
//
//        return $chat
//            ->systemMessage("You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language " . $validated['programming_language'] . ". You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown.")
//            ->send($validated['user_input']);
//    }
//
//    /**
//     * @param array<string, mixed> $validated
//     * @return string
//     *
//     * @throws ConnectionException
//     */
//    protected function chatGPT(array $validated): string
//    {
//        $chat = new ChatGPT();
//
//        return $chat
//            ->systemMessage("You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language " . $validated['programming_language'] . ". You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown. ")
//            ->send($validated['user_input']);
//    }
}
