<?php
require_once 'includes/functions.php';
$admin = require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0); $action = $_POST['action'] ?? ''; $remarks = trim($_POST['remarks'] ?? '');
    $s = db()->prepare('SELECT la.*, u.annual_entitlement FROM leave_applications la JOIN users u ON u.id=la.user_id WHERE la.id=?');
    $s->execute([$id]); $app = $s->fetch();
    if (!$app || $app['status'] !== 'pending') flash('warning', 'This application is no longer pending.');
    elseif (!in_array($action, ['approve', 'reject'], true)) flash('danger', 'Invalid action.');
    elseif ($action === 'reject' && $remarks === '') flash('danger', 'Please give a remark/reason when rejecting.');
    else {
        if ($action === 'approve') {
            $approved = days_used((int)$app['user_id'], (int)$app['leave_year'], true);
            if ($approved + $app['days'] > $app['annual_entitlement']) { flash('danger', 'Cannot approve: it would exceed the staff member\'s annual entitlement.'); redirect('admin_requests.php'); }
        }
        $new = $action === 'approve' ? 'approved' : 'rejected';
        db()->prepare('UPDATE leave_applications SET status=?, remarks=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?')->execute([$new, $remarks ?: null, $admin['id'], $id]);
        flash('success', "Application #$id $new.");
    }
    redirect('admin_requests.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}
$status = $_GET['status'] ?? 'pending'; $valid = ['pending', 'approved', 'rejected', 'cancelled', 'all'];
if (!in_array($status, $valid, true)) $status = 'pending';
$sql = 'SELECT la.*, u.full_name, u.staff_no, u.annual_entitlement, d.name AS department FROM leave_applications la JOIN users u ON u.id=la.user_id LEFT JOIN departments d ON d.id=u.department_id';
$params = []; if ($status !== 'all') { $sql .= ' WHERE la.status=?'; $params[] = $status; }
$sql .= ' ORDER BY la.created_at DESC'; $q = db()->prepare($sql); $q->execute($params); $rows = $q->fetchAll();
$title = 'Leave Requests'; require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2"><h4 class="mb-0">Leave Requests</h4><div class="btn-group">
<?php foreach ($valid as $v): ?><a href="?status=<?= $v ?>" class="btn btn-sm btn-outline-success <?= $status === $v ? 'active' : '' ?>"><?= ucfirst($v) ?></a><?php endforeach; ?>
</div></div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>#</th><th>Staff</th><th>Department</th><th>Period</th><th>Days</th><th>Relief Officer</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $bal = $r['annual_entitlement'] - days_used((int)$r['user_id'], (int)$r['leave_year'], true); ?>
<tr><td><?= $r['id'] ?></td><td><?= e($r['full_name']) ?><br><small class="text-muted"><?= e($r['staff_no']) ?></small></td><td><?= e($r['department'] ?: '-') ?></td>
<td><?= fmt_date($r['start_date']) ?> &ndash; <?= fmt_date($r['end_date']) ?><br><small class="text-muted">Resumes <?= fmt_date($r['resumption_date']) ?></small></td><td><?= $r['days'] ?></td><td><?= e($r['relief_officer'] ?: '-') ?></td><td><?= status_badge($r['status']) ?></td><td>
<?php if ($r['status'] === 'pending'): ?><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#review" data-id="<?= $r['id'] ?>" data-name="<?= e($r['full_name']) ?>" data-period="<?= e(fmt_date($r['start_date']) . ' - ' . fmt_date($r['end_date'])) ?>" data-days="<?= $r['days'] ?>" data-balance="<?= $bal ?>" data-reason="<?= e($r['reason']) ?>">Review</button><?php else: ?><small class="text-muted"><?= e($r['remarks'] ?: '') ?></small><?php endif; ?></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">No requests found.</td></tr><?php endif; ?>
</tbody></table></div></div>
<div class="modal fade" id="review" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><?= csrf_field() ?><input type="hidden" name="id" id="m-id">
<div class="modal-header"><h5 class="modal-title">Review Leave Application</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body"><p class="mb-1"><strong id="m-name"></strong></p><p class="mb-1">Period: <span id="m-period"></span> (<span id="m-days"></span> working days)</p><p class="mb-1">Approved balance before this request: <strong id="m-balance"></strong> day(s)</p><p class="mb-3 text-muted">Reason: <span id="m-reason"></span></p><label class="form-label">Remarks (required when rejecting)</label><textarea name="remarks" class="form-control" rows="3"></textarea></div>
<div class="modal-footer"><button name="action" value="reject" class="btn btn-danger">Reject</button><button name="action" value="approve" class="btn btn-success">Approve</button></div></form></div></div>
<script>
document.getElementById('review').addEventListener('show.bs.modal', ev => { const b=ev.relatedTarget; document.getElementById('m-id').value=b.dataset.id; document.getElementById('m-name').textContent=b.dataset.name; document.getElementById('m-period').textContent=b.dataset.period; document.getElementById('m-days').textContent=b.dataset.days; document.getElementById('m-balance').textContent=b.dataset.balance; document.getElementById('m-reason').textContent=b.dataset.reason || '-'; });
</script>
<?php require 'includes/footer.php'; ?>
