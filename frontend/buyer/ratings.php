<?php
$portal = 'buyer'; $active = 'ratings'; $pageTitle = 'Ratings & Reviews';
$crumb = 'Rate delivered orders and view your review history'; $userRole = 'Buyer';
$pageScript = 'ratings';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-card" style="margin-bottom:20px;">
  <h3 style="margin-bottom:14px;">Rate a Recent Order</h3>

  <div class="field">
    <label for="rateOrderSelect">Select a completed order</label>
    <select id="rateOrderSelect">
      <option value="">Select a completed order…</option>
    </select>
  </div>

  <div id="ratingFormWrap" style="display:none;margin-top:16px;">
    <form id="ratingForm">
      <div class="field">
        <label>Your rating</label>
        <div class="star-input" style="font-size:26px;cursor:pointer;">
          <input type="hidden" name="score" value="0">
          <i class="bi bi-star" style="color:var(--gold);"></i>
          <i class="bi bi-star" style="color:var(--gold);"></i>
          <i class="bi bi-star" style="color:var(--gold);"></i>
          <i class="bi bi-star" style="color:var(--gold);"></i>
          <i class="bi bi-star" style="color:var(--gold);"></i>
        </div>
      </div>
      <div class="field">
        <label for="reviewComment">Your review <span class="muted">(optional)</span></label>
        <textarea id="reviewComment" name="comment" rows="3"
          placeholder="How was the produce quality and delivery?"></textarea>
      </div>
      <div id="ratingSubmitMsg" style="display:none;margin-bottom:10px;" role="alert"></div>
      <button type="submit" class="btn btn-primary">Submit Review</button>
    </form>
  </div>
</div>

<div class="gp-card">
  <h3 style="margin-bottom:14px;">Reviews You've Given</h3>
  <div id="givenList" aria-live="polite"><p class="muted">Loading…</p></div>
  <p id="givenEmpty" class="muted" style="display:none;">You haven't submitted any reviews yet.</p>
  <div id="givenPagination" class="section-head" style="margin-top:16px;" hidden>
    <span id="givenPageLabel" class="muted" aria-live="polite"></span>
    <div style="display:flex;gap:8px;">
      <button id="givenPrevious" class="btn btn-outline btn-sm" type="button">Previous</button>
      <button id="givenNext"     class="btn btn-outline btn-sm" type="button">Next</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
