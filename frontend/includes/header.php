<?php
require_once __DIR__ . '/config.php';
/**
 * Shared header — include AFTER setting these variables in the page:
 *
 *   $portal      = 'farmer' | 'rider' | 'buyer' | 'admin'
 *   $active      = nav key of the current page (see includes/nav_<portal>.php)
 *   $pageTitle   = string shown in the browser tab + topbar
 *   $crumb       = short breadcrumb text under the title (optional)
 *   $userName    = string, e.g. "K. Perera"   (optional, defaults below)
 *   $userRole    = string, e.g. "Farmer"      (optional)
 *   $needsMap    = true|false  -> loads Leaflet
 *   $needsChart  = true|false  -> loads Chart.js
 *
 * NOTE: the signed-in user's name and role are filled in by assets/js/common.js (GET /me),
 * which also redirects logged-out users to the login page. $userName / $userRole are only fallbacks.
 */
$portal    = $portal    ?? 'farmer';
$active    = $active    ?? '';
$pageTitle = $pageTitle ?? 'Govi Paura';
$crumb     = $crumb     ?? '';
$userName  = $userName  ?? '';          // filled in by assets/js/common.js from GET /me
$userRole  = $userRole  ?? ucfirst($portal);
$needsMap  = $needsMap  ?? false;
$needsChart= $needsChart?? false;

$brandMark = ['farmer' => 'F', 'rider' => 'R', 'buyer' => 'B', 'admin' => 'A'][$portal] ?? 'G';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> · Govi Paura</title>
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Bootstrap (grid + utilities only — visual styling comes from base.css) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<?php if ($needsMap): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<?php endif; ?>

<!-- Govi Paura design system -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= htmlspecialchars($portal) ?>.css">

<!-- JS globals injected by PHP so portal scripts can build URLs without hardcoding -->
<script>
  const BASE_URL_JS = '<?= BASE_URL ?>';
</script>
</head>
<body data-portal="<?= htmlspecialchars($portal) ?>">
<div class="gp-shell">

  <?php include __DIR__ . "/sidebar_{$portal}.php"; ?>

  <div class="gp-main">
    <header class="gp-topbar">
      <div style="display:flex;align-items:center;gap:14px;">
        <button class="gp-hamburger btn-outline btn btn-sm" aria-label="Toggle menu"><i class="bi bi-list"></i></button>
        <div>
          <div class="gp-topbar__title"><?= htmlspecialchars($pageTitle) ?></div>
          <?php if ($crumb): ?><div class="gp-topbar__crumb"><?= htmlspecialchars($crumb) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="gp-topbar__right">
        <div class="lang-toggle" role="group" aria-label="Language">
          <a href="#" data-lang-switch="en" class="active">EN</a>
          <a href="#" data-lang-switch="si">සිං</a>
        </div>
        <?php if ($portal !== 'admin'): ?>
        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($portal) ?>/notifications.php" class="gp-icon-btn" aria-label="Notifications" data-i18n-title="notifications">
          <i class="bi bi-bell"></i><span class="dot"></span>
        </a>
        <?php endif; ?>
        <div class="gp-user">
          <div class="gp-user__avatar"><?= htmlspecialchars($brandMark) ?></div>
          <div>
            <div class="gp-user__name"><?= htmlspecialchars($userName) ?></div>
            <div class="gp-user__sub"><?= htmlspecialchars($userRole) ?></div>
          </div>
        </div>
      </div>
    </header>

    <main class="gp-content">
