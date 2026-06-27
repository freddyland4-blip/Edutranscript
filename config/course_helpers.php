<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function default_course_records(): array
{
    return [
        ['id' => 1, 'course_code' => 'CSC101', 'course_title' => 'Introduction to Computer Science', 'credit_unit' => 3, 'ca_max' => 30, 'exam_max' => 70],
        ['id' => 2, 'course_code' => 'MTH101', 'course_title' => 'Elementary Mathematics', 'credit_unit' => 3, 'ca_max' => 40, 'exam_max' => 60],
        ['id' => 3, 'course_code' => 'GST101', 'course_title' => 'Use of English', 'credit_unit' => 2, 'ca_max' => 30, 'exam_max' => 70],
    ];
}

function normalize_course(array $course): array
{
    $course['id'] = (string) ($course['id'] ?? $course['course_code'] ?? '');
    $course['course_code'] = (string) ($course['course_code'] ?? '');
    $course['course_title'] = (string) ($course['course_title'] ?? '');
    $course['credit_unit'] = (int) ($course['credit_unit'] ?? 0);
    $course['ca_max'] = (float) ($course['ca_max'] ?? 30);
    $course['exam_max'] = (float) ($course['exam_max'] ?? 70);
    $course['total_mark'] = $course['ca_max'] + $course['exam_max'];

    return $course;
}

function demo_course_records(): array
{
    $courses = array_merge(default_course_records(), $_SESSION['demo_courses'] ?? []);
    return array_map('normalize_course', $courses);
}

function add_demo_course(array $course): void
{
    $_SESSION['demo_courses'] ??= [];
    $course['id'] = count(demo_course_records()) + 1;
    $_SESSION['demo_courses'][] = normalize_course($course);
}

function course_table_has_mark_columns(PDO $db): bool
{
    static $hasColumns = null;

    if ($hasColumns !== null) {
        return $hasColumns;
    }

    try {
        $statement = $db->query("SHOW COLUMNS FROM courses LIKE 'ca_max'");
        $hasColumns = (bool) $statement->fetch();
    } catch (Throwable $exception) {
        $hasColumns = false;
    }

    return $hasColumns;
}

function fetch_courses(): array
{
    $db = app_db();

    if (!$db) {
        return demo_course_records();
    }

    try {
        if (course_table_has_mark_columns($db)) {
            $courses = $db->query('SELECT id, course_code, course_title, credit_unit, ca_max, exam_max FROM courses ORDER BY course_code ASC')->fetchAll();
        } else {
            $courses = $db->query('SELECT id, course_code, course_title, credit_unit FROM courses ORDER BY course_code ASC')->fetchAll();
        }

        return array_map('normalize_course', $courses);
    } catch (Throwable $exception) {
        return demo_course_records();
    }
}

function save_course_record(array $course, ?string &$error = null): bool
{
    $course = normalize_course($course);
    $db = app_db();

    if (!$db) {
        add_demo_course($course);
        return true;
    }

    try {
        if (course_table_has_mark_columns($db)) {
            $statement = $db->prepare('INSERT INTO courses (course_code, course_title, credit_unit, ca_max, exam_max) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([
                $course['course_code'],
                $course['course_title'],
                $course['credit_unit'],
                $course['ca_max'],
                $course['exam_max'],
            ]);
        } else {
            $statement = $db->prepare('INSERT INTO courses (course_code, course_title, credit_unit) VALUES (?, ?, ?)');
            $statement->execute([
                $course['course_code'],
                $course['course_title'],
                $course['credit_unit'],
            ]);
        }

        return true;
    } catch (Throwable $exception) {
        $error = 'Course was not saved to MySQL. Check that the schema has been imported and the course code is unique.';
        return false;
    }
}

function selected_course(array $courses, string $selectedId): array
{
    foreach ($courses as $course) {
        if ((string) $course['id'] === $selectedId || $course['course_code'] === $selectedId) {
            return normalize_course($course);
        }
    }

    return normalize_course($courses[0] ?? default_course_records()[0]);
}
