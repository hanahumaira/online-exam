# Requirements Specification

## 1. Objective

Build a secure web portal in which lecturers manage classes, subjects, exams, and grading, while students take only the exams assigned to their class within a server-enforced time limit.

## 2. Actors

- Lecturer : manage academic data (class, subject, exam, result).
- Student : view exams, complete an exam within its time limit, and view results.

## 3. Scope

- Authentication (Breeze)
- Lecturer and student roles.
- Subject management.
    - Assignment of subjects to classes.
- Class management
    - Assignment of students to a class.
- Exam Management
    - Creation (multiple-choice and open-text).
    - Assignment of exams to classes.
- Result Management *
    - Automatic marking of multiple-choice answers. \*
    - Manual marking of open-text answers. \*
    - Lecturer management and release of results. \*
    - Student view result. \*

\* Additional feat

## 4. Business Rules

- Only published exams are visible to eligible students.
- A student can access an exam only when it is assigned to the student's class.
- Access restrictions must be enforced by the server, not only by hidden buttons or links.
- Lecturers can assign students only to classrooms they created.
- Lecturers can manage unassigned students and students in their own classrooms.
- Lecturers cannot reassign students belonging to another lecturer's classroom.
- New exams are created as drafts.
- A lecturer can create an exam only for a subject they created.
- A lecturer can manage only exams they created.
- Only draft exams can be edited or deleted.
- Exam duration must be between 1 and 480 minutes.
- Each student is allowed only one attempt per exam.
- The server records the attempt's start and expiry times.
- The system automatically submits an attempt when its time expires.
- Submitted or expired attempts cannot be modified.
- Multiple-choice answers are graded automatically.
- Correct multiple-choice answers receive the question's allocated marks.
- Incorrect or unanswered multiple-choice questions receive zero marks.
- Open-text answers require lecturer grading.
- Awarded marks cannot exceed the question's maximum marks.
- A result cannot be released while required open-text grading is incomplete.
- Students cannot view scores before the lecturer releases the results.
- Students can view only their own attempts and results.
- Lecturers can manage only records within their authorized scope.

## 5. Functional Requirements

### Authentication and Authorization

- Users can register, log in, and log out.
- Public registration creates only student accounts.
- Lecturer accounts are created using a database seeder.
- Unauthenticated users cannot access protected pages.
- Students cannot access lecturer pages or operations.
- Authorization is checked on every protected server request.

### Subject Management

- Lecturers can create, view, update, and delete subjects.
- Each subject has a name and unique code.
- Lecturers can assign subjects to classes.

### Class Management

- Lecturers can create, view, update, and delete classes.
- Each class has a name and unique code.
- Lecturers can assign students to a class.
- Lecturers can view students belonging to a class.

### Exam Management

- Lecturers can create, view, update, and delete draft exams.
- Each exam belongs to one subject.
- Each exam has a title, instructions, and duration.
- Lecturers can create multiple-choice questions.
- Lecturers can create open-text questions.
- Lecturers can assign exams to eligible classes.
- Lecturers can publish completed exams.
- Students can view only published exams assigned to their class.

### Exam Attempts

- Students can start an eligible exam.
- The system records the attempt's start and expiry times.
- The system displays the remaining time.
- The system automatically submits the attempt when time expires. \*
- Students can submit an attempt before time expires.
- Students cannot start the same exam more than once.
- Students cannot modify submitted or expired attempts.

### Grading and Results

- The system automatically grades multiple-choice answers. \*
- Lecturers can manually grade open-text answers. \*
- The system calculates the total score.
- Results with ungraded open-text answers remain awaiting grading.
- Lecturers can review and release completed results. \*
- Students can view their results only after the lecturer releases them. \*
- Students cannot view another student's result.

## 6. Non-Functional Requirements

- The application must use Laravel 11.
- The application must use Laravel Breeze for authentication.
- Passwords must be securely hashed.
- The application must use server-side validation.
- Validation errors must be clearly displayed to users.
- Forms must use CSRF protection.
- Access control must be enforced on the server.
- Database relationships must use foreign-key constraints.
- Exam submission and grading must maintain data consistency.
- The interface should work on desktop and mobile screen sizes.
- Critical features should be covered by automated tests.
- The application must be installable by following the README.
- The `.env` file and other secrets must not be committed to Git.

## 7. Flows

### Lecturer Creates an Exam

- Lecturer logs in.
- Lecturer creates a subject.
- Lecturer creates a class.
- Lecturer assigns the subject and students to the class.
- Lecturer creates a draft exam.
- Lecturer adds multiple-choice or open-text questions.
- Lecturer assigns the exam to eligible classes.
- Lecturer publishes the exam.

### Student Takes an Exam

- Student logs in.
- Student views published exams assigned to their class.
- Student starts an exam.
- The server records the start and expiry times.
- Student answers the questions.
- The system displays the remaining time.
- Student submits the exam, or the system submits it when time expires.
- Multiple-choice answers are graded automatically.
- The attempt waits for lecturer grading if it contains open-text answers.

### Lecturer Grades and Releases Results

- Lecturer views attempts awaiting grading.
- Lecturer reviews the student's open-text answers.
- Lecturer awards marks for each open-text answer.
- The system calculates the final score.
- Lecturer releases the completed results.
- Student can view the released result.