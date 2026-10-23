<?php
require_once 'includes/functions.php';
$admin = require_admin();
$id = (int)($_GET['id'] ?? 0);
$st = ['staff_no'=>'','full_name'=>'','email'=>'','phone'=>'','department_id'=>'','designation'=>'','grade_level'=>'','date_employed'=>'','annual_entitlement'=>30,'role'=>'staff'];
if ($id) { $s=db()->prepare('SELECT * FROM users WHERE id=?'); $s->execute([$id]); $st=$s->fetch() ?: redirect('admin_staff.php'); }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_check();
 foreach (['staff_no','full_name','email','phone','designation','grade_level','date_employed'] as $f) $st[$f]=trim($_POST[$f] ?? '');
 $st['department_id']=(int)($_POST['department_id'] ?? 0) ?: null;
 $st['annual_entitlement']=max(0,(int)($_POST['annual_entitlement'] ?? 30));
 $st['role']=($_POST['role'] ?? 'staff')==='admin' ? 'admin' : 'staff'; $pw=$_POST['password'] ?? '';
 if ($st['staff_no']==='' || $st['full_name']==='') $errors[]='Staff number and full name are required.';
 if (!filter_var($st['email'],FILTER_VALIDATE_EMAIL)) $errors[]='A valid email is required.';
 if (!$id && strlen($pw)<8) $errors[]='Password must be at least 8 characters.';
 if ($id && $pw!=='' && strlen($pw)<8) $errors[]='New password must be at least 8 characters.';
 if ($id===(int)$admin['id'] && $st['role']!=='admin') $errors[]='You cannot remove your own admin role.';
 if (!$errors) { $d=db()->prepare('SELECT COUNT(*) FROM users WHERE (staff_no=? OR email=?) AND id<>?'); $d->execute([$st['staff_no'],$st['email'],$id]); if ($d->fetchColumn()) $errors[]='Staff number or email already exists.'; }
 if (!$errors) {
  $vals=[$st['staff_no'],$st['full_name'],$st['email'],$st['phone'] ?: null,$st['department_id'],$st['designation'] ?: null,$st['grade_level'] ?: null,$st['date_employed'] ?: null,$st['annual_entitlement'],$st['role']];
  if ($id) { $sql='UPDATE users SET staff_no=?,full_name=?,email=?,phone=?,department_id=?,designation=?,grade_level=?,date_employed=?,annual_entitlement=?,role=?'; if ($pw!=='') {$sql.=',password=?';$vals[]=password_hash($pw,PASSWORD_DEFAULT);} $vals[]=$id; db()->prepare($sql.' WHERE id=?')->execute($vals); flash('success','Staff record updated.'); }
  else { $vals[]=password_hash($pw,PASSWORD_DEFAULT); db()->prepare('INSERT INTO users (staff_no,full_name,email,phone,department_id,designation,grade_level,date_employed,annual_entitlement,role,password) VALUES (?,?,?,?,?,?,?,?,?,?,?)')->execute($vals); flash('success','Staff added.'); }
  redirect('admin_staff.php');
 }
}
$depts=db()->query('SELECT * FROM departments ORDER BY name')->fetchAll(); $title=$id?'Edit Staff':'Add Staff'; require 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-8"><div class="card shadow-sm"><div class="card-header bg-white"><strong><?= $title ?></strong></div><div class="card-body">
<?php foreach($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post"><?= csrf_field() ?><div class="row g-3">
<div class="col-md-4"><label class="form-label">Staff No *</label><input name="staff_no" class="form-control" required value="<?= e($st['staff_no']) ?>"></div>
<div class="col-md-8"><label class="form-label">Full Name *</label><input name="full_name" class="form-control" required value="<?= e($st['full_name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($st['email']) ?>"></div>
<div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($st['phone']) ?>"></div>
<div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">-- select --</option><?php foreach($depts as $d): ?><option value="<?= $d['id'] ?>" <?= (int)$st['department_id']===(int)$d['id']?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Designation</label><input name="designation" class="form-control" value="<?= e($st['designation']) ?>"></div>
<div class="col-md-4"><label class="form-label">Grade Level</label><input name="grade_level" class="form-control" value="<?= e($st['grade_level']) ?>"></div>
<div class="col-md-4"><label class="form-label">Date Employed</label><input type="date" name="date_employed" class="form-control" value="<?= e($st['date_employed']) ?>"></div>
<div class="col-md-4"><label class="form-label">Annual Entitlement (days)</label><input type="number" min="0" name="annual_entitlement" class="form-control" value="<?= e($st['annual_entitlement']) ?>"></div>
<div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select"><option value="staff" <?= $st['role']==='staff'?'selected':'' ?>>Staff</option><option value="admin" <?= $st['role']==='admin'?'selected':'' ?>>Administrator (approves leave)</option></select></div>
<div class="col-md-6"><label class="form-label">Password <?= $id?'(leave blank to keep)':'*' ?></label><input type="password" name="password" class="form-control" <?= $id?'':'required' ?> minlength="8"></div>
</div><button class="btn btn-success mt-3">Save</button> <a href="admin_staff.php" class="btn btn-outline-secondary mt-3">Back</a></form>
</div></div></div></div>
<?php require 'includes/footer.php'; ?>
