<?php
$portal = 'farmer'; $active = 'orders'; $pageTitle = 'Orders';
$crumb = 'Track orders generated from your sales'; $userRole = 'Farmer';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="gp-search"><i class="bi bi-search"></i><input type="text" data-gp-search="#ordersTable tbody tr" placeholder="Search by buyer, crop or order ID..."></div>
  <div class="filter-pills" id="orderFilterPills">
    <span class="pill active" data-value="all">All</span>
    <span class="pill" data-value="pending">Pending</span>
    <span class="pill" data-value="progress">In progress</span>
    <span class="pill" data-value="completed">Completed</span>
  </div>
</div>

<div class="gp-card">
  <div class="table-wrap">
    <table class="gp-table" id="ordersTable">
      <thead>
        <tr>
          <th>Order</th>
          <th>Buyer &amp; contact</th>
          <th>Items (in KG)</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="ordersBody">
        <tr><td colspan="7" class="muted">Loading…</td></tr>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
