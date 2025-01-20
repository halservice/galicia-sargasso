<?php

use App\AI\ChatGPT;
use App\AI\LLama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome',[
        'name' => 'Chiara',
    ]);
});

Route::get('/SourceCodeGeneration', function () {
    return view('sourceCodeGenerator');
});

Route::get('/FormalModelGeneration', function () {
    return view('formalModelGenerator');
});

Route::get('/CodeVerification', function () {
    return view('codeVerification');
});

Route::get('/Feedback', function () {
    return view('feedback');
});

Route::get('/Customization', function () {
    return view('customization');
});

Route::post('/generate', function (Request $request) {
    $validated = $request->validate([
        'user_input' => 'required|string',
        'programming_language' => 'required|string',
        'llm_code' => 'required|string',
    ]);

    if($validated['llm_code'] === 'chatgpt') {
        $chat = new ChatGPT();
        $code = $chat
            ->systemMessage("You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language " . $validated['programming_language'] . "You must provide only the code in appropriate code blocks, explanations aren't required. Format your response using markdown. ")
            ->send($validated['user_input']);
    } elseif ($validated['llm_code'] === 'llama') {
        $chat = new LLama();
        $code = $chat
            ->systemMessage("You are an expert programmer. Generate clean and secure code based on user requirements using the following programming language " . $validated['programming_language'] . "Always output code in appropriate code blocks with language specification. Format your response using markdown. You must provide only the code, explanations aren't required.")
            ->send($validated['user_input']);
    }

    return response()->json(['message' => $code]);

});
