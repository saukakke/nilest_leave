<?php
require_once 'includes/functions.php';
$admin = require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    csrf_check(); $id = (int)$_POST['toggle_id'];
    if ($id === (int)$admin['id']) flash('warning', 'You cannot deactivate your own account.');
    else { db()->prepare('UPDATE users SET status = IF(status="active","inactive","active") WHERE id=?')->execute([$id]); flash('success', 'Staff status updated.'); }
    redirect('admin_staff.php');
}
$year = (int)date('Y');
$rows = db()->query('SELECT u.*, d.name AS department FROM users u LEFT JOIN departments d ON d.id=u.department_id ORDER BY u.full_name')->fetchAll();
$title = 'Staff'; require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Staff Records</h4><a href="admin_staff_form.php" class="btn btn-success"><i class="bi bi-person-plus"></i> Add Staff</a></div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>Staff No</th><th>Name</th><th>Department</th><th>Designation</th><th>Role</th><th>Balance (<?= $year ?>)</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><?= e($r['staff_no']) ?></td><td><?= e($r['full_name']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td><td><?= e($r['department'] ?: '-') ?></td><td><?= e($r['designation'] ?: '-') ?></td><td><?= ucfirst(e($r['role'])) ?></td>
<td><?= $r['role'] === 'staff' ? ($r['annual_entitlement'] - days_used((int)$r['id'], $year, true)) . ' / ' . $r['annual_entitlement'] : '-' ?></td><td><span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($r['status']) ?></span></td><td class="text-nowrap">
<a href="admin_staff_form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
<form method="post" class="d-inline" onsubmit="return confirm('Change this account\'s status?')"><?= csrf_field() ?><input type="hidden" name="toggle_id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-secondary"><?= $r['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button></form>
</td></tr><?php endforeach; ?></tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
