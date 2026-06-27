<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function db_connection(): ?PDO
{
    global $pdo;
    return $pdo instanceof PDO ? $pdo : null;
}

function student_profile(string $registrationCode): array
{
    $db = db_connection();
    if ($db) {
        $statement = $db->prepare('SELECT registration_code, full_name, programme, level_name, created_at FROM students WHERE registration_code = ? LIMIT 1');
        $statement->execute([$registrationCode]);
        $student = $statement->fetch();

        if ($student) {
            return [
                'registration_code' => $student['registration_code'],
                'full_name' => $student['full_name'],
                'programme' => $student['programme'],
                'level' => $student['level_name'],
                'department' => 'Computing and Information Technology',
                'faculty' => 'Science and Technology',
                'academic_session' => '2025/2026',
                'semester' => 'First Semester',
            ];
        }
    }

    return [
        'registration_code' => $registrationCode,
        'full_name' => 'Amina Johnson',
        'programme' => 'Computer Science',
        'level' => '100 Level',
        'department' => 'Computing and Information Technology',
        'faculty' => 'Science and Technology',
        'academic_session' => '2025/2026',
        'semester' => 'First Semester',
    ];
}

function approved_results(string $registrationCode): array
{
    $db = db_connection();
    if ($db) {
        $statement = $db->prepare(
            'SELECT c.course_code, c.course_title, c.credit_unit, r.ca_score, r.exam_score, r.grade, r.grade_point
             FROM results r
             INNER JOIN students s ON s.id = r.student_id
             INNER JOIN courses c ON c.id = r.course_id
             WHERE s.registration_code = ? AND r.status IN ("approved", "published")'
        );
        $statement->execute([$registrationCode]);
        $records = $statement->fetchAll();

        if ($records) {
            return array_map(static function (array $record): array {
                $score = (float) $record['ca_score'] + (float) $record['exam_score'];
                return [
                    'code' => $record['course_code'],
                    'course' => $record['course_title'],
                    'credit' => (int) $record['credit_unit'],
                    'score' => $score,
                    'grade' => $record['grade'],
                    'point' => (float) $record['grade_point'],
                ];
            }, $records);
        }
    }

    return [
        ['code' => 'CSC101', 'course' => 'Introduction to Computer Science', 'credit' => 3, 'score' => 78, 'grade' => 'A', 'point' => 4.0],
        ['code' => 'MTH101', 'course' => 'Elementary Mathematics', 'credit' => 3, 'score' => 66, 'grade' => 'B', 'point' => 3.0],
        ['code' => 'GST101', 'course' => 'Use of English', 'credit' => 2, 'score' => 71, 'grade' => 'A', 'point' => 4.0],
    ];
}

function academic_summary(array $results): array
{
    $totalCredits = array_sum(array_column($results, 'credit'));
    $totalQualityPoints = array_reduce(
        $results,
        static fn (float $carry, array $result): float => $carry + ((float) $result['credit'] * (float) $result['point']),
        0.0
    );
    $gpa = $totalCredits > 0 ? $totalQualityPoints / $totalCredits : 0;

    return [
        'total_credits' => $totalCredits,
        'quality_points' => $totalQualityPoints,
        'gpa' => $gpa,
        'cgpa' => $gpa,
        'max_gpa' => 4.0,
    ];
}
