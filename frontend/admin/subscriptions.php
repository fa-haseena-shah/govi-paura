<?php
$portal = 'admin'; $active = 'subscriptions'; $pageTitle = 'Rider Subscriptions';
$crumb = 'Review subscription payments and transactions'; $userRole = 'Admin';
$pageScript = 'admin-management';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar" style="gap:12px;flex-wrap:wrap;">
  <div class="gp-search" style="flex:1;min-width:220px;"><i class="bi bi-search"></i><input id="adminSubscriptionSearch" type="search" placeholder="Search rider, email or transaction reference" aria-label="Search subscriptions and transactions"></div>
  <select id="adminSubscriptionStatus" aria-label="Filter by subscription status">
    <option value="">All subscription states</option><option value="pending">Pending</option><option value="scheduled">Scheduled</option><option value="active">Active</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option>
  </select>
  <select id="adminTransactionStatus" aria-label="Filter by transaction status">
    <option value="">All transaction states</option><option value="pending">Pending</option><option value="success">Successful</option><option value="failed">Failed</option><option value="cancelled">Cancelled</option><option value="chargedback">Charged back</option>
  </select>
</div>

<div class="gp-grid cols-4" style="margin-bottom:20px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Matching Subscriptions</div><div class="gp-stat__value" id="adminSubscriptionCount">—</div></div><div class="gp-stat__icon i-users"><i class="bi bi-person-vcard"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Transaction Attempts</div><div class="gp-stat__value" id="adminTransactionCount">—</div></div><div class="gp-stat__icon i-listings"><i class="bi bi-arrow-left-right"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Needs Attention</div><div class="gp-stat__value" id="adminAttentionCount">—</div></div><div class="gp-stat__icon i-orders"><i class="bi bi-exclamation-circle"></i></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Successful Volume</div><div class="gp-stat__value" id="adminSubscriptionVolume">—</div></div><div class="gp-stat__icon i-revenue"><i class="bi bi-cash-coin"></i></div></div>
</div>

<div class="gp-card">
  <div class="table-wrap"><table class="gp-table">
    <thead>
      <tr>
        <th>Rider</th>
        <th>Subscription</th>
        <th>Period</th>
        <th>Subscription status</th>
        <th>Transaction id</th>
        <th>Amount paid</th>
        <th>Payment Status</th>
        <th>Paid at</th></tr></thead>
    <tbody id="adminSubscriptionsBody"><tr><td colspan="8" class="muted">Loading subscription records…</td></tr></tbody>
  </table></div>
  <div id="adminSubscriptionsPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>