<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Govi Paura — Fair Prices, Direct from the Farm';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="Govi Paura connects farmer co-operatives directly with retailers and restaurants — no middlemen, fair prices, coordinated delivery.">
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
</head>
<body>

<!-- ===== Top Nav ===== -->
<nav class="lp-nav">
  <div class="lp-nav__inner">
    <div class="lp-nav__brand">
      <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="Govi Paura" width="80" height="70" style="border-radius:5px;">
      <span class="name">Govi Paura</span>
    </div>
    <div class="lp-nav__links" id="lpNavLinks">
      <a href="#how-it-works" data-i18n="lp_nav_how">How It Works</a>
      <a href="#portals" data-i18n="lp_nav_portals">Portals</a>
      <a href="#features" data-i18n="lp_nav_features">Features</a>
    </div>
    <div class="lp-nav__cta">
      <div class="lang-toggle" role="group" aria-label="Language">
        <a href="#" data-lang-switch="en" class="active">EN</a>
        <a href="#" data-lang-switch="si">සිං</a>
      </div>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline btn-sm" data-i18n="btn_login">Log In</a>
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary btn-sm" data-i18n="lp_get_started">Get Started</a>
      <button class="lp-nav__toggle" id="lpNavToggle" aria-label="Toggle menu" aria-expanded="false"><i class="bi bi-list"></i></button>
    </div>
  </div>
</nav>

<!-- ===== Hero ===== -->
<header class="lp-hero">
  <div class="lp-hero__inner">
    <div>
      <span class="lp-eyebrow"><i class="bi bi-stars"></i> <span data-i18n="lp_eyebrow">Web-Based B2B Farm-to-Retail Marketplace</span></span>
      <h1 data-i18n-html="lp_hero_title">Sell straight from the <span>harvest</span> — no middleman in between.</h1>
      <p class="lead" data-i18n="lp_hero_lead">Govi Paura connects farmer co-operatives directly with retailers and restaurants — transparent pricing, two-sided ratings, and delivery coordinated over existing lorry routes.</p>
      <div class="lp-hero__cta">
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-gold"><span data-i18n="lp_join_farmer">Join as a Farmer</span> <i class="bi bi-arrow-right"></i></a>
        <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-outline" style="border-color:rgba(255,255,255,.35);color:#fff;" data-i18n="lp_join_buyer">Join as a Buyer</a>
      </div>
      <div class="lp-hero__stats">
        <div><b>30&ndash;40%</b><span data-i18n="lp_stat_margin">Middleman margin removed</span></div>
        <div><b>4</b><span data-i18n="lp_stat_roles">Roles: Farmer, Buyer, Rider, Admin</span></div>
        <div><b>SI / EN</b><span data-i18n="lp_stat_lang">Sinhala-first interface</span></div>
      </div>
    </div>
    <div class="lp-hero__visual">
      <img src="<?= BASE_URL ?>/assets/images/hero-farm.svg" alt="Illustration of a farmer's harvest cart being loaded for delivery" style="width:100%;border-radius:var(--radius);margin-bottom:16px;display:block;">
      <div class="lp-flowcard">
        <div class="ic" style="background:var(--sage-light);color:var(--forest);"><i class="bi bi-basket2-fill"></i></div>
        <div><div class="t" data-i18n="lp_flow1_t">Farmer posts today's harvest</div><div class="s" data-i18n="lp_flow1_s">Crop, quantity, price, photo</div></div>
      </div>
      <div class="lp-flowcard arrow"><i class="bi bi-arrow-down"></i></div>
      <div class="lp-flowcard">
        <div class="ic" style="background:var(--gold-light);color:#8A6700;"><i class="bi bi-hammer"></i></div>
        <div><div class="t" data-i18n="lp_flow2_t">Buyer bids or buys at fixed price</div><div class="s" data-i18n="lp_flow2_s">Transparent, competitive offers</div></div>
      </div>
      <div class="lp-flowcard arrow"><i class="bi bi-arrow-down"></i></div>
      <div class="lp-flowcard">
        <div class="ic" style="background:var(--rust-light);color:var(--rust);"><i class="bi bi-truck"></i></div>
        <div><div class="t" data-i18n="lp_flow3_t">Rider delivers on an existing route</div><div class="s" data-i18n="lp_flow3_s">Shared lorry network, no new fleet</div></div>
      </div>
    </div>
  </div>
</header>

