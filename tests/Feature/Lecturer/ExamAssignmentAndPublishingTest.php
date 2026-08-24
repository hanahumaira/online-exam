<?php

namespace Tests\Feature\Lecturer;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAssignmentAndPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_manage_exam_assignments(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $this->get(
            route('lecturer.exams.classrooms.edit', $exam),
        )->assertRedirect(route('login'));
    }

    public function test_student_cannot_assign_or_publish_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $student = User::factory()->student()->create();
        $exam = $this->createExamFor($lecturer);

        $this->actingAs($student)
            ->get(
                route(
                    'lecturer.exams.classrooms.edit',
                    $exam,
                ),
            )
            ->assertForbidden();

        $this->actingAs($student)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertForbidden();
    }

    public function test_assignment_page_shows_only_eligible_classrooms(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($lecturer);

        $eligibleClassroom = Classroom::factory()->create([
            'name' => 'Eligible Classroom',
            'created_by' => $lecturer->id,
        ]);

        $eligibleClassroom->subjects()->attach(
            $exam->subject_id,
        );

        Classroom::factory()->create([
            'name' => 'Wrong Subject Classroom',
            'created_by' => $lecturer->id,
        ]);

        $otherClassroom = Classroom::factory()->create([
            'name' => 'Other Lecturer Classroom',
            'created_by' => $otherLecturer->id,
        ]);

        $otherClassroom->subjects()->attach(
            $exam->subject_id,
        );

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.classrooms.edit',
                    $exam,
                ),
            )
            ->assertOk()
            ->assertSee('Eligible Classroom')
            ->assertDontSee('Wrong Subject Classroom')
            ->assertDontSee('Other Lecturer Classroom');
    }

    public function test_lecturer_can_assign_exam_to_eligible_classrooms(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $firstClassroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $secondClassroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $response = $this
            ->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.exams.classrooms.update',
                    $exam,
                ),
                [
                    'classroom_ids' => [
                        $firstClassroom->id,
                        $secondClassroom->id,
                    ],
                ],
            );

        $response
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            )
            ->assertSessionHas(
                'success',
                'Exam classroom assignments updated successfully.',
            );

        $this->assertDatabaseHas('exam_classroom', [
            'exam_id' => $exam->id,
            'classroom_id' => $firstClassroom->id,
        ]);

        $this->assertDatabaseHas('exam_classroom', [
            'exam_id' => $exam->id,
            'classroom_id' => $secondClassroom->id,
        ]);
    }

    public function test_ineligible_classroom_is_rejected(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $ineligibleClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.exams.classrooms.update',
                    $exam,
                ),
                [
                    'classroom_ids' => [
                        $ineligibleClassroom->id,
                    ],
                ],
            )
            ->assertSessionHasErrors('classroom_ids.0');

        $this->assertDatabaseMissing('exam_classroom', [
            'exam_id' => $exam->id,
            'classroom_id' => $ineligibleClassroom->id,
        ]);
    }

    public function test_lecturer_can_clear_draft_assignments(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $classroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $exam->classrooms()->attach($classroom);

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.exams.classrooms.update',
                    $exam,
                ),
                [],
            )
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            );

        $this->assertDatabaseMissing('exam_classroom', [
            'exam_id' => $exam->id,
        ]);
    }

    public function test_published_exam_assignments_cannot_be_changed(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor(
            $lecturer,
            ['published_at' => now()],
        );

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.classrooms.edit',
                    $exam,
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.exams.classrooms.update',
                    $exam,
                ),
                [],
            )
            ->assertForbidden();
    }

    public function test_other_lecturer_cannot_assign_or_publish_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $exam = $this->createExamFor($otherLecturer);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.exams.classrooms.edit',
                    $exam,
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertForbidden();
    }

    public function test_exam_without_questions_cannot_be_published(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $classroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $exam->classrooms()->attach($classroom);

        $this->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertSessionHas(
                'error',
                'Add at least one question before publishing.',
            );

        $this->assertNull(
            $exam->fresh()->published_at,
        );
    }

    public function test_exam_without_classroom_cannot_be_published(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        Question::factory()->create([
            'exam_id' => $exam->id,
            'position' => 1,
        ]);

        $this->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertSessionHas(
                'error',
                'Assign at least one classroom before publishing.',
            );

        $this->assertNull(
            $exam->fresh()->published_at,
        );
    }

    public function test_invalid_multiple_choice_question_prevents_publication(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $classroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $exam->classrooms()->attach($classroom);

        $question = Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'position' => 1,
        ]);

        $question->options()->createMany([
            [
                'text' => 'Option A',
                'is_correct' => false,
            ],
            [
                'text' => 'Option B',
                'is_correct' => false,
            ],
        ]);

        $this->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertSessionHas(
                'error',
                'Every multiple-choice question must have at least two options and exactly one correct answer.',
            );

        $this->assertNull(
            $exam->fresh()->published_at,
        );
    }

    public function test_ready_exam_can_be_published_only_once(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $exam = $this->createExamFor($lecturer);

        $classroom = $this->createEligibleClassroom(
            $lecturer,
            $exam,
        );

        $exam->classrooms()->attach($classroom);

        Question::factory()->create([
            'exam_id' => $exam->id,
            'type' => 'open_text',
            'position' => 1,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            );

        $response
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            )
            ->assertSessionHas(
                'success',
                'Exam published successfully.',
            );

        $this->assertNotNull(
            $exam->fresh()->published_at,
        );

        $this->actingAs($lecturer)
            ->post(
                route('lecturer.exams.publish', $exam),
            )
            ->assertForbidden();
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

    private function createEligibleClassroom(
        User $lecturer,
        Exam $exam,
    ): Classroom {
        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $classroom->subjects()->attach(
            $exam->subject_id,
        );

        return $classroom;
    }
}