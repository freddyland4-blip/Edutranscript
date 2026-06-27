<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

function db_connection(): ?PDO
{
    return app_db();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function generate_registration_code(): string
{
    return 'EDU-' . date('Y') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
}

function demo_records(string $type): array
{
    $defaults = [
        'students' => [
            ['id' => 1, 'registration_code' => 'EDU-2026-001', 'full_name' => 'Amina Johnson', 'programme' => 'Computer Science', 'level_name' => '100 Level'],
            ['id' => 2, 'registration_code' => 'EDU-2026-002', 'full_name' => 'Daniel Okoro', 'programme' => 'Computer Science', 'level_name' => '100 Level'],
            ['id' => 3, 'registration_code' => 'EDU-2026-003', 'full_name' => 'Mary Williams', 'programme' => 'Information Systems', 'level_name' => '200 Level'],
        ],
        'lecturers' => [
            ['id' => 1, 'full_name' => 'Dr. Grace Adams', 'email' => 'grace.adams@school.edu'],
            ['id' => 2, 'full_name' => 'Mr. Peter Cole', 'email' => 'peter.cole@school.edu'],
        ],
        'courses' => [
            ['id' => 1, 'course_code' => 'CSC101', 'course_title' => 'Introduction to Computer Science', 'credit_unit' => 3, 'ca_max' => 30, 'exam_max' => 70],
            ['id' => 2, 'course_code' => 'MTH101', 'course_title' => 'Elementary Mathematics', 'credit_unit' => 3, 'ca_max' => 40, 'exam_max' => 60],
            ['id' => 3, 'course_code' => 'GST101', 'course_title' => 'Use of English', 'credit_unit' => 2, 'ca_max' => 30, 'exam_max' => 70],
        ],
    ];

    $records = array_merge($defaults[$type] ?? [], $_SESSION['demo_' . $type] ?? []);
    $deletedIds = $_SESSION['demo_deleted_' . $type] ?? [];
    $updates = $_SESSION['demo_updates_' . $type] ?? [];

    $records = array_filter(
        $records,
        fn (array $record): bool => !in_array((string) ($record['id'] ?? ''), $deletedIds, true)
    );

    return array_values(array_map(
        function (array $record) use ($updates): array {
            $id = (string) ($record['id'] ?? '');
            return isset($updates[$id]) ? array_merge($record, $updates[$id]) : $record;
        },
        $records
    ));
}

function add_demo_record(string $type, array $record): void
{
    $_SESSION['demo_' . $type] ??= [];
    $ids = array_map(fn (array $item): int => (int) ($item['id'] ?? 0), demo_records($type));
    $record['id'] = $ids ? max($ids) + 1 : 1;
    $_SESSION['demo_' . $type][] = $record;
}

function find_demo_record(string $type, string $id): ?array
{
    foreach (demo_records($type) as $record) {
        if ((string) ($record['id'] ?? '') === $id) {
            return $record;
        }
    }

    return null;
}

function update_demo_record(string $type, string $id, array $updates): bool
{
    $_SESSION['demo_' . $type] ??= [];

    foreach ($_SESSION['demo_' . $type] as $index => $record) {
        if ((string) ($record['id'] ?? '') === $id) {
            $_SESSION['demo_' . $type][$index] = array_merge($record, $updates);
            return true;
        }
    }

    if (find_demo_record($type, $id)) {
        $_SESSION['demo_updates_' . $type][$id] = $updates;
        return true;
    }

    return false;
}

function delete_demo_record(string $type, string $id): bool
{
    $_SESSION['demo_' . $type] ??= [];

    foreach ($_SESSION['demo_' . $type] as $index => $record) {
        if ((string) ($record['id'] ?? '') === $id) {
            array_splice($_SESSION['demo_' . $type], $index, 1);
            return true;
        }
    }

    if (find_demo_record($type, $id)) {
        $_SESSION['demo_deleted_' . $type][] = $id;
        unset($_SESSION['demo_updates_' . $type][$id]);
        return true;
    }

    return false;
}

function admin_sidebar(string $active): void
{
    $links = [
        'dashboard' => ['Dashboard', 'dashboard.php'],
        'students' => ['Students', 'students.php'],
        'lecturers' => ['Lecturers', 'lecturers.php'],
        'courses' => ['Courses', 'courses.php'],
    ];
    ?>
    <aside class="sidebar">
        <img src="../assets/logo.png" alt="EduTranscript logo">
        <div>
            <h2>Admin Portal</h2>
            <p>Manage records and permissions. Marks remain lecturer-owned.</p>
            <nav class="side-nav" aria-label="Admin navigation">
                <?php foreach ($links as $key => [$label, $href]): ?>
                    <a class="<?php echo $active === $key ? 'active' : ''; ?>" href="<?php echo e($href); ?>"><?php echo e($label); ?></a>
                <?php endforeach; ?>
                <a href="../index.php">Back to portals</a>
            </nav>
        </div>
    </aside>
    <?php
}
