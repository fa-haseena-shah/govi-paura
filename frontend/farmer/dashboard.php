<?php
$portal = 'farmer'; $active = 'dashboard'; $pageTitle = 'Dashboard';
$crumb = 'Overview of your farm activity'; $userRole = 'Farmer'; $needsChart = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-4" style="margin-bottom:22px;">
  <div class="gp-stat">
    <div><div class="gp-stat__label">Active Listings</div><div class="gp-stat__value" id="statActiveListings">–</div><div class="gp-stat__delta up"></div></div>
    <div class="gp-stat__icon i-listings"><i class="bi bi-basket"></i></div>
  </div>
  <div class="gp-stat">
    <div><div class="gp-stat__label">Pending Orders</div><div class="gp-stat__value" id="statPendingOrders">–</div><div class="gp-stat__delta up"></div></div>
    <div class="gp-stat__icon i-bids"><i class="bi bi-hourglass-split"></i></div>
  </div>
  <div class="gp-stat">
    <div><div class="gp-stat__label">Orders This Month</div><div class="gp-stat__value" id="statMonthOrders">–</div><div class="gp-stat__delta up"></div></div>
    <div class="gp-stat__icon i-orders"><i class="bi bi-box-seam"></i></div>
  </div>
  <div class="gp-stat">
    <div><div class="gp-stat__label">Earnings (This Month)</div><div class="gp-stat__value" id="statEarnings">–</div><div class="gp-stat__delta up"></div></div>
    <div class="gp-stat__icon i-earnings"><i class="bi bi-cash-coin"></i></div>
  </div>
</div>

<div class="gp-card" style="margin-top:20px;">
  <div class="section-head">
    <h3>Your Active Listings</h3>
    <a href="<?= BASE_URL ?>/farmer/listings.php" class="btn btn-accent btn-sm"><i class="bi bi-plus-lg"></i> New Listing</a>
  </div>
  <div class="table-wrap">
    <table class="gp-table">
      <thead>
        <tr>
          <th>Crop</th>
          <th>Quantity</th>
          <th>Method</th>
          <th>Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="dashListingsBody">
        <tr><td colspan="6" class="muted">Loading…</td></tr>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
