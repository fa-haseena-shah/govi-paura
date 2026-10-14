/**
 * Govi Paura — price_trends.js
 * Price history + trends page, shared by the farmer and buyer portals.
 *   GET /price-history/filters  -> crop + region dropdowns
 *   GET /price-history          -> paged list of completed-sale prices
 *   GET /price-history/trends   -> summary, chart series, per crop / per region averages
 */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('priceFilterForm');
  if (!form) return;

  const $ = (id) => document.getElementById(id);
  const pageSize = 10;
  let page = 1;
  let total = 0;
  let chart = null;
  let loading = false;

  function currentFilters() {
    const params = new URLSearchParams();
    if ($('filterCrop').value) params.set('crop_type', $('filterCrop').value);
    if ($('filterRegion').value) params.set('region', $('filterRegion').value);
    if ($('filterFrom').value) params.set('from', $('filterFrom').value);
    if ($('filterTo').value) params.set('to', $('filterTo').value);
    return params;
  }

  function fillSelect(select, values) {
    values.forEach((value) => {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = value;
      select.appendChild(option);
    });
  }

  function rangeText(row) {
    return `${Number(row.minPrice).toLocaleString('en-LK')} – ${Number(row.maxPrice).toLocaleString('en-LK')}`;
  }

  function periodLabel(period, interval) {
    if (interval === 'month') {
      const [y, m] = period.split('-');
      return new Date(Number(y), Number(m) - 1, 1).toLocaleDateString('en-LK', { month: 'short', year: 'numeric' });
    }
    const date = new Date(`${period}T00:00:00`);
    const label = date.toLocaleDateString('en-LK', { day: 'numeric', month: 'short' });
    return interval === 'week' ? `Week of ${label}` : label;
  }

  function renderBreakdown(bodyId, rows, emptyText) {
    $(bodyId).innerHTML = rows.length
      ? rows.map((r) => `
          <tr>
            <td>${gp.esc(r.name)}</td>
            <td>${gp.money(r.avgPrice)}</td>
            <td>${rangeText(r)}</td>
            <td>${r.transactions}</td>
          </tr>`).join('')
      : `<tr><td colspan="4" class="muted">${emptyText}</td></tr>`;
  }

  function renderSummary(summary) {
    const hasData = summary.transactions > 0;
    $('statAvg').textContent = hasData ? gp.money(summary.avgPrice) : '—';
    $('statRange').textContent = hasData ? rangeText(summary) : '—';
    $('statCount').textContent = summary.transactions;

    const trend = $('statTrend');
    if (summary.changePct == null) {
      trend.textContent = hasData ? 'Not enough data' : '—';
      trend.style.fontSize = hasData ? '16px' : '';
      trend.style.color = '';
    } else {
      const up = summary.changePct > 0;
      const flat = summary.changePct === 0;
      trend.style.fontSize = '';
      trend.style.color = flat ? '' : (up ? '#b3261e' : '#1b7a3a');
      trend.textContent = `${flat ? '' : (up ? '▲ ' : '▼ ')}${Math.abs(summary.changePct)}%`;
    }
  }

  function renderChart(series, interval) {
    const canvas = $('priceChart');
    const empty = !series.length;
    $('chartEmpty').hidden = !empty;
    canvas.parentElement.hidden = empty;
    $('chartNote').textContent = empty ? '' : 'Weighted by quantity sold · LKR per unit';

    if (chart) { chart.destroy(); chart = null; }
    if (empty || typeof Chart === 'undefined') return;

    chart = new Chart(canvas, {
      type: 'line',
      data: {
        labels: series.map((s) => periodLabel(s.period, interval)),
        datasets: [
          { label: 'Average', data: series.map((s) => s.avgPrice), borderColor: '#1f5a3d', backgroundColor: 'rgba(31,90,61,.12)', fill: true, tension: 0.25, pointRadius: 3 },
          { label: 'Highest', data: series.map((s) => s.maxPrice), borderColor: '#c98a1b', borderDash: [5, 4], pointRadius: 0, tension: 0.25 },
          { label: 'Lowest', data: series.map((s) => s.minPrice), borderColor: '#7a8b99', borderDash: [5, 4], pointRadius: 0, tension: 0.25 },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${gp.money(c.parsed.y)}` } } },
        scales: { y: { beginAtZero: false, title: { display: true, text: 'LKR per unit' } } },
      },
    });
  }

  function renderHistory(rows) {
    $('historyBody').innerHTML = rows.length
      ? rows.map((r) => `
          <tr>
            <td>${gp.esc(gp.formatDateTime(r.recordedAt))}</td>
            <td>${gp.esc(r.cropType)}</td>
            <td>${gp.esc(r.region)}</td>
            <td>${gp.money(r.unitPrice)}</td>
            <td>${r.qty}</td>
            <td>${gp.money(r.totalAmount)}</td>
          </tr>`).join('')
      : '<tr><td colspan="6" class="muted">No completed sales match these filters yet.</td></tr>';

    const pageCount = Math.ceil(total / pageSize);
    $('historyPagination').hidden = total <= pageSize;
    $('historyPageLabel').textContent = `Showing ${(page - 1) * pageSize + 1}-${Math.min(page * pageSize, total)} of ${total}`;
    $('historyPrev').disabled = loading || page <= 1;
    $('historyNext').disabled = loading || page >= pageCount;
  }

  async function loadTrends() {
    const params = currentFilters();
    params.set('interval', $('filterInterval').value);
    try {
      const { data } = await apiFetch(`/price-history/trends?${params}`);
      renderSummary(data.summary);
      renderChart(data.series, data.summary.interval);
      renderBreakdown('cropBody', data.byCrop, 'No data');
      renderBreakdown('regionBody', data.byRegion, 'No data');
    } catch (error) {
      $('chartEmpty').hidden = false;
      $('chartEmpty').textContent = `Could not load price trends: ${error.message}`;
    }
  }

  async function loadHistory() {
    loading = true;
    const params = currentFilters();
    params.set('page', page);
    params.set('per_page', pageSize);
    try {
      const result = await apiFetch(`/price-history?${params}`);
      total = Number(result.total) || 0;
      loading = false;
      renderHistory(result.data ?? []);
    } catch (error) {
      loading = false;
      $('historyBody').innerHTML = `<tr><td colspan="6" class="muted">Could not load price history: ${gp.esc(error.message)}</td></tr>`;
    }
  }

  function applyFilters() {
    page = 1;
    return Promise.all([loadTrends(), loadHistory()]);
  }

  form.addEventListener('submit', (event) => { event.preventDefault(); applyFilters(); });
  $('filterReset').addEventListener('click', () => { form.reset(); applyFilters(); });
  $('filterCrop').addEventListener('change', applyFilters);
  $('filterRegion').addEventListener('change', applyFilters);
  $('historyPrev').addEventListener('click', () => { if (page > 1) { page--; loadHistory(); } });
  $('historyNext').addEventListener('click', () => { page++; loadHistory(); });

  (async () => {
    try {
      await gp.session;
      const { data } = await apiFetch('/price-history/filters');
      fillSelect($('filterCrop'), data.crops ?? []);
      fillSelect($('filterRegion'), data.regions ?? []);
    } catch (error) {
      // the dropdowns just stay on "All"; the data below still loads
    }
    applyFilters();
  })();
});
