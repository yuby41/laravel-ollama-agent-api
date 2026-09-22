<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;

class PlannerController extends Controller
{
    public function __invoke(Request $request, OllamaService $ollama)
    {
        $task = $request->input('task');
    
        $prompt = 'RESPONDE SOLO JSON {"steps":["uno","dos"]}';
    
        $response = Http::post('http://host.docker.internal:11434/api/generate', [
            'model' => 'codeqwen',
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => 0
            ]
        ]);
    
        $decoded = json_decode($response->json()['response'], true);
    
        return response()->json($decoded);
    }
}
