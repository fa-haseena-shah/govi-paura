<?php
$portal = 'admin'; $active = 'manage_listings'; $pageTitle = 'Listing Moderation';
$crumb = 'Review harvest listings and assist farmers'; $userRole = 'Admin';
$pageScript = 'admin-management';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar" style="gap:12px;flex-wrap:wrap;">
  <div class="field" style="margin:0;flex:1;min-width:220px;"><label class="visually-hidden" for="adminListingSearch">Search listings</label><input id="adminListingSearch" type="search" placeholder="Search produce"></div>
  <select id="adminListingStatus" aria-label="Filter listings"><option value="">All listings</option><option value="active">Active</option><option value="sold">Sold</option><option value="closed">Removed / closed</option><option value="unavailable">Unavailable</option></select>
</div>
<div class="gp-card" style="margin-bottom:22px;">
  <div class="table-wrap"><table class="gp-table">
    <thead><tr><th>Produce</th><th>Farmer</th><th>Quantity</th><th>Sale type</th><th>Price</th><th>Status</th><th></th></tr></thead>
    <tbody id="adminListingsTbody"><tr><td colspan="7" id="adminListingsLoading" class="muted">Loading listings…</td></tr></tbody>
  </table></div>
  <p id="adminListingsEmpty" class="muted" style="display:none;margin:16px 0 0;">No listings match these filters.</p>
  <div id="adminListingsPagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;"></div>
</div>

<section class="gp-card" aria-labelledby="assistedListingTitle">
  <h2 id="assistedListingTitle">Assist a farmer with a listing</h2>
  <p>Enter listing details on behalf of an active farmer who cannot connect. The listing remains in the farmer’s account.</p>
  <form id="adminAssistedListingForm">
    <div class="field-row">
      <div class="field"><label for="assistFarmerId">Farmer account ID</label><input id="assistFarmerId" type="number" min="1" required></div>
      <div class="field"><label for="assistCrop">Produce</label><input id="assistCrop" maxlength="100" required></div>
    </div>
    <div class="field"><label for="assistCategory">Produce category</label><select id="assistCategory" required><option value="" selected disabled>Select a category</option><option value="paddy">Paddy</option><option value="vegetables">Vegetables</option></select></div>
    <div class="field-row">
      <div class="field"><label for="assistQty">Quantity (kg)</label><input id="assistQty" type="number" min="1" required></div>
      <div class="field"><label for="assistHarvestDate">Harvest date</label><input id="assistHarvestDate" type="date" required></div>
    </div>
    <div class="field-row">
      <div class="field"><label for="assistLocation">Location</label><input id="assistLocation" maxlength="255" required></div>
      <div class="field"><label for="assistSellingMethod">Sale method</label><select id="assistSellingMethod"><option value="fixed_price">Fixed price</option><option value="bidding">Bidding</option></select></div>
    </div>
    <div class="field-row" id="assistFixedPriceFields">
      <div class="field"><label for="assistPrice">Price per kg (LKR)</label><input id="assistPrice" type="number" min="0.01" step="0.01"></div><div></div>
    </div>
    <div class="field-row" id="assistBidFields" style="display:none;">
      <div class="field"><label for="assistStartingPrice">Starting price (LKR)</label><input id="assistStartingPrice" type="number" min="0.01" step="0.01"></div>
      <div class="field"><label for="assistClosingTime">Bidding closes</label><input id="assistClosingTime" type="datetime-local"></div>
    </div>
    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Create listing</button>
  </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>