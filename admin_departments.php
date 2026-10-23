<?php
require_once 'includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['add'])) {
        $n = trim($_POST['name'] ?? '');
        if ($n === '') flash('danger', 'Enter a department name.');
        else {
            try { db()->prepare('INSERT INTO departments (name) VALUES (?)')->execute([$n]); flash('success', 'Department added.'); }
            catch (PDOException $e) { flash('danger', 'That department already exists.'); }
        }
    } elseif (isset($_POST['delete_id'])) {
        db()->prepare('DELETE FROM departments WHERE id=?')->execute([(int)$_POST['delete_id']]);
        flash('success', 'Department deleted (staff in it are now unassigned).');
    }
    redirect('admin_departments.php');
}
$rows = db()->query('SELECT d.*, (SELECT COUNT(*) FROM users u WHERE u.department_id=d.id) AS staff_count FROM departments d ORDER BY name')->fetchAll();
$title = 'Departments';
require 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7">
<h4 class="mb-3">Departments</h4>
<form method="post" class="input-group mb-3"><?= csrf_field() ?><input name="name" class="form-control" placeholder="New department / unit name"><button name="add" value="1" class="btn btn-success">Add</button></form>
<div class="card shadow-sm"><table class="table mb-0 align-middle">
  <thead class="table-light"><tr><th>Name</th><th>Staff</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['name']) ?></td><td><?= $r['staff_count'] ?></td>
      <td class="text-end"><form method="post" onsubmit="return confirm('Delete this department?')"><?= csrf_field() ?><input type="hidden" name="delete_id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
  <?php endforeach; ?></tbody></table></div>
</div></div>
<?php require 'includes/footer.php'; ?>
