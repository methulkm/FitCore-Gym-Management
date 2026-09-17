<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$role = $_POST['role'] ?? $_GET['role'] ?? 'admin';
$error = null;

// UC-01: only Admin, Member, Trainer accounts exist and can log in (no visitor login, no self-registration).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1');
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No account found for that email under the selected role.';
    } elseif ($user['status'] !== 'active') {
        // UC-01 alternate path: inactive accounts denied login, told to contact admin.
        $error = 'This account is inactive. Please contact the gym admin.';
    } elseif (!password_verify($password, $user['password_hash'])) {
        $error = 'Incorrect password.';
    } else {
        $displayName = $user['email'];
        if ($role === 'member') {
            $m = $pdo->prepare('SELECT full_name FROM members WHERE user_id = ?');
            $m->execute([$user['user_id']]);
            $displayName = $m->fetchColumn() ?: $displayName;
        } elseif ($role === 'trainer') {
            $t = $pdo->prepare('SELECT full_name FROM trainers WHERE user_id = ?');
            $t->execute([$user['user_id']]);
            $displayName = $t->fetchColumn() ?: $displayName;
        }

        $_SESSION['user'] = [
            'user_id' => $user['user_id'],
            'email'   => $user['email'],
            'role'    => $user['role'],
            'name'    => $displayName,
        ];

        header('Location: ' . base_url(match ($role) {
            'admin'   => 'modules/dashboard/index.php',
            'member'  => 'modules/member-portal/index.php',
            'trainer' => 'modules/trainer-portal/index.php',
            default   => 'public/index.php',
        }));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - FitCore Gym Manager</title>
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
<body class="min-h-screen grid lg:grid-cols-2 bg-white">
  <div class="flex items-center justify-center p-8">
    <div class="w-full max-w-sm">
      <div class="flex items-center gap-3 mb-8">
        <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-600 to-teal-400 flex items-center justify-center text-white font-extrabold">FC</span>
        <span class="font-extrabold text-lg">Fit<span class="text-teal-600">Core</span></span>
      </div>
      <h1 class="text-2xl font-extrabold mb-1">Welcome back</h1>
      <p class="text-slate-500 text-sm mb-6">Sign in to your FitCore portal</p>

      <div class="grid grid-cols-3 gap-2 mb-6">
        <?php foreach (['admin' => 'Admin', 'member' => 'Member', 'trainer' => 'Trainer'] as $key => $label): ?>
          <a href="?role=<?= e($key) ?>" class="text-center text-sm font-bold py-2.5 rounded-[10px] border <?= $role === $key ? 'bg-teal-600 text-white border-teal-600' : 'border-slate-200 text-slate-500 hover:bg-slate-50' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if ($error): ?>
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <input type="hidden" name="role" value="<?= e($role) ?>">
        <div>
          <label class="text-xs font-bold text-slate-500">Email</label>
          <input type="email" name="email" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500" placeholder="you@fitcore.lk">
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Password</label>
          <input type="password" name="password" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-500" placeholder="••••••••">
        </div>
        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] py-3 text-sm">Sign In</button>
      </form>

      <p class="text-xs text-slate-400 mt-6 leading-relaxed">
        Note (BR-03 / BR-17): cleaners, receptionists and maintenance staff do not receive login accounts &mdash;
        their records are managed directly by the Admin.
      </p>
      <p class="text-xs text-slate-400 mt-3">Demo admin: <b>admin@fitcore.lk</b> / <b>Admin@123</b></p>
    </div>
  </div>
  <div class="hidden lg:flex items-center justify-center bg-[#0F172A] relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-teal-600/20 via-transparent to-transparent"></div>
    <div class="relative text-center px-10">
      <p class="text-teal-400 text-xs font-bold tracking-widest mb-4">COLOMBO'S ELITE HIGH-PERFORMANCE GYM</p>
      <h2 class="text-white text-4xl font-extrabold leading-tight mb-6">Build Strength.<br>Build Confidence.<br><span class="text-teal-400">Build Your Best.</span></h2>
      <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto">
        <?php foreach ([['450+','Active Members'],['12+','Certified Trainers'],['68+','Modern Machines'],['24/7','Secure Facility']] as [$num,$lbl]): ?>
          <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <p class="text-2xl font-extrabold text-white"><?= e($num) ?></p>
            <p class="text-xs text-slate-400"><?= e($lbl) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
