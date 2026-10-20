<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Request not found.'); }
$stmt = $pdo->prepare('SELECT r.*, u.full_name AS citizen_name FROM service_requests r JOIN users u ON u.id=r.user_id WHERE r.id=?');
$stmt->execute([$id]);
$request = $stmt->fetch();
if (!$request || (current_user()['role'] !== 'admin' && (int)$request['user_id'] !== (int)current_user()['id'])) {
    http_response_code(404); exit('Request not found.');
}
$historyStmt = $pdo->prepare('SELECT ru.*, u.full_name FROM request_updates ru LEFT JOIN users u ON u.id=ru.user_id WHERE ru.request_id=? ORDER BY ru.created_at ASC');
$historyStmt->execute([$id]); $history = $historyStmt->fetchAll();
$pageTitle = 'Request ' . $request['reference_number'];
$includeMapAssets = $request['latitude'] !== null && $request['longitude'] !== null;
require dirname(__DIR__) . '/includes/header.php';
$img = image_url($request['image_path']);
?>
<div class="page-heading"><div><a class="back-link" href="<?= e(url(current_user()['role'] === 'admin' ? 'admin.php' : 'dashboard.php')) ?>">← Back to requests</a><span class="eyebrow">REQUEST DETAILS</span><h1><?= e($request['category']) ?></h1><p>Reference <strong class="reference-text"><?= e($request['reference_number']) ?></strong></p></div><span class="status-badge status-large <?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span></div>
<div class="detail-grid"><div class="detail-main"><section class="panel detail-panel"><div class="detail-section"><span class="detail-label">DESCRIPTION</span><p class="detail-description"><?= nl2br(e($request['description'])) ?></p></div><?php if ($includeMapAssets): ?><div class="detail-section"><span class="detail-label">PINNED LOCATION</span><div class="location-map location-map-static" data-static-location data-latitude="<?= e((string)$request['latitude']) ?>" data-longitude="<?= e((string)$request['longitude']) ?>" aria-label="Map showing the pinned issue location"></div><p class="map-coordinate-note"><a href="https://www.openstreetmap.org/?mlat=<?= rawurlencode((string)$request['latitude']) ?>&amp;mlon=<?= rawurlencode((string)$request['longitude']) ?>#map=17/<?= rawurlencode((string)$request['latitude']) ?>/<?= rawurlencode((string)$request['longitude']) ?>" target="_blank" rel="noopener">Open pinned location in OpenStreetMap</a></p></div><?php endif; ?><div class="detail-meta-grid"><div><span class="detail-label">LOCATION</span><p>⌖ <?= e($request['location']) ?></p></div><div><span class="detail-label">DATE SUBMITTED</span><p><?= e(format_date($request['created_at'])) ?></p></div><div><span class="detail-label">LAST UPDATED</span><p><?= e(format_date($request['updated_at'])) ?></p></div><?php if (current_user()['role'] === 'admin'): ?><div><span class="detail-label">SUBMITTED BY</span><p><?= e($request['citizen_name']) ?></p></div><?php endif; ?></div>
<?php if ($img): ?><div class="detail-section"><span class="detail-label">ATTACHED IMAGE</span><a href="<?= e($img) ?>" target="_blank" rel="noopener"><img class="request-image" src="<?= e($img) ?>" alt="Image attached to the service request"></a></div><?php endif; ?></section>
<section class="panel history-panel"><div class="panel-heading"><div><h2>Progress history</h2><p>Updates recorded for this request.</p></div></div><div class="timeline"><?php foreach ($history as $i => $event): ?><article class="timeline-item"><span class="timeline-dot <?= e(status_class($event['new_status'])) ?>"></span><div><div class="timeline-head"><strong><?= e($event['new_status']) ?></strong><time><?= e(format_date($event['created_at'])) ?></time></div><?php if ($event['note']): ?><p><?= e($event['note']) ?></p><?php endif; ?><small><?= e($event['full_name'] ?? 'System') ?></small></div></article><?php endforeach; ?></div></section></div>
<aside class="detail-side"><section class="panel side-info"><span class="detail-label">CURRENT STATUS</span><span class="status-badge status-large <?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span><p class="muted">The service team updates the status as work progresses.</p><?php if (current_user()['role'] === 'admin'): ?><a class="btn btn-primary btn-full" href="<?= e(url('admin-request.php?id=' . (int)$request['id'])) ?>">Manage request →</a><?php endif; ?></section><section class="help-card"><h3>Keep your reference</h3><p class="muted">Use this reference when following up with the relevant service team.</p><div class="reference-box"><?= e($request['reference_number']) ?></div></section></aside></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
