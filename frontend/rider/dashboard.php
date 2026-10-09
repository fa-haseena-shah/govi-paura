<?php
$portal = 'rider'; $active = 'dashboard'; $pageTitle = 'Dashboard';
$crumb = 'Your delivery activity at a glance'; $userName = 'D. Weerasinghe'; $userRole = 'Delivery Rider';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-4" style="margin-bottom:22px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Assigned Today</div><div class="gp-stat__value">-</div></div><div class="gp-stat__icon i-assigned"><i class="bi bi-truck"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Completed Today</div><div class="gp-stat__value">-</div></div><div class="gp-stat__icon i-completed"><i class="bi bi-check2-circle"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Your Earnings</div><div class="gp-stat__value">-</div></div><div class="gp-stat__icon i-earnings"><i class="bi bi-cash-coin"></i></div></div>
</div>

<div class="gp-grid cols-2" style="align-items:start;">
  <div class="gp-card">
    <div class="section-head"><h3>Next Delivery</h3><span class="badge badge-warning">In progress</span></div>
    <p><strong>Order #</strong> <span id="orderItems"></span>—</p>
    <div class="route-stepper" data-delivery-id="1039">
      <div class="step done"><div class="dot"><i class="bi bi-check"></i></div><div class="step-label">Assigned</div></div>
      <div class="step current"><div class="dot">2</div><div class="step-label">Picked Up</div></div>
      <div class="step"><div class="dot">3</div><div class="step-label">In Transit</div></div>
      <div class="step"><div class="dot">4</div><div class="step-label">Delivered</div></div>
    </div>
    <button class="btn btn-gold btn-block" data-advance-status>Mark as In Transit</button>
  </div>

  <div class="gp-card">
    <div class="section-head"><h3>Quick Links</h3></div>
    <a id="available-deliveries" href="<?= BASE_URL ?>/rider/available_deliveries.php" class="btn btn-outline btn-block" style="margin-bottom:10px;justify-content:flex-start;"><i class="bi bi-signpost-split"></i>&nbsp; Browse available deliveries</a>
    <a id="assigned-deliveries" href="<?= BASE_URL ?>/rider/assigned_deliveries.php" class="btn btn-outline btn-block" style="margin-bottom:10px;justify-content:flex-start;"><i class="bi bi-truck"></i>&nbsp; View all assigned deliveries</a>
    <a href="<?= BASE_URL ?>/rider/subscription.php" class="btn btn-outline btn-block" style="justify-content:flex-start;"><i class="bi bi-patch-check"></i>&nbsp; Manage subscription plan</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
