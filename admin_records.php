<?php
require_once 'includes/functions.php';
require_admin();
$search = trim($_GET['q'] ?? '');
$year = $_GET['year'] ?? '';
$dept = (int)($_GET['dept'] ?? 0);
$status = $_GET['status'] ?? '';
$sql = 'SELECT la.*, u.full_name, u.staff_no, d.name AS department FROM leave_applications la JOIN users u ON u.id=la.user_id LEFT JOIN departments d ON d.id=u.department_id WHERE 1=1';
$p = [];
if ($search !== '') { $sql .= ' AND (u.full_name LIKE ? OR u.staff_no LIKE ?)'; $p[] = "%$search%"; $p[] = "%$search%"; }
if ($year !== '') { $sql .= ' AND la.leave_year=?'; $p[] = (int)$year; }
if ($dept) { $sql .= ' AND u.department_id=?'; $p[] = $dept; }
if (in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) { $sql .= ' AND la.status=?'; $p[] = $status; }
$sql .= ' ORDER BY la.start_date DESC';
$q = db()->prepare($sql); $q->execute($p); $rows = $q->fetchAll();
$depts = db()->query('SELECT * FROM departments ORDER BY name')->fetchAll();
$years = db()->query('SELECT DISTINCT leave_year FROM leave_applications ORDER BY leave_year DESC')->fetchAll(PDO::FETCH_COLUMN);
$title = 'Leave Records';
require 'includes/header.php';
?>
<h4 class="mb-3">Leave Records Database</h4>
<form class="row g-2 mb-3" method="get">
  <div class="col-md-3"><input name="q" class="form-control" placeholder="Search name or staff no" value="<?= e($search) ?>"></div>
  <div class="col-md-2"><select name="year" class="form-select"><option value="">All years</option><?php foreach ($years as $y): ?><option <?= (string)$year === (string)$y ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select name="dept" class="form-select"><option value="0">All departments</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>" <?= $dept === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="status" class="form-select"><option value="">All status</option><?php foreach (['pending','approved','rejected','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2 d-grid"><button class="btn btn-success"><i class="bi bi-search"></i> Search</button></div>
</form>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>Staff</th><th>Department</th><th>Year</th><th>Period</th><th>Days</th><th>Resumption</th><th>Status</th><th>Remarks</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?><tr><td><?= e($r['full_name']) ?><br><small class="text-muted"><?= e($r['staff_no']) ?></small></td><td><?= e($r['department'] ?: '-') ?></td><td><?= $r['leave_year'] ?></td>
    <td><?= fmt_date($r['start_date']) ?> &ndash; <?= fmt_date($r['end_date']) ?></td><td><?= $r['days'] ?></td><td><?= fmt_date($r['resumption_date']) ?></td><td><?= status_badge($r['status']) ?></td><td><?= e($r['remarks'] ?: '-') ?></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">No records match your search.</td></tr><?php endif; ?>
  </tbody></table></div><div class="card-footer bg-white small text-muted"><?= count($rows) ?> record(s)</div></div>
<?php require 'includes/footer.php'; ?>
