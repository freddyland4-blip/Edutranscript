<?php
$portals = [
    [
        'title' => 'Student Portal',
        'tagline' => 'View approved results with registration code',
        'href' => 'student/index.php',
        'items' => ['Enter registration code', 'View semester results', 'Check GPA / CGPA', 'View transcript'],
        'accent' => 'student'
    ],
    [
        'title' => 'Lecturer Portal',
        'tagline' => 'Enter and submit assigned course results',
        'href' => 'lecturer/login.php',
        'items' => ['Login securely', 'View assigned courses', 'Enter student marks', 'Modify only with admin permission'],
        'accent' => 'lecturer'
    ],
    [
        'title' => 'Admin Portal',
        'tagline' => 'Manage academic records and permissions',
        'href' => 'admin/login.php',
        'items' => ['Manage students and courses', 'Generate registration codes', 'Approve and publish results', 'Cannot alter marks'],
        'accent' => 'admin'
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTranscript | Student Results & Transcript Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main class="landing">
        <header class="site-header">
            <a class="brand-mark" href="index.php" aria-label="EduTranscript home">
                <img src="assets/logo.png" alt="EduTranscript logo">
                <span>EduTranscript</span>
            </a>

            <nav class="main-nav" aria-label="Main navigation">
                <a href="index.php">Home</a>
                <a href="student/index.php">Student</a>
                <a href="lecturer/login.php">Lecturer</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </header>

        <section class="hero">
            <p class="eyebrow">Student Results & Transcript Management System</p>
            <h1>EduTranscript</h1>
            <p class="brand-copy">Digitalizing Academic Excellence</p>
        </section>

        <section class="portal-wrap">
            <div class="portal-grid" aria-label="System portals">
                <?php foreach ($portals as $portal): ?>
                    <article class="portal-card <?php echo htmlspecialchars($portal['accent']); ?>">
                        <div>
                            <span class="portal-kicker"><?php echo htmlspecialchars($portal['title']); ?></span>
                            <h2><?php echo htmlspecialchars($portal['tagline']); ?></h2>
                        </div>

                        <ul>
                            <?php foreach ($portal['items'] as $item): ?>
                                <li><?php echo htmlspecialchars($item); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <a class="button" href="<?php echo htmlspecialchars($portal['href']); ?>">
                            Open <?php echo htmlspecialchars($portal['title']); ?>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
