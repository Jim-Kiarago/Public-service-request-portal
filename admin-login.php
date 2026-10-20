<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
if (current_user()) {
    redirect(current_user()['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
}

$pageTitle = 'Administrator sign in';
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$email]);
        $record = $stmt->fetch();
        if ($record && password_verify($password, $record['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$record['id'],
                'full_name' => $record['full_name'],
                'email' => $record['email'],
                'role' => 'admin',
            ];
            flash('success', 'Welcome back, ' . $record['full_name'] . '.');
            redirect('admin.php');
        }
        $error = 'Administrator email or password is incorrect.';
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-layout"><div class="auth-aside"><span class="eyebrow">SERVICE OPERATIONS</span><h1>Keep services<br><span>moving forward.</span></h1><p>Sign in to review public service reports, update their status, and keep citizens informed.</p><div class="aside-points"><span>Review incoming requests</span><span>Manage status updates</span><span>Track service progress</span></div></div>
<section class="auth-card"><span class="eyebrow">ADMINISTRATOR ACCESS</span><h2>Admin sign in</h2><p class="muted">Use your administrator account credentials.</p>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><?= csrf_field() ?>
<label>Email address<input type="email" name="email" autocomplete="username" required value="<?= e($email) ?>" placeholder="admin@example.com"></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required placeholder="Your password"></label>
<button class="btn btn-primary btn-full" type="submit">Sign in to admin</button></form><p class="auth-switch"><a href="<?= e(url('login.php')) ?>">Sign in as a citizen</a></p></section></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
