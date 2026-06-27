<?php
require_once __DIR__ . '/_helpers.php';

function fetch_students(string &$error): array
{
    $students = demo_records('students');
    $db = db_connection();

    if ($db) {
        try {
            return $db->query('SELECT id, registration_code, full_name, programme, level_name FROM students ORDER BY id DESC')->fetchAll();
        } catch (Throwable $exception) {
            $error = $error ?: 'Students table is not ready. Import database/schema.sql to enable MySQL records.';
        }
    }

    return $students;
}

function find_student(array $students, string $id): ?array
{
    foreach ($students as $student) {
        if ((string) $student['id'] === $id) {
            return $student;
        }
    }

    return null;
}

$message = '';
$error = '';
$suggestedCode = generate_registration_code();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = (string) ($_POST['id'] ?? '');
    $db = db_connection();

    if ($action === 'delete') {
        if ($id === '') {
            $error = 'Select a student to delete.';
        } elseif ($db) {
            try {
                $statement = $db->prepare('DELETE FROM students WHERE id = ?');
                $statement->execute([$id]);
                $message = 'Student deleted successfully.';
            } catch (Throwable $exception) {
                $error = 'Student could not be deleted because the record may already be linked to results.';
            }
        } elseif (delete_demo_record('students', $id)) {
            $message = 'Student deleted from the demo list.';
        } else {
            $error = 'Student record was not found.';
        }
    } else {
        $registrationCode = trim($_POST['registration_code'] ?? $suggestedCode);
        $fullName = trim($_POST['full_name'] ?? '');
        $programme = trim($_POST['programme'] ?? '');
        $levelName = trim($_POST['level_name'] ?? '');

        if ($registrationCode === '' || $fullName === '' || $programme === '' || $levelName === '') {
            $error = 'Please complete all student fields.';
        } elseif ($action === 'update') {
            if ($id === '') {
                $error = 'Select a student to update.';
            } elseif ($db) {
                try {
                    $statement = $db->prepare('UPDATE students SET registration_code = ?, full_name = ?, programme = ?, level_name = ? WHERE id = ?');
                    $statement->execute([$registrationCode, $fullName, $programme, $levelName, $id]);
                    $message = 'Student information updated successfully.';
                } catch (Throwable $exception) {
                    $error = 'Student was not updated. Check that the registration code is unique.';
                }
            } elseif (update_demo_record('students', $id, [
                'registration_code' => $registrationCode,
                'full_name' => $fullName,
                'programme' => $programme,
                'level_name' => $levelName,
            ])) {
                $message = 'Student information updated in the demo list.';
            } else {
                $error = 'Student record was not found.';
            }
        } elseif ($db) {
            try {
                $statement = $db->prepare('INSERT INTO students (registration_code, full_name, programme, level_name) VALUES (?, ?, ?, ?)');
                $statement->execute([$registrationCode, $fullName, $programme, $levelName]);
                $message = 'Student added successfully with registration code ' . $registrationCode . '.';
            } catch (Throwable $exception) {
                $error = 'Student was not saved to MySQL. Check that the schema has been imported and the code is unique.';
            }
        } else {
            add_demo_record('students', [
                'registration_code' => $registrationCode,
                'full_name' => $fullName,
                'programme' => $programme,
                'level_name' => $levelName,
            ]);
            $message = 'Student added to the demo list. Import the schema to save permanently in MySQL.';
        }
    }
}

$students = fetch_students($error);
$editingId = (string) ($_GET['edit'] ?? '');
$editingStudent = $editingId !== '' ? find_student($students, $editingId) : null;
$isEditing = (bool) $editingStudent;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <?php admin_sidebar('students'); ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">Student Records</p>
                    <h1>Manage Students</h1>
                </div>
                <span class="badge">Add / Edit / Delete</span>
            </div>

            <?php if ($message): ?><p class="notice"><?php echo e($message); ?></p><?php endif; ?>
            <?php if ($error): ?><p class="notice warning"><?php echo e($error); ?></p><?php endif; ?>

            <section class="management-grid">
                <form class="panel stack-form" method="post">
                    <h2><?php echo $isEditing ? 'Edit Student' : 'Add Student'; ?></h2>
                    <input type="hidden" name="action" value="<?php echo $isEditing ? 'update' : 'create'; ?>">
                    <input type="hidden" name="id" value="<?php echo e($editingStudent['id'] ?? ''); ?>">
                    <label>
                        Registration Code
                        <input type="text" name="registration_code" value="<?php echo e($editingStudent['registration_code'] ?? $suggestedCode); ?>" <?php echo $isEditing ? '' : 'readonly'; ?> required>
                    </label>
                    <label>Full Name<input type="text" name="full_name" value="<?php echo e($editingStudent['full_name'] ?? ''); ?>" placeholder="Student full name" required></label>
                    <label>Programme<input type="text" name="programme" value="<?php echo e($editingStudent['programme'] ?? ''); ?>" placeholder="Computer Science" required></label>
                    <label>Level<input type="text" name="level_name" value="<?php echo e($editingStudent['level_name'] ?? ''); ?>" placeholder="100 Level" required></label>
                    <div class="form-actions">
                        <button type="submit"><?php echo $isEditing ? 'Save Student Changes' : 'Add Student'; ?></button>
                        <?php if ($isEditing): ?>
                            <a class="button secondary" href="students.php">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>

                <section class="panel">
                    <h2>Student List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Registration Code</th>
                                <th>Full Name</th>
                                <th>Programme</th>
                                <th>Level</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo e($student['registration_code']); ?></td>
                                    <td><?php echo e($student['full_name']); ?></td>
                                    <td><?php echo e($student['programme']); ?></td>
                                    <td><?php echo e($student['level_name']); ?></td>
                                    <td><span class="badge">Active</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <a class="button small" href="students.php?edit=<?php echo e($student['id']); ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this student record?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo e($student['id']); ?>">
                                                <button class="danger small" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
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
