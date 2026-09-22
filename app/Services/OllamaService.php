<?php

namespace App\Services;

use App\Exceptions\OllamaConnectionException;
use App\Exceptions\OllamaInvalidResponseException;
use App\Exceptions\OllamaParsingException;
use App\Exceptions\OllamaRequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;

class OllamaService
{
    protected string $baseUrl;
    protected string $defaultModel;

    public function __construct()
    {
        $this->baseUrl = config(
            'services.ollama.url',
            'http://host.docker.internal:11434'
        );

        $this->defaultModel = config(
            'services.ollama.model',
            'codeqwen'
        );
    }

    /**
     * Generate a plain-text response from Ollama.
     */
    public function generate(
        string $prompt,
        string $model = null
    ): string {
        try {
            $response = Http::timeout(120)
                ->post($this->baseUrl . '/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => [
                        'temperature' => 0,
                    ],
                ])
                ->throw();

        } catch (ConnectionException $e) {
            throw new OllamaConnectionException(
                'No se pudo conectar con Ollama.',
                previous: $e
            );

        } catch (RequestException $e) {
            throw new OllamaRequestException(
                'Ollama devolvió un error HTTP.',
                previous: $e
            );
        }

        $data = $response->json();

        if (
            !is_array($data) ||
            !array_key_exists('response', $data) ||
            !is_string($data['response'])
        ) {
            throw new OllamaInvalidResponseException(
                'La respuesta de Ollama tiene un formato inesperado.'
            );
        }

        return $data['response'];
    }

    /**
     * Generate a response from Ollama and decode it as JSON.
     */
    public function generateJson(
        string $prompt,
        string $model = null
    ): array {
        $text = $this->generate($prompt, $model);

        try {
            return json_decode(
                $text,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

        } catch (JsonException $e) {
            throw new OllamaParsingException(
                'Ollama devolvió un JSON inválido.',
                previous: $e
            );
        }
    }
}