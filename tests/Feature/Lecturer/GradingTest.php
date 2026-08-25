<?php

namespace Tests\Feature\Lecturer;

use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use App\Services\AttemptGradingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_mcq_receives_full_marks(): void
    {
        [$student, $exam] = $this->createExam();

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'marks' => 4,
            'position' => 1,
        ]);

        $correctOption = $question
            ->options()
            ->create([
                'text' => 'Correct',
                'is_correct' => true,
            ]);

        $question->options()->create([
            'text' => 'Incorrect',
            'is_correct' => false,
        ]);

        $attempt = $this->createAttempt(
            $student,
            $exam,
        );

        $attempt->answers()->create([
            'question_id' => $question->id,
            'question_option_id' => $correctOption->id,
        ]);

        app(AttemptGradingService::class)
            ->finalizeAttempt(
                $attempt,
                'submitted',
            );

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'awarded_marks' => 4,
        ]);

        $attempt->refresh();

        $this->assertSame(
            'graded',
            $attempt->grading_status,
        );

        $this->assertSame(
            '4.00',
            $attempt->score,
        );
    }

    public function test_incorrect_mcq_receives_zero_marks(): void
    {
        [$student, $exam] = $this->createExam();

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'marks' => 4,
            'position' => 1,
        ]);

        $question->options()->create([
            'text' => 'Correct',
            'is_correct' => true,
        ]);

        $incorrectOption = $question
            ->options()
            ->create([
                'text' => 'Incorrect',
                'is_correct' => false,
            ]);

        $attempt = $this->createAttempt(
            $student,
            $exam,
        );

        $attempt->answers()->create([
            'question_id' => $question->id,
            'question_option_id' => $incorrectOption->id,
        ]);

        app(AttemptGradingService::class)
            ->finalizeAttempt(
                $attempt,
                'submitted',
            );

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'awarded_marks' => 0,
        ]);
    }

    public function test_answered_open_text_requires_manual_grading(): void
    {
        [$student, $exam] = $this->createExam();

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'marks' => 5,
            'position' => 1,
        ]);

        $attempt = $this->createAttempt(
            $student,
            $exam,
        );

        $attempt->answers()->create([
            'question_id' => $question->id,
            'text_answer' => 'My explanation',
        ]);

        app(AttemptGradingService::class)
            ->finalizeAttempt(
                $attempt,
                'submitted',
            );

        $attempt->refresh();

        $this->assertSame(
            'awaiting_manual',
            $attempt->grading_status,
        );

        $this->assertNull($attempt->score);
    }

    public function test_lecturer_can_grade_open_text_answer(): void
    {
        [$student, $exam, $lecturer] =
            $this->createExam();

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'marks' => 5,
            'position' => 1,
        ]);

        $attempt = $this->createAttempt(
            $student,
            $exam,
        );

        $answer = $attempt->answers()->create([
            'question_id' => $question->id,
            'text_answer' => 'My explanation',
        ]);

        app(AttemptGradingService::class)
            ->finalizeAttempt(
                $attempt,
                'submitted',
            );

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.grading.update',
                    $attempt,
                ),
                [
                    'marks' => [
                        $answer->id => 4,
                    ],
                ],
            )
            ->assertSessionHas(
                'success',
                'Manual grading saved successfully.',
            );

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'awarded_marks' => 4,
        ]);

        $attempt->refresh();

        $this->assertSame(
            'graded',
            $attempt->grading_status,
        );

        $this->assertSame(
            '4.00',
            $attempt->score,
        );
    }

    public function test_awarded_marks_cannot_exceed_question_marks(): void
    {
        [$student, $exam, $lecturer] =
            $this->createExam();

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'marks' => 5,
            'position' => 1,
        ]);

        $attempt = $this->createAttempt(
            $student,
            $exam,
        );

        $answer = $attempt->answers()->create([
            'question_id' => $question->id,
            'text_answer' => 'My explanation',
        ]);

        app(AttemptGradingService::class)
            ->finalizeAttempt(
                $attempt,
                'submitted',
            );

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.grading.update',
                    $attempt,
                ),
                [
                    'marks' => [
                        $answer->id => 6,
                    ],
                ],
            )
            ->assertSessionHasErrors(
                "marks.{$answer->id}",
            );
    }

    public function test_other_lecturer_cannot_grade_attempt(): void
    {
        [$student, $exam] = $this->createExam();

        $attempt = $this->createAttempt(
            $student,
            $exam,
            'submitted',
        );

        $otherLecturer = User::factory()
            ->lecturer()
            ->create();

        $this->actingAs($otherLecturer)
            ->get(
                route(
                    'lecturer.grading.show',
                    $attempt,
                ),
            )
            ->assertForbidden();
    }

    public function test_student_cannot_access_grading_pages(): void
    {
        [$student] = $this->createExam();

        $this->actingAs($student)
            ->get(route('lecturer.grading.index'))
            ->assertForbidden();
    }

    private function createExam(): array
    {
        $lecturer = User::factory()
            ->lecturer()
            ->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()
            ->student()
            ->create([
                'classroom_id' => $classroom->id,
            ]);

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'published_at' => now(),
        ]);

        $exam->classrooms()->attach($classroom);

        return [$student, $exam, $lecturer];
    }

    private function createAttempt(
        User $student,
        Exam $exam,
        string $status = 'in_progress',
    ): Attempt {
        return Attempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinute(),
            'expires_at' => now()->addMinutes(20),
            'submitted_at' =>
                $status === 'in_progress'
                    ? null
                    : now(),
            'status' => $status,
        ]);
    }
}