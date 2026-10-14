<?php
$portal = 'rider'; $active = 'dashboard'; $pageTitle = 'Dashboard';
$crumb = 'Your delivery activity at a glance'; $userRole = 'Delivery Rider';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-4" id="riderDashStats" style="margin-bottom:22px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Assigned Today</div><div class="gp-stat__value" data-stat="assigned">—</div></div><div class="gp-stat__icon i-assigned"><i class="bi bi-truck"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Completed Today</div><div class="gp-stat__value" data-stat="completed">—</div></div><div class="gp-stat__icon i-completed"><i class="bi bi-check2-circle"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Total Completed</div><div class="gp-stat__value" data-stat="earnings">—</div></div><div class="gp-stat__icon i-earnings"><i class="bi bi-cash-coin"></i></div></div>
</div>

<div class="gp-grid cols-2" style="align-items:start;">
  <div class="gp-card" id="riderDashNext">
    <div class="gp-empty" style="padding:24px 0;">
      <i class="bi bi-hourglass-split"></i>
      <h4>Loading…</h4>
    </div>
  </div>

  <div class="gp-card">
    <div class="section-head"><h3>Quick Links</h3></div>
    <a id="available-deliveries" href="<?= BASE_URL ?>/rider/available_deliveries.php" class="btn btn-outline btn-block" style="margin-bottom:10px;justify-content:flex-start;"><i class="bi bi-signpost-split"></i>&nbsp; Browse available deliveries</a>
    <a id="assigned-deliveries" href="<?= BASE_URL ?>/rider/assigned_deliveries.php" class="btn btn-outline btn-block" style="margin-bottom:10px;justify-content:flex-start;"><i class="bi bi-truck"></i>&nbsp; View all assigned deliveries</a>
    <a href="<?= BASE_URL ?>/rider/subscription.php" class="btn btn-outline btn-block" style="justify-content:flex-start;"><i class="bi bi-patch-check"></i>&nbsp; Manage subscription plan</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
