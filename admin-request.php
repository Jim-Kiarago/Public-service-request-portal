<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('Request not found.'); }
$stmt = $pdo->prepare('SELECT r.*, u.full_name AS citizen_name, u.email AS citizen_email FROM service_requests r JOIN users u ON u.id=r.user_id WHERE r.id=?');
$stmt->execute([$id]); $request = $stmt->fetch();
if (!$request) { http_response_code(404); exit('Request not found.'); }
$errors = [];
$statuses = ['Pending', 'In Progress', 'Resolved'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $newStatus = (string)($_POST['status'] ?? '');
    $note = trim((string)($_POST['note'] ?? ''));
    if (!in_array($newStatus, $statuses, true)) $errors[] = 'Choose a valid status.';
    if (mb_strlen($note) > 1000) $errors[] = 'The note must be 1,000 characters or fewer.';
    if (!$errors) {
        $oldStatus = $request['status'];
        if ($newStatus !== $oldStatus || $note !== '') {
            $pdo->beginTransaction();
            try {
                $update = $pdo->prepare('UPDATE service_requests SET status=?, updated_at=NOW() WHERE id=?');
                $update->execute([$newStatus, $id]);
                $history = $pdo->prepare('INSERT INTO request_updates (request_id, user_id, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)');
                $history->execute([$id, current_user()['id'], $oldStatus, $newStatus, $note !== '' ? $note : 'Status updated.']);
                $pdo->commit();
                flash('success', 'Request updated successfully.');
                redirect('admin-request.php?id=' . $id);
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('Request update failed: ' . $e->getMessage());
                $errors[] = 'The request could not be updated. Please try again.';
            }
        } else {
            flash('info', 'No changes were made.');
            redirect('admin-request.php?id=' . $id);
        }
    }
}
$pageTitle = 'Manage request';
$img = image_url($request['image_path']);
$includeMapAssets = $request['latitude'] !== null && $request['longitude'] !== null;
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-heading"><div><a class="back-link" href="<?= e(url('admin.php')) ?>">← Back to dashboard</a><span class="eyebrow">ADMINISTRATION</span><h1>Manage request</h1><p><?= e($request['reference_number']) ?> · <?= e($request['category']) ?></p></div><span class="status-badge status-large <?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span></div>
<div class="detail-grid"><div class="detail-main"><section class="panel detail-panel"><div class="detail-section"><span class="detail-label">ISSUE DESCRIPTION</span><p class="detail-description"><?= nl2br(e($request['description'])) ?></p></div><?php if ($includeMapAssets): ?><div class="detail-section"><span class="detail-label">PINNED LOCATION</span><div class="location-map location-map-static" data-static-location data-latitude="<?= e((string)$request['latitude']) ?>" data-longitude="<?= e((string)$request['longitude']) ?>" aria-label="Map showing the pinned issue location"></div><p class="map-coordinate-note"><a href="https://www.openstreetmap.org/?mlat=<?= rawurlencode((string)$request['latitude']) ?>&amp;mlon=<?= rawurlencode((string)$request['longitude']) ?>#map=17/<?= rawurlencode((string)$request['latitude']) ?>/<?= rawurlencode((string)$request['longitude']) ?>" target="_blank" rel="noopener">Open pinned location in OpenStreetMap</a></p></div><?php endif; ?><div class="detail-meta-grid"><div><span class="detail-label">LOCATION</span><p><?= e($request['location']) ?></p></div><div><span class="detail-label">SUBMITTED BY</span><p><?= e($request['citizen_name']) ?></p><small><?= e($request['citizen_email']) ?></small></div><div><span class="detail-label">SUBMITTED</span><p><?= e(format_date($request['created_at'])) ?></p></div></div><?php if ($img): ?><div class="detail-section"><span class="detail-label">ATTACHED IMAGE</span><a href="<?= e($img) ?>" target="_blank" rel="noopener"><img class="request-image" src="<?= e($img) ?>" alt="Citizen's uploaded image"></a></div><?php endif; ?></section>
<section class="panel history-panel"><div class="panel-heading"><div><h2>Request timeline</h2><p>Audit trail of status updates.</p></div></div><?php $hs=$pdo->prepare('SELECT ru.*, u.full_name FROM request_updates ru LEFT JOIN users u ON u.id=ru.user_id WHERE ru.request_id=? ORDER BY ru.created_at DESC'); $hs->execute([$id]); $events=$hs->fetchAll(); ?><div class="timeline"><?php foreach ($events as $event): ?><article class="timeline-item"><span class="timeline-dot <?= e(status_class($event['new_status'])) ?>"></span><div><div class="timeline-head"><strong><?= e($event['new_status']) ?></strong><time><?= e(format_date($event['created_at'])) ?></time></div><p><?= e($event['note'] ?: 'Status updated.') ?></p><small><?= e($event['full_name'] ?? 'System') ?></small></div></article><?php endforeach; ?></div></section></div>
<aside class="detail-side"><section class="panel form-panel"><div class="panel-heading"><div><h2>Update status</h2><p>Record the latest action taken.</p></div></div><?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" class="form-stack"><?= csrf_field() ?><label>Request status<select name="status" required><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $request['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select></label><label>Update note<textarea name="note" rows="5" maxlength="1000" placeholder="Add a progress update or explanation for the citizen..."></textarea><small>Maximum 1,000 characters.</small></label><button class="btn btn-primary btn-full" type="submit">Save update <span>→</span></button></form></section><section class="help-card"><h3>Good practice</h3><p class="muted">Use clear, respectful notes. Explain what has happened or what the next step will be, without sharing sensitive personal information.</p></section></aside></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
