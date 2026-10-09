<?php
$portal = 'buyer'; $active = 'my_bids'; $pageTitle = 'My Bids';
$crumb = 'Track your active and past bids'; $userRole = 'Buyer';

include __DIR__ . '/../includes/header.php';
?>

<div class="filter-pills" style="margin-bottom:18px;">
  <span class="pill active" data-value="all">All</span>
  <span class="pill" data-value="winning">Winning</span>
  <span class="pill" data-value="outbid">Outbid</span>
  <span class="pill" data-value="closed">Closed</span>
</div>

<div class="gp-card">
  <div class="table-wrap">
    <table class="gp-table">
      <thead>
        <tr>
          <th>Listing</th>
          <th>Farmer</th>
          <th>Your Bid</th>
          <th>Qty</th>
          <th>Bid Total</th>
          <th>Highest Bid</th>
          <th>Closes</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="myBidsBody"><tr><td colspan="9" class="muted">Loading your bids…</td></tr></tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
