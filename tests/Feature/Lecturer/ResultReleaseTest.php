<?php

namespace Tests\Feature\Lecturer;

use App\Models\Attempt;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_without_attempts_cannot_release_results(): void
    {
        [$lecturer, $exam] = $this->createExam();

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.results.release',
                    $exam,
                ),
            )
            ->assertSessionHas(
                'error',
                'Results cannot be released because no attempts exist.',
            );

        $this->assertNull(
            $exam->fresh()->results_released_at,
        );
    }

    public function test_results_cannot_be_released_while_attempt_is_in_progress(): void
    {
        [$lecturer, $exam] = $this->createExam();

        $student = User::factory()
            ->student()
            ->create();

        $this->createAttempt(
            $student,
            $exam,
            'in_progress',
            'pending',
        );

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.results.release',
                    $exam,
                ),
            )
            ->assertSessionHas(
                'error',
                'Results cannot be released while an attempt is in progress.',
            );
    }

    public function test_results_cannot_be_released_before_grading_is_complete(): void
    {
        [$lecturer, $exam] = $this->createExam();

        $student = User::factory()
            ->student()
            ->create();

        $this->createAttempt(
            $student,
            $exam,
            'submitted',
            'awaiting_manual',
        );

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.results.release',
                    $exam,
                ),
            )
            ->assertSessionHas(
                'error',
                'Complete all grading before releasing results.',
            );
    }

    public function test_lecturer_can_release_fully_graded_results(): void
    {
        [$lecturer, $exam] = $this->createExam();

        $student = User::factory()
            ->student()
            ->create();

        $this->createAttempt(
            $student,
            $exam,
            'submitted',
            'graded',
            8,
        );

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.results.release',
                    $exam,
                ),
            )
            ->assertSessionHas(
                'success',
                'Results released successfully.',
            );

        $this->assertNotNull(
            $exam->fresh()->results_released_at,
        );
    }

    public function test_result_release_is_irreversible(): void
    {
        [$lecturer, $exam] = $this->createExam([
            'results_released_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->post(
                route(
                    'lecturer.results.release',
                    $exam,
                ),
            )
            ->assertForbidden();
    }

    public function test_other_lecturer_cannot_manage_results(): void
    {
        [$lecturer, $exam] = $this->createExam();

        $otherLecturer = User::factory()
            ->lecturer()
            ->create();

        $this->actingAs($otherLecturer)
            ->get(
                route(
                    'lecturer.results.show',
                    $exam,
                ),
            )
            ->assertForbidden();
    }

    private function createExam(
        array $attributes = [],
    ): array {
        $lecturer = User::factory()
            ->lecturer()
            ->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create(array_merge([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'published_at' => now(),
            'results_released_at' => null,
        ], $attributes));

        return [$lecturer, $exam];
    }

    private function createAttempt(
        User $student,
        Exam $exam,
        string $status,
        string $gradingStatus,
        ?float $score = null,
    ): Attempt {
        return Attempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'started_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(10),
            'submitted_at' => $status === 'in_progress'
                ? null
                : now()->subMinutes(10),
            'status' => $status,
            'grading_status' => $gradingStatus,
            'score' => $score,
        ]);
    }
}