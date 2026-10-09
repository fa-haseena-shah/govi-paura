<?php
$portal = 'buyer'; $active = 'browse_listings'; $pageTitle = 'Place A Bid';
$crumb = 'Place your bid, stand a chance to win'; $userRole = 'Buyer';
$listingId = (int) ($_GET['id'] ?? 0);
include __DIR__ . '/../includes/header.php';
?>
<script>window._bidListingId = <?= $listingId ?>;</script>

<div class="gp-grid cols-2" style="align-items:start;">
  <div class="gp-card" id="bidListingCard" style="display:none;">
    <div class="listing-card__photo" style="height:180px;border-radius:var(--radius);margin-bottom:14px;"><img src="<?= BASE_URL ?>/assets/images/crops/carrots.svg" alt="Carrots produce photo"></div>
    <h3 id="bidListingName" style="margin-bottom:4px;"></h3>
    <p class="muted" id="bidListingMeta"></p>
    <div class="badge badge-warning" id="bidClosingBadge" style="margin-bottom:14px;display:none;">Closes <span id="bidClosingAt"></span></div>
    <hr class="divider">
    <div class="section-head"><span class="muted">Current highest bid</span><strong id="highestBid">—</strong></div>
    <div class="section-head"><span class="muted">Total bids placed</span><strong id="totalBids">—</strong></div>
    <div class="section-head"><span class="muted">Minimum next bid</span><strong id="minimumNextBid">—</strong></div>
  </div>

  <div class="gp-card" id="placeBidCard" style="display:none;">
    <h3 style="margin-bottom:14px;">Your Bid</h3>
    <form id="placeBidForm" data-validate>
      <div class="field">
        <label for="bidAmount">Bid amount (LKR per kg)</label>
        <input type="number" id="bidAmount" name="amount" step="0.01" required>
        <div class="hint" id="bidHint"></div>
      </div>
      <div class="field">
        <label for="bidQty">Quantity you want (kg)</label>
        <input type="number" id="bidQty" name="quantity" min="1" step="1" required>
      </div>
      <div class="gp-card" style="background:var(--sage-light);border:none;padding:14px;margin-bottom:16px;">
        <p class="muted" style="margin:0;font-size:12.5px;"><i class="bi bi-info-circle"></i> If you're outbid, you'll get a notification and can raise your bid before the closing time.</p>
      </div>
      <p id="placeBidError" class="field-error" style="display:none;"></p>
      <button type="submit" id="placeBidSubmit" class="btn btn-gold btn-block">Submit Bid</button>
    </form>
  </div>
</div>

<div id="placeBidLoading" class="gp-empty"><i class="bi bi-hourglass-split"></i><h4>Loading listing…</h4></div>
<div id="placeBidUnavailable" class="gp-empty" style="display:none;"><i class="bi bi-exclamation-circle"></i><h4 id="placeBidUnavailableText">This listing is unavailable for bidding.</h4></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
