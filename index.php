<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
$pageTitle = 'Home';
$stats = ['total' => 0, 'resolved' => 0];
try {
    $stats = $pdo->query("SELECT COUNT(*) total, SUM(status='Resolved') resolved FROM service_requests")->fetch() ?: $stats;
} catch (Throwable $e) { /* Database is available in normal operation; keep homepage resilient. */ }
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow"><span class="eyebrow-dot"></span> YOUR COMMUNITY. YOUR VOICE.</span>
    <h1>See a problem?<br><span>Let's solve it together.</span></h1>
    <p>Report public service issues in your area and follow their progress from submission to resolution. Every report helps build a better community.</p>
    <div class="hero-actions">
      <?php if (current_user()): ?>
        <a class="btn btn-primary" href="<?= e(url('request-new.php')) ?>">Report an issue <span>→</span></a>
        <a class="btn btn-outline" href="<?= e(url('dashboard.php')) ?>">Track my requests</a>
      <?php else: ?>
        <a class="btn btn-primary" href="<?= e(url('register.php')) ?>">Report an issue <span>→</span></a>
        <a class="btn btn-outline" href="<?= e(url('login.php')) ?>">Sign in to track</a>
      <?php endif; ?>
    </div>
    <div class="hero-trust"><span class="trust-check">✓</span> Free to use <span class="trust-divider">·</span> Secure submissions <span class="trust-divider">·</span> Trackable updates</div>
  </div>
  <div class="hero-art" aria-label="Community service illustration">
    <div class="art-circle"></div>
    <div class="art-card art-card-top"><span class="art-icon teal">✓</span><div><strong>Request received</strong><small>Your report is in the system</small></div></div>
    <div class="art-building"><div class="building-roof"></div><div class="building-body"><div class="building-sign">PUBLIC<br>SERVICE</div><div class="building-columns"><i></i><i></i><i></i></div><div class="building-steps"></div></div></div>
    <div class="art-card art-card-bottom"><span class="art-icon gold">↗</span><div><strong>Progress you can follow</strong><small>Stay informed at every step</small></div></div>
    <div class="art-spark spark-one">✳</div><div class="art-spark spark-two">✦</div>
  </div>
</section>

<section class="section categories-section">
  <div class="section-heading"><div><span class="eyebrow">WHAT NEEDS ATTENTION?</span><h2>Public services, improved together.</h2></div><p>Choose a category when reporting a problem. Add the location and details so the right team can act.</p></div>
  <div class="category-grid">
    <article class="category-card"><span class="category-icon icon-blue">☼</span><h3>Street lighting</h3><p>Broken or non-working streetlights.</p></article>
    <article class="category-card"><span class="category-icon icon-teal">≋</span><h3>Water supply</h3><p>Leaks, interruptions, or water issues.</p></article>
    <article class="category-card"><span class="category-icon icon-gold">♻</span><h3>Waste collection</h3><p>Missed collection and overflowing bins.</p></article>
    <article class="category-card"><span class="category-icon icon-coral">⌁</span><h3>Roads & drainage</h3><p>Potholes, blocked drains, and flooding.</p></article>
  </div>
</section>

<section class="how-section">
  <div class="section-heading centered"><span class="eyebrow">SIMPLE. CLEAR. ACCOUNTABLE.</span><h2>From report to resolution.</h2><p>Three simple steps to help improve your neighbourhood.</p></div>
  <div class="steps-grid">
    <article class="step-card"><span class="step-number">01</span><div class="step-symbol">✎</div><h3>Report the issue</h3><p>Tell us what happened, where it happened, and add a photo if helpful.</p></article>
    <article class="step-card"><span class="step-number">02</span><div class="step-symbol">⌕</div><h3>Follow progress</h3><p>Use your dashboard and reference number to see status updates.</p></article>
    <article class="step-card"><span class="step-number">03</span><div class="step-symbol">✓</div><h3>See the outcome</h3><p>Track the request until it is marked as resolved by the service team.</p></article>
  </div>
</section>
<section class="closing-cta"><div><span class="eyebrow">MAKE A DIFFERENCE</span><h2>A better community starts with a report.</h2><p>Your report helps service teams understand what needs attention.</p></div><a class="btn btn-light" href="<?= e(url(current_user() ? 'request-new.php' : 'register.php')) ?>">Get started <span>→</span></a></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
