<?php
$portal = 'farmer'; $active = 'ratings'; $pageTitle = 'Ratings & Reviews';
$crumb = 'What buyers are saying about your produce'; $userRole = 'Farmer'; $pageScript = 'ratings';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid cols-3" style="margin-bottom:20px;">
  <div class="gp-stat">
    <div><div class="gp-stat__label">Overall Rating</div><div class="gp-stat__value"><span id="ratingAverage">—</span> <span id="ratingAverageStars" class="stars" aria-hidden="true"></span></div></div>
  </div>
  <div class="gp-stat"><div><div class="gp-stat__label">Total Reviews</div><div class="gp-stat__value" id="ratingCount">—</div></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">5-star Reviews</div><div class="gp-stat__value" id="fiveStarCount">—</div></div></div>
</div>

<div class="gp-card">
  <h3 style="margin-bottom:16px;">Recent Reviews</h3>
  <div id="ratingsList" aria-live="polite"><p class="muted">Loading reviews...</p></div>
  <div id="ratingsPagination" class="section-head" style="margin-top:16px;" hidden>
    <span id="ratingsPageLabel" class="muted" aria-live="polite"></span>
    <div style="display:flex;gap:8px;">
      <button id="ratingsPrevious" class="btn btn-outline btn-sm" type="button">Previous</button>
      <button id="ratingsNext" class="btn btn-outline btn-sm" type="button">Next</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
