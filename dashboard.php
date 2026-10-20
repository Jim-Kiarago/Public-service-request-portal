<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_login();
$pageTitle = 'My requests';
$user = current_user();
$stmt = $pdo->prepare("SELECT id, reference_number, category, location, description, status, created_at, updated_at FROM service_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();
$counts = ['total' => count($requests), 'Pending' => 0, 'In Progress' => 0, 'Resolved' => 0];
foreach ($requests as $request) $counts[$request['status']] = ($counts[$request['status']] ?? 0) + 1;
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">CITIZEN PORTAL</span><h1>My requests</h1><p>Welcome back, <?= e($user['full_name']) ?>. Here's the latest on your reports.</p></div><a class="btn btn-primary" href="<?= e(url('request-new.php')) ?>">＋ New request</a></div>
<div class="stats-grid stats-four">
  <article class="stat-card"><span class="stat-label">Total requests</span><strong><?= (int)$counts['total'] ?></strong><span class="stat-foot">All submissions</span></article>
  <article class="stat-card"><span class="stat-label">Pending</span><strong><?= (int)$counts['Pending'] ?></strong><span class="stat-foot"><i class="dot dot-pending"></i> Awaiting review</span></article>
  <article class="stat-card"><span class="stat-label">In progress</span><strong><?= (int)$counts['In Progress'] ?></strong><span class="stat-foot"><i class="dot dot-progress"></i> Being addressed</span></article>
  <article class="stat-card"><span class="stat-label">Resolved</span><strong><?= (int)$counts['Resolved'] ?></strong><span class="stat-foot"><i class="dot dot-resolved"></i> Completed</span></article>
</div>
<section class="panel table-panel"><div class="panel-heading"><div><h2>Request history</h2><p>Follow each report using its reference number.</p></div></div>
<?php if (!$requests): ?><div class="empty-state"><div class="empty-icon">＋</div><h3>No requests yet</h3><p>When you report a public service issue, it will appear here.</p><a class="btn btn-primary" href="<?= e(url('request-new.php')) ?>">Submit your first request</a></div>
<?php else: ?><div class="table-scroll"><table><thead><tr><th>Reference</th><th>Issue</th><th>Location</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($requests as $r): ?><tr><td><a class="reference-link" href="<?= e(url('request-view.php?id=' . (int)$r['id'])) ?>"><?= e($r['reference_number']) ?></a></td><td><strong><?= e($r['category']) ?></strong><small class="cell-sub"><?= e(mb_strimwidth($r['description'], 0, 65, '…')) ?></small></td><td><?= e($r['location']) ?></td><td><?= e(format_date($r['created_at'])) ?></td><td><span class="status-badge <?= e(status_class($r['status'])) ?>"><?= e($r['status']) ?></span></td><td><a class="table-action" href="<?= e(url('request-view.php?id=' . (int)$r['id'])) ?>">View →</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
