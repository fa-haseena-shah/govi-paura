<?php
$portal = 'admin'; $active = 'dashboard'; $pageTitle = 'Dashboard';
$crumb = 'Platform overview'; $userRole = 'Admin';
$pageScript = 'verification';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-4" style="margin-bottom:22px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Total Users</div><div class="gp-stat__value" id="adminTotalUsers">—</div></div><div class="gp-stat__icon i-users"><i class="bi bi-people"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Active Listings</div><div class="gp-stat__value" id="adminActiveListings">—</div></div><div class="gp-stat__icon i-listings"><i class="bi bi-basket"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Orders This Month</div><div class="gp-stat__value" id="adminOrdersThisMonth">—</div></div><div class="gp-stat__icon i-orders"><i class="bi bi-box-seam"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Paid Order Volume</div><div class="gp-stat__value" id="adminPaidOrderVolume">—</div></div><div class="gp-stat__icon i-revenue"><i class="bi bi-cash-coin"></i></div></div>
</div>

<div class="gp-card">
  <h3 style="margin-bottom:6px;">Verification requests</h3>
  <p class="muted">Farmers and riders must be approved before they can sell or deliver.</p>
  <a href="<?= BASE_URL ?>/admin/verify_users.php" class="btn btn-accent">Review requests</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
