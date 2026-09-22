<?php

namespace Tests\Feature;

use Tests\TestCase;

class TaskApiTest extends TestCase
{
    public function test_task_is_required(): void
    {
        $response = $this->postJson('/api/task', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task']);
    }

    public function test_task_must_be_a_string(): void
    {
        $response = $this->postJson('/api/task', [
            'task' => ['invalid'],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task']);
    }

    public function test_task_must_have_minimum_length(): void
    {
        $response = $this->postJson('/api/task', [
            'task' => 'a',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['task']);
    }
}
