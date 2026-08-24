<?php

namespace Tests\Feature\Student;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_student_exams(): void
    {
        $this->get(route('student.exams.index'))
            ->assertRedirect(route('login'));
    }

    public function test_lecturer_cannot_access_student_exam_pages(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->get(route('student.exams.index'))
            ->assertForbidden();
    }

    public function test_student_without_classroom_sees_no_exams(): void
    {
        $student = User::factory()->student()->create([
            'classroom_id' => null,
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.index'))
            ->assertOk()
            ->assertSee(
                'You have not been assigned to a classroom yet.',
            );
    }

    public function test_student_sees_only_published_exams_assigned_to_their_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $classroom->id,
        ]);

        $visibleExam = $this->createExam(
            $lecturer,
            [
                'title' => 'Visible Published Exam',
                'published_at' => now(),
            ],
        );

        $visibleExam->classrooms()->attach($classroom);

        $draftExam = $this->createExam(
            $lecturer,
            [
                'title' => 'Hidden Draft Exam',
                'published_at' => null,
            ],
        );

        $draftExam->classrooms()->attach($classroom);

        $unassignedExam = $this->createExam(
            $lecturer,
            [
                'title' => 'Hidden Unassigned Exam',
                'published_at' => now(),
            ],
        );

        $this->actingAs($student)
            ->get(route('student.exams.index'))
            ->assertOk()
            ->assertSee('Visible Published Exam')
            ->assertDontSee('Hidden Draft Exam')
            ->assertDontSee('Hidden Unassigned Exam');
    }

    public function test_student_can_view_an_eligible_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $classroom->id,
        ]);

        $exam = $this->createExam(
            $lecturer,
            [
                'title' => 'Eligible Exam',
                'instructions' => 'Answer every question.',
                'published_at' => now(),
            ],
        );

        $exam->classrooms()->attach($classroom);

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertOk()
            ->assertSee('Eligible Exam')
            ->assertSee('Answer every question.');
    }

    public function test_student_cannot_view_a_draft_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $classroom->id,
        ]);

        $exam = $this->createExam(
            $lecturer,
            ['published_at' => null],
        );

        $exam->classrooms()->attach($classroom);

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertForbidden();
    }

    public function test_student_cannot_view_an_unassigned_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $studentClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $studentClassroom->id,
        ]);

        $exam = $this->createExam(
            $lecturer,
            ['published_at' => now()],
        );

        $exam->classrooms()->attach($otherClassroom);

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertForbidden();
    }

    private function createExam(
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