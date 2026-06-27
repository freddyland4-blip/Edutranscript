<?php
require_once __DIR__ . '/../config/auth.php';

$error = null;
$email = 'lecturer@school.edu';
$password = 'password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login_user('lecturer', $email, $password, $error)) {
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login | EduTranscript</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="login-wrap">
        <section class="login-card">
            <img src="../assets/logo.png" alt="EduTranscript logo">
            <p class="eyebrow">Lecturer Portal</p>
            <h1>Login</h1>
            <?php if ($error): ?>
                <p class="notice warning"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <label>Email Address<input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required></label>
                <label>Password<input type="password" name="password" value="" required></label>
                <button type="submit">Open Dashboard</button>
                <a class="button secondary" href="../index.php">Back to Portals</a>
            </form>
        </section>
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
