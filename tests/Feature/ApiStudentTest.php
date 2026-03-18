<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

it('allows authenticated user to list students via API', function () {
    $user = User::factory()->create(['role' => 'admin']);
    Student::factory()->count(3)->create();

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->json('GET', '/api/v1/students', [], ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(200)
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('status', 'success')
                ->has('data.data', 3)
                ->etc()
        );
});

it('returns 401 for unauthenticated API access', function () {
    $response = $this->getJson('/api/v1/students');

    $response->assertStatus(401);
});
