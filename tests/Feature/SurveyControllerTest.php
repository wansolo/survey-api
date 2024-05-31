<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SurveyControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function test_store_creates_a_survey_with_questions()
    {
        // Assuming you have a User factory and Survey factory set up.
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'title' => 'Customer Satisfaction Survey',
            'questions' => [
                [
                    'question_text' => 'How satisfied are you with our service?',
                    'question_type' => 'multiple_choice',
                    'options' => ['Very satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very dissatisfied'],
                ],
                [
                    'question_text' => 'Any suggestions?',
                    'question_type' => 'text',
                    'options' => null,
                ],
            ],
        ];

        $response = $this->postJson('/api/surveys', $requestData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'title',
            'questions' => [
                '*' => [
                    'id',
                    'survey_id',
                    'question',
                    'type',
                    'options',
                ],
            ],
        ]);

        $this->assertDatabaseHas('surveys', ['title' => 'Customer Satisfaction Survey']);
        $this->assertDatabaseHas('questions', ['question' => 'How satisfied are you with our service?']);
        $this->assertDatabaseHas('questions', ['question' => 'Any suggestions?']);
    }

    public function test_store_requires_title()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'questions' => [
                [
                    'question_text' => 'How satisfied are you with our service?',
                    'question_type' => 'multiple_choice',
                    'options' => ['Very satisfied', 'Satisfied', 'Neutral', 'Dissatisfied', 'Very dissatisfied'],
                ],
            ],
        ];

        $response = $this->postJson('/api/surveys', $requestData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_store_requires_questions()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $requestData = [
            'title' => 'Customer Satisfaction Survey',
        ];

        $response = $this->postJson('/api/surveys', $requestData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['questions']);
    }
}
