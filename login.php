<?php
require_once 'includes/functions.php';
if (current_user()) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = trim($_POST['login'] ?? '');
    $pw = $_POST['password'] ?? '';
    $s = db()->prepare('SELECT * FROM users WHERE (email=? OR staff_no=?) AND status="active"');
    $s->execute([$id, $id]);
    $u = $s->fetch();
    if ($u && password_verify($pw, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $u['id'];
        redirect('dashboard.php');
    }
    $error = 'Invalid login details or inactive account.';
}
$title = 'Login';
require 'includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="text-center mb-3">
          <i class="bi bi-calendar-check text-success" style="font-size:2.5rem"></i>
          <h5 class="mt-2 mb-0">Workers' Annual Leave<br>Management System</h5>
          <small class="text-muted">NILEST, Samaru-Zaria</small>
        </div>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Email or Staff Number</label>
            <input type="text" name="login" class="form-control" required autofocus value="<?= e($_POST['login'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-success w-100">Login</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