<!-- ===== How It Works ===== -->
<section class="lp-section" id="how-it-works">
  <div class="lp-section-head">
    <span class="lp-kicker" data-i18n="lp_kicker_how">How It Works</span>
    <h2 data-i18n="lp_how_title">From farm gate to retail shelf in three steps</h2>
    <p data-i18n="lp_how_sub">The same core loop every listing follows — simple enough for a farmer with a feature phone, transparent enough for a buyer comparing prices.</p>
  </div>
  <div class="gp-grid cols-3">
    <div class="lp-step">
      <div class="lp-step__num">1</div>
      <h3 data-i18n="lp_step1_t">List the Harvest</h3>
      <p data-i18n="lp_step1_p">A farmer or co-op posts today's crop, quantity and price — fixed or open to bidding — via the web app or a simple SMS command.</p>
    </div>
    <div class="lp-step">
      <div class="lp-step__num">2</div>
      <h3 data-i18n="lp_step2_t">Negotiate &amp; Confirm</h3>
      <p data-i18n="lp_step2_p">Retailers and restaurants browse listings, place a bid or buy outright. The farmer accepts, and an order is created automatically.</p>
    </div>
    <div class="lp-step">
      <div class="lp-step__num">3</div>
      <h3 data-i18n="lp_step3_t">Deliver &amp; Rate</h3>
      <p data-i18n="lp_step3_p">A delivery rider picks up and drops off the order on an existing route. Both sides rate each other, building trust over time.</p>
    </div>
  </div>
</section>

<!-- ===== Portals ===== -->
<section class="lp-section lp-section--tight" id="portals" style="background:var(--canvas);max-width:100%;padding-top:0;">
  <div style="max-width:1180px;margin:0 auto;padding:84px 24px;">
    <div class="lp-section-head">
      <span class="lp-kicker" data-i18n="lp_kicker_portals">One Platform, Four Portals</span>
      <h2 data-i18n="lp_portals_title">Built for everyone in the chain</h2>
      <p data-i18n="lp_portals_sub">Each role gets a dashboard tailored to what they actually need to do — no clutter, no irrelevant menus.</p>
    </div>
    <div class="gp-grid cols-4">
      <div class="gp-card lp-portal-card">
        <div class="ic" style="background:var(--sage-light);color:var(--forest);"><i class="bi bi-basket2-fill"></i></div>
        <h3 data-i18n="role_farmer">Farmer</h3>
        <p data-i18n="lp_portal_farmer_p">List harvests, review bids, track orders and see fair-price trends.</p>
      </div>
      <div class="gp-card lp-portal-card">
        <div class="ic" style="background:var(--gold-light);color:#8A6700;"><i class="bi bi-shop"></i></div>
        <h3 data-i18n="role_buyer">Buyer</h3>
        <p data-i18n="lp_portal_buyer_p">Browse listings, place bids, check out orders and rate farmers.</p>
      </div>
      <div class="gp-card lp-portal-card">
        <div class="ic" style="background:var(--rust-light);color:var(--rust);"><i class="bi bi-truck"></i></div>
        <h3 data-i18n="lp_portal_rider_title">Delivery Rider</h3>
        <p data-i18n="lp_portal_rider_p">Accept deliveries, update status, and manage route subscriptions.</p>
      </div>
      <div class="gp-card lp-portal-card">
        <div class="ic" style="background:#E4EDE2;color:var(--forest);"><i class="bi bi-shield-check"></i></div>
        <h3 data-i18n="role_admin">Admin</h3>
        <p data-i18n="lp_portal_admin_p">Verify users, moderate listings, and oversee platform activity.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== Features ===== -->
