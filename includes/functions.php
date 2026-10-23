<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try { $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
        catch (PDOException $e) { die('Database connection failed. Check config.php and make sure database.sql was imported.'); }
    }
    return $pdo;
}
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): void { header('Location: ' . $url); exit; }
function fmt_date($d): string { return $d ? date('d M Y', strtotime($d)) : '-'; }
function flash(string $type, string $msg): void { $_SESSION['flash'][] = [$type, $msg]; }
function show_flash(): void {
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) echo '<div class="alert alert-' . e($t) . ' alert-dismissible fade show">' . e($m) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['flash']);
}
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); die('Invalid or expired form token. Go back, refresh the page and try again.'); }
}
function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;
    static $u = null;
    if ($u === null) {
        $s=db()->prepare('SELECT u.*, d.name AS department FROM users u LEFT JOIN departments d ON d.id=u.department_id WHERE u.id=? AND u.status="active"');
        $s->execute([$_SESSION['uid']]); $u=$s->fetch() ?: null;
        if (!$u) session_destroy();
    }
    return $u;
}
function require_login(): array { $u=current_user(); if (!$u) redirect('login.php'); return $u; }
function require_admin(): array { $u=require_login(); if ($u['role']!=='admin') { flash('danger','Access denied. Administrators only.'); redirect('dashboard.php'); } return $u; }
/** Count working days (Mon-Fri) from $start to $end inclusive. */
function working_days(string $start, string $end): int {
    $d=new DateTime($start); $e=new DateTime($end); $n=0;
    while ($d <= $e) { if ((int)$d->format('N')<6) $n++; $d->modify('+1 day'); }
    return $n;
}
/** First working day after the end date. */
function resumption_date(string $end): string {
    $d=new DateTime($end); do { $d->modify('+1 day'); } while ((int)$d->format('N')>=6);
    return $d->format('Y-m-d');
}
/** Approved + pending days already used in a year (pending reserved so staff cannot over-apply). */
function days_used(int $uid, int $year, bool $approvedOnly=false): int {
    $sql='SELECT COALESCE(SUM(days),0) FROM leave_applications WHERE user_id=? AND leave_year=? AND status IN (' . ($approvedOnly ? "'approved'" : "'approved','pending'") . ')';
    $s=db()->prepare($sql); $s->execute([$uid,$year]); return (int)$s->fetchColumn();
}
function status_badge(string $s): string {
    $c=['pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'][$s] ?? 'secondary';
    return '<span class="badge bg-' . $c . '">' . ucfirst(e($s)) . '</span>';
}
