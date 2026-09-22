<?php

namespace App\Agents;

use App\Services\OllamaService;

class WriterAgent
{
    public function __construct(protected OllamaService $ollama) {
    
    }

    public function handle(string $task, array $plan): string
    {
        $planJson = json_encode(
            $plan,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        $prompt = <<<PROMPT
Eres un experto en redacción técnica.

Usa el siguiente plan para resolver la tarea.

PLAN:
{$planJson}

TAREA:
{$task}

Genera una respuesta clara, estructurada y profesional.
PROMPT;

        return $this->ollama->generate($prompt);
    }
}