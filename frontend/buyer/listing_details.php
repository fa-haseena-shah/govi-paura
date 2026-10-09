<?php
$portal = 'buyer'; $active = 'browse_listings'; $pageTitle = 'Listing Details';
$crumb = 'Browse Produce'; $userRole = 'Buyer';
$listingId = (int) ($_GET['id'] ?? 0);
include __DIR__ . '/../includes/header.php';
?>
<script>window._listingId = <?= $listingId ?>;</script>

<div id="listingLoadingState" class="gp-empty">
  <i class="bi bi-hourglass-split"></i>
  <h4>Loading listing…</h4>
</div>

<div id="listingNotFound" class="gp-empty" style="display:none;">
  <i class="bi bi-emoji-frown"></i>
  <h4>Listing not found</h4>
  <p class="muted">It may have been removed. <a href="<?= BASE_URL ?>/buyer/browse_listings.php">Back to browse</a></p>
</div>

<div id="listingDetail" class="gp-grid cols-2" style="align-items:start;display:none;">
  <div>
    <div class="listing-card__photo" style="height:340px;border-radius:var(--radius);font-size:44px;">
      <img id="listingPhoto" src="<?= BASE_URL ?>/assets/images/crops/produce-placeholder.svg" alt="Produce photo">
    </div>
  </div>

  <div>
    <div class="section-head">
      <h1 style="margin:0;" id="listingCropType"></h1>
      <span class="badge" id="listingBadge"></span>
    </div>
    <p class="muted" id="listingMeta"></p>
    <div style="font-size:12px;" id="harvestDate"></div>

    <div class="gp-card" style="padding:16px;margin:16px 0;">
      <div style="display:flex;align-items:center;gap:10px;">
        <div class="gp-user__avatar" id="farmerInitials"></div>
        <div>
          <div class="gp-user__name" id="farmerName"></div>
          <div class="muted" style="font-size:12px;" id="farmerLocation"></div>
        </div>
      </div>
    </div>

    <div id="listingInactive" style="display:none;" class="gp-card">
      <p style="margin:0;">This listing is no longer available for purchase.</p>
    </div>

    <div id="fixedPriceSection" style="display:none;">
      <h3 style="font-size:18px;">Price</h3>
      <div class="produce-card__price" style="font-size:26px;margin-bottom:14px;" id="listingPriceFixed"></div>

      <div class="field" style="max-width:200px;">
        <label for="orderQty">Quantity (kg)</label>
        <input type="number" id="orderQty" min="1" value="1">
      </div>
      <div class="muted" style="font-size:12.5px;margin:-6px 0 12px;" id="orderQtyTotal"></div>

      <div style="display:flex;gap:10px;margin-bottom:12px;">
        <button class="btn btn-gold" style="flex:1;" id="buyNowBtn">Buy now</button>
        <button class="btn btn-outline" id="addToCartBtn"><i class="bi bi-cart-plus"></i> Add to cart</button>
      </div>
    </div>

    <div id="biddingSection" style="display:none;">
      <h3 style="font-size:18px;">Starting Price</h3>
      <div class="produce-card__price" style="font-size:26px;margin-bottom:6px;" id="listingPriceBid"></div>
      <p class="muted">Closes <span id="bidClosingAt"></span></p>
      <a class="btn btn-gold" style="width:100%;" id="placeBidLink">Place a bid</a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
