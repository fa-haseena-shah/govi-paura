<?php
$portal = 'farmer'; $active = 'price_trends'; $pageTitle = 'Price Trends';
$crumb = 'See what crops have recently sold for, before you set your price'; $userRole = 'Farmer'; $needsChart = true; $pageScript = 'price_trends';
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-card" style="margin-bottom:20px;">
  <form id="priceFilterForm" class="field-row" style="grid-template-columns:repeat(auto-fit,minmax(150px,1fr));align-items:end;">
    <div class="field" style="margin-bottom:0;">
      <label for="filterCrop">Crop</label>
      <select id="filterCrop"><option value="">All crops</option></select>
    </div>
    <div class="field" style="margin-bottom:0;">
      <label for="filterRegion">Region</label>
      <select id="filterRegion"><option value="">All regions</option></select>
    </div>
    <div class="field" style="margin-bottom:0;">
      <label for="filterFrom">From</label>
      <input type="date" id="filterFrom">
    </div>
    <div class="field" style="margin-bottom:0;">
      <label for="filterTo">To</label>
      <input type="date" id="filterTo">
    </div>
    <div class="field" style="margin-bottom:0;">
      <label for="filterInterval">Group by</label>
      <select id="filterInterval">
        <option value="day">Day</option>
        <option value="week" selected>Week</option>
        <option value="month">Month</option>
      </select>
    </div>
    <div style="display:flex;gap:8px;">
      <button type="submit" class="btn btn-primary btn-sm">Apply</button>
      <button type="button" id="filterReset" class="btn btn-outline btn-sm">Reset</button>
    </div>
  </form>
</div>

<div class="gp-grid cols-4" style="margin-bottom:20px;">
  <div class="gp-stat"><div><div class="gp-stat__label">Average Price / unit</div><div class="gp-stat__value" id="statAvg">—</div></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Lowest – Highest</div><div class="gp-stat__value" id="statRange" style="font-size:20px;">—</div></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Trend</div><div class="gp-stat__value" id="statTrend">—</div></div></div>
  <div class="gp-stat"><div><div class="gp-stat__label">Completed Sales</div><div class="gp-stat__value" id="statCount">—</div></div></div>
</div>

<div class="gp-card" style="margin-bottom:20px;">
  <div class="section-head"><h3 style="margin:0;">Average price over time</h3><span class="muted" id="chartNote"></span></div>
  <div style="position:relative;height:300px;"><canvas id="priceChart" aria-label="Price trend chart" role="img"></canvas></div>
  <p id="chartEmpty" class="muted" hidden>No completed sales match these filters yet.</p>
</div>

<div class="gp-grid cols-2" style="margin-bottom:20px;">
  <div class="gp-card">
    <h3 style="margin-bottom:12px;">By crop</h3>
    <div class="table-wrap"><table class="gp-table"><thead><tr><th>Crop</th><th>Avg price</th><th>Low – High</th><th>Sales</th></tr></thead><tbody id="cropBody"><tr><td colspan="4" class="muted">Loading…</td></tr></tbody></table></div>
  </div>
  <div class="gp-card">
    <h3 style="margin-bottom:12px;">By region</h3>
    <div class="table-wrap"><table class="gp-table"><thead><tr><th>Region</th><th>Avg price</th><th>Low – High</th><th>Sales</th></tr></thead><tbody id="regionBody"><tr><td colspan="4" class="muted">Loading…</td></tr></tbody></table></div>
  </div>
</div>

<div class="gp-card">
  <h3 style="margin-bottom:12px;">Price history</h3>
  <div class="table-wrap">
    <table class="gp-table">
      <thead><tr><th>Date</th><th>Crop</th><th>Region</th><th>Price / unit</th><th>Quantity</th><th>Total</th></tr></thead>
      <tbody id="historyBody"><tr><td colspan="6" class="muted">Loading…</td></tr></tbody>
    </table>
  </div>
  <div id="historyPagination" class="section-head" style="margin-top:16px;" hidden>
    <span id="historyPageLabel" class="muted" aria-live="polite"></span>
    <div style="display:flex;gap:8px;">
      <button id="historyPrev" class="btn btn-outline btn-sm" type="button">Previous</button>
      <button id="historyNext" class="btn btn-outline btn-sm" type="button">Next</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
