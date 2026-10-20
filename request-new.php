<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_login();
$pageTitle = 'Submit a request';
$categories = ['Street lighting', 'Water supply', 'Waste collection', 'Roads and potholes', 'Drainage and flooding', 'Public facilities', 'Other'];
$errors = [];
$category = $location = $description = $latitude = $longitude = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $category = trim((string)($_POST['category'] ?? ''));
    $location = trim((string)($_POST['location'] ?? ''));
    $latitude = trim((string)($_POST['latitude'] ?? ''));
    $longitude = trim((string)($_POST['longitude'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    if (!in_array($category, $categories, true)) $errors[] = 'Choose a valid service category.';
    if (mb_strlen($location) < 4 || mb_strlen($location) > 255) $errors[] = 'Enter a location between 4 and 255 characters.';
    if (!is_numeric($latitude) || !is_numeric($longitude) || (float)$latitude < -90 || (float)$latitude > 90 || (float)$longitude < -180 || (float)$longitude > 180) $errors[] = 'Select a valid point on the map.';
    if (mb_strlen($description) < 10 || mb_strlen($description) > 5000) $errors[] = 'Description must be between 10 and 5,000 characters.';
    $imageName = null;
    if (!$errors) {
        try { $imageName = save_request_image($_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE]); }
        catch (RuntimeException $e) { $errors[] = $e->getMessage(); }
    }
    if (!$errors) {
        $reference = generate_reference();
        $stmt = $pdo->prepare('INSERT INTO service_requests (reference_number, user_id, category, location, latitude, longitude, description, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "Pending")');
        $stmt->execute([$reference, current_user()['id'], $category, $location, $latitude, $longitude, $description, $imageName]);
        $id = (int)$pdo->lastInsertId();
        $history = $pdo->prepare('INSERT INTO request_updates (request_id, user_id, old_status, new_status, note) VALUES (?, ?, NULL, "Pending", ?)');
        $history->execute([$id, current_user()['id'], 'Request submitted by citizen.']);
        flash('success', 'Request submitted successfully. Your reference is ' . $reference . '.');
        redirect('request-view.php?id=' . $id);
    }
}
$includeMapAssets = true;
require dirname(__DIR__) . '/includes/header.php';
?>
<div class="page-heading"><div><a class="back-link" href="<?= e(url('dashboard.php')) ?>">← Back to my requests</a><span class="eyebrow">NEW SUBMISSION</span><h1>Report a public issue</h1><p>Give us enough detail to help the right team locate and address the problem.</p></div></div>
<div class="form-layout"><section class="panel form-panel"><div class="panel-heading"><div><h2>Request details</h2><p>Fields marked with * are required.</p></div><span class="form-step">01 / 01</span></div>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form-stack"><?= csrf_field() ?>
<label>Service category *<select name="category" required><option value="">Choose a category</option><?php foreach ($categories as $c): ?><option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></label>
<label>Where is the issue? *<input name="location" required minlength="4" maxlength="255" value="<?= e($location) ?>" placeholder="e.g. near the market, along Kenyatta Road"><small>Include an area, street, landmark, or estate name.</small></label>
<div class="map-picker-field"><span class="map-field-label">Pin the exact location *</span><p class="map-help">Click or tap the map to place a pin, or use your device location.</p><div class="map-controls"><button class="btn btn-outline btn-small" type="button" data-locate-me>Use my location</button><span data-map-coordinates aria-live="polite">No point selected</span></div><div class="location-map" data-location-picker data-latitude="<?= e($latitude) ?>" data-longitude="<?= e($longitude) ?>" role="application" aria-label="Map to select the issue location"></div><input type="hidden" name="latitude" value="<?= e($latitude) ?>" data-latitude-input><input type="hidden" name="longitude" value="<?= e($longitude) ?>" data-longitude-input><small data-map-error class="map-error" role="status"></small></div>
<label>Describe the issue *<textarea name="description" required minlength="10" maxlength="5000" rows="6" placeholder="What happened? When did you notice it? Add any useful details."><?= e($description) ?></textarea><small><span data-char-count>0</span>/5000 characters</small></label>
<label>Attach a photo <span class="optional-label">OPTIONAL</span><span class="upload-box"><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-file-input><span class="upload-icon">↑</span><strong data-file-name>Choose an image to upload</strong><small>JPG, PNG, or WebP · Maximum 3 MB</small></span></label>
<div class="form-actions"><a class="btn btn-outline" href="<?= e(url('dashboard.php')) ?>">Cancel</a><button class="btn btn-primary" type="submit">Submit request <span>→</span></button></div>
</form></section><aside class="help-card"><span class="help-symbol">✦</span><h3>Tips for a useful report</h3><ul><li><strong>Be specific.</strong> Describe what is wrong and what you observed.</li><li><strong>Pinpoint the place.</strong> Use a street, estate, landmark, or nearby building.</li><li><strong>Add a photo.</strong> An image can help explain the issue.</li><li><strong>Keep your reference.</strong> Use it when following up on your report.</li></ul><div class="privacy-note">🔒 Your request is linked to your account and is visible to authorized service administrators.</div></aside></div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
