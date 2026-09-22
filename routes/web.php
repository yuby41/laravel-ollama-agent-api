<?php

use Illuminate\Support\Facades\Route;
use App\Services\OllamaService;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-ollama', function () {
    return app(OllamaService::class)->generate("Di hola desde Laravel en español");
});


