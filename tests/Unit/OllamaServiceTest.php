<?php

namespace Tests\Unit;

use App\Exceptions\OllamaInvalidResponseException;
use App\Exceptions\OllamaParsingException;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaServiceTest extends TestCase
{
    public function test_generate_returns_text_from_ollama(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => 'Hello from Ollama',
                'done' => true,
            ], 200),
        ]);

        $service = app(OllamaService::class);

        $result = $service->generate('Say hello');

        $this->assertSame('Hello from Ollama', $result);
    }

    public function test_generate_json_returns_array(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => '{"steps":["analyse","implement","test"]}',
                'done' => true,
            ], 200),
        ]);

        $service = app(OllamaService::class);

        $result = $service->generateJson('Create a plan');

        $this->assertSame([
            'steps' => [
                'analyse',
                'implement',
                'test',
            ],
        ], $result);
    }

    public function test_generate_throws_exception_for_invalid_response_structure(): void
    {
        Http::fake([
            '*' => Http::response([
                'model' => 'codeqwen',
                'done' => true,
            ], 200),
        ]);

        $service = app(OllamaService::class);

        $this->expectException(OllamaInvalidResponseException::class);

        $service->generate('Say hello');
    }

    public function test_generate_json_throws_exception_for_invalid_json(): void
    {
        Http::fake([
            '*' => Http::response([
                'response' => 'this is not valid JSON',
                'done' => true,
            ], 200),
        ]);

        $service = app(OllamaService::class);

        $this->expectException(OllamaParsingException::class);

        $service->generateJson('Create a plan');
    }
}