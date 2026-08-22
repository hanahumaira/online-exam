# Database Design

## 1. `users`

- `id` (PK)
- `name`
- `email` (unique)
- `email_verified_at` (nullable)
- `password`
- `role` : `lecturer` or `student`
- `classroom_id` (FK → `classrooms.id`, nullable)
- `remember_token` (nullable)


## 2. `classrooms`

- `id` (PK)
- `name`
- `code` (unique)
- `created_by` (FK → `users.id`)


## 3. `subjects`

- `id` (PK)
- `name`
- `code` (unique)
- `created_by` (FK → `users.id`)


## 4. `classroom_subject`

- `id` (PK)
- `classroom_id` (FK → `classrooms.id`)
- `subject_id` (FK → `subjects.id`)


## 5. `exams`

- `id` (PK)
- `subject_id` (FK → `subjects.id`)
- `created_by` (FK → `users.id`)
- `title`
- `instructions` (nullable)
- `duration_minutes`
- `published_at` (nullable)
- `results_released_at` (nullable)


## 6. `exam_classroom`

- `id` (PK)
- `exam_id` (FK → `exams.id`)
- `classroom_id` (FK → `classrooms.id`)


## 7. `questions`

- `id` (PK)
- `exam_id` (FK → `exams.id`)
- `type` : `multiple_choice` or `open_text`
- `prompt`
- `marks`
- `position`


## 8. `question_options`

- `id` (PK)
- `question_id` (FK → `questions.id`)
- `text`
- `is_correct`


## 9. `attempts`

- `id` (PK)
- `exam_id` (FK → `exams.id`)
- `user_id` (FK → `users.id`)
- `started_at`
- `expires_at`
- `submitted_at` (nullable)
- `status` :`in_progress`, `submitted`, `awaiting_grading`, or `graded`
- `score` (nullable)


## 10. `answers`

- `id` (PK)
- `attempt_id` (FK → `attempts.id`)
- `question_id` (FK → `questions.id`)
- `question_option_id` (FK → `question_options.id`, nullable)
- `text_answer` (nullable)
- `awarded_marks` (nullable)


###############################


## Table Relationships

- `classrooms` 1 ... * `users (students)`
- `users (lecturer)` 1 ... * `classrooms`
- `users (lecturer)` 1 ... * `subjects`
- `users (lecturer)` 1 ... * `exams`
- `classrooms` * ... * `subjects`
- `subjects` 1 ... * `exams`
- `exams` * ... * `classrooms`
- `exams` 1 ... * `questions`
- `questions` 1 ... * `question_options`
- `users (students)` 1 ... * `attempts`
- `exams` 1 ... * `attempts`
- `attempts` 1 ... * `answers`
- `questions` 1 ... * `answers`
- `question_options` 1 ... * `answers`
