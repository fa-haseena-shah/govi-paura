<?php
$portal = 'rider'; $active = 'assigned_deliveries'; $pageTitle = 'Assigned Deliveries';
$crumb = 'Deliveries currently on your route'; $userRole = 'Delivery Rider'; $needsMap = true;
include __DIR__ . '/../includes/header.php';
?>

<div id="assignedLoading" class="gp-empty">
  <i class="bi bi-hourglass-split"></i>
  <h4>Loading your deliveries…</h4>
</div>

<div id="assignedDeliveriesList" style="display:none;"></div>

<div id="assignedEmpty" class="gp-empty" style="display:none;">
  <i class="bi bi-truck"></i>
  <h4>No active deliveries</h4>
  <p class="muted">You have no deliveries assigned right now. <a href="<?= BASE_URL ?>/rider/available_deliveries.php">Browse available deliveries</a> to claim one.</p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
