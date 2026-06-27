<?php
require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/../config/course_helpers.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
    $courseTitle = trim($_POST['course_title'] ?? '');
    $creditUnit = (int) ($_POST['credit_unit'] ?? 0);
    $caMax = (float) ($_POST['ca_max'] ?? 0);
    $examMax = (float) ($_POST['exam_max'] ?? 0);

    if ($courseCode === '' || $courseTitle === '' || $creditUnit < 1 || $caMax < 1 || $examMax < 1) {
        $error = 'Please complete all course fields.';
    } elseif (($caMax + $examMax) <= 0) {
        $error = 'Course total mark must be greater than zero.';
    } else {
        $saved = save_course_record([
            'course_code' => $courseCode,
            'course_title' => $courseTitle,
            'credit_unit' => $creditUnit,
            'ca_max' => $caMax,
            'exam_max' => $examMax,
        ], $error);

        if ($saved) {
            $message = app_db()
                ? 'Course added successfully.'
                : 'Course added to the demo list. It will now appear in the lecturer result-entry portal.';
        }
    }
}

$courses = fetch_courses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <?php admin_sidebar('courses'); ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">Course Records</p>
                    <h1>Manage Courses</h1>
                </div>
                <span class="badge">Admin Managed</span>
            </div>

            <?php if ($message): ?><p class="notice"><?php echo e($message); ?></p><?php endif; ?>
            <?php if ($error): ?><p class="notice warning"><?php echo e($error); ?></p><?php endif; ?>

            <section class="management-grid">
                <form class="panel stack-form" method="post">
                    <h2>Add Course</h2>
                    <label>Course Code<input type="text" name="course_code" placeholder="CSC101" required></label>
                    <label>Course Title<input type="text" name="course_title" placeholder="Introduction to Computer Science" required></label>
                    <label>Credit Unit<input type="number" name="credit_unit" min="1" max="12" value="3" required></label>
                    <label>CA Mark<input type="number" name="ca_max" min="1" max="100" step="0.01" value="30" required></label>
                    <label>Exam Mark<input type="number" name="exam_max" min="1" max="100" step="0.01" value="70" required></label>
                    <button type="submit">Add Course</button>
                </form>

                <section class="panel">
                    <h2>Course List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th>Credit Unit</th>
                                <th>CA</th>
                                <th>Exam</th>
                                <th>Total</th>
                                <th>Status</th>
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
                                    <td><span class="badge">Active</span></td>
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
