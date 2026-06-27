<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function app_db(): ?PDO
{
    global $pdo;
    return $pdo instanceof PDO ? $pdo : null;
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return auth_user() !== null;
}

function get_app_root(): string
{
    $root = dirname($_SERVER['SCRIPT_NAME']);
    return $root === DIRECTORY_SEPARATOR ? '' : $root;
}

function redirect_to_index(): void
{
    $root = get_app_root();
    header('Location: ' . $root . '/index.php');
    exit;
}

function require_auth(string $role): void
{
    if (!is_logged_in() || auth_user()['role'] !== $role) {
        redirect_to_index();
    }
}

function login_user(string $role, string $email, string $password, ?string &$error = null): bool
{
    $error = null;
    $email = trim($email);
    $password = (string) $password;

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
        return false;
    }

    $db = app_db();
    if ($db) {
        $table = $role === 'admin' ? 'admins' : 'lecturers';
        $statement = $db->prepare("SELECT id, full_name, email, password_hash FROM {$table} WHERE email = ? LIMIT 1");
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'role' => $role,
                'id' => (int) $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
            ];
            return true;
        }

        $error = 'Invalid credentials.';
        return false;
    }

    $fallback = [
        'admin' => [
            'id' => 0,
            'full_name' => 'Admin User',
            'email' => 'admin@school.edu',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        ],
        'lecturer' => [
            'id' => 0,
            'full_name' => 'Lecturer User',
            'email' => 'lecturer@school.edu',
            'password_hash' => password_hash('password', PASSWORD_DEFAULT),
        ],
    ];

    $user = $fallback[$role] ?? null;
    if ($user && $user['email'] === $email && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'role' => $role,
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
        ];
        return true;
    }

    $error = 'Invalid credentials.';
    return false;
}

function logout_user(): void
{
    unset($_SESSION['user']);
}
