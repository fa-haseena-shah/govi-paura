<aside class="gp-sidebar">
  <div class="gp-sidebar__brand">
    <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="36" height="36" style="border-radius:10px;">
    <div>
      <div class="name">Govi Paura</div>
      <div class="role">Rider Portal</div>
    </div>
  </div>
  <nav class="gp-nav">
    <a href="<?= BASE_URL ?>/rider/dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> <span data-i18n="nav_dashboard">Dashboard</span></a>
    <a href="<?= BASE_URL ?>/rider/available_deliveries.php" class="<?= $active === 'available_deliveries' ? 'active' : '' ?>"><i class="bi bi-signpost-split"></i> <span data-i18n="nav_available_deliveries">Available Deliveries</span></a>
    <a href="<?= BASE_URL ?>/rider/assigned_deliveries.php" class="<?= $active === 'assigned_deliveries' ? 'active' : '' ?>"><i class="bi bi-truck"></i> <span data-i18n="nav_assigned_deliveries">Assigned Deliveries</span></a>
    <a href="<?= BASE_URL ?>/rider/delivery_history.php" class="<?= $active === 'delivery_history' ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> <span data-i18n="nav_delivery_history">Delivery History</span></a>
    <a href="<?= BASE_URL ?>/rider/subscription.php" class="<?= $active === 'subscription' ? 'active' : '' ?>"><i class="bi bi-patch-check"></i> <span data-i18n="nav_subscription">Subscription</span></a>
    <div class="gp-nav__group-label" data-i18n="nav_account">Account</div>
    <a href="<?= BASE_URL ?>/rider/notifications.php" class="<?= $active === 'notifications' ? 'active' : '' ?>"><i class="bi bi-bell"></i> <span data-i18n="nav_notifications">Notifications</span></a>
    <a href="<?= BASE_URL ?>/rider/helpdesk.php" class="<?= $active === 'helpdesk' ? 'active' : '' ?>"><i class="bi bi-life-preserver"></i> <span data-i18n="nav_helpdesk">Helpdesk</span></a>
    <a href="<?= BASE_URL ?>/rider/profile.php" class="<?= $active === 'profile' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i> <span data-i18n="nav_profile">Profile</span></a>
  </nav>
  <div class="gp-sidebar__foot">
    <span data-i18n="signedInAs">Signed in as</span> Rider · <a href="<?= BASE_URL ?>/auth/login.php" data-i18n="logout">Log out</a>
  </div>
</aside>
