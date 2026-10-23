<?php
require_once 'includes/functions.php';
$user=require_login(); $title='Dashboard'; $year=(int)date('Y'); require 'includes/header.php';
if ($user['role']==='staff'):
 $used=days_used($user['id'],$year,true); $reserved=days_used($user['id'],$year)-$used; $balance=$user['annual_entitlement']-$used-$reserved;
 $recent=db()->prepare('SELECT * FROM leave_applications WHERE user_id=? ORDER BY id DESC LIMIT 5'); $recent->execute([$user['id']]);
?>
<h4 class="mb-1">Welcome, <?= e($user['full_name']) ?></h4><p class="text-muted"><?= e($user['designation']) ?> &bull; <?= e($user['department']) ?> &bull; Staff No: <?= e($user['staff_no']) ?></p>
<div class="row g-3 mb-4">
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><div class="text-muted small">Entitlement (<?= $year ?>)</div><div class="num"><?= (int)$user['annual_entitlement'] ?></div><small>working days</small></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><div class="text-muted small">Approved / Used</div><div class="num"><?= $used ?></div><small>working days</small></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm" style="border-color:#ffc107"><div class="card-body"><div class="text-muted small">Pending approval</div><div class="num"><?= $reserved ?></div><small>working days</small></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm" style="border-color:#0d6efd"><div class="card-body"><div class="text-muted small">Balance</div><div class="num"><?= $balance ?></div><small>working days</small></div></div></div></div>
<div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between align-items-center"><strong>Recent Applications</strong><a href="apply.php" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> New Application</a></div>
<div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th>Period</th><th>Days</th><th>Resumption</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><?= fmt_date($r['start_date']) ?> &ndash; <?= fmt_date($r['end_date']) ?></td><td><?= $r['days'] ?></td><td><?= fmt_date($r['resumption_date']) ?></td><td><?= status_badge($r['status']) ?></td></tr><?php endforeach; if(!$recent->rowCount()): ?><tr><td colspan="4" class="text-center text-muted py-4">No applications yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php else:
$pdo=db(); $staffCount=$pdo->query('SELECT COUNT(*) FROM users WHERE role="staff" AND status="active"')->fetchColumn();
$pending=$pdo->query('SELECT COUNT(*) FROM leave_applications WHERE status="pending"')->fetchColumn();
$approvedYear=$pdo->query('SELECT COUNT(*) FROM leave_applications WHERE status="approved" AND leave_year='.$year)->fetchColumn();
$onLeave=$pdo->query('SELECT la.*, u.full_name, u.staff_no FROM leave_applications la JOIN users u ON u.id=la.user_id WHERE la.status="approved" AND CURDATE() BETWEEN la.start_date AND la.end_date')->fetchAll();
$pend=$pdo->query('SELECT la.*, u.full_name, u.staff_no FROM leave_applications la JOIN users u ON u.id=la.user_id WHERE la.status="pending" ORDER BY la.created_at LIMIT 5')->fetchAll();
?>
<h4 class="mb-3">Administrator Dashboard</h4><div class="row g-3 mb-4">
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm"><div class="card-body"><div class="text-muted small">Active staff</div><div class="num"><?= $staffCount ?></div></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm" style="border-color:#ffc107"><div class="card-body"><div class="text-muted small">Pending requests</div><div class="num"><?= $pending ?></div></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm" style="border-color:#0d6efd"><div class="card-body"><div class="text-muted small">Approved (<?= $year ?>)</div><div class="num"><?= $approvedYear ?></div></div></div></div>
<div class="col-6 col-md-3"><div class="card stat-card shadow-sm" style="border-color:#dc3545"><div class="card-body"><div class="text-muted small">On leave today</div><div class="num"><?= count($onLeave) ?></div></div></div></div></div>
<div class="row g-3"><div class="col-lg-7"><div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between"><strong>Awaiting Approval</strong><a href="admin_requests.php?status=pending" class="small">View all</a></div>
<div class="table-responsive"><table class="table mb-0 align-middle"><thead class="table-light"><tr><th>Staff</th><th>Period</th><th>Days</th></tr></thead><tbody>
<?php foreach($pend as $r): ?><tr><td><?= e($r['full_name']) ?><br><small class="text-muted"><?= e($r['staff_no']) ?></small></td><td><?= fmt_date($r['start_date']) ?> &ndash; <?= fmt_date($r['end_date']) ?></td><td><?= $r['days'] ?></td></tr><?php endforeach; if(!$pend): ?><tr><td colspan="3" class="text-center text-muted py-4">Nothing pending.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<div class="col-lg-5"><div class="card shadow-sm"><div class="card-header bg-white"><strong>Currently on leave</strong></div><ul class="list-group list-group-flush">
<?php foreach($onLeave as $r): ?><li class="list-group-item"><?= e($r['full_name']) ?> <small class="text-muted">resumes <?= fmt_date($r['resumption_date']) ?></small></li><?php endforeach; if(!$onLeave): ?><li class="list-group-item text-muted">No one is on leave today.</li><?php endif; ?>
</ul></div></div></div>
<?php endif; require 'includes/footer.php'; ?>
