<?php
require_once __DIR__ . '/functions.php';
$user = current_user();
$page = basename($_SERVER['PHP_SELF']);
function nav_link(string $file, string $icon, string $label, string $page): string {
    $a = $page === $file ? ' active' : '';
    return '<li class="nav-item"><a class="nav-link' . $a . '" href="' . $file . '"><i class="bi bi-' . $icon . ' me-1"></i>' . $label . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Home') ?> | <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body { background:#f4f6f9; }
  .navbar-brand small { display:block; font-size:.65rem; opacity:.8; font-weight:400; }
  .stat-card { border:0; border-left:4px solid #198754; }
  .stat-card .num { font-size:1.9rem; font-weight:700; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#14532d">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-calendar-check me-1"></i>NILEST Leave System<small>Samaru-Zaria</small></a>
    <?php if ($user): ?>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav me-auto">
      <?= nav_link('dashboard.php', 'speedometer2', 'Dashboard', $page) ?>
      <?php if ($user['role'] === 'staff'): ?>
        <?= nav_link('apply.php', 'pencil-square', 'Apply for Leave', $page) ?>
        <?= nav_link('my_leaves.php', 'list-check', 'My Applications', $page) ?>
      <?php else: ?>
        <?= nav_link('admin_requests.php', 'inbox', 'Leave Requests', $page) ?>
        <?= nav_link('admin_records.php', 'database', 'Leave Records', $page) ?>
        <?= nav_link('admin_staff.php', 'people', 'Staff', $page) ?>
        <?= nav_link('admin_departments.php', 'building', 'Departments', $page) ?>
      <?php endif; ?>
    </ul><ul class="navbar-nav"><li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle me-1"></i><?= e($user['full_name']) ?></a>
      <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="profile.php">My Profile / Password</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="logout.php">Logout</a></li></ul>
    </li></ul></div>
    <?php endif; ?>
  </div>
</nav>
<main class="container py-4">
<?php show_flash(); ?>
