<?php
$portal = 'admin'; $active = 'manage_bids_orders'; $pageTitle = 'Bids & Orders';
$crumb = 'Monitor platform transactions and fulfilment'; $userRole = 'Admin';
$pageScript = 'admin-management';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="filter-pills" id="adminActivityTabs">
    <button type="button" class="pill active" data-value="bids">Bidding history</button>
    <button type="button" class="pill" data-value="orders">Buyer orders</button>
  </div>
</div>
<div class="gp-card">
  <div class="table-wrap"><table class="gp-table">
    <thead id="adminActivityHead"></thead>
    <tbody id="adminActivityBody"><tr><td class="muted">Loading activity…</td></tr></tbody>
  </table></div>
  <div id="adminActivityPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>