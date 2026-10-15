/**
 * receipt.js — Fetches and renders an order receipt.
 *
 * Expects:  window._receiptOrderId  (integer, injected by the PHP page)
 * Requires: api.js (apiFetch, API_BASE_URL), common.js (gp.esc, gp.money, gp.formatDateTime)
 */
document.addEventListener('DOMContentLoaded', async () => {
    const orderId = window._receiptOrderId;
    if (!orderId) return;

    const loading = document.getElementById('receiptLoading');
    const error   = document.getElementById('receiptError');
    const wrap    = document.getElementById('receiptWrap');

    function showError(msg) {
        loading.style.display = 'none';
        wrap.style.display    = 'none';
        error.textContent     = msg;
        error.style.display   = '';
    }

    let data;
    try {
        const res = await apiFetch(`/orders/${orderId}/receipt`);
        data = res.data;
    } catch (err) {
        showError(err.message || 'Could not load the receipt.');
        return;
    }

    loading.style.display = 'none';

    // ── header ──────────────────────────────────────────────────────────────
    document.getElementById('rcptNumber').textContent   = data.receipt_number;
    document.getElementById('rcptIssuedAt').textContent = gp.formatDateTime(data.issued_at);
    document.getElementById('rcptReference').textContent = data.reference;

    // ── method label ────────────────────────────────────────────────────────
    const methodLabel = { card: 'Card', cash_on_delivery: 'Cash on Delivery' };
    document.getElementById('rcptMethod').textContent = methodLabel[data.payment_method] ?? data.payment_method;

    // ── parties ─────────────────────────────────────────────────────────────
    document.getElementById('rcptBuyer').textContent  = data.buyer_business
        ? `${data.buyer_name} (${data.buyer_business})`
        : (data.buyer_name ?? '—');
    document.getElementById('rcptFarmer').textContent = data.farmer_name ?? '—';
    document.getElementById('rcptOrderId').textContent = `#${data.order_id}`;
    document.getElementById('rcptOrderDate').textContent = gp.formatDateTime(data.order_date);

    // ── line items ───────────────────────────────────────────────────────────
    const tbody = document.getElementById('rcptItemsBody');
    if (data.items && data.items.length) {
        tbody.innerHTML = data.items.map((item) => `
            <tr>
                <td>${gp.esc(item.product)}</td>
                <td style="text-align:right;">${gp.esc(String(item.qty))} kg</td>
                <td style="text-align:right;">${gp.money(item.unit_price)}</td>
                <td style="text-align:right;">${gp.money(item.sub_total)}</td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="4" class="muted">No line items.</td></tr>';
    }

    // ── total ────────────────────────────────────────────────────────────────
    document.getElementById('rcptTotal').textContent = `${data.currency ?? 'LKR'} ${Number(data.amount).toLocaleString('en-LK', { minimumFractionDigits: 2 })}`;

    wrap.style.display = '';

    // ── print ────────────────────────────────────────────────────────────────
    const printBtn = document.getElementById('rcptPrint');
    if (printBtn) {
        printBtn.addEventListener('click', () => window.print());
    }
});

