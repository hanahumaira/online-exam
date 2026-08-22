<?php

namespace Tests\Feature\Lecturer;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_classroom_management(): void
    {
        $response = $this->get(route('lecturer.classrooms.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_classroom_management(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->get(route('lecturer.classrooms.index'));

        $response->assertForbidden();
    }

    public function test_lecturer_only_sees_their_own_classrooms(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        Classroom::factory()->create([
            'name' => 'My Classroom',
            'created_by' => $lecturer->id,
        ]);

        Classroom::factory()->create([
            'name' => 'Other Classroom',
            'created_by' => $otherLecturer->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->get(route('lecturer.classrooms.index'));

        $response
            ->assertOk()
            ->assertSee('My Classroom')
            ->assertDontSee('Other Classroom');
    }

    public function test_lecturer_can_create_a_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $response = $this
            ->actingAs($lecturer)
            ->post(route('lecturer.classrooms.store'), [
                'name' => 'Software Engineering',
                'code' => 'se-01',
            ]);

        $response
            ->assertRedirect(route('lecturer.classrooms.index'))
            ->assertSessionHas(
                'success',
                'Classroom created successfully.',
            );

        $this->assertDatabaseHas('classrooms', [
            'name' => 'Software Engineering',
            'code' => 'SE-01',
            'created_by' => $lecturer->id,
        ]);
    }

    public function test_classroom_name_and_code_are_required(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $response = $this
            ->actingAs($lecturer)
            ->post(route('lecturer.classrooms.store'), [
                'name' => '',
                'code' => '',
            ]);

        $response->assertSessionHasErrors([
            'name',
            'code',
        ]);
    }

    public function test_classroom_code_must_be_unique(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        Classroom::factory()->create([
            'code' => 'CLASS-A',
            'created_by' => $lecturer->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->post(route('lecturer.classrooms.store'), [
                'name' => 'Another Classroom',
                'code' => 'class-a',
            ]);

        $response->assertSessionHasErrors('code');

        $this->assertDatabaseCount('classrooms', 1);
    }

    public function test_lecturer_can_update_their_own_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'name' => 'Old Name',
            'code' => 'OLD-01',
            'created_by' => $lecturer->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->put(
                route('lecturer.classrooms.update', $classroom),
                [
                    'name' => 'New Name',
                    'code' => 'new-01',
                ],
            );

        $response->assertRedirect(
            route('lecturer.classrooms.index'),
        );

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'name' => 'New Name',
            'code' => 'NEW-01',
        ]);
    }

    public function test_lecturer_cannot_manage_another_lecturers_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.classrooms.show', $classroom))
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->put(
                route('lecturer.classrooms.update', $classroom),
                [
                    'name' => 'Unauthorized Change',
                    'code' => $classroom->code,
                ],
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(
                route('lecturer.classrooms.destroy', $classroom),
            )
            ->assertForbidden();

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'created_by' => $otherLecturer->id,
        ]);
    }

    public function test_lecturer_can_delete_their_own_classroom(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $classroom = Classroom::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $student = User::factory()->student()->create([
            'classroom_id' => $classroom->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->delete(
                route('lecturer.classrooms.destroy', $classroom),
            );

        $response
            ->assertRedirect(route('lecturer.classrooms.index'))
            ->assertSessionHas(
                'success',
                'Classroom deleted successfully.',
            );

        $this->assertDatabaseMissing('classrooms', [
            'id' => $classroom->id,
        ]);

        $this->assertNull(
            $student->fresh()->classroom_id,
        );
    }
}
