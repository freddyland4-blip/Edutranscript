<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/course_helpers.php';

require_auth('lecturer');

function gradeFromScore(float $score): string
{
    if ($score >= 70) {
        return 'A';
    }

    if ($score >= 60) {
        return 'B';
    }

    if ($score >= 50) {
        return 'C';
    }

    if ($score >= 45) {
        return 'D';
    }

    if ($score >= 40) {
        return 'E';
    }

    return 'F';
}

$courses = fetch_courses();
$selectedCourseId = (string) ($_POST['course_id'] ?? $_GET['course_id'] ?? ($courses[0]['id'] ?? ''));
$course = selected_course($courses, $selectedCourseId);

$students = [
    ['reg' => 'EDU-2026-001', 'name' => 'Amina Johnson', 'ca' => 24, 'exam' => 54],
    ['reg' => 'EDU-2026-002', 'name' => 'Daniel Okoro', 'ca' => 20, 'exam' => 46],
    ['reg' => 'EDU-2026-003', 'name' => 'Mary Williams', 'ca' => 22, 'exam' => 49],
];

$message = '';
$isSubmitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$adminPermission = false;
$canModify = !$isSubmitted || $adminPermission;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($students as $index => $student) {
        $ca = (float) ($_POST['ca'][$index] ?? 0);
        $exam = (float) ($_POST['exam'][$index] ?? 0);
        $students[$index]['ca'] = min(max($ca, 0), (float) $course['ca_max']);
        $students[$index]['exam'] = min(max($exam, 0), (float) $course['exam_max']);
    }

    $message = 'Results submitted successfully. Further modification now requires admin permission.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <aside class="sidebar">
            <img src="../assets/logo.png" alt="EduTranscript logo">
            <div>
                <h2>Lecturer Portal</h2>
                <p>Enter marks for assigned courses only.</p>
                <a href="../index.php">Back to portals</a>
            </div>
        </aside>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">Assigned Course</p>
                    <h1><?php echo htmlspecialchars($course['course_code']); ?> Result Entry</h1>
                </div>
                <span class="badge <?php echo $canModify ? '' : 'locked'; ?>">
                    <?php echo $canModify ? 'Open For Entry' : 'Locked After Submission'; ?>
                </span>
            </div>

            <?php if ($message): ?>
                <p class="notice"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <section class="panel">
                <form method="get" class="form-grid">
                    <label>
                        Select Course For Result Filling
                        <select name="course_id">
                            <?php foreach ($courses as $availableCourse): ?>
                                <option value="<?php echo htmlspecialchars((string) $availableCourse['id']); ?>" <?php echo (string) $availableCourse['id'] === (string) $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($availableCourse['course_code'] . ' - ' . $availableCourse['course_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit">Load Course</button>
                </form>
            </section>

            <section class="panel">
                <div class="course-summary">
                    <div>
                        <span>Course</span>
                        <strong><?php echo htmlspecialchars($course['course_title']); ?></strong>
                    </div>
                    <div>
                        <span>Credit Unit</span>
                        <strong><?php echo htmlspecialchars((string) $course['credit_unit']); ?></strong>
                    </div>
                    <div>
                        <span>CA Mark</span>
                        <strong><?php echo htmlspecialchars((string) $course['ca_max']); ?></strong>
                    </div>
                    <div>
                        <span>Exam Mark</span>
                        <strong><?php echo htmlspecialchars((string) $course['exam_max']); ?></strong>
                    </div>
                    <div>
                        <span>Total Mark</span>
                        <strong><?php echo htmlspecialchars((string) $course['total_mark']); ?></strong>
                    </div>
                </div>
            </section>

            <form method="post" class="panel" id="result-form">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars((string) $course['id']); ?>">
                <h2>Student Marks</h2>
                <p class="form-hint">Enter marks based on the course mark structure. CA cannot exceed <?php echo htmlspecialchars((string) $course['ca_max']); ?> and exam cannot exceed <?php echo htmlspecialchars((string) $course['exam_max']); ?>.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Registration Code</th>
                            <th>Student Name</th>
                            <th>CA / <?php echo htmlspecialchars((string) $course['ca_max']); ?></th>
                            <th>Exam / <?php echo htmlspecialchars((string) $course['exam_max']); ?></th>
                            <th>Total</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <?php
                                $total = (float) $student['ca'] + (float) $student['exam'];
                                $grade = gradeFromScore($total);
                            ?>
                            <tr class="mark-row">
                                <td><?php echo htmlspecialchars($student['reg']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td>
                                    <input class="mark-input ca-input" type="number" name="ca[]" min="0" max="<?php echo htmlspecialchars((string) $course['ca_max']); ?>" step="0.01" value="<?php echo htmlspecialchars((string) $student['ca']); ?>" <?php echo $canModify ? '' : 'readonly'; ?>>
                                </td>
                                <td>
                                    <input class="mark-input exam-input" type="number" name="exam[]" min="0" max="<?php echo htmlspecialchars((string) $course['exam_max']); ?>" step="0.01" value="<?php echo htmlspecialchars((string) $student['exam']); ?>" <?php echo $canModify ? '' : 'readonly'; ?>>
                                </td>
                                <td class="total-cell"><?php echo htmlspecialchars((string) $total); ?></td>
                                <td><span class="badge grade-cell"><?php echo htmlspecialchars($grade); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="form-actions">
                    <button type="submit" <?php echo $canModify ? '' : 'disabled'; ?>>Submit Results</button>
                    <?php if (!$canModify): ?>
                        <span class="badge locked">Ask admin for edit permission</span>
                    <?php endif; ?>
                </div>
            </form>

        </main>
    </div>

    <script>
        const rows = document.querySelectorAll('.mark-row');

        function gradeFromScore(score) {
            if (score >= 70) return 'A';
            if (score >= 60) return 'B';
            if (score >= 50) return 'C';
            if (score >= 45) return 'D';
            if (score >= 40) return 'E';
            return 'F';
        }

        rows.forEach((row) => {
            const caInput = row.querySelector('.ca-input');
            const examInput = row.querySelector('.exam-input');
            const totalCell = row.querySelector('.total-cell');
            const gradeCell = row.querySelector('.grade-cell');

            function updateTotal() {
                const ca = Number(caInput.value || 0);
                const exam = Number(examInput.value || 0);
                const caMax = Number(caInput.max);
                const examMax = Number(examInput.max);
                const caInvalid = ca < 0 || ca > caMax;
                const examInvalid = exam < 0 || exam > examMax;

                caInput.classList.toggle('invalid', caInvalid);
                examInput.classList.toggle('invalid', examInvalid);

                const total = ca + exam;
                totalCell.textContent = total.toFixed(total % 1 === 0 ? 0 : 2);
                gradeCell.textContent = gradeFromScore(total);
            }

            caInput.addEventListener('input', updateTotal);
            examInput.addEventListener('input', updateTotal);
            updateTotal();
        });
    </script>
</body>
</html>
