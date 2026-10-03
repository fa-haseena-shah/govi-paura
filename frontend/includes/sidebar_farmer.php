<aside class="gp-sidebar">
  <div class="gp-sidebar__brand">
    <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="36" height="36" style="border-radius:10px;">
    <div>
      <div class="name">Govi Paura</div>
      <div class="role">Farmer Portal</div>
    </div>
  </div>
  <nav class="gp-nav">
    <a href="<?= BASE_URL ?>/farmer/dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> <span data-i18n="nav_dashboard">Dashboard</span></a>
    <a href="<?= BASE_URL ?>/farmer/listings.php" class="<?= $active === 'listings' ? 'active' : '' ?>"><i class="bi bi-basket"></i> <span data-i18n="nav_listings">My Listings</span></a>
    <a href="<?= BASE_URL ?>/farmer/bids.php" class="<?= $active === 'bids' ? 'active' : '' ?>"><i class="bi bi-hammer"></i> <span data-i18n="nav_bids">Bids Received</span></a>
    <a href="<?= BASE_URL ?>/farmer/orders.php" class="<?= $active === 'orders' ? 'active' : '' ?>"><i class="bi bi-box-seam"></i> <span data-i18n="nav_orders">Orders</span></a>
    <a href="<?= BASE_URL ?>/farmer/price_trends.php" class="<?= $active === 'price_trends' ? 'active' : '' ?>"><i class="bi bi-graph-up"></i> <span data-i18n="nav_price_trends">Price Trends</span></a>
    <a href="<?= BASE_URL ?>/farmer/ratings.php" class="<?= $active === 'ratings' ? 'active' : '' ?>"><i class="bi bi-star"></i> <span data-i18n="nav_ratings">Ratings & Reviews</span></a>
    <div class="gp-nav__group-label" data-i18n="nav_account">Account</div>
    <a href="<?= BASE_URL ?>/farmer/notifications.php" class="<?= $active === 'notifications' ? 'active' : '' ?>"><i class="bi bi-bell"></i> <span data-i18n="nav_notifications">Notifications</span></a>
    <a href="<?= BASE_URL ?>/farmer/helpdesk.php" class="<?= $active === 'helpdesk' ? 'active' : '' ?>"><i class="bi bi-life-preserver"></i> <span data-i18n="nav_helpdesk">Helpdesk</span></a>
    <a href="<?= BASE_URL ?>/farmer/profile.php" class="<?= $active === 'profile' ? 'active' : '' ?>"><i class="bi bi-person-circle"></i> <span data-i18n="nav_profile">Profile</span></a>
  </nav>
  <div class="gp-sidebar__foot">
    <span data-i18n="signedInAs">Signed in as</span> Farmer · <a href="<?= BASE_URL ?>/auth/login.php" data-i18n="logout">Log out</a>
  </div>
</aside>
