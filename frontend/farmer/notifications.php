<?php
$portal = 'farmer'; $active = 'notifications'; $pageTitle = 'Notifications';
$crumb = 'Bids, orders and system alerts'; $userRole = 'Farmer'; $pageScript = 'notifications';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="filter-pills" id="notificationFilters">
    <span class="pill active" data-value="all">All</span>
    <span class="pill" data-value="bids">Bids</span>
    <span class="pill" data-value="orders">Orders</span>
    <span class="pill" data-value="system">System</span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;">
    <span id="notificationUnreadCount" class="muted" aria-live="polite">Loading…</span>
    <button type="button" class="btn btn-outline btn-sm" id="markAllNotificationsRead">Mark all as read</button>
  </div>
</div>

<div class="gp-card">
  <div id="notificationsList" aria-live="polite"><p class="muted">Loading notifications…</p></div>
  <div id="notificationsPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
