# Laravel Ollama Agent API

[![CI](https://github.com/yuby41/laravel-ollama-agent-api/actions/workflows/ci.yml/badge.svg)](https://github.com/yuby41/laravel-ollama-agent-api/actions/workflows/ci.yml)

A local AI agent API built with **Laravel 12**, **PHP 8.3**, and **Ollama**.

The project implements a simple two-agent pipeline where a planner generates a structured execution plan and a writer uses that plan to produce the final response through a locally running LLM.

The main goal of the project is to explore practical LLM integration from a backend engineering perspective: API design, agent orchestration, structured outputs, error handling, automated testing, and CI.

## Architecture

```text
Client
  |
  | POST /api/task
  v
Laravel API
  |
  | Request validation
  v
PlannerAgent
  |
  | generateJson()
  v
OllamaService
  |
  | HTTP
  v
Ollama / Local LLM
  |
  | Structured JSON plan
  v
PlannerAgent
  |
  | plan
  v
WriterAgent
  |
  | generate()
  v
OllamaService
  |
  | HTTP
  v
Ollama / Local LLM
  |
  | Final text response
  v
JSON API Response
```

## Features

* Local LLM integration through Ollama
* Planner / Writer agent orchestration
* Structured JSON generation for agent communication
* Laravel dependency injection
* Request validation
* Dedicated service layer for LLM communication
* Custom exception handling for connection, HTTP, response and JSON parsing failures
* Configurable Ollama URL and model
* Unit and feature tests
* Mocked Ollama HTTP responses with `Http::fake()`
* Continuous Integration with GitHub Actions

## Tech Stack

| Technology          | Purpose                    |
| ------------------- | -------------------------- |
| PHP 8.3             | Backend language           |
| Laravel 12          | API framework              |
| Ollama              | Local LLM runtime          |
| CodeQwen            | Default local coding model |
| PHPUnit             | Automated testing          |
| Laravel HTTP Client | Ollama communication       |
| GitHub Actions      | Continuous Integration     |
| SQLite              | Test environment           |

## API

### Execute a task

```http
POST /api/task
Content-Type: application/json
```

Example request:

```json
{
  "task": "Explain briefly what a REST API is"
}
```

The request passes through the following pipeline:

```text
Task
  -> validation
  -> PlannerAgent
  -> structured plan
  -> WriterAgent
  -> final response
```

Example response structure:

```json
{
  "plan": {
    "steps": [
      "Analyse the task",
      "Prepare the response",
      "Validate the result"
    ]
  },
  "result": "Generated response..."
}
```

The exact generated content depends on the local model configured in Ollama.

## Ollama Service

`OllamaService` provides two different contracts for interacting with the model.

### Text generation

```php
$ollama->generate($prompt);
```

Returns:

```text
string
```

### Structured generation

```php
$ollama->generateJson($prompt);
```

Returns:

```text
array
```

`generateJson()` reuses the normal generation pipeline and validates the model output using PHP JSON exceptions.

This separation allows agents to explicitly request either free-form text or structured data.

## Error Handling

Infrastructure errors from the Laravel HTTP client are translated into application-specific exceptions:

```text
ConnectionException
        |
        v
OllamaConnectionException

RequestException
        |
        v
OllamaRequestException

Unexpected response structure
        |
        v
OllamaInvalidResponseException

JsonException
        |
        v
OllamaParsingException
```

This keeps the agent layer independent from the underlying HTTP client implementation.

## Installation

Clone the repository:

```bash
git clone git@github.com:yuby41/laravel-ollama-agent-api.git
cd laravel-ollama-agent-api
```

Install PHP dependencies:

```bash
composer install
```

Create the environment configuration:

```bash
cp .env.example .env
php artisan key:generate
```

Configure Ollama in `.env`:

```env
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=codeqwen
```

The URL may need to be changed depending on whether Laravel and Ollama are running directly on the host or inside containers.

Start Ollama and make sure the configured model is available.

Then start Laravel:

```bash
php artisan serve
```

## Testing

The project includes unit and feature tests.

Run the complete test suite with:

```bash
php artisan test
```

The Ollama integration is mocked during automated tests:

```php
Http::fake([
    '*' => Http::response([
        'response' => 'Hello from Ollama',
        'done' => true,
    ], 200),
]);
```

This means the test suite does **not require a running Ollama server or a downloaded LLM**.

The current tests cover:

* text generation
* structured JSON generation
* unexpected Ollama response structures
* invalid model-generated JSON
* API request validation

## Continuous Integration

GitHub Actions automatically runs the test suite on:

```text
push
pull_request
```

The CI environment installs PHP 8.3 and the required SQLite extensions, installs Composer dependencies, prepares the Laravel environment and executes:

```bash
php artisan test
```

Because Ollama is mocked in the tests, the CI pipeline remains independent from local model infrastructure.

## Project Structure

```text
app/
├── Agents/
│   ├── PlannerAgent.php
│   └── WriterAgent.php
│
├── Exceptions/
│   ├── OllamaConnectionException.php
│   ├── OllamaInvalidResponseException.php
│   ├── OllamaParsingException.php
│   └── OllamaRequestException.php
│
└── Services/
    └── OllamaService.php

tests/
├── Feature/
│   └── TaskApiTest.php
└── Unit/
    └── OllamaServiceTest.php

.github/
└── workflows/
    └── ci.yml
```

## Design Decisions

### Local-first LLM execution

Ollama allows the application to interact with an LLM locally without coupling the application to a specific external AI API.

### Separate text and JSON contracts

Free-form generation and structured generation are exposed through separate service methods:

```text
generate()      -> string
generateJson()  -> array
```

This makes the expected output explicit for each agent.

### Application-specific exceptions

Laravel HTTP exceptions are translated into domain-specific Ollama exceptions before reaching higher application layers.

### Testable AI integration

LLM communication is isolated behind `OllamaService`, allowing the HTTP layer to be mocked during automated testing.

This makes the AI integration deterministic enough for conventional CI pipelines.

## Current Limitations

This project currently implements a simple two-agent pipeline. It does not yet provide:

* Retrieval-Augmented Generation (RAG)
* vector database integration
* persistent conversation memory
* model fallback
* asynchronous agent execution
* production observability

These are intentionally kept outside the current scope rather than being simulated or hidden behind placeholder implementations.

## Roadmap

Planned areas of experimentation include:

* RAG with document retrieval
* embeddings and vector storage
* structured output schema validation
* improved planner constraints
* dedicated orchestration layer
* containerized deployment
* logging and observability
* model fallback strategies
* additional CI checks

## Author

**Yualbert Luis**

Backend developer working with PHP/Laravel, Python, APIs, automation and local LLM integrations.

GitHub: `@yuby41`

## License

This project is intended primarily as an educational and portfolio project.
