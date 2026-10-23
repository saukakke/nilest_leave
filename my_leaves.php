<?php
require_once 'includes/functions.php';
$user=require_login();
if ($user['role']!=='staff') redirect('admin_records.php');
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cancel_id'])) {
 csrf_check();
 $s=db()->prepare('UPDATE leave_applications SET status="cancelled" WHERE id=? AND user_id=? AND status="pending"');
 $s->execute([(int)$_POST['cancel_id'],$user['id']]);
 flash($s->rowCount()?'success':'warning',$s->rowCount()?'Application cancelled.':'Only pending applications can be cancelled.');
 redirect('my_leaves.php');
}
$q=db()->prepare('SELECT * FROM leave_applications WHERE user_id=? ORDER BY id DESC'); $q->execute([$user['id']]); $rows=$q->fetchAll();
$title='My Applications'; require 'includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">My Leave Applications</h4><a href="apply.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> New Application</a></div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead class="table-light"><tr><th>#</th><th>Applied</th><th>Period</th><th>Days</th><th>Resumption</th><th>Status</th><th>Admin Remarks</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= $r['id'] ?></td><td><?= fmt_date($r['created_at']) ?></td><td><?= fmt_date($r['start_date']) ?> &ndash; <?= fmt_date($r['end_date']) ?></td><td><?= $r['days'] ?></td><td><?= fmt_date($r['resumption_date']) ?></td><td><?= status_badge($r['status']) ?></td><td><?= e($r['remarks'] ?: '-') ?></td><td>
<?php if($r['status']==='pending'): ?><form method="post" onsubmit="return confirm('Cancel this application?')"><?= csrf_field() ?><input type="hidden" name="cancel_id" value="<?= $r['id'] ?>"><button class="btn btn-outline-danger btn-sm">Cancel</button></form><?php endif; ?>
</td></tr><?php endforeach; if(!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">You have not applied for leave yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
