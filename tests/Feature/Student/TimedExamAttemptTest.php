<?php

namespace Tests\Feature\Student;

use App\Models\Attempt;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimedExamAttemptTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligible_student_can_start_an_attempt(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $this->actingAs($student)
            ->post(
                route(
                    'student.exams.attempts.store',
                    $exam,
                ),
            )
            ->assertRedirect();

        $attempt = Attempt::query()->firstOrFail();

        $this->assertSame(
            $student->id,
            $attempt->user_id,
        );

        $this->assertSame(
            $exam->id,
            $attempt->exam_id,
        );

        $this->assertSame(
            'in_progress',
            $attempt->status,
        );

        $this->assertTrue(
            $attempt->expires_at->equalTo(
                $attempt->started_at->copy()->addMinutes(
                    $exam->duration_minutes,
                ),
            ),
        );
    }

    public function test_student_gets_only_one_attempt_per_exam(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $this->actingAs($student)
            ->post(
                route(
                    'student.exams.attempts.store',
                    $exam,
                ),
            );

        $this->actingAs($student)
            ->post(
                route(
                    'student.exams.attempts.store',
                    $exam,
                ),
            );

        $this->assertDatabaseCount('attempts', 1);
    }

    public function test_ineligible_student_cannot_start_exam(): void
    {
        [$eligibleStudent, $exam] =
            $this->createEligibleExam();

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $exam->created_by,
        ]);

        $otherStudent = User::factory()
            ->student()
            ->create([
                'classroom_id' => $otherClassroom->id,
            ]);

        $this->actingAs($otherStudent)
            ->post(
                route(
                    'student.exams.attempts.store',
                    $exam,
                ),
            )
            ->assertForbidden();

        $this->assertDatabaseMissing('attempts', [
            'user_id' => $otherStudent->id,
            'exam_id' => $exam->id,
        ]);
    }

    public function test_student_cannot_view_another_students_attempt(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $attempt = $this->startAttempt(
            $student,
            $exam,
        );

        $otherStudent = User::factory()
            ->student()
            ->create();

        $this->actingAs($otherStudent)
            ->get(
                route(
                    'student.attempts.show',
                    $attempt,
                ),
            )
            ->assertForbidden();
    }

    public function test_student_can_save_answers_before_deadline(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        [$multipleChoice, $openText, $correctOption] =
            $this->addQuestions($exam);

        $attempt = $this->startAttempt(
            $student,
            $exam,
        );

        $this->actingAs($student)
            ->put(
                route(
                    'student.attempts.update',
                    $attempt,
                ),
                [
                    'action' => 'save',
                    'option_answers' => [
                        $multipleChoice->id =>
                            $correctOption->id,
                    ],
                    'text_answers' => [
                        $openText->id =>
                            'My written answer',
                    ],
                ],
            )
            ->assertRedirect(
                route(
                    'student.attempts.show',
                    $attempt,
                ),
            );

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $multipleChoice->id,
            'question_option_id' => $correctOption->id,
        ]);

        $this->assertDatabaseHas('answers', [
            'attempt_id' => $attempt->id,
            'question_id' => $openText->id,
            'text_answer' => 'My written answer',
        ]);

        $this->assertSame(
            'in_progress',
            $attempt->fresh()->status,
        );
    }

    public function test_option_from_another_question_is_rejected(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        [$firstQuestion] = $this->addQuestions($exam);

        $secondQuestion = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'position' => 3,
        ]);

        $wrongOption = $secondQuestion
            ->options()
            ->create([
                'text' => 'Wrong question option',
                'is_correct' => true,
            ]);

        $attempt = $this->startAttempt(
            $student,
            $exam,
        );

        $this->actingAs($student)
            ->put(
                route(
                    'student.attempts.update',
                    $attempt,
                ),
                [
                    'action' => 'save',
                    'option_answers' => [
                        $firstQuestion->id =>
                            $wrongOption->id,
                    ],
                ],
            )
            ->assertSessionHasErrors(
                "option_answers.{$firstQuestion->id}",
            );

        $this->assertDatabaseMissing('answers', [
            'attempt_id' => $attempt->id,
            'question_option_id' => $wrongOption->id,
        ]);
    }

    public function test_student_can_submit_before_deadline(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $attempt = $this->startAttempt(
            $student,
            $exam,
        );

        $this->actingAs($student)
            ->put(
                route(
                    'student.attempts.update',
                    $attempt,
                ),
                [
                    'action' => 'submit',
                ],
            )
            ->assertSessionHas(
                'success',
                'Exam submitted successfully.',
            );

        $attempt->refresh();

        $this->assertSame(
            'submitted',
            $attempt->status,
        );

        $this->assertNotNull(
            $attempt->submitted_at,
        );

        $this->actingAs($student)
            ->put(
                route(
                    'student.attempts.update',
                    $attempt,
                ),
                [
                    'action' => 'save',
                ],
            )
            ->assertSessionHas(
                'error',
                'This attempt can no longer be modified.',
            );
    }

    public function test_expired_attempt_cannot_be_modified(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $attempt = Attempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(10),
            'expires_at' => now()->subMinute(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($student)
            ->put(
                route(
                    'student.attempts.update',
                    $attempt,
                ),
                [
                    'action' => 'submit',
                ],
            )
            ->assertSessionHas(
                'error',
                'The exam time has expired.',
            );

        $attempt->refresh();

        $this->assertSame(
            'expired',
            $attempt->status,
        );

        $this->assertNotNull(
            $attempt->submitted_at,
        );
    }

    public function test_command_expires_abandoned_attempts(): void
    {
        [$student, $exam] = $this->createEligibleExam();

        $attempt = Attempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(10),
            'expires_at' => now()->subMinute(),
            'status' => 'in_progress',
        ]);

        $this->artisan('attempts:expire')
            ->assertSuccessful();

        $attempt->refresh();

        $this->assertSame(
            'expired',
            $attempt->status,
        );

        $this->assertTrue(
            $attempt->submitted_at->equalTo(
                $attempt->expires_at,
            ),
        );
    }

    private function createEligibleExam(): array
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

        $classroom->subjects()->attach($subject);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'duration_minutes' => 30,
            'published_at' => now(),
        ]);

        $exam->classrooms()->attach($classroom);

        return [$student, $exam];
    }

    private function addQuestions(Exam $exam): array
    {
        $multipleChoice = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'position' => 1,
            'marks' => 2,
        ]);

        $incorrectOption = $multipleChoice
            ->options()
            ->create([
                'text' => 'Incorrect answer',
                'is_correct' => false,
            ]);

        $correctOption = $multipleChoice
            ->options()
            ->create([
                'text' => 'Correct answer',
                'is_correct' => true,
            ]);

        $openText = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'position' => 2,
            'marks' => 5,
        ]);

        return [
            $multipleChoice,
            $openText,
            $correctOption,
        ];
    }

    private function startAttempt(
        User $student,
        Exam $exam,
    ): Attempt {
        $this->actingAs($student)
            ->post(
                route(
                    'student.exams.attempts.store',
                    $exam,
                ),
            );

        return Attempt::query()
            ->where('user_id', $student->id)
            ->where('exam_id', $exam->id)
            ->firstOrFail();
    }
}