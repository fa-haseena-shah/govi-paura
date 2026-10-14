<?php
$portal = 'rider'; $active = 'available_deliveries'; $pageTitle = 'Deliveries Near You';
$crumb = 'Unassigned deliveries near you'; $userRole = 'Delivery Rider';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="gp-search"><i class="bi bi-search"></i><input type="text" data-gp-search=".delivery-card" data-gp-empty="#deliveriesEmpty" placeholder="Search by area or order ID..."></div>
</div>

<div id="opportunitiesLoading" class="gp-empty">
  <i class="bi bi-hourglass-split"></i>
  <h4>Loading deliveries…</h4>
</div>

<div class="gp-grid cols-3" id="opportunitiesGrid" style="display:none;"></div>

<div id="deliveriesEmpty" class="gp-empty" style="display:none;">
  <i class="bi bi-signpost-split"></i>
  <h4>No deliveries found in your area</h4>
  <p class="muted">Check back shortly — new deliveries appear here as farmers confirm orders.</p>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
