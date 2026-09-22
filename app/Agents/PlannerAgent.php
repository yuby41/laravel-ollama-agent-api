<?php

namespace App\Agents;

use App\Services\OllamaService;

class PlannerAgent
{
    public function __construct(
        protected OllamaService $ollama
    ) {
    }

    public function handle(string $task): array
    {
        $prompt = <<<PROMPT
Eres un agente PLANIFICADOR.

Debes dividir una tarea en pasos.

RESPONDE ÚNICAMENTE en JSON válido.
NO escribas explicaciones.
NO escribas texto antes o después.
NO uses markdown.
NO uses ```.

Formato EXACTO:
{"steps":["paso 1","paso 2","paso 3"]}

Tarea:
{$task}
PROMPT;

        return $this->ollama->generateJson($prompt);
    }
}