<?php

namespace Tests\Feature\Lecturer;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentClassroomAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_student_assignments(): void
    {
        $this->get(route('lecturer.students.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_student_assignments(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('lecturer.students.index'))
            ->assertForbidden();
    }

    public function test_lecturer_sees_unassigned_and_own_classroom_students(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $ownClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        User::factory()->student()->create([
            'name' => 'Unassigned Student',
            'classroom_id' => null,
        ]);

        User::factory()->student()->create([
            'name' => 'My Classroom Student',
            'classroom_id' => $ownClassroom->id,
        ]);

        User::factory()->student()->create([
            'name' => 'Other Classroom Student',
            'classroom_id' => $otherClassroom->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.students.index'))
            ->assertOk()
            ->assertSee('Unassigned Student')
            ->assertSee('My Classroom Student')
            ->assertDontSee('Other Classroom Student');
    }

    public function test_lecturer_can_open_assignment_form(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'name' => 'My Classroom',
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => null,
        ]);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.students.classroom.edit',
                    $student,
                ),
            )
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($classroom->name);
    }

    public function test_lecturer_can_assign_student_to_own_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => null,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.students.classroom.update',
                    $student,
                ),
                [
                    'classroom_id' => $classroom->id,
                ],
            );

        $response
            ->assertRedirect(route('lecturer.students.index'))
            ->assertSessionHas(
                'success',
                'Student classroom assignment updated successfully.',
            );

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'classroom_id' => $classroom->id,
        ]);
    }

    public function test_lecturer_can_unassign_student_from_own_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $classroom->id,
        ]);

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.students.classroom.update',
                    $student,
                ),
                [
                    'classroom_id' => null,
                ],
            )
            ->assertRedirect(route('lecturer.students.index'));

        $this->assertNull(
            $student->fresh()->classroom_id,
        );
    }

    public function test_lecturer_cannot_assign_another_lecturers_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => null,
        ]);

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.students.classroom.update',
                    $student,
                ),
                [
                    'classroom_id' => $otherClassroom->id,
                ],
            )
            ->assertSessionHasErrors('classroom_id');

        $this->assertNull(
            $student->fresh()->classroom_id,
        );
    }

    public function test_lecturer_cannot_manage_student_from_another_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $ownClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $otherClassroom->id,
        ]);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.students.classroom.edit',
                    $student,
                ),
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->put(
                route(
                    'lecturer.students.classroom.update',
                    $student,
                ),
                [
                    'classroom_id' => $ownClassroom->id,
                ],
            )
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'classroom_id' => $otherClassroom->id,
        ]);
    }

    public function test_lecturer_account_cannot_be_assigned_as_student(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->get(
                route(
                    'lecturer.students.classroom.edit',
                    $otherLecturer,
                ),
            )
            ->assertForbidden();
    }
}
