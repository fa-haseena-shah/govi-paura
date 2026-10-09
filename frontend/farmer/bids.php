<?php
$portal = 'farmer'; $active = 'bids'; $pageTitle = 'Bids Received';
$crumb = 'Review and respond to buyer bids'; $userRole = 'Farmer';
include __DIR__ . '/../includes/header.php';
?>

<div class="field" style="max-width:420px;margin-bottom:18px;">
  <label for="receivedBidsListing">Bidding listing</label>
  <select id="receivedBidsListing"><option value="">Loading your listings…</option></select>
</div>

<div class="gp-card" id="receivedBidsPanel" style="display:none;">
  <div class="section-head">
    <h3 id="receivedBidsListingTitle"></h3>
    <span class="muted" id="receivedBidsHighest"></span>
  </div>
  <div class="table-wrap">
    <table class="gp-table">
      <thead><tr><th>Buyer</th><th>Bid Amount</th><th>Quantity</th><th>Placed</th><th>Status</th><th></th></tr></thead>
      <tbody id="receivedBidsBody"><tr><td colspan="6" class="muted">Select a bidding listing to view bids.</td></tr></tbody>
    </table>
  </div>
</div>
<div id="receivedBidsEmpty" class="gp-empty" style="display:none;"><i class="bi bi-inbox"></i><h4>No bidding listings found</h4><p class="muted">Create an active listing using timed bidding to receive bids here.</p></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
