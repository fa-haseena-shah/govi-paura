<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Something Went Wrong · Govi Paura</title>
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Sinhala:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
</head>
<body style="background:var(--canvas);">
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;">
  <div style="text-align:center;max-width:440px;">
    <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="Govi Paura" width="56" height="56" style="border-radius:14px;margin-bottom:22px;">
    <i class="bi bi-exclamation-triangle" style="font-size:44px;color:var(--rust);"></i>
    <h1 style="margin:14px 0 8px;">Something went wrong on our end</h1>
    <p class="muted" style="margin-bottom:24px;">
      Our team has been notified. Please try again in a moment — your data hasn't been lost.
    </p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
      <a href="javascript:location.reload()" class="btn btn-primary">Try Again</a>
      <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">Back to Login</a>
    </div>
    <p class="muted" style="margin-top:26px;font-size:12.5px;">
      Error persisting? Contact <a href="mailto:support@govipaura.lk">support@govipaura.lk</a>
    </p>
  </div>
</div>
</body>
</html>
