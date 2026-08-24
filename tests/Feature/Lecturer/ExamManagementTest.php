<?php

namespace Tests\Feature\Lecturer;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_exam_management(): void
    {
        $this->get(route('lecturer.exams.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_exam_management(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('lecturer.exams.index'))
            ->assertForbidden();
    }

    public function test_lecturer_can_open_exam_creation_page(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'name' => 'Mathematics',
            'created_by' => $lecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.exams.create'))
            ->assertOk()
            ->assertSee('Create Exam')
            ->assertSee($subject->name);
    }

    public function test_lecturer_only_sees_their_own_exams(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $ownSubject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $otherSubject = Subject::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        Exam::factory()->create([
            'subject_id' => $ownSubject->id,
            'created_by' => $lecturer->id,
            'title' => 'My Exam',
        ]);

        Exam::factory()->create([
            'subject_id' => $otherSubject->id,
            'created_by' => $otherLecturer->id,
            'title' => 'Other Exam',
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.exams.index'))
            ->assertOk()
            ->assertSee('My Exam')
            ->assertDontSee('Other Exam');
    }

    public function test_lecturer_can_create_a_draft_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->post(route('lecturer.exams.store'), [
                'subject_id' => $subject->id,
                'title' => 'Midterm Examination',
                'instructions' => 'Answer every question.',
                'duration_minutes' => 90,
            ]);

        $exam = Exam::query()
            ->where('title', 'Midterm Examination')
            ->firstOrFail();

        $response
            ->assertRedirect(
                route('lecturer.exams.show', $exam),
            )
            ->assertSessionHas(
                'success',
                'Exam created successfully.',
            );

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'title' => 'Midterm Examination',
            'duration_minutes' => 90,
            'published_at' => null,
        ]);
    }

    public function test_exam_fields_are_validated(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->post(route('lecturer.exams.store'), [
                'subject_id' => '',
                'title' => '',
                'duration_minutes' => 0,
            ])
            ->assertSessionHasErrors([
                'subject_id',
                'title',
                'duration_minutes',
            ]);
    }

    public function test_lecturer_cannot_use_another_lecturers_subject(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $otherSubject = Subject::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->post(route('lecturer.exams.store'), [
                'subject_id' => $otherSubject->id,
                'title' => 'Unauthorized Exam',
                'duration_minutes' => 60,
            ])
            ->assertSessionHasErrors('subject_id');

        $this->assertDatabaseMissing('exams', [
            'title' => 'Unauthorized Exam',
        ]);
    }

    public function test_lecturer_can_update_their_draft_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'title' => 'Old Exam Title',
            'published_at' => null,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->put(
                route('lecturer.exams.update', $exam),
                [
                    'subject_id' => $subject->id,
                    'title' => 'Updated Exam Title',
                    'instructions' => 'Updated instructions.',
                    'duration_minutes' => 120,
                ],
            );

        $response->assertRedirect(
            route('lecturer.exams.show', $exam),
        );

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'title' => 'Updated Exam Title',
            'duration_minutes' => 120,
        ]);
    }

    public function test_lecturer_cannot_manage_another_lecturers_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();
        $otherLecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $otherLecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $otherLecturer->id,
        ]);

        $this->actingAs($lecturer)
            ->get(route('lecturer.exams.show', $exam))
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->put(
                route('lecturer.exams.update', $exam),
                [
                    'subject_id' => $subject->id,
                    'title' => 'Unauthorized Change',
                    'duration_minutes' => 60,
                ],
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(route('lecturer.exams.destroy', $exam))
            ->assertForbidden();
    }

    public function test_published_exam_cannot_be_updated_or_deleted(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'title' => 'Published Exam',
            'published_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->put(
                route('lecturer.exams.update', $exam),
                [
                    'subject_id' => $subject->id,
                    'title' => 'Changed Published Exam',
                    'duration_minutes' => 60,
                ],
            )
            ->assertForbidden();

        $this->actingAs($lecturer)
            ->delete(route('lecturer.exams.destroy', $exam))
            ->assertForbidden();

        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'title' => 'Published Exam',
        ]);
    }

    public function test_lecturer_can_delete_their_draft_exam(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $subject = Subject::factory()->create([
            'created_by' => $lecturer->id,
        ]);

        $exam = Exam::factory()->create([
            'subject_id' => $subject->id,
            'created_by' => $lecturer->id,
            'published_at' => null,
        ]);

        $response = $this
            ->actingAs($lecturer)
            ->delete(route('lecturer.exams.destroy', $exam));

        $response
            ->assertRedirect(route('lecturer.exams.index'))
            ->assertSessionHas(
                'success',
                'Exam deleted successfully.',
            );

        $this->assertDatabaseMissing('exams', [
            'id' => $exam->id,
        ]);
    }
}
