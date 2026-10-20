<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_admin();
$pageTitle = 'Admin dashboard';
$counts = $pdo->query("SELECT COUNT(*) total, SUM(status='Pending') pending, SUM(status='In Progress') in_progress, SUM(status='Resolved') resolved FROM service_requests")->fetch();
$categoryRows = $pdo->query('SELECT category, COUNT(*) total FROM service_requests GROUP BY category ORDER BY total DESC')->fetchAll();
$maxCategory = max(1, ...array_map(fn($r) => (int)$r['total'], $categoryRows));
$filterStatus = (string)($_GET['status'] ?? '');
$filterCategory = (string)($_GET['category'] ?? '');
$search = trim((string)($_GET['q'] ?? ''));
$validStatuses = ['Pending', 'In Progress', 'Resolved'];
$sql = 'SELECT r.id, r.reference_number, r.category, r.location, r.status, r.created_at, u.full_name FROM service_requests r JOIN users u ON u.id=r.user_id WHERE 1=1';
$params = [];
if (in_array($filterStatus, $validStatuses, true)) { $sql .= ' AND r.status=?'; $params[] = $filterStatus; }
if ($filterCategory !== '') { $sql .= ' AND r.category=?'; $params[] = $filterCategory; }
if ($search !== '') { $sql .= ' AND (r.reference_number LIKE ? OR r.location LIKE ? OR u.full_name LIKE ?)'; $like = '%' . $search . '%'; array_push($params, $like, $like, $like); }
$sql .= ' ORDER BY r.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $requests = $stmt->fetchAll();
$categories = ['Street lighting', 'Water supply', 'Waste collection', 'Roads and potholes', 'Drainage and flooding', 'Public facilities', 'Other'];
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-heading"><div><span class="eyebrow">SERVICE OPERATIONS</span><h1>Admin dashboard</h1><p>Monitor incoming reports and keep service requests moving.</p></div><span class="admin-chip"><span class="admin-chip-dot"></span> Administrator view</span></div>
<div class="stats-grid stats-four">
<article class="stat-card"><span class="stat-label">Total requests</span><strong><?= (int)$counts['total'] ?></strong><span class="stat-foot">All submissions</span></article>
<article class="stat-card"><span class="stat-label">Pending</span><strong><?= (int)$counts['pending'] ?></strong><span class="stat-foot"><i class="dot dot-pending"></i> Awaiting review</span></article>
<article class="stat-card"><span class="stat-label">In progress</span><strong><?= (int)$counts['in_progress'] ?></strong><span class="stat-foot"><i class="dot dot-progress"></i> Being addressed</span></article>
<article class="stat-card"><span class="stat-label">Resolved</span><strong><?= (int)$counts['resolved'] ?></strong><span class="stat-foot"><i class="dot dot-resolved"></i> Completed</span></article>
</div>
<div class="analytics-grid"><section class="panel analytics-panel"><div class="panel-heading"><div><h2>Requests by category</h2><p>Distribution of submitted reports.</p></div></div>
<?php if (!$categoryRows): ?><div class="empty-inline">Category data will appear when requests are submitted.</div><?php else: ?><div class="bar-chart"><?php foreach ($categoryRows as $row): ?><div class="bar-row"><div class="bar-label"><span><?= e($row['category']) ?></span><strong><?= (int)$row['total'] ?></strong></div><div class="bar-track"><div class="bar-fill" style="width: <?= round(((int)$row['total'] / $maxCategory) * 100, 1) ?>%"></div></div></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="panel analytics-panel resolution-panel"><div class="panel-heading"><div><h2>Resolution overview</h2><p>Share of resolved requests.</p></div></div><div class="resolution-number"><?= (int)$counts['resolved'] ?><span> / <?= (int)$counts['total'] ?></span></div><div class="progress-track"><div class="progress-fill" style="width: <?= (int)$counts['total'] > 0 ? round(((int)$counts['resolved'] / (int)$counts['total']) * 100, 1) : 0 ?>%"></div></div><p class="muted"><?= (int)$counts['total'] > 0 ? round(((int)$counts['resolved'] / (int)$counts['total']) * 100) : 0 ?>% of all submitted requests resolved</p><div class="resolution-legend"><span><i class="dot dot-pending"></i> Pending <?= (int)$counts['pending'] ?></span><span><i class="dot dot-progress"></i> In progress <?= (int)$counts['in_progress'] ?></span><span><i class="dot dot-resolved"></i> Resolved <?= (int)$counts['resolved'] ?></span></div></section></div>
<section class="panel table-panel"><div class="panel-heading"><div><h2>All service requests</h2><p>Review and update the latest reports.</p></div><span class="result-count"><?= count($requests) ?> shown</span></div>
<form method="get" class="filter-bar"><label class="filter-search"><span>⌕</span><input name="q" value="<?= e($search) ?>" placeholder="Search reference, location, citizen"></label><select name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach ($validStatuses as $s): ?><option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select><select name="category" aria-label="Filter by category"><option value="">All categories</option><?php foreach ($categories as $c): ?><option value="<?= e($c) ?>" <?= $filterCategory === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select><button class="btn btn-primary btn-small" type="submit">Filter</button><a class="btn btn-outline btn-small" href="<?= e(url('admin.php')) ?>">Reset</a></form>
<?php if (!$requests): ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No matching requests</h3><p>Try changing the search terms or filters.</p></div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Reference</th><th>Citizen</th><th>Category</th><th>Location</th><th>Submitted</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach ($requests as $r): ?><tr><td><a class="reference-link" href="<?= e(url('request-view.php?id=' . (int)$r['id'])) ?>"><?= e($r['reference_number']) ?></a></td><td><?= e($r['full_name']) ?></td><td><?= e($r['category']) ?></td><td><?= e($r['location']) ?></td><td><?= e(format_date($r['created_at'])) ?></td><td><span class="status-badge <?= e(status_class($r['status'])) ?>"><?= e($r['status']) ?></span></td><td><a class="table-action" href="<?= e(url('admin-request.php?id=' . (int)$r['id'])) ?>">Manage →</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
