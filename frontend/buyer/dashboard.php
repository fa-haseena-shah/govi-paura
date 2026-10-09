<?php
$portal = 'buyer'; $active = 'dashboard'; $pageTitle = 'Dashboard';
$crumb = 'Welcome back — here\'s what\'s fresh today'; $userRole = 'Buyer';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-3" style="margin-bottom:22px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Active Orders</div><div class="gp-stat__value" id="statActiveOrders">–</div></div><div class="gp-stat__icon i-orders"><i class="bi bi-box-seam"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Awaiting Payment</div><div class="gp-stat__value" id="statUnpaidOrders">–</div></div><div class="gp-stat__icon i-bids"><i class="bi bi-credit-card"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Completed Orders</div><div class="gp-stat__value" id="statCompletedOrders">–</div></div><div class="gp-stat__icon i-saved"><i class="bi bi-check2-circle"></i></div></div>
</div>

<div class="section-head">
  <h3>Fresh on the Market</h3>
  <a href="<?= BASE_URL ?>/buyer/browse_listings.php" class="muted">Browse all →</a>
</div>
<div class="gp-grid cols-3" id="freshGrid" style="margin-bottom:24px;">
  <p class="muted">Loading…</p>
</div>

<div class="gp-card">
  <div class="section-head"><h3>Your Active Orders</h3></div>
  <div class="table-wrap">
    <table class="gp-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Items (in KG)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="dashOrdersBody">
        <tr><td colspan="5" class="muted">Loading…</td></tr>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
