<?php
require_once __DIR__ . '/_records.php';

$registrationCode = trim($_POST['registration_code'] ?? 'EDU-2026-001');
$error = null;

if ($registrationCode === '') {
    $registrationCode = 'EDU-2026-001';
}

$sampleResults = approved_results($registrationCode);
$summary = academic_summary($sampleResults);
$student = student_profile($registrationCode);
if ($student['registration_code'] !== $registrationCode && count($sampleResults) === 0) {
    $error = 'Registration code not found. Showing sample results.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <aside class="sidebar">
            <img src="../assets/logo.png" alt="EduTranscript logo">
            <div>
                <h2>Student Portal</h2>
                <p>Results are view-only and require a registration code.</p>
                <a href="../index.php">Back to portals</a>
            </div>
        </aside>
        <div style="position:fixed;right:18px;top:18px;z-index:9999">
            <select id="eduthm">
                <option value="default">Default</option>
                <option value="image">Image</option>
                <option value="dark">Dark</option>
            </select>
        </div>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">Approved Results</p>
                    <h1>Student Result Viewer</h1>
                </div>
                <span class="badge">View Only</span>
            </div>

            <section class="panel">
                <form method="post" class="form-grid">
                    <label>
                        Registration Code
                        <input type="text" name="registration_code" value="<?php echo htmlspecialchars($registrationCode); ?>" placeholder="Example: EDU-2026-001" required>
                    </label>
                    <button type="submit">View Results</button>
                </form>
                <?php if ($error): ?>
                    <p class="notice warning"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Results for <?php echo htmlspecialchars($registrationCode); ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Credit</th>
                            <th>Score</th>
                            <th>Grade</th>
                            <th>Point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sampleResults as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['code']); ?></td>
                                <td><?php echo htmlspecialchars($result['course']); ?></td>
                                <td><?php echo htmlspecialchars((string) $result['credit']); ?></td>
                                <td><?php echo htmlspecialchars((string) $result['score']); ?></td>
                                <td><?php echo htmlspecialchars($result['grade']); ?></td>
                                <td><?php echo htmlspecialchars((string) $result['point']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="stats-grid">
                <div class="stat">Semester GPA<strong><?php echo htmlspecialchars(number_format($summary['gpa'], 2)); ?></strong></div>
                <div class="stat">CGPA<strong><?php echo htmlspecialchars(number_format($summary['cgpa'], 2)); ?></strong></div>
                <div class="stat">Total Credits<strong><?php echo htmlspecialchars((string) $summary['total_credits']); ?></strong></div>
                <div class="stat">Max GPA<strong>4.00</strong></div>
            </section>

            <section class="panel transcript-action-panel">
                <div>
                    <h2>Transcript</h2>
                    <p class="form-hint">Generate a printable transcript from approved results for this registration code.</p>
                </div>
                <a class="button secondary" href="transcript.php?registration_code=<?php echo urlencode($registrationCode); ?>">Generate Transcript</a>
            </section>
        </main>
    </div>
</body>
<script>
(() => {
    const sel = document.getElementById('eduthm');
    if (!sel) return;
    function apply(mode, bg){
        document.documentElement.classList.remove('theme-dark');
        if(mode === 'dark') document.documentElement.classList.add('theme-dark');
        document.body.classList.remove('bg-image','bg-photo1','bg-photo2');
        if(bg === 'pattern') document.body.classList.add('bg-image');
        if(bg === 'photo1') document.body.classList.add('bg-photo1');
        if(bg === 'photo2') document.body.classList.add('bg-photo2');
        localStorage.setItem('edutranscript_mode', mode);
        localStorage.setItem('edutranscript_bg', bg);
    }

    sel.addEventListener('change', e=>{
        const mode = localStorage.getItem('edutranscript_mode') || 'light';
        apply(mode, e.target.value);
    });

    const savedMode = localStorage.getItem('edutranscript_mode') || 'light';
    const savedBg = localStorage.getItem('edutranscript_bg') || 'none';
    sel.value = savedBg;
    apply(savedMode, savedBg);
})();
</script>
</html>
