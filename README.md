# Online Examination Portal

A portal for lecturers to manage classes, subjects, exams and results, and for students to take timed exams assigned to their class.

## Features

- Lecturer and student authentication with Laravel Breeze.
- Role-based authorization enforced by the server.
- Classroom, subject and student-class assignment management.
- Draft exams with multiple-choice and open-text questions.
- Exam assignment to eligible classrooms and irreversible publishing.
- One server-timed attempt per student and exam.
- Automatic submission when time expires.
- Automatic multiple-choice grading and manual open-text grading.
- Lecturer-controlled result release and student result viewing.
- Server-side validation, CSRF protection and automated tests.

## Technology

- PHP 8.2+
- Laravel 11
- Laravel Breeze
- MySQL
- Blade, Alpine.js and Tailwind CSS
- PHPUnit

## Setup

### 1. Install the project

```bash
git clone https://github.com/hanahumaira/online-exam.git
cd online-exam
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Configure MySQL

Create a MySQL database, then update these values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=online_exams
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create the tables and demo accounts

```bash
php artisan migrate --seed
```

### 4. Run the application

Run:

```bash
composer run dev
```

In another terminal, run:

```bash
php artisan schedule:work
```

Open `http://127.0.0.1:8000`.

## Demo Accounts

Lecturer:

```text
Email: lecturer@example.com
Password: password
```

Student:

```text
Email: student@example.com
Password: password
```

These accounts are for local demonstration only.

## Testing

```bash
php artisan test
```

For a step-by-step user test, follow the [manual testing guide](docs/manual-testing.md).

## Main Assumptions

- A student belongs to one classroom.
- A classroom can have multiple subjects, and a subject can belong to multiple classrooms.
- An exam belongs to one subject and can be assigned to multiple classrooms.
- Public registration creates student accounts; lecturer accounts are seeded.
- A student has one attempt per exam.
- Only published exams assigned to the student's classroom are accessible.
- Multiple-choice answers are graded automatically.
- Open-text answers require lecturer grading.
- Students see results only after the lecturer releases them.
- Exam deadlines are enforced by the server, not only by JavaScript.

## Documentation

- [Requirements](docs/requirements.md)
- [Database design](docs/database-design.md)
- [Manual testing guide](docs/manual-testing.md)
