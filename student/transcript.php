<?php
require_once __DIR__ . '/_records.php';

$registrationCode = trim($_GET['registration_code'] ?? 'EDU-2026-001');
$student = student_profile($registrationCode);
$results = approved_results($registrationCode);
$summary = academic_summary($results);
$issuedDate = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transcript | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="transcript-body">
    <div class="print-actions">
        <a class="button secondary" href="index.php">Back to Results</a>
        <button type="button" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="transcript-sheet">
        <header class="transcript-header">
            <img src="../assets/logo.png" alt="EduTranscript logo">
            <div>
                <p class="eyebrow">Official Academic Transcript</p>
                <h1>EduTranscript</h1>
                <p>Student Results & Transcript Management System</p>
            </div>
        </header>

        <section class="transcript-meta">
            <div>
                <span>Student Name</span>
                <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
            </div>
            <div>
                <span>Registration Code</span>
                <strong><?php echo htmlspecialchars($student['registration_code']); ?></strong>
            </div>
            <div>
                <span>Programme</span>
                <strong><?php echo htmlspecialchars($student['programme']); ?></strong>
            </div>
            <div>
                <span>Level</span>
                <strong><?php echo htmlspecialchars($student['level']); ?></strong>
            </div>
            <div>
                <span>Department</span>
                <strong><?php echo htmlspecialchars($student['department']); ?></strong>
            </div>
            <div>
                <span>Faculty</span>
                <strong><?php echo htmlspecialchars($student['faculty']); ?></strong>
            </div>
            <div>
                <span>Academic Session</span>
                <strong><?php echo htmlspecialchars($student['academic_session']); ?></strong>
            </div>
            <div>
                <span>Semester</span>
                <strong><?php echo htmlspecialchars($student['semester']); ?></strong>
            </div>
        </section>

        <section>
            <h2>Approved Academic Results</h2>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Credit</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Grade Point</th>
                        <th>Quality Point</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['code']); ?></td>
                            <td><?php echo htmlspecialchars($result['course']); ?></td>
                            <td><?php echo htmlspecialchars((string) $result['credit']); ?></td>
                            <td><?php echo htmlspecialchars((string) $result['score']); ?></td>
                            <td><?php echo htmlspecialchars($result['grade']); ?></td>
                            <td><?php echo htmlspecialchars(number_format((float) $result['point'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(number_format((float) $result['credit'] * (float) $result['point'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="transcript-summary">
            <div>Total Credits<strong><?php echo htmlspecialchars((string) $summary['total_credits']); ?></strong></div>
            <div>Total Quality Points<strong><?php echo htmlspecialchars(number_format($summary['quality_points'], 2)); ?></strong></div>
            <div>GPA<strong><?php echo htmlspecialchars(number_format($summary['gpa'], 2)); ?></strong></div>
            <div>CGPA<strong><?php echo htmlspecialchars(number_format($summary['cgpa'], 2)); ?></strong></div>
            <div>Max GPA<strong><?php echo htmlspecialchars(number_format($summary['max_gpa'], 2)); ?></strong></div>
        </section>

        <footer class="transcript-footer">
            <div>
                <span>Issued Date</span>
                <strong><?php echo htmlspecialchars($issuedDate); ?></strong>
            </div>
            <div>
                <span>Status</span>
                <strong>Approved and Published</strong>
            </div>
        </footer>
    </main>
</body>
    <script>
    (() => {
        const savedMode = localStorage.getItem('edutranscript_mode') || 'light';
        const savedBg = localStorage.getItem('edutranscript_bg') || 'none';
        document.body.classList.remove('bg-image','bg-photo1','bg-photo2');
        if(savedBg === 'pattern') document.body.classList.add('bg-image');
        if(savedBg === 'photo1') document.body.classList.add('bg-photo1');
        if(savedBg === 'photo2') document.body.classList.add('bg-photo2');
        document.documentElement.classList.remove('theme-dark');
        if(savedMode === 'dark') document.documentElement.classList.add('theme-dark');
    })();
    </script>
</html>
