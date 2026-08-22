<?php

namespace Tests\Feature\Lecturer;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_subject_management(): void
    {
        $response = $this->get(
            route('lecturer.subjects.index'),
        );

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_subject_management(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('lecturer.subjects.index'))
            ->assertForbidden();
    }

    public function test_lecturer_can_open_the_create_subject_page(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.subjects.create'))
            ->assertOk()
            ->assertSee('Create Subject');
    }

    public function test_lecturer_only_sees_their_own_subjects(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        Subject::factory()->create([
            'name' => 'My Subject',
            'created_by' => $lecturer->id,
        ]);

        Subject::factory()->create([
            'name' => 'Other Subject',
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.subjects.index'))
            ->assertOk()
            ->assertSee('My Subject')
            ->assertDontSee('Other Subject');
    }

    public function test_lecturer_can_create_and_assign_a_subject(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->post(route('lecturer.subjects.store'), [
                'name' => 'Software Engineering',
                'code' => 'se-01',
                'classroom_ids' => [$classroom->id],
            ]);

        $response
            ->assertRedirect(route('lecturer.subjects.index'))
            ->assertSessionHas(
                'success',
                'Subject created successfully.',
            );

        $subject = Subject::query()
            ->where('code', 'SE-01')
            ->firstOrFail();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Software Engineering',
            'code' => 'SE-01',
            'created_by' => $lecturer->id,
        ]);

        $this->assertDatabaseHas('classroom_subject', [
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_subject_name_and_code_are_required(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->post(route('lecturer.subjects.store'), [
                'name' => '',
                'code' => '',
            ])
            ->assertSessionHasErrors(['name', 'code']);
    }

    public function test_subject_code_must_be_unique(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        Subject::factory()->create([
            'code' => 'MATH',
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->post(route('lecturer.subjects.store'), [
                'name' => 'Another Mathematics',
                'code' => 'math',
            ])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('subjects', 1);
    }

    public function test_lecturer_cannot_assign_another_lecturers_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $otherClassroom = Classroom::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->post(route('lecturer.subjects.store'), [
                'name' => 'Unauthorized Subject',
                'code' => 'UNAUTH-01',
                'classroom_ids' => [$otherClassroom->id],
            ])
            ->assertSessionHasErrors('classroom_ids.0');

        $this->assertDatabaseMissing('subjects', [
            'code' => 'UNAUTH-01',
        ]);
    }

    public function test_lecturer_can_update_subject_assignments(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $firstClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $secondClassroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $subject = Subject::factory()->create([
            'name' => 'Old Subject',
            'code' => 'OLD-01',
            'created_by' => $lecturer->id,
        ]);

        $subject->classrooms()->attach($firstClassroom);

        $response = $this
            ->actingAs($lecturer)
            ->put(
                route('lecturer.subjects.update', $subject),
                [
                    'name' => 'Updated Subject',
                    'code' => 'new-01',
                    'classroom_ids' => [$secondClassroom->id],
                ],
            );

        $response->assertRedirect(
            route('lecturer.subjects.index'),
        );

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Updated Subject',
            'code' => 'NEW-01',
        ]);

        $this->assertDatabaseMissing('classroom_subject', [
            'classroom_id' => $firstClassroom->id,
            'subject_id' => $subject->id,
        ]);

        $this->assertDatabaseHas('classroom_subject', [
            'classroom_id' => $secondClassroom->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_lecturer_cannot_manage_another_lecturers_subject(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.subjects.show', $subject))
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->put(
                route('lecturer.subjects.update', $subject),
                [
                    'name' => 'Unauthorized Change',
                    'code' => $subject->code,
                ],
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(
                route('lecturer.subjects.destroy', $subject),
            )
            ->assertForbidden();

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'created_by' => $otherLecturer->id,
        ]);
    }

    public function test_lecturer_can_delete_their_own_subject(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $subject->classrooms()->attach($classroom);

        $response = $this
            ->actingAs($lecturer)
            ->delete(
                route('lecturer.subjects.destroy', $subject),
            );

        $response
            ->assertRedirect(route('lecturer.subjects.index'))
            ->assertSessionHas(
                'success',
                'Subject deleted successfully.',
            );

        $this->assertDatabaseMissing('subjects', [
            'id' => $subject->id,
        ]);

        $this->assertDatabaseMissing('classroom_subject', [
            'subject_id' => $subject->id,
        ]);
    }
}
