<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_lecturer_dashboard(): void
    {
        $response = $this->get(route('lecturer.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_student_dashboard(): void
    {
        $response = $this->get(route('student.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_lecturer_is_redirected_to_lecturer_dashboard(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $response = $this
            ->actingAs($lecturer)
            ->get(route('dashboard'));

        $response->assertRedirect(route('lecturer.dashboard'));
    }

    public function test_student_is_redirected_to_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->get(route('dashboard'));

        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_lecturer_can_access_lecturer_dashboard(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $response = $this
            ->actingAs($lecturer)
            ->get(route('lecturer.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Lecturer Dashboard');
    }

    public function test_student_cannot_access_lecturer_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->get(route('lecturer.dashboard'));

        $response->assertForbidden();
    }

    public function test_student_can_access_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this
            ->actingAs($student)
            ->get(route('student.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Student Dashboard');
    }

    public function test_lecturer_cannot_access_student_dashboard(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $response = $this
            ->actingAs($lecturer)
            ->get(route('student.dashboard'));

        $response->assertForbidden();
    }
}
