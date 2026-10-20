<?php
$portal = 'rider'; $active = 'delivery_history'; $pageTitle = 'Delivery History';
$crumb = 'Your completed deliveries'; $userRole = 'Delivery Rider';
include __DIR__ . '/../includes/header.php';
?>

<div id="historySummary" class="gp-grid cols-4" style="margin-bottom:22px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Total Completed</div><div class="gp-stat__value" data-stat="total">—</div></div><div class="gp-stat__icon i-completed"><i class="bi bi-check2-circle"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">On Time</div><div class="gp-stat__value" data-stat="on-time">—</div></div><div class="gp-stat__icon i-assigned"><i class="bi bi-clock-history"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Late</div><div class="gp-stat__value" data-stat="late">—</div></div><div class="gp-stat__icon i-earnings"><i class="bi bi-exclamation-circle"></i></div></div>
</div>

<div class="gp-toolbar">
  <div class="gp-search"><i class="bi bi-search"></i><input type="text" data-gp-search="#historyTable tbody tr" data-gp-empty="#historyEmpty" placeholder="Search order ID or buyer..."></div>
</div>

<div class="gp-card">
  <div class="table-wrap">
    <table class="gp-table" id="historyTable">
      <thead><tr><th>Order ID</th><th>Items</th><th>Buyer</th><th>Distance</th><th>Delivered On</th></tr></thead>
      <tbody id="historyTableBody">
        <tr><td colspan="5" class="muted">Loading history…</td></tr>
      </tbody>
    </table>
  </div>
  <div id="historyEmpty" class="gp-empty" style="display:none;">
    <i class="bi bi-clock-history"></i>
    <h4>No deliveries found</h4>
    <p class="muted">Try a different order ID or buyer name.</p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
