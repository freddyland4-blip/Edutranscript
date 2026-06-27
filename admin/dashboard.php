<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/../config/course_helpers.php';

require_auth('admin');

function dashboard_students(): array
{
    $db = db_connection();

    if ($db) {
        try {
            return $db->query('SELECT id, registration_code, full_name, programme, level_name FROM students ORDER BY id DESC')->fetchAll();
        } catch (Throwable $exception) {
            return demo_records('students');
        }
    }

    return demo_records('students');
}

function dashboard_lecturers(): array
{
    $db = db_connection();

    if ($db) {
        try {
            return $db->query('SELECT id, full_name, email FROM lecturers ORDER BY id DESC')->fetchAll();
        } catch (Throwable $exception) {
            return demo_records('lecturers');
        }
    }

    return demo_records('lecturers');
}

function dashboard_pending_approvals(): array
{
    $db = db_connection();

    if ($db) {
        try {
            $query = "
                SELECT
                    r.id,
                    s.registration_code,
                    s.full_name AS student_name,
                    c.course_code,
                    c.course_title,
                    l.full_name AS lecturer_name,
                    r.total_score,
                    r.grade,
                    r.status
                FROM results r
                INNER JOIN students s ON s.id = r.student_id
                INNER JOIN courses c ON c.id = r.course_id
                INNER JOIN lecturers l ON l.id = r.lecturer_id
                WHERE r.status = 'submitted'
                ORDER BY r.updated_at DESC
                LIMIT 8
            ";

            return $db->query($query)->fetchAll();
        } catch (Throwable $exception) {
            return [];
        }
    }

    return [
        [
            'id' => 1,
            'registration_code' => 'EDU-2026-001',
            'student_name' => 'Amina Johnson',
            'course_code' => 'CSC101',
            'course_title' => 'Introduction to Computer Science',
            'lecturer_name' => 'Dr. Grace Adams',
            'total_score' => 78,
            'grade' => 'A',
            'status' => 'submitted',
        ],
        [
            'id' => 2,
            'registration_code' => 'EDU-2026-002',
            'student_name' => 'Daniel Okoro',
            'course_code' => 'MTH101',
            'course_title' => 'Elementary Mathematics',
            'lecturer_name' => 'Mr. Peter Cole',
            'total_score' => 66,
            'grade' => 'B',
            'status' => 'submitted',
        ],
    ];
}

$students = dashboard_students();
$lecturers = dashboard_lecturers();
$courses = fetch_courses();
$pendingApprovals = dashboard_pending_approvals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <?php admin_sidebar('dashboard'); ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">System Control</p>
                    <h1>Admin Dashboard</h1>
                </div>
                <span class="badge locked">Cannot Alter Marks</span>
            </div>

            <section class="stats-grid">
                <a class="stat stat-link" href="#student-details">Students<strong><?php echo e(count($students)); ?></strong></a>
                <a class="stat stat-link" href="#course-details">Courses<strong><?php echo e(count($courses)); ?></strong></a>
                <a class="stat stat-link" href="#lecturer-details">Lecturers<strong><?php echo e(count($lecturers)); ?></strong></a>
                <a class="stat stat-link" href="#approval-details">Pending Approvals<strong><?php echo e(count($pendingApprovals)); ?></strong></a>
            </section>

            <section class="panel">
                <h2>Manage Academic Records</h2>
                <div class="action-grid">
                    <a class="action-card" href="students.php">
                        <span>Students</span>
                        <strong>Add and manage student information</strong>
                    </a>
                    <a class="action-card" href="lecturers.php">
                        <span>Lecturers</span>
                        <strong>Add lecturer accounts for result entry</strong>
                    </a>
                    <a class="action-card" href="courses.php">
                        <span>Courses</span>
                        <strong>Create and manage course records</strong>
                    </a>
                </div>
            </section>

            <section class="panel">
                <h2>Lecturer Result Permission</h2>
                <form class="form-grid">
                    <label>
                        Lecturer
                        <select>
                            <option>Dr. Grace Adams - CSC101</option>
                            <option>Mr. Peter Cole - MTH101</option>
                        </select>
                    </label>
                    <button type="button">Grant Edit Permission</button>
                </form>
            </section>

            <section class="dashboard-details">
                <section class="panel" id="student-details">
                    <div class="section-heading">
                        <h2>Student Details</h2>
                        <a class="button small" href="students.php">Manage Students</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Registration Code</th>
                                <th>Full Name</th>
                                <th>Programme</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo e($student['registration_code']); ?></td>
                                    <td><?php echo e($student['full_name']); ?></td>
                                    <td><?php echo e($student['programme']); ?></td>
                                    <td><?php echo e($student['level_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section class="panel" id="course-details">
                    <div class="section-heading">
                        <h2>Course Details</h2>
                        <a class="button small" href="courses.php">Manage Courses</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Credit Unit</th>
                                <th>CA</th>
                                <th>Exam</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?php echo e($course['course_code']); ?></td>
                                    <td><?php echo e($course['course_title']); ?></td>
                                    <td><?php echo e($course['credit_unit']); ?></td>
                                    <td><?php echo e($course['ca_max']); ?></td>
                                    <td><?php echo e($course['exam_max']); ?></td>
                                    <td><?php echo e($course['total_mark']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section class="panel" id="lecturer-details">
                    <div class="section-heading">
                        <h2>Lecturer Details</h2>
                        <a class="button small" href="lecturers.php">Manage Lecturers</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Result Edit Permission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <tr>
                                    <td><?php echo e($lecturer['full_name']); ?></td>
                                    <td><?php echo e($lecturer['email']); ?></td>
                                    <td><span class="badge locked">Locked</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <section class="panel" id="approval-details">
                    <div class="section-heading">
                        <h2>Pending Approval Details</h2>
                        <span class="badge locked">Admin approves, does not edit marks</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Registration Code</th>
                                <th>Course</th>
                                <th>Lecturer</th>
                                <th>Total Score</th>
                                <th>Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$pendingApprovals): ?>
                                <tr>
                                    <td colspan="7">No pending approvals at the moment.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($pendingApprovals as $approval): ?>
                                <tr>
                                    <td><?php echo e($approval['student_name']); ?></td>
                                    <td><?php echo e($approval['registration_code']); ?></td>
                                    <td><?php echo e($approval['course_code'] . ' - ' . $approval['course_title']); ?></td>
                                    <td><?php echo e($approval['lecturer_name']); ?></td>
                                    <td><?php echo e($approval['total_score']); ?></td>
                                    <td><?php echo e($approval['grade']); ?></td>
                                    <td><span class="badge locked"><?php echo e(ucfirst((string) $approval['status'])); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </section>
        </main>
    </div>
</body>
</html>
