<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lecturer = User::query()->firstOrNew([
            'email' => 'lecturer@example.com',
        ]);

        $lecturer->forceFill([
            'name' => 'Demo Lecturer',
            'password' => Hash::make('password'),
            'role' => User::ROLE_LECTURER,
            'classroom_id' => null,
            'email_verified_at' => now(),
        ])->save();

        $classroom = Classroom::query()->updateOrCreate(
            ['code' => 'CLASS-A'],
            [
                'name' => 'Class A',
                'created_by' => $lecturer->id,
            ]
        );

        $subject = Subject::query()->updateOrCreate(
            ['code' => 'MATH'],
            [
                'name' => 'Mathematics',
                'created_by' => $lecturer->id,
            ],
        );

        $classroom->subjects()->syncWithoutDetaching([
            $subject->id,
        ]);

        $student = User::query()->firstOrNew([
            'email' => 'student@example.com',
        ]);

        $student->forceFill([
            'name' => 'Demo Student',
            'password' => Hash::make('password'),
            'role' => User::ROLE_STUDENT,
            'classroom_id' => $classroom->id,
            'email_verified_at' => now(),
        ])->save();
    }
}
