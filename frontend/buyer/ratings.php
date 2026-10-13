<?php
$portal = 'buyer'; $active = 'ratings'; $pageTitle = 'Ratings & Reviews';
$crumb = 'Rate delivered orders and view your review history'; $userName = 'S. Fernando'; $userRole = 'Buyer';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-card" style="margin-bottom:20px;">
  <h3 style="margin-bottom:14px;">Rate a Recent Order</h3>
  <div class="section-head" style="margin-bottom:12px;">
    <div>
      <strong>Order #GP-1028</strong>
      <div class="muted">Carrots, 20kg — from K. Perera</div>
    </div>
  </div>
  <form data-validate>
    <div class="field">
      <label>Your rating</label>
      <div class="star-input" style="font-size:26px;">
        <input type="hidden" name="rating" value="0">
        <i class="bi bi-star" style="cursor:pointer;color:var(--gold);"></i>
        <i class="bi bi-star" style="cursor:pointer;color:var(--gold);"></i>
        <i class="bi bi-star" style="cursor:pointer;color:var(--gold);"></i>
        <i class="bi bi-star" style="cursor:pointer;color:var(--gold);"></i>
        <i class="bi bi-star" style="cursor:pointer;color:var(--gold);"></i>
      </div>
    </div>
    <div class="field">
      <label for="reviewText">Your review</label>
      <textarea id="reviewText" name="review" rows="3" placeholder="How was the produce quality and delivery?"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit Review</button>
  </form>
</div>

<div class="gp-card">
  <h3 style="margin-bottom:14px;">Reviews You've Given</h3>
  <div style="padding:12px 0;border-bottom:1px solid var(--line);">
    <div class="section-head" style="margin-bottom:4px;"><strong>K. Perera — Pumpkin, 30kg</strong><span class="stars">★★★★★</span></div>
    <p style="margin:0;">"Great quality, arrived fresh."</p>
  </div>
  <div style="padding:12px 0;">
    <div class="section-head" style="margin-bottom:4px;"><strong>A. Jayasuriya — Tomatoes, 15kg</strong><span class="stars">★★★★★</span></div>
    <p style="margin:0;">"Excellent service and fast delivery!"</p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
