<?php
require_once 'includes/functions.php';
$user=require_login();
if ($user['role']!=='staff') { flash('info','Only staff accounts apply for leave.'); redirect('dashboard.php'); }
$errors=[]; $year=(int)date('Y');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_check(); $start=$_POST['start_date'] ?? ''; $end=$_POST['end_date'] ?? ''; $reason=trim($_POST['reason'] ?? ''); $relief=trim($_POST['relief_officer'] ?? '');
 $ds=DateTime::createFromFormat('Y-m-d',$start); $de=DateTime::createFromFormat('Y-m-d',$end);
 if (!$ds || !$de) $errors[]='Please provide valid start and end dates.';
 else {
  if ($ds<new DateTime('today')) $errors[]='Start date cannot be in the past.';
  if ($de<$ds) $errors[]='End date cannot be before the start date.';
  if ($ds->format('Y')!==$de->format('Y')) $errors[]='A single application must fall within one calendar year.';
 }
 if (!$errors) {
  $leaveYear=(int)$ds->format('Y'); $days=working_days($start,$end);
  if ($days<1) $errors[]='The selected period contains no working days.';
  $available=$user['annual_entitlement']-days_used($user['id'],$leaveYear);
  if ($days>$available) $errors[]="You requested $days working day(s) but only $available remain for $leaveYear.";
  $o=db()->prepare('SELECT COUNT(*) FROM leave_applications WHERE user_id=? AND status IN ("pending","approved") AND start_date<=? AND end_date>=?'); $o->execute([$user['id'],$end,$start]);
  if ($o->fetchColumn()>0) $errors[]='This period overlaps with an existing pending or approved application.';
 }
 if (!$errors) { $ins=db()->prepare('INSERT INTO leave_applications (user_id,leave_year,start_date,end_date,days,resumption_date,reason,relief_officer) VALUES (?,?,?,?,?,?,?,?)'); $ins->execute([$user['id'],$leaveYear,$start,$end,$days,resumption_date($end),$reason,$relief ?: null]); flash('success',"Leave application submitted ($days working days). Awaiting approval."); redirect('my_leaves.php'); }
}
$used=days_used($user['id'],$year); $title='Apply for Leave'; require 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7"><div class="card shadow-sm"><div class="card-header bg-white"><strong>Annual Leave Application Form</strong></div><div class="card-body">
<div class="alert alert-info py-2 small"><?= $year ?> entitlement: <strong><?= (int)$user['annual_entitlement'] ?></strong> working days &bull; Used/pending: <strong><?= $used ?></strong> &bull; Balance: <strong><?= $user['annual_entitlement']-$used ?></strong>. Weekends are not counted.</div>
<?php foreach($errors as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" id="leaveForm"><?= csrf_field() ?><div class="row g-3">
<div class="col-md-6"><label class="form-label">Staff Name</label><input class="form-control" value="<?= e($user['full_name']) ?>" disabled></div>
<div class="col-md-6"><label class="form-label">Department</label><input class="form-control" value="<?= e($user['department']) ?>" disabled></div>
<div class="col-md-6"><label class="form-label">Start Date *</label><input type="date" name="start_date" id="start" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['start_date'] ?? '') ?>" required></div>
<div class="col-md-6"><label class="form-label">End Date *</label><input type="date" name="end_date" id="end" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= e($_POST['end_date'] ?? '') ?>" required></div>
<div class="col-12"><div class="text-muted small" id="preview"></div></div>
<div class="col-12"><label class="form-label">Relief Officer (who will act for you)</label><input type="text" name="relief_officer" class="form-control" value="<?= e($_POST['relief_officer'] ?? '') ?>"></div>
<div class="col-12"><label class="form-label">Remarks / Reason</label><textarea name="reason" rows="3" class="form-control"><?= e($_POST['reason'] ?? '') ?></textarea></div>
</div><button class="btn btn-success mt-3"><i class="bi bi-send"></i> Submit Application</button> <a href="dashboard.php" class="btn btn-outline-secondary mt-3">Cancel</a></form>
</div></div></div></div>
<script>
const s=document.getElementById('start'),e=document.getElementById('end'),p=document.getElementById('preview');
function calc(){if(!s.value||!e.value){p.textContent='';return;}let a=new Date(s.value),b=new Date(e.value);if(b<a){p.textContent='End date is before start date.';return;}let n=0,d=new Date(a);while(d<=b){const w=d.getDay();if(w!==0&&w!==6)n++;d.setDate(d.getDate()+1);}let r=new Date(b);do{r.setDate(r.getDate()+1);}while(r.getDay()===0||r.getDay()===6);p.textContent=n+' working day(s). Expected resumption: '+r.toDateString();}
s.addEventListener('change',()=>{e.min=s.value;calc();});e.addEventListener('change',calc);calc();
</script>
<?php require 'includes/footer.php'; ?>
