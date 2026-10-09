/**
 * Govi Paura — buyer.js
 * API wiring for the Buyer portal. Loaded by footer.php after api.js, main.js and common.js.
 *
 *   /buyer/dashboard.php       → stats, fresh listings, active orders
 *   /buyer/browse_listings.php → browse, filter, search, paginate
 *   /buyer/listing_details.php → one listing, add to cart / buy now
 *   /buyer/cart_checkout.php   → cart (kept in localStorage), checkout, payment
 *   /buyer/orders.php          → order history, pay for unpaid orders
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc, money } = gp;
  const PLACEHOLDER = `${BASE_URL_JS}/assets/images/crops/produce-placeholder.svg`;

  /* ---------- the cart lives in the browser until the order is placed ---------- */
  const CART_KEY = 'gp_cart';
  const cart = {
    get() {
      try {
        const items = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
        return Array.isArray(items) ? items : [];
      } catch (err) { return []; }
    },
    save(items) { localStorage.setItem(CART_KEY, JSON.stringify(items)); },
    quantityOf(listingId) { return this.get().find((i) => i.listingId === listingId)?.quantity ?? 0; },
    add(listingId, quantity) {
      const items = this.get();
      const found = items.find((i) => i.listingId === listingId);
      if (found) found.quantity += quantity; else items.push({ listingId, quantity });
      this.save(items);
    },
    setQuantity(listingId, quantity) {
      this.save(this.get().map((i) => (i.listingId === listingId ? { ...i, quantity } : i)));
    },
    remove(listingIds) {
      const ids = [].concat(listingIds);
      this.save(this.get().filter((i) => !ids.includes(i.listingId)));
    },
  };

  /* ---------- shared rendering ---------- */
  function priceLabel(listing) {
    if (listing.sellingMethod === 'fixed_price' && listing.pricePerUnit != null) return `${money(listing.pricePerUnit)} /kg`;
    if (listing.sellingMethod === 'bidding' && listing.startingPrice != null) return `from ${money(listing.startingPrice)} /kg`;
    return '—';
  }

  function produceCard(listing) {
    const badge = listing.sellingMethod === 'bidding'
      ? '<span class="badge badge-warning produce-card__badge">Bidding</span>'
      : '<span class="badge badge-success produce-card__badge">Fixed price</span>';

    const a = document.createElement('a');
    a.href = `${BASE_URL_JS}/buyer/listing_details.php?id=${listing.id}`;
    a.className = 'produce-card';
    a.style.cssText = 'text-decoration:none;color:inherit;';
    a.innerHTML = `
      <div class="produce-card__photo">
        ${badge}
        <img src="${esc(listing.photoUrl || PLACEHOLDER)}" alt="${esc(listing.cropType)} produce photo" onerror="this.onerror=null;this.src='${PLACEHOLDER}'">
      </div>
      <div class="produce-card__body">
        <h4 style="margin-bottom:2px;">${esc(listing.cropType)}</h4>
        <div class="produce-card__price">${priceLabel(listing)}</div>
        <div class="produce-card__farmer"><i class="bi bi-calendar3"></i>${esc(listing.harvestDate ?? 'N/A')}</div>
        <div class="muted" style="font-size:12px;margin-top:4px;"<span class="muted"></span><i class="bi bi-geo-alt"></i>  ${esc(listing.location)}</div>
        <div class="muted" style="font-size:12px;margin-top:4px;">${listing.qty} kg available</div>
      </div>`;
    return a;
  }

  /* ══════════════ DASHBOARD ══════════════ */
  const freshGrid = document.getElementById('freshGrid');
  if (freshGrid) {
    (async () => {
      const ordersBody = document.getElementById('dashOrdersBody');
      try {
        await gp.session;
        const [freshRes, ordersRes] = await Promise.all([apiFetch('/listings?per_page=3'), apiFetch('/orders')]);
        const fresh = freshRes.data ?? [];
        const orders = ordersRes.data ?? [];

        freshGrid.innerHTML = fresh.length ? '' : '<p class="muted">No listings on the market yet.</p>';
        fresh.forEach((l) => freshGrid.appendChild(produceCard(l)));

        const active = orders.filter((o) => o.status !== 'completed');
        document.getElementById('statActiveOrders').textContent = active.length;
        document.getElementById('statUnpaidOrders').textContent = orders.filter((o) => o.paymentStatus !== 'paid' && !o.paymentMethod).length;
        document.getElementById('statCompletedOrders').textContent = orders.length - active.length;

        ordersBody.innerHTML = active.length
          ? active.slice(0, 5).map((o) => `
              <tr>
                <td>${gp.orderRef(o.id)}</td>
                <td>${gp.itemsSummary(o.items)}</td>
                <td>${gp.orderStatusBadge(o.status)}</td>
                <td><a href="${BASE_URL_JS}/buyer/orders.php" class="btn btn-outline btn-sm">Track</a></td>
              </tr>`).join('')
          : '<tr><td colspan="5" class="muted">You have no active orders.</td></tr>';
      } catch (err) {
        freshGrid.innerHTML = '<p class="muted">Could not load listings.</p>';
        ordersBody.innerHTML = '<tr><td colspan="5" class="muted">Could not load orders.</td></tr>';
        window.gpToast('Could not load dashboard: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════ BROWSE ══════════════ */
  const marketGrid = document.getElementById('marketGrid');
  if (marketGrid) {
    const marketEmpty = document.getElementById('marketEmpty');
    const browseLoading = document.getElementById('browseLoadingState');
    const paginationWrap = document.getElementById('browsePagination');
    const priceRange = document.getElementById('priceMax');
    const priceLabelEl = document.getElementById('priceMaxLabel');
    const PER_PAGE = 12;
    let currentPage = 1;

    function renderPagination(total, page, perPage) {
      paginationWrap.innerHTML = '';
      const totalPages = Math.ceil(total / perPage);
      if (totalPages <= 1) return;
      for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.className = `btn btn-sm ${i === page ? 'btn-accent' : 'btn-outline'}`;
        btn.textContent = i;
        btn.addEventListener('click', () => { currentPage = i; fetchListings(); });
        paginationWrap.appendChild(btn);
      }
    }

    async function fetchListings() {
      browseLoading.style.display = '';
      marketGrid.style.display = 'none';
      marketEmpty.style.display = 'none';
      paginationWrap.innerHTML = '';

      const fixedOn = document.getElementById('filterFixed').checked;
      const biddingOn = document.getElementById('filterBidding').checked;

      if (!fixedOn && !biddingOn) {
        browseLoading.style.display = 'none';
        marketEmpty.style.display = '';
        return;
      }

      const params = new URLSearchParams({ page: currentPage, per_page: PER_PAGE });
      const crop = document.getElementById('cropFilter').value.trim();
      const region = document.getElementById('regionFilter').value.trim();
      const search = document.getElementById('browseSearch').value.trim();
      if (crop) params.set('crop_type', crop);
      if (region) params.set('region', region);
      if (search) params.set('search', search);
      if (Number(priceRange.value) < Number(priceRange.max)) params.set('max_price', priceRange.value);
      if (fixedOn !== biddingOn) params.set('selling_method', fixedOn ? 'fixed_price' : 'bidding');

      try {
        const result = await apiFetch(`/listings?${params.toString()}`);
        const listings = result.data ?? [];
        browseLoading.style.display = 'none';

        if (listings.length === 0) {
          marketEmpty.style.display = '';
          return;
        }

        marketGrid.innerHTML = '';
        marketGrid.style.display = '';
        listings.forEach((l) => marketGrid.appendChild(produceCard(l)));
        renderPagination(result.total, result.page, result.perPage);
      } catch (err) {
        browseLoading.style.display = 'none';
        marketEmpty.style.display = '';
        window.gpToast('Could not load listings: ' + err.message, 'error');
      }
    }

    // suggestions for the crop / region boxes come from what is currently on the market
    async function loadFilterOptions() {
      try {
        const result = await apiFetch('/listings?per_page=100');
        const unique = (values) => [...new Set(values)].sort();
        const fill = (id, values) => {
          document.getElementById(id).innerHTML = unique(values).map((v) => `<option value="${esc(v)}"></option>`).join('');
        };
        fill('cropOptions', (result.data ?? []).map((l) => l.cropType));
        fill('regionOptions', (result.data ?? []).map((l) => l.location));
      } catch (err) { /* suggestions are optional */ }
    }

    const showPrice = () => {
      priceLabelEl.textContent = Number(priceRange.value) >= Number(priceRange.max) ? 'Any' : `LKR ${Number(priceRange.value).toLocaleString()}`;
    };
    priceRange.addEventListener('input', showPrice);
    showPrice();

    document.getElementById('applyFiltersBtn').addEventListener('click', () => { currentPage = 1; fetchListings(); });

    let searchTimer;
    document.getElementById('browseSearch').addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => { currentPage = 1; fetchListings(); }, 400);
    });

    loadFilterOptions();
    fetchListings();
  }

  /* ══════════════ LISTING DETAILS ══════════════ */
  const listingDetail = document.getElementById('listingDetail');
  if (listingDetail) {
    const listingLoading = document.getElementById('listingLoadingState');
    const listingNotFound = document.getElementById('listingNotFound');
    const listingId = window._listingId;

    (async () => {
      if (!listingId) {
        listingLoading.style.display = 'none';
        listingNotFound.style.display = '';
        return;
      }

      try {
        await gp.session;
        const l = (await apiFetch(`/listings/${listingId}`)).data;

        document.querySelector('.gp-topbar__crumb').textContent = `Browse Produce / ${l.cropType}`;

        const photo = document.getElementById('listingPhoto');
        photo.onerror = () => { photo.onerror = null; photo.src = PLACEHOLDER; };
        photo.src = l.photoUrl || PLACEHOLDER;
        photo.alt = `${l.cropType} produce photo`;

        document.getElementById('listingCropType').textContent = l.cropType;
        document.getElementById('listingMeta').textContent = `Listing #${l.id} · ${l.qty} kg available · ${l.location}`;
        document.getElementById('farmerName').textContent = l.farmerName ?? `Farmer #${l.farmerId}`;
        document.getElementById('farmerInitials').textContent = (l.farmerName ?? 'F').split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('');
        document.getElementById('farmerLocation').textContent = l.location;
        document.getElementById('harvestDate').textContent = `Harvested On: ${l.harvestDate}`;

        const badge = document.getElementById('listingBadge');
        const onSale = l.status === 'active' && l.qty > 0;

        if (!onSale) {
          badge.className = 'badge badge-muted';
          badge.textContent = l.status === 'active' ? 'out of stock' : l.status;
          document.getElementById('listingInactive').style.display = '';
        } else if (l.sellingMethod === 'bidding') {
          badge.className = 'badge badge-warning';
          badge.textContent = 'Bidding open';
          document.getElementById('biddingSection').style.display = '';
          document.getElementById('listingPriceBid').textContent = `from ${money(l.startingPrice)} /kg`;
          document.getElementById('placeBidLink').href = `${BASE_URL_JS}/buyer/place_bid.php?id=${l.id}`;
          document.getElementById('bidClosingAt').textContent = gp.formatDateTime(l.closingTime);
        } else {
          badge.className = 'badge badge-success';
          badge.textContent = 'Fixed price';
          document.getElementById('fixedPriceSection').style.display = '';
          document.getElementById('listingPriceFixed').textContent = `${money(l.pricePerUnit)} /kg`;

          const qtyInput = document.getElementById('orderQty');
          qtyInput.max = l.qty;
          const showTotal = () => {
            const qty = parseInt(qtyInput.value, 10) || 0;
            document.getElementById('orderQtyTotal').textContent = qty > 0 ? `Total: ${money(qty * l.pricePerUnit)}` : '';
          };
          qtyInput.addEventListener('input', showTotal);
          showTotal();

          // returns the quantity to add, or null (after telling the buyer why not)
          const validQuantity = () => {
            const qty = parseInt(qtyInput.value, 10);
            const inCart = cart.quantityOf(l.id);
            if (!qty || qty < 1) { window.gpToast('Enter a quantity of at least 1 kg.', 'error'); return null; }
            if (qty + inCart > l.qty) {
              window.gpToast(inCart ? `You already have ${inCart} kg in your cart. Only ${l.qty} kg available.` : `Only ${l.qty} kg available.`, 'error');
              return null;
            }
            return qty;
          };

          document.getElementById('addToCartBtn').addEventListener('click', () => {
            const qty = validQuantity();
            if (qty === null) return;
            cart.add(l.id, qty);
            window.gpToast(`Added ${qty} kg of ${l.cropType} to your cart.`);
          });

          document.getElementById('buyNowBtn').addEventListener('click', () => {
            const qty = validQuantity();
            if (qty === null) return;
            cart.add(l.id, qty);
            window.location.href = `${BASE_URL_JS}/buyer/cart_checkout.php`;
          });
        }

        listingLoading.style.display = 'none';
        listingDetail.style.display = '';
      } catch (err) {
        listingLoading.style.display = 'none';
        listingNotFound.style.display = '';
        if (err.status !== 404) window.gpToast('Could not load listing: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════ CART & CHECKOUT ══════════════ */
  const cartGroups = document.getElementById('cartGroups');
  if (cartGroups) {
    const cartEmpty = document.getElementById('cartEmpty');
    const cartLayout = document.getElementById('cartLayout');
    const placeBtn = document.getElementById('placeOrderBtn');
    const checkoutError = document.getElementById('checkoutError');
    let lines = []; // { listing, quantity }

    const isBuyable = (l) => l.status === 'active' && l.sellingMethod === 'fixed_price' && l.qty > 0;

    async function loadCart() {
      const stored = cart.get();

      // fetch the current price and stock of everything in the cart
      const results = await Promise.all(stored.map(async (item) => {
        try {
          return { listing: (await apiFetch(`/listings/${item.listingId}`)).data, quantity: item.quantity };
        } catch (err) {
          if (err.status === 404) return null; // the listing no longer exists
          throw err;
        }
      }));

      lines = results.filter(Boolean);
      if (lines.length !== stored.length) {
        cart.remove(stored.filter((s) => !lines.some((l) => l.listing.id === s.listingId)).map((s) => s.listingId));
      }

      // never ask for more than is in stock
      lines.forEach((line) => {
        if (isBuyable(line.listing) && line.quantity > line.listing.qty) {
          line.quantity = line.listing.qty;
          cart.setQuantity(line.listing.id, line.quantity);
        }
      });

      render();
    }

    function groupLines() {
      const groups = new Map();
      lines.forEach((line) => {
        const key = line.listing.farmerId;
        if (!groups.has(key)) groups.set(key, { farmerName: line.listing.farmerName ?? `Farmer #${key}`, location: line.listing.location, lines: [] });
        groups.get(key).lines.push(line);
      });
      return [...groups.values()];
    }

    const lineTotal = (line) => (isBuyable(line.listing) ? line.quantity * line.listing.pricePerUnit : 0);

    function render() {
      const empty = lines.length === 0;
      cartEmpty.style.display = empty ? '' : 'none';
      cartLayout.style.display = empty ? 'none' : '';
      document.getElementById('cartCount').textContent = empty ? '' : `(${lines.length})`;

      const groups = groupLines();
      cartGroups.innerHTML = groups.map((g) => {
        const subtotal = g.lines.reduce((sum, line) => sum + lineTotal(line), 0);
        return `
          <div class="cart-group" style="margin-bottom:18px;">
            <h4 style="margin-bottom:4px;"><i class="bi bi-person-circle"></i> ${esc(g.farmerName)} <span class="muted" style="font-weight:400;font-size:13px;">· ${esc(g.location)}</span></h4>
            ${g.lines.map((line) => {
              const l = line.listing;
              const buyable = isBuyable(l);
              return `
                <div class="cart-line" data-listing="${l.id}">
                  <div class="cart-line__thumb"><i class="bi bi-basket"></i></div>
                  <div style="flex:1;">
                    <strong>${esc(l.cropType)}</strong>
                    <div class="muted" style="font-size:12.5px;">${buyable ? `${money(l.pricePerUnit)}/kg · Available` : ''}</div>
                    ${buyable ? '' : '<div style="font-size:12.5px;color:#c0392b;">No longer available for direct purchase. Remove it to continue.</div>'}
                  </div>
                  ${buyable ? `
                    <div class="qty-stepper">
                      <button type="button" data-cart-step="down">−</button>
                      <input type="text" value="${line.quantity}" readonly>kg
                      <button type="button" data-cart-step="up">+</button>
                    </div>
                    <div class="line-total" style="width:100px;text-align:right;font-weight:600;">${money(lineTotal(line))}</div>` : ''}
                  <button class="btn btn-outline btn-sm" data-cart-remove aria-label="Remove"><i class="bi bi-trash"></i></button>
                </div>`;
            }).join('')}
            <div class="section-head" style="margin-top:8px;"><span class="muted">Subtotal for this order</span><strong>${money(subtotal)}</strong></div>
          </div>`;
      }).join('');

      const total = lines.reduce((sum, line) => sum + lineTotal(line), 0);
      document.querySelector('[data-cart-subtotal]').textContent = money(total);
      document.querySelector('[data-cart-total]').textContent = money(total);
      document.getElementById('cartOrderCount').textContent = groups.length > 1 ? `Placed as ${groups.length} separate orders, one per farmer.` : '';
      placeBtn.disabled = empty || !lines.every((line) => isBuyable(line.listing));
    }

    cartGroups.addEventListener('click', (e) => {
      const row = e.target.closest('.cart-line');
      if (!row) return;
      const id = parseInt(row.dataset.listing, 10);
      const line = lines.find((l) => l.listing.id === id);

      if (e.target.closest('[data-cart-remove]')) {
        cart.remove(id);
        lines = lines.filter((l) => l.listing.id !== id);
        render();
        return;
      }

      const step = e.target.closest('[data-cart-step]');
      if (step && line) {
        const next = line.quantity + (step.dataset.cartStep === 'up' ? 1 : -1);
        if (next < 1) return;
        if (next > line.listing.qty) { window.gpToast(`Only ${line.listing.qty} kg available.`, 'error'); return; }
        line.quantity = next;
        cart.setQuantity(id, next);
        render();
      }
    });

    placeBtn.addEventListener('click', async () => {
      checkoutError.style.display = 'none';
      const method = document.querySelector('input[name="payment"]:checked').value;
      const groups = groupLines();
      const placed = [];

      placeBtn.disabled = true;
      placeBtn.textContent = 'Placing order…';

      try {
        const itemsOf = (g) => g.lines.map((line) => ({ listing_id: line.listing.id, quantity: line.quantity }));

        // 1. check every farmer's items first, so nothing is created if stock has run out
        for (const g of groups) {
          await apiFetch('/checkout/preview', { method: 'POST', body: JSON.stringify({ items: itemsOf(g) }) });
        }

        // 2. one order per farmer, each paid straight away
        for (const g of groups) {
          const created = await apiFetch('/orders', { method: 'POST', body: JSON.stringify({ items: itemsOf(g) }) });
          const orderId = created.data.id;
          placed.push(orderId);
          cart.remove(g.lines.map((line) => line.listing.id));

          if (method === 'cash_on_delivery') {
            await apiFetch(`/orders/${orderId}/pay`, { method: 'POST', body: JSON.stringify({ method }) });
          }
        }

        if (method === 'card') {
          // each order is paid on its own page, one after another
          const [first, ...rest] = placed;
          window.location.href = `${BASE_URL_JS}/buyer/payment.php?id=${first}${rest.length ? `&queue=${rest.join(',')}` : ''}`;
          return;
        }

        window.gpToast('Order placed!');
        window.location.href = `${BASE_URL_JS}/buyer/orders.php`;;
      } catch (err) {
        checkoutError.textContent = placed.length
          ? `${err.message} Order ${placed.map((id) => '#' + id).join(', ')} was already placed. You can pay for it from My Orders.`
          : err.message;
        checkoutError.style.display = '';
        placeBtn.textContent = 'Place Order';
        try { await loadCart(); } catch (reloadError) { render(); }
      }
      placeBtn.textContent = 'Place Order';
    });

    (async () => {
      try {
        await gp.session;
        await loadCart();
      } catch (err) {
        cartGroups.innerHTML = '<p class="muted">Could not load your cart.</p>';
        window.gpToast('Could not load cart: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════ PLACE BID ══════════════ */
  const placeBidForm = document.getElementById('placeBidForm');
  if (placeBidForm) {
    const listingId = Number(window._bidListingId);
    const loading = document.getElementById('placeBidLoading');
    const unavailable = document.getElementById('placeBidUnavailable');
    const listingCard = document.getElementById('bidListingCard');
    const formCard = document.getElementById('placeBidCard');
    const amountInput = document.getElementById('bidAmount');
    const quantityInput = document.getElementById('bidQty');
    const submitButton = document.getElementById('placeBidSubmit');
    const errorEl = document.getElementById('placeBidError');

    (async () => {
      try {
        await gp.session;
        if (!Number.isInteger(listingId) || listingId < 1) throw new Error('Choose a valid bidding listing.');

        const [listingResult, summaryResult] = await Promise.all([
          apiFetch(`/listings/${listingId}`),
          apiFetch(`/listings/${listingId}/bid-summary`),
        ]);
        const listing = listingResult.data;
        const summary = summaryResult.data;
        if (listing.sellingMethod !== 'bidding' || listing.status !== 'active' || !summary.is_open) {
          throw new Error('Bidding on this listing has closed.');
        }

        amountInput.min = summary.minimum_next_bid;
        amountInput.value = Number(summary.minimum_next_bid).toFixed(2);
        quantityInput.max = listing.qty;
        quantityInput.value = listing.qty;
        document.getElementById('bidListingName').textContent = `${listing.cropType} — ${listing.qty} kg`;
        document.getElementById('bidListingMeta').textContent = `${listing.farmerName ?? `Farmer #${listing.farmerId}`} · ${listing.location} · Listing #${listing.id}`;
        document.getElementById('highestBid').textContent = summary.highest_bid == null ? 'No bids yet' : `${money(summary.highest_bid)} /kg`;
        document.getElementById('totalBids').textContent = summary.total_bids;
        document.getElementById('minimumNextBid').textContent = `${money(summary.minimum_next_bid)} /kg`;
        document.getElementById('bidHint').textContent = `Minimum bid: ${money(summary.minimum_next_bid)} per kg`;

        if (summary.bidding_closes_at) {
          document.getElementById('bidClosingAt').textContent = gp.formatDateTime(summary.bidding_closes_at);
          document.getElementById('bidClosingBadge').style.display = '';
        }
        listingCard.style.display = '';
        formCard.style.display = '';
      } catch (err) {
        document.getElementById('placeBidUnavailableText').textContent = err.message;
        unavailable.style.display = '';
      } finally {
        loading.style.display = 'none';
      }
    })();

    placeBidForm.addEventListener('submit', async (e) => {
      const invalid = e.defaultPrevented;
      e.preventDefault();
      if (invalid) return;

      errorEl.style.display = 'none';
      submitButton.disabled = true;
      submitButton.textContent = 'Submitting…';
      try {
        await apiFetch('/bids', {
          method: 'POST',
          body: JSON.stringify({
            listing_id: listingId,
            amount: Number(amountInput.value),
            quantity: Number(quantityInput.value),
          }),
        });
        window.gpToast('Bid placed successfully.');
        window.location.href = `${BASE_URL_JS}/buyer/my_bids.php`;
      } catch (err) {
        errorEl.textContent = err.message;
        errorEl.style.display = '';
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Submit Bid';
      }
    });
  }

  /* ══════════════ MY BIDS ══════════════ */
  const myBidsBody = document.getElementById('myBidsBody');
  if (myBidsBody) {
    const filterPills = document.querySelector('.filter-pills');
    let bids = [];
    let filter = 'all';

    function categoryOf(bid) {
      if (bid.status !== 'pending' || !bid.summary?.is_open) return 'closed';
      return Number(bid.amount) >= Number(bid.summary.highest_bid ?? 0) ? 'winning' : 'outbid';
    }

    function renderMyBids() {
      const visible = bids.filter((bid) => filter === 'all' || categoryOf(bid) === filter);
      if (!visible.length) {
        myBidsBody.innerHTML = '<tr><td colspan="9" class="muted">No bids to show.</td></tr>';
        return;
      }

      myBidsBody.innerHTML = visible.map((bid) => {
        const category = categoryOf(bid);
        const badge = category === 'winning'
          ? '<span class="badge badge-success">Winning</span>'
          : category === 'outbid'
            ? '<span class="badge badge-danger">Outbid</span>'
            : `<span class="badge badge-muted">${esc(bid.status)}</span>`;
        const listing = bid.listing;
        const closing = esc(gp.formatDateTime(listing?.closingTime));
        const raise = bid.summary?.is_open
          ? `<a href="${BASE_URL_JS}/buyer/place_bid.php?id=${bid.listing_id}" class="btn btn-outline btn-sm">Raise bid</a>`
          : '';

        return `<tr>
          <td>${esc(listing?.cropType ?? `Listing #${bid.listing_id}`)}${listing ? `<div class="muted" style="font-size:12px;">${listing.qty} kg · #${listing.id}</div>` : ''}</td>
          <td>${esc(listing?.farmerName ?? '—')}</td>
          <td>${money(bid.amount)} /kg</td>
          <td>${Number(bid.quantity).toLocaleString('en-LK')} kg</td>
          <td>${money(Number(bid.amount) * Number(bid.quantity))}</td>
          <td>${bid.summary?.highest_bid == null ? '—' : `${money(bid.summary.highest_bid)} /kg`}</td>
          <td>${closing}</td>
          <td>${badge}</td>
          <td>${raise}</td>
        </tr>`;
      }).join('');
    }

    async function loadMyBids() {
      myBidsBody.innerHTML = '<tr><td colspan="9" class="muted">Loading your bids…</td></tr>';
      try {
        await gp.session;
        const result = await apiFetch('/bids/my?per_page=100');
        const rows = result.data ?? [];
        const listingIds = [...new Set(rows.map((bid) => bid.listing_id))];
        const contexts = await Promise.all(listingIds.map(async (id) => {
          const [listingResult, summaryResult] = await Promise.allSettled([
            apiFetch(`/listings/${id}`),
            apiFetch(`/listings/${id}/bid-summary`),
          ]);
          return [id, {
            listing: listingResult.status === 'fulfilled' ? listingResult.value.data : null,
            summary: summaryResult.status === 'fulfilled' ? summaryResult.value.data : null,
          }];
        }));
        const contextByListing = new Map(contexts);
        bids = rows.map((bid) => ({ ...bid, ...contextByListing.get(bid.listing_id) }));
        renderMyBids();
      } catch (err) {
        myBidsBody.innerHTML = `<tr><td colspan="9" class="muted">Could not load your bids: ${esc(err.message)}</td></tr>`;
        window.gpToast('Could not load bids: ' + err.message, 'error');
      }
    }

    filterPills?.addEventListener('gp:filterchange', (e) => {
      filter = e.detail;
      renderMyBids();
    });
    loadMyBids();
  }

  /* ══════════════ ORDERS ══════════════ */
  const ordersBody = document.getElementById('ordersBody');
  if (ordersBody) {
    const pills = document.getElementById('orderFilterPills');
    let orders = [];
    let filter = 'all';

    const needsPayment = (o) => o.paymentStatus !== 'paid' && o.paymentMethod !== 'cash_on_delivery';

    function render() {
      const shown = orders.filter((o) => filter === 'all' || (filter === 'completed') === (o.status === 'completed'));

      if (shown.length === 0) {
        ordersBody.innerHTML = '<tr><td colspan="7" class="muted">No orders to show.</td></tr>';
        return;
      }

      ordersBody.innerHTML = shown.map((o) => `
        <tr>
          <td>${gp.orderRef(o.id)}</td>
          <td>${gp.itemsSummary(o.items)}</td>
          <td>${money(o.totalAmount)}</td>
          <td>${gp.paymentBadge(o)}</td>
          <td>${gp.orderStatusBadge(o.status)}</td>
          <td class="no-export">${needsPayment(o) ? `<a class="btn btn-accent btn-sm" href="${BASE_URL_JS}/buyer/payment.php?id=${o.id}">Pay now</a>` : ''}</td>
        </tr>`).join('');
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

    loadOrders();
  }

    /* ══════════════ CARD PAYMENT (payment.php) ══════════════ */
  const paymentForm = document.getElementById('paymentForm');
  if (paymentForm) {
    const orderId = window._paymentOrderId;
    // orders still waiting to be paid after this one (a cart with several farmers makes several orders)
    const queue = (new URLSearchParams(window.location.search).get('queue') || '')
      .split(',').map((n) => parseInt(n, 10)).filter(Boolean);

    const loading = document.getElementById('paymentLoading');
    const unavailable = document.getElementById('paymentUnavailable');
    const layout = document.getElementById('paymentLayout');
    const formError = document.getElementById('paymentError');
    const payBtn = document.getElementById('payNowButton');
    const payBtnHtml = payBtn.innerHTML;
    const fields = {
      name: document.getElementById('cardholderName'),
      number: document.getElementById('cardNumber'),
      expiry: document.getElementById('cardExpiry'),
      cvc: document.getElementById('cardCvc'),
    };
    let submitted = false;

    function showUnavailable(message) {
      loading.style.display = 'none';
      layout.style.display = 'none';
      document.getElementById('paymentUnavailableMessage').textContent = message;
      unavailable.style.display = '';
    }

    fields.number.addEventListener('input', () => {
      const digits = fields.number.value.replace(/\D/g, '').slice(0, 19);
      fields.number.value = digits.replace(/(.{4})/g, '$1 ').trim();
    });

    fields.expiry.addEventListener('input', (e) => {
      let digits = fields.expiry.value.replace(/\D/g, '').slice(0, 4);
      if (digits.length === 1 && digits > '1') digits = `0${digits}`;
      if (digits.length >= 3) digits = `${digits.slice(0, 2)} / ${digits.slice(2)}`;
      else if (digits.length === 2 && e.inputType !== 'deleteContentBackward') digits += ' / ';
      fields.expiry.value = digits;
    });

    fields.cvc.addEventListener('input', () => {
      fields.cvc.value = fields.cvc.value.replace(/\D/g, '').slice(0, 4);
    });

    // card detail validations
    function luhnValid(number) {
      let sum = 0;
      let double = false;
      for (let i = number.length - 1; i >= 0; i--) {
        let digit = parseInt(number[i], 10);
        if (double) { digit *= 2; if (digit > 9) digit -= 9; }
        sum += digit;
        double = !double;
      }
      return sum % 10 === 0;
    }

    function validate() {
      const errors = {};

      if (!/^[\p{L}][\p{L} .'\-]{1,99}$/u.test(fields.name.value.trim())) {
        errors.name = 'Enter the name as shown on the card.';
      }

      const number = fields.number.value.replace(/\s/g, '');
      if (!/^\d{13,19}$/.test(number) || !luhnValid(number)) {
        errors.number = 'Enter a valid card number.';
      }

      const match = fields.expiry.value.match(/^(\d{2}) \/ (\d{2})$/);
      if (!match || +match[1] < 1 || +match[1] > 12) {
        errors.expiry = 'Enter the expiry date as MM / YY.';
      } else {
        const year = 2000 + +match[2];
        const lastMomentValid = new Date(year, +match[1], 0, 23, 59, 59); // a card works until the end of its expiry month
        if (lastMomentValid < new Date()) errors.expiry = 'This card has expired.';
        else if (year > new Date().getFullYear() + 20) errors.expiry = 'Enter a valid expiry date.';
      }

      const isAmex = /^3[47]/.test(number);
      const cvcLength = isAmex ? 4 : 3;
      if (!new RegExp(`^\\d{${cvcLength}}$`).test(fields.cvc.value)) {
        errors.cvc = `Enter the ${cvcLength}-digit security code.`;
      }

      return errors;
    }

    function showErrors(errors) {
      Object.entries(fields).forEach(([key, input]) => {
        const holder = input.closest('.field');
        let message = holder.querySelector('.field-error');
        if (errors[key]) {
          if (!message) {
            message = document.createElement('p');
            message.className = 'field-error';
            holder.appendChild(message);
          }
          message.textContent = errors[key];
          input.setAttribute('aria-invalid', 'true');
        } else {
          message?.remove();
          input.removeAttribute('aria-invalid');
        }
      });
    }

    // after the first failed attempt, re-check as the buyer types
    Object.values(fields).forEach((input) => input.addEventListener('input', () => {
      if (submitted) showErrors(validate());
    }));

    paymentForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      formError.style.display = 'none';
      submitted = true;

      const errors = validate();
      showErrors(errors);
      const firstInvalid = Object.keys(errors)[0];
      if (firstInvalid) { fields[firstInvalid].focus(); return; }

      payBtn.disabled = true;
      payBtn.textContent = 'Processing…';

      try {
        // only the payment method is sent; the card details stay in this form
        const result = await apiFetch(`/orders/${orderId}/pay`, { method: 'POST', body: JSON.stringify({ method: 'card' }) });
        paymentForm.reset();
        window.gpToast(result.data?.receiptNumber ? `Payment successful. Receipt ${result.data.receiptNumber}` : 'Payment successful.');

        const [next, ...rest] = queue;
        window.location.href = next
          ? `${BASE_URL_JS}/buyer/payment.php?id=${next}${rest.length ? `&queue=${rest.join(',')}` : ''}`
          : `${BASE_URL_JS}/buyer/orders.php`;
      } catch (err) {
        formError.textContent = err.message;
        formError.style.display = '';
        payBtn.disabled = false;
        payBtn.innerHTML = payBtnHtml;
      }
    });

    // order summary
    (async () => {
      if (!orderId) { showUnavailable('No order was selected.'); return; }

      try {
        const user = await gp.session;
        const order = (await apiFetch(`/orders/${orderId}`)).data;

        if (order.buyerId !== user.id) { showUnavailable('This order does not belong to you.'); return; }
        if (order.paymentStatus === 'paid') { showUnavailable('This order has already been paid.'); return; }

        document.getElementById('paymentOrderNumber').textContent = gp.orderRef(order.id);
        document.getElementById('paymentItems').innerHTML = order.items.map((item) => `
          <div class="section-head" style="margin:0;">
            <span>${esc(item.cropType)} <span class="muted">· ${item.qty} kg × ${money(item.unitPrice)}</span></span>
            <strong>${money(item.subTotal)}</strong>
          </div>`).join('');
        document.getElementById('paymentTotal').textContent = money(order.totalAmount);

        loading.style.display = 'none';
        layout.style.display = '';
      } catch (err) {
        showUnavailable(err.status === 404 ? 'Order not found.' : err.message);
      }
    })();
  }
});
