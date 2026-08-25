# Requirements Specification

## 1. Objective

Build a secure online examination portal where lecturers manage academic data and students take timed exams assigned to their classroom.

## 2. Actors

- Lecturer: manages classrooms, subjects, students, exams, grading and results.
- Student: takes eligible exams and views released results.

## 3. Functional Requirements

### Authentication and Authorization

- Users can register, log in and log out.
- Public registration creates student accounts.
- Lecturer accounts are created by a database seeder.
- Guests cannot access protected pages.
- Students and lecturers can access only features allowed for their role.

### Classroom and Subject Management

- Lecturers can create, view, update and delete their classrooms and subjects.
- Lecturers can assign students to their classrooms.
- Lecturers can assign subjects to multiple classrooms.

### Exam Management

- Lecturers can create, update and delete draft exams for their subjects.
- Exams can contain multiple-choice and open-text questions.
- A multiple-choice question has two to four options and one correct answer.
- Lecturers can assign exams only to eligible classrooms.
- An exam requires a question and classroom assignment before publication.
- Published exams cannot be modified.

### Exam Attempts

- Students see only published exams assigned to their classroom.
- A student can attempt each exam once.
- The server records the attempt start and expiry times.
- Students can save answers and submit before the deadline.
- Attempts are automatically closed when time expires.
- Submitted or expired attempts cannot be changed.

### Grading and Results

- Multiple-choice answers are graded automatically.
- Lecturers manually grade answered open-text questions.
- Awarded marks cannot exceed the question marks.
- Results cannot be released while attempts are active or grading is incomplete.
- Result release is irreversible.
- Students can view only their own released results.

## 4. Business Rules

- A student belongs to one classroom.
- A classroom can have multiple subjects.
- A subject can belong to multiple classrooms.
- An exam belongs to one subject and can be assigned to multiple classrooms.
- Lecturers manage only records they own.
- New exams are drafts.
- Exam duration is between 1 and 480 minutes.
- Publishing and result release are irreversible.
- Access restrictions and deadlines are enforced by the server.

## 5. Non-Functional Requirements

- Use Laravel 11 with Laravel Breeze.
- Use MySQL and foreign-key constraints.
- Hash passwords securely.
- Validate requests on the server and display clear errors.
- Protect forms with CSRF tokens.
- Support desktop and mobile layouts.
- Cover critical behaviour with automated tests.
- Keep `.env` and secrets out of Git.
- Provide installation instructions in the README.

## 6. Main Flow

- Lecturer creates a classroom and subject, then assigns students and the subject to the classroom.
- Lecturer creates an exam, adds questions, assigns a classroom and publishes it.
- Student starts the exam, answers questions and submits before the deadline or is automatically timed out.
- The system grades multiple-choice answers.
- Lecturer grades open-text answers and releases the completed results.
- Student views the released result.
