<aside class="gp-sidebar">
  <div class="gp-sidebar__brand">
    <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="36" height="36" style="border-radius:10px;">
    <div>
      <div class="name">Govi Paura</div>
      <div class="role">Admin Portal</div>
    </div>
  </div>
  <nav class="gp-nav">
    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> <span data-i18n="nav_dashboard">Dashboard</span></a>
    <div class="gp-nav__group-label" data-i18n="nav_moderation">Moderation</div>
    <a href="<?= BASE_URL ?>/admin/verify_users.php" class="<?= $active === 'verify_users' ? 'active' : '' ?>"><i class="bi bi-patch-check"></i> <span data-i18n="nav_verify_users">Verify Users</span></a>
    <a href="<?= BASE_URL ?>/admin/manage_users.php" class="<?= $active === 'manage_users' ? 'active' : '' ?>"><i class="bi bi-people"></i> <span data-i18n="nav_manage_users">Manage Users</span></a>
    <a href="<?= BASE_URL ?>/admin/manage_listings.php" class="<?= $active === 'manage_listings' ? 'active' : '' ?>"><i class="bi bi-basket"></i> <span data-i18n="nav_manage_listings">Manage Listings</span></a>
    <a href="<?= BASE_URL ?>/admin/manage_bids_orders.php" class="<?= $active === 'manage_bids_orders' ? 'active' : '' ?>"><i class="bi bi-receipt"></i> <span data-i18n="nav_manage_bids_orders">Bids & Orders</span></a>
    <a href="<?= BASE_URL ?>/admin/manage_reviews.php" class="<?= $active === 'manage_reviews' ? 'active' : '' ?>"><i class="bi bi-star"></i> <span data-i18n="nav_manage_reviews">Reviews</span></a>
    <a href="<?= BASE_URL ?>/admin/complaints.php" class="<?= $active === 'complaints' ? 'active' : '' ?>"><i class="bi bi-life-preserver"></i> <span data-i18n="nav_complaints">Helpdesk / Complaints</span></a>
    <div class="gp-nav__group-label" data-i18n="nav_system">System</div>
    <a href="<?= BASE_URL ?>/admin/reports.php" class="<?= $active === 'reports' ? 'active' : '' ?>"><i class="bi bi-file-earmark-bar-graph"></i> <span data-i18n="nav_reports">Reports</span></a>
    <a href="<?= BASE_URL ?>/admin/system_logs.php" class="<?= $active === 'system_logs' ? 'active' : '' ?>"><i class="bi bi-terminal"></i> <span data-i18n="nav_system_logs">System Logs</span></a>
  </nav>
  <div class="gp-sidebar__foot">
    <span data-i18n="signedInAs">Signed in as</span> Admin · <a href="<?= BASE_URL ?>/auth/login.php" data-i18n="logout">Log out</a>
  </div>
</aside>
