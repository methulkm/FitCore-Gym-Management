<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// BR-21 says forgotten passwords are reset by admin - but there's no one above the admin to do that
// for the admin's own account. Since this environment has no SMTP/email server to send reset links,
// recovery uses a security question set at seed time, kept local to this app (no email dependency).

$stage = $_POST['stage'] ?? 'email';
$error = null;
$success = null;
$email = trim($_POST['email'] ?? '');
$question = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stage === 'email') {
    $stmt = $pdo->prepare('SELECT security_question FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $question = $stmt->fetchColumn();

    if (!$question) {
        $error = 'No recovery question is set up for that email. Please contact another admin.';
        $stage = 'email';
    } else {
        $stage = 'answer';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stage === 'answer') {
    $answer = trim($_POST['answer'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT user_id, security_question, security_answer_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    $question = $user['security_question'] ?? null;

    if (!$user || !$question) {
        $error = 'No recovery question is set up for that email.';
        $stage = 'email';
    } elseif (!password_verify(strtolower($answer), $user['security_answer_hash'])) {
        $error = 'That answer is incorrect.';
        $stage = 'answer';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters.';
        $stage = 'answer';
    } elseif ($newPassword !== $confirm) {
        $error = 'New password and confirmation do not match.';
        $stage = 'answer';
    } else {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['user_id']]);
        $success = 'Password reset. You can now sign in with your new password.';
        $stage = 'done';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password - FitCore Gym Manager</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { teal: { 50:'#F0FDFA',500:'#14B8A6',600:'#0D9488',700:'#0F766E' } },
    fontFamily: { sans: ["'Plus Jakarta Sans'","Inter","sans-serif"] }
  } } };
</script>
<style> body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; } </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-50 p-6">
  <div class="w-full max-w-sm">
    <a href="<?= e(base_url('auth/login.php')) ?>" class="inline-flex items-center gap-1.5 text-slate-500 hover:text-teal-600 text-sm font-semibold mb-6">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
      Back to login
    </a>
    <div class="flex items-center gap-3 mb-8">
      <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-600 to-teal-400 flex items-center justify-center text-white font-extrabold">FC</span>
      <span class="font-extrabold text-lg">Fit<span class="text-teal-600">Core</span></span>
    </div>
    <h1 class="text-2xl font-extrabold mb-1">Reset your password</h1>
    <p class="text-slate-500 text-sm mb-6">Answer your security question to set a new password.</p>

    <?php if ($error): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium px-4 py-3 mb-4"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($stage === 'email'): ?>
      <form method="post" class="space-y-4">
        <input type="hidden" name="stage" value="email">
        <div>
          <label class="text-xs font-bold text-slate-500">Account Email</label>
          <input type="email" name="email" required value="<?= e($email) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500" placeholder="you@fitcore.lk">
        </div>
        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] py-3 text-sm">Continue</button>
      </form>
    <?php elseif ($stage === 'answer'): ?>
      <form method="post" class="space-y-4">
        <input type="hidden" name="stage" value="answer">
        <input type="hidden" name="email" value="<?= e($email) ?>">
        <div>
          <label class="text-xs font-bold text-slate-500">Security Question</label>
          <p class="mt-1 text-sm font-semibold text-slate-800"><?= e($question) ?></p>
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Your Answer</label>
          <input name="answer" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500">
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">New Password</label>
          <input type="password" name="new_password" required minlength="8" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500">
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Confirm New Password</label>
          <input type="password" name="confirm_password" required minlength="8" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500">
        </div>
        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] py-3 text-sm">Reset Password</button>
      </form>
    <?php else: ?>
      <a href="<?= e(base_url('auth/login.php')) ?>" class="block text-center w-full bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] py-3 text-sm">Go to Login</a>
    <?php endif; ?>
  </div>
</body>
</html>
