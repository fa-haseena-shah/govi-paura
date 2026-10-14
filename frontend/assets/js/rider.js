/**
 * Govi Paura — rider.js
 * API wiring for the Rider portal. Loaded by footer.php after api.js, main.js and common.js.
 *
 *   /rider/dashboard.php           → stats + next active delivery
 *   /rider/available_deliveries.php→ browse open opportunities, claim / view detail
 *   /rider/assigned_deliveries.php → active deliveries + Leaflet route map, advance status
 *   /rider/delivery_history.php    → completed deliveries with summary stats
 *   /rider/update_status.php       → update status + optional note for a specific delivery
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc, money } = gp;

  /* ─── shared helpers ─────────────────────────────────────────────── */

  /** Map backend delivery status values to badge CSS classes and short labels. */
  function deliveryStatusBadge(status, label) {
    const map = {
      pending:    ['badge-warning',  label ?? 'Pending'],
      assigned:   ['badge-warning',  label ?? 'Assigned'],
      picked_up:  ['badge-sage',     label ?? 'Picked Up'],
      in_transit: ['badge-sage',     label ?? 'In Transit'],
      delivered:  ['badge-success',  label ?? 'Delivered'],
    };
    const [cls, text] = map[status] ?? ['badge-muted', label ?? status];
    return `<span class="badge ${cls}">${esc(text)}</span>`;
  }

  /** Produce summary line: "Carrots x40 kg, Beans x10 kg". */
  function produceSummary(produce) {
    if (!produce?.length) return '—';
    return produce.map((p) => `${esc(p.cropType ?? '?')} x${p.quantity} ${esc(p.unit ?? 'kg')}`).join(', ');
  }

  /** Render the 4-step Assigned → Picked Up → In Transit → Delivered stepper. */
  function renderStepper(stages) {
    return stages.map((s) => {
      const cls = s.done ? 'done' : s.current ? 'current' : '';
      const dot = s.done
        ? '<i class="bi bi-check"></i>'
        : `${stages.findIndex((x) => x.current || x.done === false) + 1}`;
      return `<div class="step ${cls}"><div class="dot">${dot}</div><div class="step-label">${esc(s.label)}</div></div>`;
    }).join('');
  }

  /** Next-status label for the advance button. */
  function advanceBtnLabel(nextStatus) {
    const labels = {
      picked_up:  'Mark as Picked Up',
      in_transit: 'Mark as In Transit',
      delivered:  'Mark as Delivered',
    };
    return labels[nextStatus] ?? 'Advance Status';
  }

  /**
   * Call PATCH /deliveries/{id}/status then re-render the page section.
   * `onSuccess` is called with the updated delivery data.
   */
  async function advanceStatus(deliveryId, nextStatus, note, btn, onSuccess) {
    if (!deliveryId || !nextStatus) return;
    btn.disabled = true;
    btn.textContent = 'Updating…';
    try {
      const res = await apiFetch(`/deliveries/${deliveryId}/status`, {
        method: 'PATCH',
        body: JSON.stringify({ status: nextStatus, note: note ?? null }),
      });
      window.gpToast('Delivery status updated.');
      if (onSuccess) onSuccess(res.data);
    } catch (err) {
      window.gpToast(err.message, 'error');
      btn.disabled = false;
      btn.textContent = advanceBtnLabel(nextStatus);
    }
  }

  /* ══════════════ DASHBOARD ══════════════════════════════════════════ */
  const dashStats = document.getElementById('riderDashStats');
  const dashNext  = document.getElementById('riderDashNext');

  if (dashStats || dashNext) {
    (async () => {
      try {
        await gp.session;
        const [assignedRes, historyRes] = await Promise.all([
          apiFetch('/deliveries/assigned'),
          apiFetch('/deliveries/history?per_page=100'),
        ]);

        const assigned = assignedRes.data ?? [];
        const history  = historyRes.data ?? [];
        const meta     = historyRes.meta ?? {};
        const summary  = meta.summary ?? {};

        /* ---- stat cards ---- */
        const today = new Date().toDateString();
        const assignedToday = assigned.filter((d) =>
          d.stages?.some((s) => s.done && s.status === 'assigned' &&
            new Date(s.reachedAt).toDateString() === today),
        ).length;

        const completedToday = history.filter((d) =>
          d.timeline?.some((t) => t.status === 'delivered' &&
            new Date(t.at).toDateString() === today),
        ).length;

        // Fee earned: distance_km × some fixed rate; backend doesn't expose fee yet,
        // so we show total deliveries completed instead until the earnings field is added.
        if (dashStats) {
          const statAssigned  = dashStats.querySelector('[data-stat="assigned"]');
          const statCompleted = dashStats.querySelector('[data-stat="completed"]');
          const statEarnings  = dashStats.querySelector('[data-stat="earnings"]');
          if (statAssigned)  statAssigned.textContent  = assignedToday;
          if (statCompleted) statCompleted.textContent  = completedToday;
          if (statEarnings)  statEarnings.textContent   = summary.completed ?? 0;
        }

        /* ---- next delivery card ---- */
        if (dashNext) {
          // The most urgent assigned delivery (first in the list — backend orders by deadline_at)
          const next = assigned[0] ?? null;

          if (!next) {
            dashNext.innerHTML = `
              <div class="gp-empty" style="padding:24px 0;">
                <i class="bi bi-truck"></i>
                <h4>No active deliveries</h4>
                <p class="muted">Browse the available deliveries list to claim one.</p>
              </div>`;
            return;
          }

          const stepperHtml = renderStepper(next.stages ?? []);
          const nextStatus  = next.nextStatus;
          const btnHtml = nextStatus
            ? `<button class="btn btn-gold btn-block" data-advance-status data-delivery-id="${next.id}" data-next-status="${esc(nextStatus)}">${esc(advanceBtnLabel(nextStatus))}</button>`
            : `<p class="muted" style="margin:0;">This delivery has been completed.</p>`;

          dashNext.innerHTML = `
            <div class="section-head">
              <h3>Next Delivery</h3>
              ${deliveryStatusBadge(next.status, next.statusLabel)}
            </div>
            <p><strong>Order #${next.orderId}</strong> — ${produceSummary(next.produce)}</p>
            ${next.sla?.deadlineAt ? `<p class="muted" style="font-size:12.5px;"><i class="bi bi-clock"></i> Deadline: <span data-countdown="${esc(next.sla.deadlineAt)}"></span></p>` : ''}
            <div class="route-stepper">${stepperHtml}</div>
            ${btnHtml}`;

          // Wire advance button
          dashNext.querySelector('[data-advance-status]')?.addEventListener('click', async (e) => {
            const btn = e.currentTarget;
            await advanceStatus(btn.dataset.deliveryId, btn.dataset.nextStatus, null, btn, () => {
              window.location.reload();
            });
          });

          gp.startCountdowns(dashNext);
        }
      } catch (err) {
        if (dashStats) dashStats.innerHTML = `<p class="muted">Could not load dashboard stats.</p>`;
        if (dashNext)  dashNext.innerHTML  = `<p class="muted">${esc(err.message)}</p>`;
        window.gpToast('Dashboard load failed: ' + err.message, 'error');
      }
    })();
  }

  /* ══════════════ AVAILABLE DELIVERIES ═══════════════════════════════ */
  const opportunitiesGrid  = document.getElementById('opportunitiesGrid');
  const opportunitiesEmpty = document.getElementById('deliveriesEmpty');
  const opportunitiesLoading = document.getElementById('opportunitiesLoading');

  if (opportunitiesGrid) {
    let allOpportunities = [];
    let activeArea = 'all';

    function renderOpportunity(d) {
      const km = d.distanceKm != null ? `${Number(d.distanceKm).toFixed(1)} km` : '?';
      const mins = d.estimatedMinutes != null ? `~${d.estimatedMinutes} min` : '';
      const produceText = produceSummary(d.produce);
      const pickup  = d.pickup?.address  ?? (d.pickup?.approximate  ? '(wait)' : 'Pickup TBD');
      const dropoff = d.dropoff?.address ?? (d.dropoff?.approximate ? '(uhgh)' : 'Drop-off TBD');
      const deadline = d.sla?.deadlineAt
        ? `<p class="muted" style="font-size:12px;margin:4px 0 0;"><i class="bi bi-clock"></i> Accept within: <span data-countdown="${esc(d.sla.deadlineAt)}"></span></p>`
        : '';
      const atRisk = d.sla?.atRisk
        ? '<span class="badge badge-danger" style="margin-left:6px;">At risk</span>'
        : '';
      const region = d.region ? esc(d.region).toLowerCase() : '';

      return `
        <div class="delivery-card" data-delivery-id="${d.id}" data-region="${region}">
          <div class="section-head">
            <strong>#GP-${d.orderId}</strong>
            ${atRisk}
          </div>
          <p class="muted" style="margin:6px 0;">${produceText}</p>
          <p style="font-size:13px;">
            <i class="bi bi-geo-alt" style="color:var(--sage);"></i> Pickup: ${esc(pickup)}<br>
            <i class="bi bi-flag" style="color:var(--rust);"></i> Drop-off: ${esc(dropoff)}
          </p>
          ${deadline}
          <button class="btn btn-gold btn-block" style="margin-top:10px;" data-claim-delivery="${d.id}">
            Accept Delivery
          </button>
        </div>`;
    }

    function applyAreaFilter() {
      const cards = opportunitiesGrid.querySelectorAll('.delivery-card');
      let visible = 0;
      cards.forEach((card) => {
        const match = activeArea === 'all' || card.dataset.region.includes(activeArea.toLowerCase());
        card.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      if (opportunitiesEmpty) opportunitiesEmpty.style.display = visible === 0 ? '' : 'none';
    }

    async function loadOpportunities() {
      if (opportunitiesLoading) opportunitiesLoading.style.display = '';
      opportunitiesGrid.style.display = 'none';
      if (opportunitiesEmpty) opportunitiesEmpty.style.display = 'none';

      try {
        await gp.session;
        const res = await apiFetch('/deliveries/opportunities?per_page=50');
        allOpportunities = res.data ?? [];
      } catch (err) {
        allOpportunities = [];
        window.gpToast('Could not load deliveries: ' + err.message, 'error');
      }

      if (opportunitiesLoading) opportunitiesLoading.style.display = 'none';

      if (allOpportunities.length === 0) {
        opportunitiesGrid.innerHTML = '';
        opportunitiesGrid.style.display = '';
        if (opportunitiesEmpty) opportunitiesEmpty.style.display = '';
        return;
      }

      opportunitiesGrid.innerHTML = allOpportunities.map(renderOpportunity).join('');
      opportunitiesGrid.style.display = '';
      gp.startCountdowns(opportunitiesGrid);
      applyAreaFilter();
    }

    // Filter pills
    document.querySelectorAll('.filter-pills .pill').forEach((pill) => {
      pill.addEventListener('click', () => {
        document.querySelectorAll('.filter-pills .pill').forEach((p) => p.classList.remove('active'));
        pill.classList.add('active');
        activeArea = pill.dataset.value ?? 'all';
        applyAreaFilter();
      });
    });

    // Claim / accept a delivery
    opportunitiesGrid.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-claim-delivery]');
      if (!btn) return;
      const deliveryId = btn.dataset.claimDelivery;
      btn.disabled = true;
      btn.textContent = 'Accepting…';
      try {
        await apiFetch(`/deliveries/${deliveryId}/claim`, { method: 'POST' });
        window.gpToast('Delivery accepted! Head to Assigned Deliveries.');
        await loadOpportunities();
      } catch (err) {
        window.gpToast(err.message, 'error');
        btn.disabled = false;
        btn.textContent = 'Accept Delivery';
      }
    });

    loadOpportunities();
  }

  /* ══════════════ ASSIGNED DELIVERIES ════════════════════════════════ */
  const assignedList    = document.getElementById('assignedDeliveriesList');
  const assignedEmpty   = document.getElementById('assignedEmpty');
  const assignedLoading = document.getElementById('assignedLoading');

  if (assignedList) {
    let assignedDeliveries = [];

    function renderAssigned(d) {
      const stepperHtml = renderStepper(d.stages ?? []);
      const nextStatus  = d.nextStatus;
      const advanceBtn  = nextStatus
        ? `<button class="btn btn-gold" data-advance-status data-delivery-id="${d.id}" data-next-status="${esc(nextStatus)}">${esc(advanceBtnLabel(nextStatus))}</button>`
        : `<span class="badge badge-success">Delivered</span>`;

      const pickup  = d.pickup?.address  ?? 'Pickup address not set';
      const dropoff = d.dropoff?.address ?? 'Drop-off address not set';

      const buyerName  = d.dropoff?.contactName ?? d.dropoff?.businessName ?? null;
      const farmerName = d.pickup?.contactName ?? null;
      const byline = [farmerName && `Farmer: ${farmerName}`, buyerName && `Buyer: ${buyerName}`]
        .filter(Boolean).join(' · ');

      const cashInfo = d.cashOnDelivery
        ? `<p class="muted" style="font-size:12.5px;margin:6px 0;"><i class="bi bi-cash-coin"></i> Collect ${money(d.cashOnDelivery.amountToCollect)} at door${d.cashOnDelivery.collected ? ' <span class="badge badge-success">Collected</span>' : ''}</p>`
        : '';

      const deadline = d.sla?.deadlineAt
        ? `<p class="muted" style="font-size:12px;margin:4px 0;"><i class="bi bi-clock"></i> SLA: <span data-countdown="${esc(d.sla.deadlineAt)}"></span></p>`
        : '';

      const mapDivId = `deliveryMap-${d.id}`;
      const mapDiv = `<div id="${mapDivId}" style="height:240px;border-radius:var(--radius);margin-bottom:14px;"></div>`;

      const pickupDue = d.sla?.pickupDueAt
        ? `<p class="muted" style="font-size:12px;margin:4px 0;"><i class="bi bi-stopwatch"></i> Pick up by: <span data-countdown="${esc(d.sla.pickupDueAt)}"></span></p>`
        : '';

      return `
        <div class="gp-card" style="margin-bottom:20px;" data-delivery-id="${d.id}">
          <div class="section-head">
            <div>
              <h3 style="margin-bottom:2px;">Order #GP-${d.orderId} — ${produceSummary(d.produce)}</h3>
              ${byline ? `<span class="muted">${esc(byline)}</span>` : ''}
            </div>
            ${deliveryStatusBadge(d.status, d.statusLabel)}
          </div>

          <p style="font-size:13px;margin:10px 0 4px;">
            <i class="bi bi-geo-alt" style="color:var(--sage);"></i> ${esc(pickup)}<br>
            <i class="bi bi-flag"    style="color:var(--rust);"></i> ${esc(dropoff)}
          </p>
          ${cashInfo}
          ${deadline}
          ${pickupDue}

          <div class="route-stepper" style="margin:14px 0;">${stepperHtml}</div>

          ${mapDiv}

          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            ${advanceBtn}
            <a href="${BASE_URL_JS}/rider/update_status.php?delivery=${d.id}" class="btn btn-outline">Add delivery notes</a>
          </div>
        </div>`;
    }

    async function loadAssigned() {
      if (assignedLoading) assignedLoading.style.display = '';
      assignedList.style.display = 'none';
      if (assignedEmpty) assignedEmpty.style.display = 'none';

      try {
        await gp.session;
        const res = await apiFetch('/deliveries/assigned');
        assignedDeliveries = res.data ?? [];
      } catch (err) {
        assignedDeliveries = [];
        window.gpToast('Could not load assigned deliveries: ' + err.message, 'error');
      }

      if (assignedLoading) assignedLoading.style.display = 'none';

      if (assignedDeliveries.length === 0) {
        assignedList.innerHTML = '';
        assignedList.style.display = '';
        if (assignedEmpty) assignedEmpty.style.display = '';
        return;
      }

      assignedList.innerHTML = assignedDeliveries.map(renderAssigned).join('');
      assignedList.style.display = '';
      gp.startCountdowns(assignedList);

      // Load Leaflet maps for each card
      for (const d of assignedDeliveries) {
        const mapEl = document.getElementById(`deliveryMap-${d.id}`);
        if (mapEl && window.gpMap) {
          try {
            const mapRes = await apiFetch(`/deliveries/${d.id}/map`);
            gpMap.init(mapEl, mapRes.data);
          } catch {
            mapEl.innerHTML = '<p class="muted" style="padding:12px;font-size:12.5px;">Map unavailable.</p>';
          }
        }
      }
    }

    // Advance status (delegated click on the list)
    assignedList.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-advance-status]');
      if (!btn) return;
      await advanceStatus(btn.dataset.deliveryId, btn.dataset.nextStatus, null, btn, () => {
        loadAssigned();
      });
    });

    loadAssigned();
  }

  /* ══════════════ DELIVERY HISTORY ═══════════════════════════════════ */
  const historyBody    = document.getElementById('historyTableBody');
  const historyEmpty   = document.getElementById('historyEmpty');
  const historySummary = document.getElementById('historySummary');

  if (historyBody) {
    async function loadHistory() {
      try {
        await gp.session;
        const res  = await apiFetch('/deliveries/history?per_page=100');
        const list = res.data ?? [];
        const meta = res.meta ?? {};
        const sum  = meta.summary ?? {};

        // Summary stats
        if (historySummary) {
          const elTotal   = historySummary.querySelector('[data-stat="total"]');
          const elOnTime  = historySummary.querySelector('[data-stat="on-time"]');
          const elLate    = historySummary.querySelector('[data-stat="late"]');
          if (elTotal)  elTotal.textContent  = sum.completed ?? 0;
          if (elOnTime) elOnTime.textContent  = sum.onTime ?? 0;
          if (elLate)   elLate.textContent    = sum.late ?? 0;
        }

        if (list.length === 0) {
          historyBody.innerHTML = '';
          if (historyEmpty) historyEmpty.style.display = '';
          return;
        }

        if (historyEmpty) historyEmpty.style.display = 'none';

        historyBody.innerHTML = list.map((d) => {
          const deliveredAt = d.timeline?.find((t) => t.status === 'delivered')?.at;
          const deliveredStr = deliveredAt
            ? new Date(deliveredAt).toLocaleDateString('en-LK', { year: 'numeric', month: 'short', day: 'numeric' })
            : '—';
          const km = d.distanceKm != null ? `${Number(d.distanceKm).toFixed(1)} km` : '—';
          const late = d.sla?.deliveredLate;
          const lateBadge = late ? '<span class="badge badge-danger" style="margin-left:4px;">Late</span>' : '';

          return `
            <tr>
              <td>#GP-${d.orderId}</td>
              <td>${produceSummary(d.produce)}</td>
              <td>${esc(d.dropoff?.contactName ?? d.dropoff?.businessName ?? '—')}</td>
              <td>${esc(km)}</td>
              <td>${deliveredStr}${lateBadge}</td>
            </tr>`;
        }).join('');
      } catch (err) {
        historyBody.innerHTML = `<tr><td colspan="5" class="muted">Could not load history: ${esc(err.message)}</td></tr>`;
        window.gpToast('History load failed: ' + err.message, 'error');
      }
    }

    loadHistory();
  }

  /* ══════════════ UPDATE STATUS PAGE ═════════════════════════════════ */
  const updateStatusForm = document.getElementById('updateStatusForm');

  if (updateStatusForm) {
    // Delivery ID comes from the query string (?delivery=123)
    const deliveryId = new URLSearchParams(window.location.search).get('delivery');
    const statusSelect  = document.getElementById('status');
    const noteTextarea  = document.getElementById('note');
    const saveBtn       = updateStatusForm.querySelector('button[type="submit"]');
    const errorEl       = document.getElementById('updateStatusError');

    // Pre-load current delivery status to pre-select the next status
    if (deliveryId) {
      (async () => {
        try {
          await gp.session;
          const res = await apiFetch(`/deliveries/${deliveryId}`);
          const d   = res.data;
          if (d.nextStatus && statusSelect) {
            // Select the next expected status automatically
            const opt = statusSelect.querySelector(`option[value="${d.nextStatus}"]`);
            if (opt) {
              statusSelect.value = d.nextStatus;
            }
          }
          // Update order heading
          const orderHeading = document.getElementById('updateStatusOrderHeading');
          if (orderHeading) {
            orderHeading.textContent = `Order #GP-${d.orderId} — ${d.statusLabel}`;
          }
        } catch {
          /* non-fatal: user can still pick status manually */
        }
      })();
    }

    updateStatusForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!deliveryId) {
        if (errorEl) { errorEl.textContent = 'No delivery ID in the URL.'; errorEl.style.display = ''; }
        return;
      }

      if (errorEl) errorEl.style.display = 'none';
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving…';

      const newStatus = statusSelect?.value;
      const note      = noteTextarea?.value?.trim() || null;

      try {
        await apiFetch(`/deliveries/${deliveryId}/status`, {
          method: 'PATCH',
          body: JSON.stringify({ status: newStatus, note }),
        });
        window.gpToast('Status updated!');
        setTimeout(() => {
          window.location.href = `${BASE_URL_JS}/rider/assigned_deliveries.php`;
        }, 800);
      } catch (err) {
        if (errorEl) { errorEl.textContent = err.message; errorEl.style.display = ''; }
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Update';
      }
    });
  }
});

