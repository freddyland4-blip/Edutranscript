<?php
require_once __DIR__ . '/_helpers.php';

function fetch_lecturers(string &$error): array
{
    $lecturers = demo_records('lecturers');
    $db = db_connection();

    if ($db) {
        try {
            return $db->query('SELECT id, full_name, email FROM lecturers ORDER BY id DESC')->fetchAll();
        } catch (Throwable $exception) {
            $error = $error ?: 'Lecturers table is not ready. Import database/schema.sql to enable MySQL records.';
        }
    }

    return $lecturers;
}

function find_lecturer(array $lecturers, string $id): ?array
{
    foreach ($lecturers as $lecturer) {
        if ((string) $lecturer['id'] === $id) {
            return $lecturer;
        }
    }

    return null;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $id = (string) ($_POST['id'] ?? '');
    $db = db_connection();

    if ($action === 'delete') {
        if ($id === '') {
            $error = 'Select a lecturer to delete.';
        } elseif ($db) {
            try {
                $statement = $db->prepare('DELETE FROM lecturers WHERE id = ?');
                $statement->execute([$id]);
                $message = 'Lecturer deleted successfully.';
            } catch (Throwable $exception) {
                $error = 'Lecturer could not be deleted because the record may already be linked to courses or results.';
            }
        } elseif (delete_demo_record('lecturers', $id)) {
            $message = 'Lecturer deleted from the demo list.';
        } else {
            $error = 'Lecturer record was not found.';
        }
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($fullName === '' || $email === '') {
            $error = 'Please complete the lecturer name and email.';
        } elseif ($action === 'create' && $password === '') {
            $error = 'Please create a temporary password for the lecturer.';
        } elseif ($action === 'update') {
            if ($id === '') {
                $error = 'Select a lecturer to update.';
            } elseif ($db) {
                try {
                    if ($password !== '') {
                        $statement = $db->prepare('UPDATE lecturers SET full_name = ?, email = ?, password_hash = ? WHERE id = ?');
                        $statement->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $id]);
                    } else {
                        $statement = $db->prepare('UPDATE lecturers SET full_name = ?, email = ? WHERE id = ?');
                        $statement->execute([$fullName, $email, $id]);
                    }
                    $message = 'Lecturer information updated successfully.';
                } catch (Throwable $exception) {
                    $error = 'Lecturer was not updated. Check that the email is unique.';
                }
            } elseif (update_demo_record('lecturers', $id, [
                'full_name' => $fullName,
                'email' => $email,
            ])) {
                $message = 'Lecturer information updated in the demo list.';
            } else {
                $error = 'Lecturer record was not found.';
            }
        } elseif ($db) {
            try {
                $statement = $db->prepare('INSERT INTO lecturers (full_name, email, password_hash) VALUES (?, ?, ?)');
                $statement->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);
                $message = 'Lecturer added successfully.';
            } catch (Throwable $exception) {
                $error = 'Lecturer was not saved to MySQL. Check that the schema has been imported and the email is unique.';
            }
        } else {
            add_demo_record('lecturers', [
                'full_name' => $fullName,
                'email' => $email,
            ]);
            $message = 'Lecturer added to the demo list. Import the schema to save permanently in MySQL.';
        }
    }
}

$lecturers = fetch_lecturers($error);
$editingId = (string) ($_GET['edit'] ?? '');
$editingLecturer = $editingId !== '' ? find_lecturer($lecturers, $editingId) : null;
$isEditing = (bool) $editingLecturer;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lecturers | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-shell">
        <?php admin_sidebar('lecturers'); ?>

        <main class="content">
            <div class="topbar">
                <div>
                    <p class="eyebrow">Lecturer Records</p>
                    <h1>Manage Lecturers</h1>
                </div>
                <span class="badge">Add / Edit / Delete</span>
            </div>

            <?php if ($message): ?><p class="notice"><?php echo e($message); ?></p><?php endif; ?>
            <?php if ($error): ?><p class="notice warning"><?php echo e($error); ?></p><?php endif; ?>

            <section class="management-grid">
                <form class="panel stack-form" method="post">
                    <h2><?php echo $isEditing ? 'Edit Lecturer' : 'Add Lecturer'; ?></h2>
                    <input type="hidden" name="action" value="<?php echo $isEditing ? 'update' : 'create'; ?>">
                    <input type="hidden" name="id" value="<?php echo e($editingLecturer['id'] ?? ''); ?>">
                    <label>Full Name<input type="text" name="full_name" value="<?php echo e($editingLecturer['full_name'] ?? ''); ?>" placeholder="Lecturer full name" required></label>
                    <label>Email Address<input type="email" name="email" value="<?php echo e($editingLecturer['email'] ?? ''); ?>" placeholder="lecturer@school.edu" required></label>
                    <label>
                        <?php echo $isEditing ? 'New Password Optional' : 'Temporary Password'; ?>
                        <input type="password" name="password" placeholder="<?php echo $isEditing ? 'Leave blank to keep current password' : 'Create login password'; ?>" <?php echo $isEditing ? '' : 'required'; ?>>
                    </label>
                    <div class="form-actions">
                        <button type="submit"><?php echo $isEditing ? 'Save Lecturer Changes' : 'Add Lecturer'; ?></button>
                        <?php if ($isEditing): ?>
                            <a class="button secondary" href="lecturers.php">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>

                <section class="panel">
                    <h2>Lecturer List</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email Address</th>
                                <th>Permission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lecturers as $lecturer): ?>
                                <tr>
                                    <td><?php echo e($lecturer['full_name']); ?></td>
                                    <td><?php echo e($lecturer['email']); ?></td>
                                    <td><span class="badge locked">Edit Locked</span></td>
                                    <td>
                                        <div class="table-actions">
                                            <a class="button small" href="lecturers.php?edit=<?php echo e($lecturer['id']); ?>">Edit</a>
                                            <form method="post" onsubmit="return confirm('Delete this lecturer record?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo e($lecturer['id']); ?>">
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
