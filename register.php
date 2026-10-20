<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
if (current_user()) redirect('dashboard.php');
$pageTitle = 'Create account';
$errors = [];
$name = $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Enter a name between 2 and 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 10 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) $errors[] = 'Password must be at least 10 characters and include a letter and a number.';
    if ($password !== $confirm) $errors[] = 'The passwords do not match.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'An account with that email already exists.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, "citizen")');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$pdo->lastInsertId(), 'full_name' => $name, 'email' => $email, 'role' => 'citizen'];
        flash('success', 'Your account is ready. You can now submit a service request.');
        redirect('dashboard.php');
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="auth-layout"><div class="auth-aside"><span class="eyebrow">YOUR VOICE MATTERS</span><h1>Small reports.<br><span>Real change.</span></h1><p>Create your account to report local issues, keep track of progress, and stay informed.</p><div class="aside-points"><span>✓ Your requests in one place</span><span>✓ Clear status updates</span><span>✓ Secure account access</span></div></div>
<section class="auth-card"><span class="eyebrow">GET STARTED</span><h2>Create your account</h2><p class="muted">It only takes a minute.</p>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-stack" novalidate><?= csrf_field() ?>
<label>Full name<input name="full_name" autocomplete="name" required maxlength="100" value="<?= e($name) ?>" placeholder="e.g. Jane Wanjiku"></label>
<label>Email address<input type="email" name="email" autocomplete="email" required maxlength="190" value="<?= e($email) ?>" placeholder="you@example.com"></label>
<label>Password<input type="password" name="password" autocomplete="new-password" required minlength="10" placeholder="At least 10 characters"><small>Use at least 10 characters, including a letter and a number.</small></label>
<label>Confirm password<input type="password" name="confirm_password" autocomplete="new-password" required placeholder="Enter password again"></label>
<button class="btn btn-primary btn-full" type="submit">Create account <span>→</span></button></form><p class="auth-switch">Already registered? <a href="<?= e(url('login.php')) ?>">Sign in</a></p></section></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
