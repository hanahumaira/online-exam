<?php

namespace Tests\Feature\Student;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unreleased_result_is_not_listed(): void
    {
        [$student, $attempt] =
            $this->createResult(false);

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertDontSee(
                $attempt->exam->title,
            );
    }

    public function test_student_cannot_open_unreleased_result(): void
    {
        [$student, $attempt] =
            $this->createResult(false);

        $this->actingAs($student)
            ->get(
                route(
                    'student.results.show',
                    $attempt,
                ),
            )
            ->assertForbidden();
    }

    public function test_student_can_view_released_result(): void
    {
        [$student, $attempt, $question] =
            $this->createResult(true);

        $this->actingAs($student)
            ->get(
                route(
                    'student.results.show',
                    $attempt,
                ),
            )
            ->assertOk()
            ->assertSee($attempt->exam->title)
            ->assertSee('8.00')
            ->assertSee($question->prompt);
    }

    public function test_student_cannot_view_another_students_result(): void
    {
        [$student, $attempt] =
            $this->createResult(true);

        $otherStudent = User::factory()
            ->student()
            ->create();

        $this->actingAs($otherStudent)
            ->get(
                route(
                    'student.results.show',
                    $attempt,
                ),
            )
            ->assertForbidden();
    }

    public function test_ungraded_result_is_not_visible_even_if_exam_is_released(): void
    {
        [$student, $attempt] =
            $this->createResult(true);

        $attempt->update([
            'grading_status' => 'awaiting_manual',
            'score' => null,
        ]);

        $this->actingAs($student)
            ->get(
                route(
                    'student.results.show',
                    $attempt,
                ),
            )
            ->assertForbidden();
    }

    private function createResult(
        bool $released,
    ): array {
        $lecturer = User::factory()
            ->lecturer()
            ->create();

        $student = User::factory()
            ->student()
            ->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'title' => 'Result Test Exam',
            'published_at' => now()->subDay(),
            'results_released_at' => $released
                ? now()
                : null,
        ]);

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'prompt' => 'Explain your answer.',
            'marks' => 10,
            'position' => 1,
        ]);

        $attempt = Attempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subHour(),
            'expires_at' => now()->subMinutes(30),
            'submitted_at' => now()->subMinutes(30),
            'status' => 'submitted',
            'grading_status' => 'graded',
            'score' => 8,
        ]);

        $attempt->answers()->create([
            'question_id' => $question->id,
            'text_answer' => 'My answer',
            'awarded_marks' => 8,
        ]);

        return [
            $student,
            $attempt->load('exam'),
            $question,
        ];
    }
}