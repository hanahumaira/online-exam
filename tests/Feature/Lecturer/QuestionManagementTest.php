<?php

namespace Tests\Feature\Lecturer;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_question_management(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $this->get(
            route(
                'lecturer.exams.questions.create',
                $exam,
            ),
        )->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_question_management(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $student = User::factory()->student()->create();

        $exam = $this->createExamFor($lecturer);

        $this->actingAs($student)
            ->get(
                route(
                    'lecturer.exams.questions.create',
                    $exam,
                ),
            )
            ->assertForbidden();
    }

    public function test_lecturer_can_open_question_creation_page(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.questions.create',
                    $exam,
                ),
            )
            ->assertOk()
            ->assertSee('Add Question')
            ->assertSee($exam->title);
    }

    public function test_lecturer_can_create_open_text_question(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $response = $this
            ->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.exams.questions.store',
                    $exam,
                ),
                [
                    'type' => 'open_text',
                    'prompt' => 'Explain polymorphism.',
                    'marks' => 10,
                    'options' => ['', '', '', ''],
                ],
            );

        $response
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            )
            ->assertSessionHas(
                'success',
                'Question created successfully.',
            );

        $this->assertDatabaseHas('questions', [
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'prompt' => 'Explain polymorphism.',
            'marks' => 10,
            'position' => 1,
        ]);

        $this->assertDatabaseCount(
            'question_options',
            0,
        );
    }

    public function test_lecturer_can_create_multiple_choice_question(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $response = $this
            ->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.exams.questions.store',
                    $exam,
                ),
                [
                    'type' => 'multiple_choice',
                    'prompt' => 'What is 2 + 2?',
                    'marks' => 5,
                    'options' => [
                        '2',
                        '3',
                        '4',
                        '5',
                    ],
                    'correct_option' => 2,
                ],
            );

        $response->assertRedirect(
            route('lecturer.exams.show', $exam),
        );

        $question = Question::query()
            ->where('exam_id', $exam->id)
            ->firstOrFail();

        $this->assertDatabaseCount(
            'question_options',
            4,
        );

        $this->assertDatabaseHas('question_options', [
            'question_id' => $question->id,
            'text' => '4',
            'is_correct' => true,
        ]);
    }

    public function test_question_fields_are_validated(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.exams.questions.store',
                    $exam,
                ),
                [
                    'type' => 'invalid',
                    'prompt' => '',
                    'marks' => 0,
                ],
            )
            ->assertSessionHasErrors([
                'type',
                'prompt',
                'marks',
            ]);
    }

    public function test_multiple_choice_requires_options_and_correct_answer(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.exams.questions.store',
                    $exam,
                ),
                [
                    'type' => 'multiple_choice',
                    'prompt' => 'Incomplete question',
                    'marks' => 5,
                    'options' => [
                        'Only one option',
                        '',
                        '',
                        '',
                    ],
                ],
            )
            ->assertSessionHasErrors([
                'options.1',
                'correct_option',
            ]);
    }

    public function test_lecturer_cannot_manage_questions_in_another_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $otherExam = $this->createExamFor(
            $otherLecturer,
        );

        $question = Question::factory()->create([
            'exam_id' => $otherExam->id,
            'position' => 1,
        ]);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.questions.create',
                    $otherExam,
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.questions.edit',
                    [$otherExam, $question],
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(
                route(
                    'lecturer.exams.questions.destroy',
                    [$otherExam, $question],
                ),
            )
            ->assertForbidden();
    }

    public function test_published_exam_questions_cannot_be_changed(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor(
            $lecturer,
            ['published_at' => now()],
        );

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'position' => 1,
        ]);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.questions.create',
                    $exam,
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.questions.edit',
                    [$exam, $question],
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(
                route(
                    'lecturer.exams.questions.destroy',
                    [$exam, $question],
                ),
            )
            ->assertForbidden();
    }

    public function test_multiple_choice_can_be_changed_to_open_text(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'position' => 1,
        ]);

        $question->options()->createMany([
            [
                'text' => 'Option A',
                'is_correct' => true,
            ],
            [
                'text' => 'Option B',
                'is_correct' => false,
            ],
        ]);

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.exams.questions.update',
                    [$exam, $question],
                ),
                [
                    'type' => 'open_text',
                    'prompt' => 'Explain your answer.',
                    'marks' => 10,
                    'options' => ['', '', '', ''],
                ],
            )
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            );

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'type' => 'open_text',
            'prompt' => 'Explain your answer.',
        ]);

        $this->assertDatabaseMissing(
            'question_options',
            ['question_id' => $question->id],
        );
    }

    public function test_lecturer_can_delete_draft_question(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'position' => 1,
        ]);

        $this->actingAs($lecturer)
            ->delete(
                route(
                    'lecturer.exams.questions.destroy',
                    [$exam, $question],
                ),
            )
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            );

        $this->assertDatabaseMissing('questions', [
            'id' => $question->id,
        ]);
    }

    private function createExamFor(
        User $lecturer,
        array $attributes = [],
    ): Exam {
        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        return Exam::factory()->create(array_merge([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'published_at' => null,
        ], $attributes));
    }
}
