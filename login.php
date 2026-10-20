<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
if (current_user()) redirect('dashboard.php');
$pageTitle = 'Sign in';
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $record = $stmt->fetch();
        if ($record && password_verify($password, $record['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$record['id'], 'full_name' => $record['full_name'], 'email' => $record['email'], 'role' => $record['role']];
            flash('success', 'Welcome back, ' . $record['full_name'] . '.');
            redirect($record['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
        }
        $error = 'Email or password is incorrect.';
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-layout"><div class="auth-aside"><span class="eyebrow">WELCOME BACK</span><h1>Your community.<br><span>Your say.</span></h1><p>Sign in to follow your reports and see the latest updates from your service team.</p><div class="aside-points"><span>✓ Track every submission</span><span>✓ See progress updates</span><span>✓ Help make services better</span></div></div>
<section class="auth-card"><span class="eyebrow">SIGN IN</span><h2>Welcome back</h2><p class="muted">Enter your account details below.</p>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><?= csrf_field() ?>
<label>Email address<input type="email" name="email" autocomplete="email" required value="<?= e($email) ?>" placeholder="you@example.com"></label>
<label>Password<input type="password" name="password" autocomplete="current-password" required placeholder="Your password"></label>
<button class="btn btn-primary btn-full" type="submit">Sign in <span>→</span></button></form><p class="auth-switch">New to the portal? <a href="<?= e(url('register.php')) ?>">Create an account</a></p><p class="auth-switch"><a href="<?= e(url('admin-login.php')) ?>">Administrator sign in</a></p></section></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
