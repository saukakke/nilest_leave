<?php
require_once 'includes/functions.php';
$user=require_login();
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_check(); $cur=$_POST['current'] ?? ''; $new=$_POST['new'] ?? ''; $conf=$_POST['confirm'] ?? '';
 $row=db()->prepare('SELECT password FROM users WHERE id=?'); $row->execute([$user['id']]);
 if (!password_verify($cur,$row->fetchColumn())) flash('danger','Current password is incorrect.');
 elseif (strlen($new)<8) flash('danger','New password must be at least 8 characters.');
 elseif ($new!==$conf) flash('danger','Password confirmation does not match.');
 else { db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),$user['id']]); flash('success','Password changed successfully.'); }
 redirect('profile.php');
}
$title='My Profile'; require 'includes/header.php';
?>
<div class="row g-3"><div class="col-md-6"><div class="card shadow-sm"><div class="card-header bg-white"><strong>My Details</strong></div><table class="table mb-0">
<tr><th>Name</th><td><?= e($user['full_name']) ?></td></tr><tr><th>Staff No</th><td><?= e($user['staff_no']) ?></td></tr><tr><th>Email</th><td><?= e($user['email']) ?></td></tr><tr><th>Department</th><td><?= e($user['department']) ?></td></tr><tr><th>Designation</th><td><?= e($user['designation']) ?></td></tr><tr><th>Grade Level</th><td><?= e($user['grade_level']) ?></td></tr><tr><th>Date Employed</th><td><?= fmt_date($user['date_employed']) ?></td></tr><tr><th>Role</th><td><?= ucfirst(e($user['role'])) ?></td></tr>
</table></div></div>
<div class="col-md-6"><div class="card shadow-sm"><div class="card-header bg-white"><strong>Change Password</strong></div><div class="card-body"><form method="post"><?= csrf_field() ?>
<div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current" class="form-control" required></div>
<div class="mb-3"><label class="form-label">New password (min 8 characters)</label><input type="password" name="new" class="form-control" required minlength="8"></div>
<div class="mb-3"><label class="form-label">Confirm new password</label><input type="password" name="confirm" class="form-control" required></div>
<button class="btn btn-success">Update Password</button></form></div></div></div></div>
<?php require 'includes/footer.php'; ?>
