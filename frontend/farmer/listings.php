<?php
$portal = 'farmer'; $active = 'listings'; $pageTitle = 'My Listings';
$crumb = 'Create and manage your harvest listings';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar">
  <div class="gp-search"><i class="bi bi-search"></i><input type="text" data-gp-search="#listingsGrid .listing-card" data-gp-empty="#listingsEmpty" placeholder="Search your listings..."></div>
  <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#newListingModal"><i class="bi bi-plus-lg"></i> New Listing</button>
</div>

<div id="listingsEmpty" class="gp-empty" style="display:none;">
  <i class="bi bi-search"></i>
  <h4>No listings match your search</h4>
  <p class="muted">Try a different crop name, or <a href="#" data-bs-toggle="modal" data-bs-target="#newListingModal">create a new listing</a>.</p>
</div>

<div class="filter-pills" id="listingFilterPills" style="margin-bottom:18px;">
  <span class="pill active" data-value="all">All</span>
  <span class="pill" data-value="active">Active</span>
  <span class="pill" data-value="bidding">Bidding Open</span>
  <span class="pill" data-value="sold">Closed / Sold</span>
</div>

<div id="listingsLoadingState" class="gp-empty">
  <i class="bi bi-hourglass-split"></i>
  <h4>Loading your listings…</h4>
</div>

<div id="listingsNoData" class="gp-empty" style="display:none;">
  <i class="bi bi-basket"></i>
  <h4>Nothing to show here</h4>
  <p class="muted">Create a listing to start selling, or pick a different filter.</p>
</div>

<div class="gp-grid cols-3" id="listingsGrid" style="display:none;"></div>

<!-- New Listing Modal -->
<div class="modal fade" id="newListingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none;">
      <div class="modal-header" style="border-bottom:1px solid var(--line);">
        <h3 style="margin:0;">Create a New Listing</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <form id="newListingForm" data-validate enctype="multipart/form-data">
          <div class="field-row">
            <div class="field">
              <label for="cropType">Crop name</label>
              <input type="text" id="cropType" name="crop_type" placeholder="e.g. Carrots" required>
            </div>
            <div class="field">
              <label for="qty">Quantity (kg)</label>
              <input type="number" id="qty" name="qty" min="1" placeholder="e.g. 40" required>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="harvestDate">Harvest date</label>
              <input type="date" id="harvestDate" name="harvest_date" required>
            </div>
            <div class="field">
              <label for="location">Region / farm location</label>
              <input type="text" id="location" name="location" placeholder="e.g. Kottawa" required>
            </div>
          </div>

          <div class="field">
            <label>Selling method</label>
            <div class="radio-card-group">
              <label class="radio-card selected">
                <input type="radio" name="selling_method" value="fixed_price" checked> <strong>Fixed price</strong>
                <p class="muted" style="margin:4px 0 0 26px;">Buyers purchase instantly at your set price.</p>
              </label>
              <label class="radio-card">
                <input type="radio" name="selling_method" value="bidding"> <strong>Bidding</strong>
                <p class="muted" style="margin:4px 0 0 26px;">Buyers bid until a closing time you set.</p>
              </label>
            </div>
          </div>

          <div id="fixedPriceFields" class="field">
            <label for="pricePerUnit">Price per kg (LKR)</label>
            <input type="number" id="pricePerUnit" name="price_per_unit" min="1" placeholder="e.g. 320">
          </div>
          <div id="biddingFields" style="display:none;">
            <div class="field-row">
              <div class="field">
                <label for="startingPrice">Starting bid (LKR/kg)</label>
                <input type="number" id="startingPrice" name="starting_price" min="1" placeholder="e.g. 700">
              </div>
              <div class="field">
                <label for="closingTime">Bidding closes at</label>
                <input type="datetime-local" id="closingTime" name="closing_time">
              </div>
            </div>
          </div>

          <div class="field">
            <label for="listingPhoto">Photos</label>
            <label class="upload-box" for="listingPhoto">
              <i class="bi bi-camera"></i> Click to upload a photo (JPG or PNG, max 5 MB)
            </label>
            <input type="file" id="listingPhoto" name="photo" accept="image/jpeg,image/png" style="display:none;">
            <div id="listingPhotoPreview" style="margin-top:10px;"></div>
          </div>

          <p id="newListingError" class="error-text" style="display:none;color:#c0392b;font-size:13px;margin:12px 0 0;"></p>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-accent" id="newListingSubmitBtn">Publish Listing</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Edit Listing Modal -->
<div class="modal fade" id="editListingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:var(--radius-lg);border:none;">
      <div class="modal-header" style="border-bottom:1px solid var(--line);">
        <h3 style="margin:0;">Edit Listing</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <form id="editListingForm">
          <input type="hidden" id="editListingId">
          <div class="field-row">
            <div class="field">
              <label for="editCropName">Crop name</label>
              <input type="text" id="editCropName" required>
            </div>
            <div class="field">
              <label for="editQuantity">Quantity (kg)</label>
              <input type="number" id="editQuantity" min="0" required>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="editHarvestDate">Harvest date</label>
              <input type="date" id="editHarvestDate" required>
            </div>
            <div class="field">
              <label for="editLocation">Region / farm location</label>
              <input type="text" id="editLocation" required>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label for="editPrice" id="editPriceLabel">Price per kg (LKR)</label>
              <input type="number" id="editPrice" min="1" step="0.01">
            </div>
            <div class="field" id="editClosingField" style="display:none;">
              <label for="editClosingTime">Bidding closes at</label>
              <input type="datetime-local" id="editClosingTime">
            </div>
          </div>
          <p class="muted" style="font-size:12.5px;margin:0;">Setting quantity to 0 marks the listing as unavailable. Setting it above 0 again puts it back on sale.</p>

          <p id="editListingError" class="error-text" style="display:none;color:#c0392b;font-size:13px;margin:12px 0 0;"></p>

          <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-accent" id="editListingSubmitBtn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
