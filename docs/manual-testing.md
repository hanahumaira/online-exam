# Manual Testing Guide

This guide tests the complete flow from exam creation to result release.

## 1. Before Testing

Start the application:

```bash
composer run dev
```

In another terminal, start the scheduler:

```bash
php artisan schedule:work
```

Open `http://127.0.0.1:8000` in two browser windows:

- Normal window: lecturer
- Incognito window: student

Demo accounts:

```text
Lecturer: lecturer@example.com / password
Student: student@example.com / password
```

## 2. Test Login and Role Access

1. Open the login page.
2. Enter the lecturer email and password.
3. Click **Log In**.
4. Confirm the lecturer dashboard and menus are displayed.
5. In the incognito window, log in as the student.
6. Enter `/lecturer/classrooms` in the student browser.

Expected:

- Both accounts can log in.
- The student receives `403 Forbidden` when opening a lecturer page.

## 3. Create a Classroom

In the lecturer window:

1. Click **Classrooms** in the menu.
2. Click **Create Classroom**.
3. Enter:
   - Class Name: `Manual Test Class`
   - Class Code: `MTC-01`
4. Click **Create Classroom**.

Expected:

- A success message is displayed.
- `Manual Test Class` appears in the classroom list.

## 4. Create and Assign a Subject

1. Click **Subjects** in the menu.
2. Click **Create Subject**.
3. Enter:
   - Subject Name: `General Science`
   - Subject Code: `SCI-01`
4. Under **Assign Classrooms**, select `Manual Test Class`.
5. Click **Create Subject**.

Expected:

- The subject is created.
- Its assigned classroom is `Manual Test Class`.

## 5. Assign the Student to the Classroom

1. Click **Students** in the menu.
2. Find `Demo Student`.
3. Click **Assign Class** or **Change Class**.
4. Select `Manual Test Class (MTC-01)`.
5. Click **Save Assignment**.

Expected:

- A success message is displayed.
- Demo Student is assigned to `Manual Test Class`.

## 6. Create an Exam

1. Click **Exams** in the menu.
2. Click **Create Exams**.
3. Enter:
   - Subject: `General Science`
   - Title: `Manual Science Assessment`
   - Instructions: `Answer every question before the timer expires.`
   - Duration: `3` minutes
4. Click **Create Exam**.

Expected:

- The exam is created as a draft.
- Its title, subject and duration are displayed.

## 7. Add a Multiple-Choice Question

1. On the exam page, click **Add Question**.
2. Select **Multiple Choice**.
3. Enter:
   - Question: `What is 2 + 2?`
   - Marks: `4`
   - Option 1: `3`
   - Option 2: `4`
   - Option 3: `5`
4. Select Option 2 as the correct answer.
5. Leave Option 4 empty.
6. Click **Create Question**.

Expected:

- The question is created with three options.
- Option `4` is shown as the correct answer.

## 8. Add an Open-Text Question

1. Click **Add Question**.
2. Select **Open Text**.
3. Enter:
   - Question: `Why is water important for living organisms?`
   - Marks: `6`
4. Click **Create Question**.

Expected:

- The open-text question is created.
- The exam total is `10.00` marks.

## 9. Assign and Publish the Exam

1. On the exam page, click **Manage Assignments**.
2. Select `Manual Test Class`.
3. Click **Save Assignments**.
4. Return to the exam page.
5. Click **Publish Exam**.
6. Click **OK** in the confirmation prompt.

Expected:

- The exam status changes to Published.
- Published exams no longer show edit or delete controls.

## 10. Take the Exam as a Student

In the student window:

1. Click **Exams** in the menu.
2. Find `Manual Science Assessment`.
3. Click **View Exam**.
4. Click **Start Exam**.
5. Click **OK** in the confirmation prompt.
6. Select `4` for the multiple-choice question.
7. Enter `Water supports cells and biological processes.` for the open-text question.
8. Click **Save Progress**.
9. Refresh the page and confirm both answers remain selected.
10. Click **Submit Exam**.
11. Click **OK** in the confirmation prompt.

Expected:

- The timer starts when the attempt begins.
- Saved answers remain after refresh.
- The attempt becomes Submitted.
- The student cannot change the submitted answers or start another attempt.
- The result is not visible before lecturer grading and release.

## 11. Grade the Attempt

In the lecturer window:

1. Click **Grading** in the menu.
2. Find the Demo Student attempt.
3. Click **Review**.
4. Confirm the MCQ received `4.00 / 4.00` automatically.
5. Enter `5` as the awarded marks for the open-text answer.
6. Click **Save Manual Grading**.

Expected:

- The grading status changes to Graded.
- The final score is `9.00 / 10.00`.

## 12. Release the Result

1. Click **Results** in the lecturer menu.
2. Find `Manual Science Assessment`.
3. Click **Manage**.
4. Click **Release Results**.
5. Click **OK** in the confirmation prompt.

Expected:

- The release date is displayed.
- The release button disappears.
- Released results cannot be reversed.

## 13. View the Student Result

In the student window:

1. Click **Results** in the menu.
2. Find `Manual Science Assessment`.
3. Click **View Result**.

Expected:

- Score: `9.00 / 10.00`
- Percentage: `90.00%`
- The selected MCQ answer and open-text answer are displayed.
- The awarded marks for each question are displayed.

## 14. Test Automatic Expiry

1. As the lecturer, create another exam with a duration of `1` minute.
2. Add one MCQ, assign `Manual Test Class`, and publish it.
3. As the student, open the exam and click **Start Exam**.
4. Do not submit it and wait for the timer to reach `00:00`.

Expected:

- The attempt is automatically closed.
- The page shows that the time expired.
- The student cannot edit or restart the attempt.

## 15. Run Automated Tests

```bash
php artisan test
```

Expected:

- All tests pass.