<section class="lp-section" id="features">
  <div class="lp-section-head">
    <span class="lp-kicker" data-i18n="lp_kicker_features">Why Govi Paura</span>
    <h2 data-i18n="lp_features_title">Everything the old WhatsApp-and-a-mudalali process was missing</h2>
    <p data-i18n="lp_features_sub">Each feature maps directly to a gap in the current informal trading process.</p>
  </div>
  <div class="gp-grid cols-3">
    <div class="lp-feature">
      <div class="ic" style="background:var(--sage-light);color:var(--forest);"><i class="bi bi-graph-up-arrow"></i></div>
      <div><h3 data-i18n="lp_feat1_t">Live Price History</h3><p data-i18n="lp_feat1_p">Aggregated, anonymised price trends by crop and region, so no one trades blind.</p></div>
    </div>
    <div class="lp-feature">
      <div class="ic" style="background:var(--gold-light);color:#8A6700;"><i class="bi bi-star-fill"></i></div>
      <div><h3 data-i18n="lp_feat2_t">Two-Sided Ratings</h3><p data-i18n="lp_feat2_p">Farmers and buyers rate each other after every order, building a real trust score.</p></div>
    </div>
    <div class="lp-feature">
      <div class="ic" style="background:var(--rust-light);color:var(--rust);"><i class="bi bi-robot"></i></div>
      <div><h3 data-i18n="lp_feat3_t">AI Chatbot Assistant</h3><p data-i18n="lp_feat3_p">Post a listing, check an order, or ask for a price trend in plain Sinhala or English.</p></div>
    </div>
    <div class="lp-feature">
      <div class="ic" style="background:#E4EDE2;color:var(--forest);"><i class="bi bi-chat-dots-fill"></i></div>
      <div><h3 data-i18n="lp_feat4_t">SMS Fallback</h3><p data-i18n="lp_feat4_p">No smartphone or data? Post a harvest listing with a simple structured text message.</p></div>
    </div>
    <div class="lp-feature">
      <div class="ic" style="background:var(--sage-light);color:var(--forest);"><i class="bi bi-map-fill"></i></div>
      <div><h3 data-i18n="lp_feat5_t">Route-Aware Delivery</h3><p data-i18n="lp_feat5_p">Deliveries are matched to existing lorry routes instead of requiring a new fleet.</p></div>
    </div>
    <div class="lp-feature">
      <div class="ic" style="background:var(--gold-light);color:#8A6700;"><i class="bi bi-translate"></i></div>
      <div><h3 data-i18n="lp_feat6_t">Sinhala-First Design</h3><p data-i18n="lp_feat6_p">Every screen is built Sinhala-first with English as a toggle, not an afterthought.</p></div>
    </div>
  </div>
</section>

<!-- ===== CTA Band ===== -->
<section class="lp-section" style="padding-top:0;">
  <div class="lp-cta-band">
    <h2 data-i18n="lp_cta_title">Ready to cut out the middleman?</h2>
    <p class="muted" data-i18n="lp_cta_sub">Create an account as a Farmer, Buyer or Delivery Rider — it takes less than two minutes.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary" data-i18n="lp_cta_create">Create Free Account</a>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline" data-i18n="lp_cta_have">I Already Have an Account</a>
    </div>
  </div>
</section>

<!-- ===== Footer ===== -->
<footer class="lp-footer">
  <div class="lp-footer__inner">
    <div class="lp-footer__top">
      <div>
        <div class="lp-footer__brand">
          <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="" width="50" height="45" style="border-radius:8px;">
          <span class="name">Govi Paura</span>
        </div>
        <p style="font-size:13px;line-height:1.6;max-width:260px;" data-i18n="lp_footer_about">A web-based B2B marketplace connecting farmer co-operatives directly with retailers and restaurants across Sri Lanka.</p>
      </div>
      <div>
        <h4 data-i18n="lp_footer_portals">Portals</h4>
        <a href="<?= BASE_URL ?>/farmer/dashboard.php" data-i18n="role_farmer">Farmer</a>
        <a href="<?= BASE_URL ?>/buyer/dashboard.php" data-i18n="role_buyer">Buyer</a>
        <a href="<?= BASE_URL ?>/rider/dashboard.php" data-i18n="lp_portal_rider_title">Delivery Rider</a>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" data-i18n="role_admin">Admin</a>
      </div>
      <div>
        <h4 data-i18n="nav_account">Account</h4>
        <a href="<?= BASE_URL ?>/auth/login.php" data-i18n="btn_login">Log In</a>
        <a href="<?= BASE_URL ?>/auth/register.php" data-i18n="lp_footer_register">Register</a>
      </div>
      <div>
        <h4 data-i18n="lp_footer_support">Support</h4>
        <a href="mailto:support@govipaura.lk">support@govipaura.lk</a>
        <a href="<?= BASE_URL ?>/farmer/helpdesk.php" data-i18n="nav_helpdesk">Helpdesk</a>
      </div>
    </div>
    <div class="lp-footer__bottom">
      <span>&copy; <?= date('Y') ?> Govi Paura. <span data-i18n="lp_footer_copyright">Academic final-year project.</span></span>
      <span data-i18n="lp_footer_tagline">Made for farmers, buyers &amp; riders across Sri Lanka.</span>
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script>
  // Mobile nav toggle (landing page only)
  (function () {
    var btn = document.getElementById('lpNavToggle');
    var links = document.getElementById('lpNavLinks');
    if (!btn || !links) return;
    btn.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    links.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { links.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); });
    });
  })();
</script>
</body>
</html>
