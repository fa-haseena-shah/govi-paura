/**
 * Govi Paura — farmer.js
 * API wiring for the Farmer portal. Loaded by footer.php after api.js, main.js and common.js.
 *
 *   /farmer/dashboard.php  → stats + active listings
 *   /farmer/listings.php   → load, create, edit, close listings
 *   /farmer/orders.php     → orders, status updates, confirm cash payments
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc, money } = gp;

  /* ---------- shared helpers ---------- */
  function listingStatusBadge(listing) {
    if (listing.sellingMethod === 'bidding' && listing.status === 'active') return '<span class="badge badge-warning">Bidding</span>';
    if (listing.status === 'active') return '<span class="badge badge-success">Active</span>';
    if (listing.status === 'sold') return '<span class="badge badge-muted">Sold out</span>';
    if (listing.status === 'closed') return '<span class="badge badge-muted">Closed</span>';
    if (listing.status === 'unavailable') return '<span class="badge badge-danger">Out of stock</span>';
    return `<span class="badge badge-muted">${esc(listing.status)}</span>`;
  }

  function priceLabel(listing) {
    if (listing.sellingMethod === 'fixed_price' && listing.pricePerUnit != null) {
      return `${money(listing.pricePerUnit)} /kg`;
    }
    if (listing.sellingMethod === 'bidding' && listing.startingPrice != null) {
      const countdown = listing.closingTime
        ? `<span class="bid-timer"><i class="bi bi-clock"></i> ${esc(gp.formatDateTime(listing.closingTime))}</span>`
        : '';
      return `from ${money(listing.startingPrice)} ${countdown}`;
    }
    return '—';
  }

  function harvestAgo(harvestDate) {
    if (!harvestDate) return '';
    const diff = Math.floor((Date.now() - new Date(harvestDate).getTime()) / 86400000);
    if (diff <= 0) return 'Harvested today';
    if (diff === 1) return 'Harvested yesterday';
    return `Harvested ${diff} days ago`;
  }

  const isOnSale = (l) => l.status === 'active';
  const isFinished = (l) => ['sold', 'closed'].includes(l.status);

  /* ══════════════ DASHBOARD ══════════════ */
  const dashBody = document.getElementById('dashListingsBody');
  if (dashBody) {
    (async () => {
      try {
        await gp.session;
        const [listingsRes, ordersRes] = await Promise.all([apiFetch('/listings/mine'), apiFetch('/orders')]);
        const listings = listingsRes.data ?? [];
        const orders = ordersRes.data ?? [];

        const now = new Date();
        const thisMonth = orders.filter((o) => {
          const d = new Date(o.createdAt);
          return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth();
        });

        const active = listings.filter(isOnSale);
        document.getElementById('statActiveListings').textContent = active.length;
        document.getElementById('statPendingOrders').textContent = orders.filter((o) => o.status === 'pending').length;
        document.getElementById('statMonthOrders').textContent = thisMonth.length;
        document.getElementById('statEarnings').textContent = money(
          thisMonth.filter((o) => o.paymentStatus === 'paid').reduce((sum, o) => sum + o.totalAmount, 0),
        );

        if (active.length === 0) {
          dashBody.innerHTML = '<tr><td colspan="6" class="muted">You have no active listings yet.</td></tr>';
          return;
        }

        dashBody.innerHTML = active.slice(0, 6).map((l) => `
          <tr>
            <td>${esc(l.cropType)}</td>
            <td>${l.qty} kg</td>
            <td>${l.sellingMethod === 'bidding' ? 'Bidding' : 'Fixed price'}</td>
            <td>${priceLabel(l)}</td>
            <td>${listingStatusBadge(l)}</td>
            <td><a href="${BASE_URL_JS}/farmer/listings.php" class="btn btn-outline btn-sm">Manage</a></td>
          </tr>`).join('');
      } catch (err) {
        dashBody.innerHTML = '<tr><td colspan="6" class="muted">Could not load your data.</td></tr>';
        window.gpToast('Could not load dashboard: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════ LISTINGS ══════════════ */
  const listingsGrid = document.getElementById('listingsGrid');
  if (listingsGrid) {
    const listingsLoading = document.getElementById('listingsLoadingState');
    const listingsNoData = document.getElementById('listingsNoData');
    const filterPills = document.getElementById('listingFilterPills');

    let allListings = [];
    let activeFilter = 'all';

    function renderCard(listing) {
      const isBidding = listing.sellingMethod === 'bidding';

      const actions = isFinished(listing)
        ? '<span class="muted" style="font-size:12.5px;">No further changes</span>'
        : `
          <button class="btn btn-outline btn-sm" data-edit-listing="${listing.id}"><i class="bi bi-pencil"></i> Edit</button>
          <button class="btn btn-danger btn-sm" data-close-listing="${listing.id}"><i class="bi bi-x-circle"></i> Close</button>`;

      const placeholder = `${BASE_URL_JS}/assets/images/crops/produce-placeholder.svg`;
      const div = document.createElement('div');
      div.className = 'listing-card';
      div.innerHTML = `
        <div class="listing-card__photo">
          <img src="${esc(listing.photoUrl || placeholder)}" alt="${esc(listing.cropType)} produce photo" onerror="this.onerror=null;this.src='${placeholder}'">
        </div>
        <div class="listing-card__body">
          <div class="section-head" style="margin-bottom:6px;">
            <h4 style="margin:0;">${esc(listing.cropType)}</h4>
            ${listingStatusBadge(listing)}
          </div>
          <p class="muted" style="margin-bottom:6px;">${listing.qty} kg available · ${harvestAgo(listing.harvestDate)} · ${esc(listing.location)}</p>
          <div class="listing-card__price">${priceLabel(listing)}</div>
          <div style="display:flex;gap:8px;margin-top:12px;">${actions}</div>
        </div>`;
      return div;
    }

    function matchesFilter(l, filter) {
      if (filter === 'active') return l.status === 'active' && l.sellingMethod !== 'bidding';
      if (filter === 'bidding') return l.sellingMethod === 'bidding' && l.status === 'active';
      if (filter === 'sold') return l.status !== 'active';
      return true;
    }

    function updatePillCounts() {
      const set = (value, label) => {
        const pill = filterPills?.querySelector(`[data-value="${value}"]`);
        if (pill) pill.textContent = `${label} (${allListings.filter((l) => matchesFilter(l, value)).length})`;
      };
      set('all', 'All');
      set('active', 'Active');
      set('bidding', 'Bidding Open');
      set('sold', 'Closed / Sold');
    }

    function renderGrid() {
      updatePillCounts();
      const shown = allListings.filter((l) => matchesFilter(l, activeFilter));
      listingsGrid.innerHTML = '';

      if (shown.length === 0) {
        listingsNoData.style.display = '';
        listingsGrid.style.display = 'none';
        return;
      }
      listingsNoData.style.display = 'none';
      listingsGrid.style.display = '';
      shown.forEach((l) => listingsGrid.appendChild(renderCard(l)));
    }

    async function loadListings() {
      listingsLoading.style.display = '';
      listingsGrid.style.display = 'none';
      listingsNoData.style.display = 'none';

      try {
        await gp.session;
        const result = await apiFetch('/listings/mine');
        allListings = result.data ?? [];
      } catch (err) {
        allListings = [];
        window.gpToast('Could not load listings: ' + err.message, 'error');
      }

      listingsLoading.style.display = 'none';
      renderGrid();
    }

    filterPills?.addEventListener('gp:filterchange', (e) => {
      activeFilter = e.detail;
      renderGrid();
    });

    /* ---------- create ---------- */
    const newListingForm = document.getElementById('newListingForm');
    const newListingError = document.getElementById('newListingError');
    const newListingSubmitBtn = document.getElementById('newListingSubmitBtn');

    function syncSellingMethodFields() {
      const method = newListingForm.querySelector('input[name="selling_method"]:checked')?.value;
      document.getElementById('fixedPriceFields').style.display = method === 'fixed_price' ? '' : 'none';
      document.getElementById('biddingFields').style.display = method === 'bidding' ? '' : 'none';
    }

    if (newListingForm) {
      newListingForm.querySelectorAll('input[name="selling_method"]').forEach((radio) => radio.addEventListener('change', syncSellingMethodFields));
      syncSellingMethodFields();

      document.getElementById('listingPhoto')?.addEventListener('change', (e) => {
        const file = e.target.files[0];
        document.getElementById('listingPhotoPreview').textContent = file ? `Selected: ${file.name}` : '';
      });

      newListingForm.addEventListener('submit', async (e) => {
        const invalid = e.defaultPrevented; // main.js (runs first) cancels the submit when a field is invalid
        e.preventDefault();
        if (invalid) return;

        newListingError.style.display = 'none';
        newListingSubmitBtn.disabled = true;
        newListingSubmitBtn.textContent = 'Publishing…';

        const formData = new FormData(newListingForm);
        const method = formData.get('selling_method');

        // send only the fields that belong to the chosen selling method, and drop empty values
        if (method === 'fixed_price') { formData.delete('starting_price'); formData.delete('closing_time'); }
        if (method === 'bidding') { formData.delete('price_per_unit'); }
        for (const [key, value] of [...formData.entries()]) {
          if (value === '' || (value instanceof File && value.size === 0)) formData.delete(key);
        }

        try {
          await apiFetch('/listings', { method: 'POST', body: formData });
          window.gpToast('Listing published!');
          bootstrap.Modal.getInstance(document.getElementById('newListingModal'))?.hide();
          newListingForm.reset();
          document.getElementById('listingPhotoPreview').textContent = '';
          syncSellingMethodFields();
          await loadListings();
        } catch (err) {
          newListingError.textContent = err.message;
          newListingError.style.display = '';
        } finally {
          newListingSubmitBtn.disabled = false;
          newListingSubmitBtn.textContent = 'Publish Listing';
        }
      });
    }

    /* ---------- edit + close (delegated clicks on the cards) ---------- */
    const editModalEl = document.getElementById('editListingModal');
    const editListingForm = document.getElementById('editListingForm');
    const editListingError = document.getElementById('editListingError');
    const editSubmitBtn = document.getElementById('editListingSubmitBtn');
    let editingListing = null;

    listingsGrid.addEventListener('click', (e) => {
      const editBtn = e.target.closest('[data-edit-listing]');
      if (editBtn) {
        editingListing = allListings.find((l) => l.id === parseInt(editBtn.dataset.editListing, 10));
        if (!editingListing) return;

        const isBidding = editingListing.sellingMethod === 'bidding';
        document.getElementById('editListingId').value = editingListing.id;
        document.getElementById('editCropName').value = editingListing.cropType;
        document.getElementById('editQuantity').value = editingListing.qty;
        document.getElementById('editHarvestDate').value = editingListing.harvestDate;
        document.getElementById('editLocation').value = editingListing.location;
        document.getElementById('editPriceLabel').textContent = isBidding ? 'Starting bid (LKR/kg)' : 'Price per kg (LKR)';
        document.getElementById('editPrice').value = (isBidding ? editingListing.startingPrice : editingListing.pricePerUnit) ?? '';
        document.getElementById('editClosingField').style.display = isBidding ? '' : 'none';
        document.getElementById('editClosingTime').value = isBidding ? gp.toLocalInput(editingListing.closingTime) : '';

        editListingError.style.display = 'none';
        bootstrap.Modal.getOrCreateInstance(editModalEl).show();
        return;
      }

      const closeBtn = e.target.closest('[data-close-listing]');
      if (closeBtn) closeListing(parseInt(closeBtn.dataset.closeListing, 10));
    });

    editListingForm?.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!editingListing) return;

      editListingError.style.display = 'none';
      editSubmitBtn.disabled = true;
      editSubmitBtn.textContent = 'Saving…';

      const isBidding = editingListing.sellingMethod === 'bidding';
      const value = (id) => document.getElementById(id).value;
      const payload = {
        crop_type: value('editCropName'),
        qty: parseInt(value('editQuantity'), 10),
        harvest_date: value('editHarvestDate'),
        location: value('editLocation'),
      };
      if (value('editPrice') !== '') payload[isBidding ? 'starting_price' : 'price_per_unit'] = value('editPrice');
      if (isBidding && value('editClosingTime') !== '') payload.closing_time = value('editClosingTime');

      try {
        await apiFetch(`/listings/${editingListing.id}`, { method: 'PUT', body: JSON.stringify(payload) });
        window.gpToast('Listing updated!');
        bootstrap.Modal.getInstance(editModalEl)?.hide();
        await loadListings();
      } catch (err) {
        editListingError.textContent = err.message;
        editListingError.style.display = '';
      } finally {
        editSubmitBtn.disabled = false;
        editSubmitBtn.textContent = 'Save Changes';
      }
    });

    async function closeListing(listingId) {
      if (!confirm('Close this listing? Buyers will no longer see it, and this cannot be undone.')) return;
      try {
        await apiFetch(`/listings/${listingId}/close`, { method: 'POST' });
        window.gpToast('Listing closed.');
        await loadListings();
      } catch (err) {
        window.gpToast(err.message, 'error');
      }
    }

    loadListings();
  }

  /* ══════════════ BIDS RECEIVED ══════════════ */
  const receivedBidsSelect = document.getElementById('receivedBidsListing');
  if (receivedBidsSelect) {
    const bidsPanel = document.getElementById('receivedBidsPanel');
    const bidsBody = document.getElementById('receivedBidsBody');
    const bidsEmpty = document.getElementById('receivedBidsEmpty');
    let biddingListings = [];
    let currentBids = [];
    let currentSummary = null;

    function renderReceivedBids(listing) {
      document.getElementById('receivedBidsListingTitle').textContent = `${listing.cropType} — ${listing.qty} kg`;
      document.getElementById('receivedBidsHighest').textContent = currentSummary.highest_bid == null
        ? 'No bids yet'
        : `Highest bid: ${money(currentSummary.highest_bid)} /kg`;

      if (!currentBids.length) {
        bidsBody.innerHTML = '<tr><td colspan="6" class="muted">No bids have been placed on this listing yet.</td></tr>';
        return;
      }

      bidsBody.innerHTML = currentBids.map((bid) => {
        const pending = bid.status === 'pending';
        const highest = pending && Number(bid.amount) === Number(currentSummary.highest_bid);
        const badge = bid.status === 'accepted'
          ? '<span class="badge badge-success">Accepted</span>'
          : bid.status === 'rejected'
            ? '<span class="badge badge-danger">Rejected</span>'
            : `<span class="badge ${highest ? 'badge-warning' : 'badge-muted'}">${highest ? 'Highest' : 'Pending'}</span>`;
        const actions = pending && currentSummary.is_open
          ? `<button class="btn btn-accent btn-sm" data-bid-action="accept" data-bid-id="${bid.id}">Accept</button>
             <button class="btn btn-danger btn-sm" data-bid-action="reject" data-bid-id="${bid.id}">Reject</button>`
          : '';
        const placed = new Date(bid.created_at).toLocaleString();
        return `<tr>
          <td>${esc(bid.buyer_name ?? `Buyer #${bid.buyer_id}`)}</td>
          <td><strong>${money(bid.amount)} /kg</strong></td>
          <td>${bid.quantity} kg</td>
          <td>${esc(placed)}</td>
          <td>${badge}</td>
          <td style="white-space:nowrap;">${actions}</td>
        </tr>`;
      }).join('');
    }

    async function loadListingBids(listingId) {
      if (!listingId) {
        bidsPanel.style.display = 'none';
        return;
      }
      bidsBody.innerHTML = '<tr><td colspan="6" class="muted">Loading bids…</td></tr>';
      bidsPanel.style.display = '';
      try {
        const [listingResult, summaryResult, bidsResult] = await Promise.all([
          apiFetch(`/listings/${listingId}`),
          apiFetch(`/listings/${listingId}/bid-summary`),
          apiFetch(`/listings/${listingId}/bids?per_page=100`),
        ]);
        currentSummary = summaryResult.data;
        currentBids = bidsResult.data ?? [];
        renderReceivedBids(listingResult.data);
      } catch (err) {
        bidsBody.innerHTML = `<tr><td colspan="6" class="muted">${esc(err.message)}</td></tr>`;
        window.gpToast('Could not load bids: ' + err.message, 'error');
      }
    }

    (async () => {
      try {
        await gp.session;
        const result = await apiFetch('/listings/mine');
        biddingListings = (result.data ?? []).filter((listing) => listing.sellingMethod === 'bidding');
        const requestedId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!biddingListings.length) {
          receivedBidsSelect.innerHTML = '<option value="">No bidding listings</option>';
          receivedBidsSelect.disabled = true;
          bidsEmpty.style.display = '';
          return;
        }

        receivedBidsSelect.innerHTML = biddingListings.map((listing) =>
          `<option value="${listing.id}">${esc(listing.cropType)} · #${listing.id} · ${esc(listing.status)}</option>`,
        ).join('');
        const selected = biddingListings.some((listing) => listing.id === requestedId)
          ? requestedId
          : biddingListings[0].id;
        receivedBidsSelect.value = selected;
        await loadListingBids(selected);
      } catch (err) {
        receivedBidsSelect.innerHTML = '<option value="">Could not load listings</option>';
        bidsBody.innerHTML = `<tr><td colspan="6" class="muted">${esc(err.message)}</td></tr>`;
        bidsPanel.style.display = '';
      }
    })();

    receivedBidsSelect.addEventListener('change', () => loadListingBids(Number(receivedBidsSelect.value)));
    bidsBody.addEventListener('click', async (e) => {
      const button = e.target.closest('[data-bid-action]');
      if (!button) return;
      const bidId = Number(button.dataset.bidId);
      const action = button.dataset.bidAction;
      if (action === 'accept' && !confirm('Accept this bid? This will close bidding on the listing.')) return;

      button.disabled = true;
      try {
        const options = { method: 'POST' };
        if (action === 'reject') {
          const reason = prompt('Optional reason for rejecting this bid:');
          if (reason === null) { button.disabled = false; return; }
          options.body = JSON.stringify({ reason });
        }
        await apiFetch(`/bids/${bidId}/${action}`, options);
        window.gpToast(action === 'accept' ? 'Bid accepted.' : 'Bid rejected.');
        await loadListingBids(Number(receivedBidsSelect.value));
      } catch (err) {
        window.gpToast(err.message, 'error');
        button.disabled = false;
      }
    });
  }

  /* ══════════════ ORDERS ══════════════ */
  const ordersBody = document.getElementById('ordersBody');
  if (ordersBody) {
    const pills = document.getElementById('orderFilterPills');
    let orders = [];
    let filter = 'all';

    // the farmer moves an order one step at a time: pending -> confirmed -> processing -> delivered -> completed
    const NEXT = {
      pending: ['confirmed', 'Confirm order'],
      confirmed: ['processing', 'Start processing'],
      processing: ['delivered', 'Mark delivered'],
      delivered: ['completed', 'Complete order'],
    };

    const groupOf = (status) => (status === 'pending' ? 'pending' : status === 'completed' ? 'completed' : 'progress');

    function render() {
      const shown = orders.filter((o) => filter === 'all' || groupOf(o.status) === filter);

      if (shown.length === 0) {
        ordersBody.innerHTML = '<tr><td colspan="7" class="muted">No orders to show.</td></tr>';
        return;
      }

      ordersBody.innerHTML = shown.map((o) => {
        const next = NEXT[o.status];
        const cashPending = o.paymentMethod === 'cash_on_delivery' && o.transactionStatus === 'pending';
        const actions = [
          next ? `<button class="btn btn-outline btn-sm" data-advance="${o.id}" data-next="${next[0]}">${next[1]}</button>` : '',
          cashPending ? `<button class="btn btn-accent btn-sm" data-cash="${o.id}">Cash received</button>` : '',
        ].join(' ');

        return `
          <tr>
            <td>${gp.orderRef(o.id)}</td>
            <td>
              <strong>${esc(o.buyerBusinessName ?? o.buyerName ?? '—')}</strong>
              ${o.buyerBusinessName && o.buyerName ? `<div class="muted" style="font-size:12.5px;">${esc(o.buyerName)}</div>` : ''}
              ${o.buyerPhone ? `<div style="font-size:12.5px;"><i class="bi bi-telephone"></i> <a href="tel:${esc(o.buyerPhone)}">${esc(o.buyerPhone)}</a></div>` : ''}
            </td>
            <td>${gp.itemsSummary(o.items)}</td>
            <td>${money(o.totalAmount)}</td>
            <td>${gp.paymentBadge(o)}</td>
            <td>${gp.orderStatusBadge(o.status)}</td>
            <td class="no-export" style="white-space:nowrap;">${actions}</td>
          </tr>`;
      }).join('');
    }

    async function loadOrders() {
      try {
        await gp.session;
        orders = (await apiFetch('/orders')).data ?? [];
      } catch (err) {
        orders = [];
        window.gpToast('Could not load orders: ' + err.message, 'error');
      }
      render();
    }

    pills?.addEventListener('gp:filterchange', (e) => { filter = e.detail; render(); });

    ordersBody.addEventListener('click', async (e) => {
      const advance = e.target.closest('[data-advance]');
      const cash = e.target.closest('[data-cash]');

      try {
        if (advance) {
          advance.disabled = true;
          await apiFetch(`/orders/${advance.dataset.advance}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status: advance.dataset.next }),
          });
          window.gpToast('Order updated.');
        } else if (cash) {
          if (!confirm('Confirm that you received the cash payment for this order?')) return;
          cash.disabled = true;
          await apiFetch(`/orders/${cash.dataset.cash}/confirm-cash-collected`, { method: 'POST' });
          window.gpToast('Cash payment confirmed. A receipt was issued.');
        } else {
          return;
        }
        await loadOrders();
      } catch (err) {
        window.gpToast(err.message, 'error');
        await loadOrders();
      }
    });

    loadOrders();
  }
});
