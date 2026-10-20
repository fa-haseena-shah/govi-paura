document.addEventListener('DOMContentLoaded', () => {
  const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[char]));
  const dateLabel = (value) => value ? new Date(value).toLocaleDateString() : '—';
  const badge = (value) => {
    const status = String(value ?? 'unknown');
    const type = ['active', 'success', 'completed', 'delivered', 'approved'].includes(status) ? 'success'
      : ['pending', 'pending_verification', 'processing'].includes(status) ? 'warning'
        : ['suspended', 'rejected', 'failed', 'closed', 'cancelled'].includes(status) ? 'danger' : 'muted';
    return `<span class="badge badge-${type}">${escapeHtml(status.replaceAll('_', ' '))}</span>`;
  };
  const nextOrderStatuses = {
    pending: ['confirmed'], confirmed: ['processing'], processing: ['delivered'], delivered: ['completed'], completed: [],
  };
  const paginate = (target, result, changePage) => {
    target.innerHTML = '';
    const pages = Math.ceil(result.total / result.perPage);
    for (let page = 1; page <= pages; page++) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `btn btn-sm ${page === result.page ? 'btn-accent' : 'btn-outline'}`;
      button.textContent = page;
      button.addEventListener('click', () => changePage(page));
      target.appendChild(button);
    }
  };
  const failed = (err) => window.gpToast(err.message, 'error');

  const usersBody = document.getElementById('adminUsersBody');
  if (usersBody) {
    const search = document.getElementById('adminUserSearch');
    const role = document.getElementById('adminUserRole');
    const status = document.getElementById('adminUserStatus');
    const pagination = document.getElementById('adminUsersPagination');
    let page = 1;
    const load = async () => {
      usersBody.innerHTML = '<tr><td colspan="6" class="muted">Loading users…</td></tr>';
      const params = new URLSearchParams({ page, per_page: 20 });
      if (search.value.trim()) params.set('search', search.value.trim());
      if (role.value) params.set('role', role.value);
      if (status.value) params.set('status', status.value);
      try {
        const result = await apiFetch(`/admin/users?${params}`);
        usersBody.innerHTML = result.data.length ? result.data.map((user) => `<tr>
          <td><strong>${escapeHtml(user.full_name)}</strong><div class="muted">#${user.id}</div></td><td>${escapeHtml(user.role)}</td>
          <td>${escapeHtml(user.email)}<div class="muted">${escapeHtml(user.phone)}</div></td><td>${badge(user.status)}</td><td>${dateLabel(user.created_at)}</td><td>
          ${user.role !== 'admin' && !['pending_verification', 'rejected'].includes(user.status)
            ? `<button class="btn btn-sm ${user.status === 'suspended' ? 'btn-outline' : 'btn-danger'}" data-user-toggle="${user.id}" data-next-status="${user.status === 'suspended' ? 'active' : 'suspended'}">${user.status === 'suspended' ? 'Reactivate' : 'Suspend'}</button>` : '—'}</td></tr>`).join('')
          : '<tr><td colspan="6" class="muted">No user accounts match these filters.</td></tr>';
        paginate(pagination, result, (selected) => { page = selected; load(); });
      } catch (err) { usersBody.innerHTML = '<tr><td colspan="6" class="muted">Could not load user accounts.</td></tr>'; failed(err); }
    };
    [role, status].forEach((field) => field.addEventListener('change', () => { page = 1; load(); }));
    let timer;
    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => { page = 1; load(); }, 250); });
    usersBody.addEventListener('click', async (event) => {
      const button = event.target.closest('[data-user-toggle]');
      if (!button) return;
      const nextStatus = button.dataset.nextStatus;
      if (!confirm(`${nextStatus === 'suspended' ? 'Suspend' : 'Reactivate'} this account?`)) return;
      try {
        await apiFetch(`/admin/users/${button.dataset.userToggle}/status`, { method: 'PATCH', body: JSON.stringify({ status: nextStatus }) });
        window.gpToast(`Account ${nextStatus === 'suspended' ? 'suspended' : 'reactivated'}.`);
        load();
      } catch (err) { failed(err); }
    });
    load();
  }

  const listingsBody = document.getElementById('adminListingsTbody');
  if (listingsBody) {
    const search = document.getElementById('adminListingSearch');
    const status = document.getElementById('adminListingStatus');
    const pagination = document.getElementById('adminListingsPagination');
    let page = 1;
    const load = async () => {
      listingsBody.innerHTML = '<tr><td colspan="7" class="muted">Loading listings…</td></tr>';
      document.getElementById('adminListingsEmpty').style.display = 'none';
      const params = new URLSearchParams({ page, per_page: 20 });
      if (search.value.trim()) params.set('search', search.value.trim());
      if (status.value) params.set('status', status.value);
      try {
        const result = await apiFetch(`/admin/listings?${params}`);
        listingsBody.innerHTML = result.data.length ? result.data.map((listing) => `<tr>
          <td>${escapeHtml(listing.crop_type)}</td><td>${escapeHtml(listing.farmer?.full_name)} <span class="muted">#${listing.farmer_id}</span></td>
          <td>${escapeHtml(listing.qty)} kg</td><td>${escapeHtml(String(listing.selling_method).replaceAll('_', ' '))}</td>
          <td>${listing.price_per_unit != null ? `LKR ${Number(listing.price_per_unit).toLocaleString()}/kg` : listing.starting_price != null ? `from LKR ${Number(listing.starting_price).toLocaleString()}` : '—'}</td>
          <td>${badge(listing.status)}</td><td>${listing.status === 'active' ? `<button class="btn btn-danger btn-sm" data-remove-listing="${listing.id}">Remove</button>` : '—'}</td></tr>`).join('')
          : '<tr><td colspan="7" class="muted">No matching listings.</td></tr>';
        document.getElementById('adminListingsEmpty').style.display = result.data.length ? 'none' : '';
        paginate(pagination, result, (selected) => { page = selected; load(); });
      } catch (err) { listingsBody.innerHTML = '<tr><td colspan="7" class="muted">Could not load listings.</td></tr>'; failed(err); }
    };
    let filterTimer;
    search.addEventListener('input', () => { clearTimeout(filterTimer); filterTimer = setTimeout(() => { page = 1; load(); }, 250); });
    status.addEventListener('change', () => { page = 1; load(); });
    listingsBody.addEventListener('click', async (event) => {
      const button = event.target.closest('[data-remove-listing]');
      if (!button || !confirm('Remove this active listing from the marketplace?')) return;
      try {
        await apiFetch(`/admin/listings/${button.dataset.removeListing}/remove`, { method: 'POST' });
        window.gpToast('Listing removed from the marketplace.');
        load();
      } catch (err) { failed(err); }
    });

    const method = document.getElementById('assistSellingMethod');
    const category = document.getElementById('assistCategory');
    const form = document.getElementById('adminAssistedListingForm');
    const updateFields = () => {
      const biddingOption = method.querySelector('option[value="bidding"]');
      const paddySelected = category.value === 'paddy';
      biddingOption.hidden = !paddySelected;
      biddingOption.disabled = !paddySelected;
      if (!paddySelected && method.value === 'bidding') method.value = 'fixed_price';

      const fixed = method.value === 'fixed_price';
      document.getElementById('assistFixedPriceFields').style.display = fixed ? '' : 'none';
      document.getElementById('assistBidFields').style.display = fixed ? 'none' : '';
      document.getElementById('assistPrice').required = fixed;
      document.getElementById('assistStartingPrice').required = !fixed;
    };
    method.addEventListener('change', updateFields);
    category.addEventListener('change', updateFields);
    updateFields();
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (category.value !== 'paddy' && method.value === 'bidding') {
        method.value = 'fixed_price';
        updateFields();
        window.gpToast('Bidding is only available for paddy listings.', 'error');
        return;
      }
      const fixed = method.value === 'fixed_price';
      const payload = {
        farmer_id: Number(document.getElementById('assistFarmerId').value),
        crop_type: document.getElementById('assistCrop').value.trim(),
        qty: Number(document.getElementById('assistQty').value),
        harvest_date: document.getElementById('assistHarvestDate').value,
        location: document.getElementById('assistLocation').value.trim(),
        selling_method: method.value,
        price_per_unit: fixed ? Number(document.getElementById('assistPrice').value) : null,
        starting_price: fixed ? null : Number(document.getElementById('assistStartingPrice').value),
        closing_time: document.getElementById('assistClosingTime').value || null,
      };
      try {
        await apiFetch('/admin/assisted-listings', { method: 'POST', body: JSON.stringify(payload) });
        window.gpToast('Assisted listing created for the farmer.');
        form.reset();
        updateFields();
        page = 1;
        load();
      } catch (err) { failed(err); }
    });
    load();
  }

  const activityBody = document.getElementById('adminActivityBody');
  if (activityBody) {
    const head = document.getElementById('adminActivityHead');
    const tabs = document.getElementById('adminActivityTabs');
    const pagination = document.getElementById('adminActivityPagination');
    let type = 'bids';
    let page = 1;
    const load = async () => {
      const bids = type === 'bids';
      head.innerHTML = `<tr>${(bids ? ['Produce', 'Buyer', 'Farmer', 'Bid amount', 'Quantity', 'Status', 'Placed'] : ['Order', 'Buyer', 'Farmer', 'Total', 'Payment', 'Placed']).map((name) => `<th>${name}</th>`).join('')}</tr>`;
      activityBody.innerHTML = `<tr><td colspan="7" class="muted">Loading ${type}…</td></tr>`;
      try {
        const result = await apiFetch(`/admin/activity?type=${type}&page=${page}&per_page=20`);
        activityBody.innerHTML = result.data.length ? result.data.map((item) => bids ? `<tr>
          <td>${escapeHtml(item.listing?.crop_type ?? 'Listing')} <span class="muted">#${item.listing_id}</span></td><td>${escapeHtml(item.buyer?.full_name ?? '—')}</td>
          <td>${escapeHtml(item.listing?.farmer?.full_name ?? '—')}</td><td>LKR ${Number(item.bid_amount).toLocaleString()}</td>
          <td>${escapeHtml(item.bid_qty)} kg</td><td>${badge(item.bid_status)}</td><td>${dateLabel(item.created_at)}</td></tr>` : `<tr>
          <td>#${item.id}</td><td>${escapeHtml(item.buyer?.full_name ?? '—')}</td><td>${escapeHtml(item.farmer?.full_name ?? '—')}</td>
          <td>LKR ${Number(item.total_amount).toLocaleString()}</td><td>${badge(item.payment_status)}</td>
          <td>${dateLabel(item.created_at)}</td></tr>`).join('') : `<tr><td colspan="7" class="muted">No ${type} found.</td></tr>`;
        paginate(pagination, result, (selected) => { page = selected; load(); });
      } catch (err) { activityBody.innerHTML = '<tr><td colspan="7" class="muted">Could not load platform activity.</td></tr>'; failed(err); }
    };
    tabs.addEventListener('click', (event) => {
      const button = event.target.closest('[data-value]');
      if (!button || button.dataset.value === type) return;
      type = button.dataset.value;
      page = 1;
      tabs.querySelectorAll('[data-value]').forEach((tab) => tab.classList.toggle('active', tab === button));
      load();
    });
    activityBody.addEventListener('change', async (event) => {
      const select = event.target.closest('[data-order-status]');
      if (!select) return;
      const oldStatus = select.dataset.previousStatus;
      try {
        await apiFetch(`/admin/orders/${select.dataset.orderStatus}/status`, { method: 'PATCH', body: JSON.stringify({ status: select.value }) });
        select.dataset.previousStatus = select.value;
        window.gpToast('Order status updated.');
      } catch (err) { select.value = oldStatus; failed(err); }
    });
    load();
  }

  const subscriptionsBody = document.getElementById('adminSubscriptionsBody');
  if (subscriptionsBody) {
    const search = document.getElementById('adminSubscriptionSearch');
    const subscriptionStatus = document.getElementById('adminSubscriptionStatus');
    const transactionStatus = document.getElementById('adminTransactionStatus');
    const pagination = document.getElementById('adminSubscriptionsPagination');
    let page = 1;
    let searchTimer;
    const setSummary = (summary) => {
      document.getElementById('adminSubscriptionCount').textContent = Number(summary.subscriptions).toLocaleString();
      document.getElementById('adminTransactionCount').textContent = Number(summary.transactions).toLocaleString();
      document.getElementById('adminAttentionCount').textContent = Number(summary.attention_transactions).toLocaleString();
      document.getElementById('adminSubscriptionVolume').textContent = gp.money(summary.successful_volume);
    };
    const load = async () => {
      try {
        const params = new URLSearchParams({ type: 'subscriptions', page, per_page: 20 });
        if (search.value.trim()) params.set('search', search.value.trim());
        if (subscriptionStatus.value) params.set('subscription_status', subscriptionStatus.value);
        if (transactionStatus.value) params.set('transaction_status', transactionStatus.value);
        subscriptionsBody.innerHTML = '<tr><td colspan="8" class="muted">Loading payment records…</td></tr>';
        const result = await apiFetch(`/admin/activity?${params}`);
        setSummary(result.summary);
        subscriptionsBody.innerHTML = result.data.length ? result.data.flatMap((item) => {
          const payments = item.payments?.length ? item.payments : [null];
          return payments.map((payment) => `<tr>
            <td><strong>${escapeHtml(item.rider?.user?.full_name ?? '—')}</strong><div class="muted">${escapeHtml(item.rider?.user?.email ?? '')}</div><div class="muted">${escapeHtml(item.rider?.user?.phone ?? '')}</div></td>
            <td>#${escapeHtml(item.id)}</td>
            <td>${dateLabel(item.start_date)} – ${dateLabel(item.end_date)}</td>
            <td>${badge(item.payment_status)}</td>
            <td>${payment ? `#${escapeHtml(payment.id)}<div class="muted">${escapeHtml(payment.reference)}</div>` : '<span class="muted">Not paid</span>'}</td>
            <td>${payment ? `${escapeHtml(payment.currency)} ${Number(payment.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}<div class="muted">${escapeHtml(payment.method ?? '—')}</div>` : '—'}</td>
            <td>${payment ? badge(payment.status) : '—'}</td>
            <td>${dateLabel(payment?.created_at ?? item.paid_at)}</td>
          </tr>`);
        }).join('') : '<tr><td colspan="8" class="muted">No rider subscription records match these filters.</td></tr>';
        paginate(pagination, result, (selected) => { page = selected; load(); });
      } catch (err) { subscriptionsBody.innerHTML = '<tr><td colspan="8" class="muted">Could not load subscription records.</td></tr>'; failed(err); }
    };
    search.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => { page = 1; load(); }, 300); });
    subscriptionStatus.addEventListener('change', () => { page = 1; load(); });
    transactionStatus.addEventListener('change', () => { page = 1; load(); });
    load();
  }

  const runReport = document.getElementById('adminRunReport');
  if (runReport) {
    const summary = document.getElementById('adminReportSummary');
    const rows = document.getElementById('adminReportRows');
    const exportButton = document.getElementById('adminExportReport');
    let report;
    let chart;
    const reportTitles = { users: 'Registered users', listings: 'Produce listings', bids: 'Bids', orders: 'Orders' };
    const renderSummary = (data, type, from, to) => {
      const total = Number(data.summary.total) || 0;
      const activeDays = data.series.length;
      const range = from && to ? `${from} to ${to}` : from ? `From ${from}` : to ? `Through ${to}` : 'All time';
      const metrics = [`<div class="report-summary__metric"><span class="report-summary__metric-label">Active days</span><strong>${activeDays}</strong></div>`];
      if (activeDays) {
        metrics.push(`<div class="report-summary__metric"><span class="report-summary__metric-label">Average per active day</span><strong>${(total / activeDays).toFixed(1)}</strong></div>`);
      }
      if (data.summary.purchasing_volume != null) {
        metrics.push(`<div class="report-summary__metric"><span class="report-summary__metric-label">Purchasing volume</span><strong>LKR ${Number(data.summary.purchasing_volume).toLocaleString('en-LK', { maximumFractionDigits: 2 })}</strong></div>`);
      }

      const groups = Object.entries(data.summary).filter(([key, value]) => key !== 'total' && key !== 'purchasing_volume' && value && typeof value === 'object');
      const breakdowns = groups.map(([key, values]) => {
        const entries = Object.entries(values);
        const max = Math.max(0, ...entries.map(([, value]) => Number(value) || 0));
        const title = key === 'by_role' ? 'By user role' : key === 'by_status' ? 'By status' : key === 'by_crop' ? 'Leading crops' : key.replaceAll('_', ' ');
        const rowsHtml = entries.length ? entries.map(([label, rawValue]) => {
          const value = Number(rawValue) || 0;
          const width = max ? (value / max) * 100 : 0;
          const share = total ? Math.round((value / total) * 100) : 0;
          return `<div class="report-summary__row"><div class="report-summary__row-head"><strong>${escapeHtml(label.replaceAll('_', ' '))}</strong><span>${value.toLocaleString()} · ${share}%</span></div><div class="report-summary__bar" role="progressbar" aria-label="${escapeHtml(label)}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${share}"><span style="width:${width}%"></span></div></div>`;
        }).join('') : '<p class="muted">No grouped records.</p>';
        return `<section class="report-summary__group"><h3>${escapeHtml(title)}</h3>${rowsHtml}</section>`;
      }).join('');

      summary.innerHTML = `<div class="report-summary__lead"><div><span class="report-summary__eyebrow">${escapeHtml(reportTitles[type] ?? 'Records')}</span><strong class="report-summary__total">${total.toLocaleString()}</strong></div><div class="report-summary__range"><i class="bi bi-calendar3"></i> ${escapeHtml(range)}</div></div><div class="report-summary__metrics">${metrics.join('')}</div>${breakdowns || '<p class="muted">No category breakdown available.</p>'}`;
    };
    const load = async () => {
      const params = new URLSearchParams({ type: document.getElementById('adminReportType').value });
      const from = document.getElementById('adminReportFrom').value;
      const to = document.getElementById('adminReportTo').value;
      if (from) params.set('from', from);
      if (to) params.set('to', to);
      runReport.disabled = true;
      try {
        report = (await apiFetch(`/admin/reports?${params}`)).data;
        renderSummary(report, params.get('type'), from, to);
        rows.innerHTML = report.series.length ? report.series.map((item) => `<tr><td>${escapeHtml(item.date)}</td><td>${escapeHtml(item.count)}</td></tr>`).join('') : '<tr><td colspan="2" class="muted">No records for this date range.</td></tr>';
        if (chart) chart.destroy();
        chart = new Chart(document.getElementById('adminReportChart'), {
          type: 'bar', data: { labels: report.series.map((item) => item.date), datasets: [{ label: 'Records', data: report.series.map((item) => Number(item.count)), backgroundColor: '#6D9773' }] },
          options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
        exportButton.disabled = false;
      } catch (err) { summary.textContent = err.message; failed(err); }
      finally { runReport.disabled = false; }
    };
    runReport.addEventListener('click', load);
    exportButton.addEventListener('click', () => {
      if (!report) return;
      const lines = [['date', 'records'], ...report.series.map((item) => [item.date, item.count])];
      const csv = lines.map((line) => line.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
      const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
      const link = document.createElement('a');
      link.href = url;
      link.download = `${report.type}-report.csv`;
      link.click();
      URL.revokeObjectURL(url);
    });
    load();
  }

  const supportForm = document.getElementById('adminSupportContactForm');
  if (supportForm) {
    const email = document.getElementById('adminSupportEmail');
    const phone = document.getElementById('adminSupportPhone');
    apiFetch('/admin/support-contact').then(({ data }) => { email.value = data.email; phone.value = data.phone; }).catch(failed);
    supportForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      try {
        await apiFetch('/admin/support-contact', { method: 'PUT', body: JSON.stringify({ email: email.value.trim(), phone: phone.value.trim() }) });
        window.gpToast('Support contact details saved.');
      } catch (err) { failed(err); }
    });
  }
});