<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Agents\PlannerAgent;
use App\Agents\WriterAgent;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/planner', function (\Illuminate\Http\Request $request) {
    $planner = app(PlannerAgent::class);

    return $planner->handle($request->input('task'));
});


Route::post('/task', function (Request $request) {
    $validated = $request->validate([
        'task' => ['required', 'string', 'min:3', 'max:2000'],
    ]);

    $task = $validated['task'];

    $planner = app(PlannerAgent::class);
    $writer = app(WriterAgent::class);

    $plan = $planner->handle($task);
    $result = $writer->handle($task, $plan);

    return response()->json([
        'plan' => $plan,
        'result' => $result,
    ]);
});