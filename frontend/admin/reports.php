<?php
$portal = 'admin'; $active = 'reports'; $pageTitle = 'Reports & Analytics';
$crumb = 'Explore account, listing, bidding and order trends'; $userRole = 'Admin';
$pageScript = 'admin-management';
$needsChart = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="gp-toolbar" style="gap:12px;flex-wrap:wrap;">
  <select id="adminReportType" aria-label="Report type"><option value="users">Registered users</option><option value="listings">Produce listing trends</option><option value="bids">Bidding history</option><option value="orders">Buyer orders</option></select>
  <label class="field" style="margin:0;">From <input id="adminReportFrom" type="date"></label>
  <label class="field" style="margin:0;">To <input id="adminReportTo" type="date"></label>
  <button class="btn btn-primary" id="adminRunReport" type="button"><i class="bi bi-bar-chart-line"></i> Generate</button>
  <button class="btn btn-outline" id="adminExportReport" type="button" disabled aria-label="Export report as CSV"><i class="bi bi-download"></i> Export CSV</button>
</div>
<div class="gp-grid cols-2" style="align-items:start;">
  <section class="gp-card" aria-labelledby="reportTrendTitle"><h2 id="reportTrendTitle">Activity over time</h2><canvas id="adminReportChart" aria-label="Report activity chart" role="img"></canvas></section>
  <section class="gp-card" aria-labelledby="reportSummaryTitle"><h2 id="reportSummaryTitle">Summary</h2><div id="adminReportSummary" class="report-summary" aria-live="polite"><p class="muted">Choose a report to begin.</p></div></section>
</div>
<div class="gp-card" style="margin-top:20px;">
  <h2>Daily activity</h2>
  <div class="table-wrap"><table class="gp-table"><thead><tr><th>Date</th><th>Records</th></tr></thead><tbody id="adminReportRows"><tr><td colspan="2" class="muted">No report generated.</td></tr></tbody></table></div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>