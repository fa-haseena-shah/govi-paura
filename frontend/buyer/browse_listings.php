<?php
$portal = 'buyer'; $active = 'browse_listings'; $pageTitle = 'Browse Produce';
$crumb = 'Discover fresh listings from verified farmers'; $userRole = 'Buyer';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-grid" style="grid-template-columns: 260px 1fr; align-items:start; gap:22px;">
  <aside class="market-filters">
    <h4 style="margin-bottom:12px;">Filters</h4>
    <div class="field">
      <label for="cropFilter">Crop</label>
      <input type="text" id="cropFilter" list="cropOptions" placeholder="All crops" autocomplete="off">
      <datalist id="cropOptions"></datalist>
    </div>
    <div class="field">
      <label for="regionFilter">Region</label>
      <input type="text" id="regionFilter" list="regionOptions" placeholder="All regions" autocomplete="off">
      <datalist id="regionOptions"></datalist>
    </div>
    <div class="field">
      <label>Selling method</label>
      <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-bottom:6px;"><input type="checkbox" id="filterFixed" checked style="width:auto;"> Fixed price</label>
      <label style="display:flex;align-items:center;gap:8px;font-weight:400;"><input type="checkbox" id="filterBidding" checked style="width:auto;"> Bidding open</label>
    </div>
    <div class="field">
      <label for="priceMax">Max price: <span id="priceMaxLabel">Any</span></label>
      <input type="range" id="priceMax" min="50" max="1000" step="10" value="1000">
      <div class="muted" style="font-size:12px;margin-top:4px;">Applies to fixed-price listings. Slide fully right for any price.</div>
    </div>
    <button class="btn btn-primary btn-block" id="applyFiltersBtn">Apply Filters</button>
  </aside>

  <div>
    <div class="gp-toolbar">
      <div class="gp-search"><i class="bi bi-search"></i><input type="text" id="browseSearch" placeholder="Search crops or locations..."></div>
    </div>

    <div id="browseLoadingState" class="gp-empty">
      <i class="bi bi-hourglass-split"></i>
      <h4>Loading fresh produce…</h4>
    </div>

    <div class="market-grid" id="marketGrid" style="display:none;"></div>

    <div id="marketEmpty" class="gp-empty" style="display:none;">
      <i class="bi bi-emoji-frown"></i>
      <h4>No produce matches your search</h4>
      <p class="muted">Try a different crop or location, or clear your filters.</p>
    </div>

    <div id="browsePagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:20px;"></div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
