document.addEventListener('DOMContentLoaded', () => {
  const content = document.getElementById('subscriptionContent');
  if (!content) return;

  const loading = document.getElementById('subscriptionLoading');
  const errorState = document.getElementById('subscriptionError');
  const errorMessage = document.getElementById('subscriptionErrorMessage');
  const plansElement = document.getElementById('subscriptionPlans');
  const historyBody = document.getElementById('subscriptionHistory');
  const { esc, money } = gp;

  let plans = [];
  let status = null;
  let history = [];
  let busy = false;

  function dateLabel(value) {
    if (!value) return '—';
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, month - 1, day).toLocaleDateString('en-LK', {
      day: 'numeric', month: 'short', year: 'numeric',
    });
  }

  function badge(statusValue) {
    const classes = {
      active: 'badge-success',
      scheduled: 'badge-sage',
      pending: 'badge-warning',
      expired: 'badge-muted',
      cancelled: 'badge-danger',
      paid: 'badge-success',
      unpaid: 'badge-warning',
    };
    const label = String(statusValue ?? 'unknown').replaceAll('_', ' ');
    return `<span class="badge ${classes[statusValue] ?? 'badge-muted'}">${esc(label)}</span>`;
  }

  function renderStatus() {
    const pending = status.pending;
    const current = status.current;
    const upcoming = status.upcoming;
    const title = document.getElementById('subscriptionStateTitle');
    const detail = document.getElementById('subscriptionStateDetail');
    const stateBadge = document.getElementById('subscriptionStateBadge');
    const notice = document.getElementById('subscriptionNotice');

    if (pending) {
      title.textContent = 'Payment required';
      detail.textContent = `${pending.planName} · ${pending.periodMonths} month · ${money(pending.fee)}`;
      stateBadge.className = 'badge badge-warning';
      stateBadge.textContent = 'Pending payment';
      notice.textContent = 'Complete card payment below to activate this subscription.';
      notice.style.display = '';
    } else if (current) {
      title.textContent = `${current.planName} active`;
      detail.textContent = `Active until ${dateLabel(current.endDate)}${current.daysRemaining != null ? ` · ${current.daysRemaining} days remaining` : ''}`;
      stateBadge.className = 'badge badge-success';
      stateBadge.textContent = 'Active';
      notice.textContent = upcoming ? `Renewal scheduled through ${dateLabel(upcoming.endDate)}.` : '';
      notice.style.display = upcoming ? '' : 'none';
    } else if (upcoming) {
      title.textContent = `${upcoming.planName} scheduled`;
      detail.textContent = `Starts ${dateLabel(upcoming.startDate)} · Ends ${dateLabel(upcoming.endDate)}`;
      stateBadge.className = 'badge badge-sage';
      stateBadge.textContent = 'Scheduled';
      notice.style.display = 'none';
    } else if (status.state === 'expired') {
      title.textContent = 'Subscription expired';
      detail.textContent = status.expiredOn ? `Expired ${dateLabel(status.expiredOn)}` : 'Renew your plan to restore route priority.';
      stateBadge.className = 'badge badge-muted';
      stateBadge.textContent = 'Expired';
      notice.style.display = 'none';
    } else {
      title.textContent = 'No active subscription';
      detail.textContent = 'Choose a monthly plan to get route-based delivery priority.';
      stateBadge.className = 'badge badge-muted';
      stateBadge.textContent = 'Not subscribed';
      notice.style.display = 'none';
    }
  }

  function planAction(plan) {
    if (status.pending?.plan === plan.key) {
      return `<button type="button" class="btn btn-gold btn-block" data-subscription-action="pay" data-subscription-id="${status.pending.id}">Complete payment</button>`;
    }
    if (status.pending) {
      return '<button type="button" class="btn btn-outline btn-block" disabled>Payment pending</button>';
    }
    if (status.canSubscribe) {
      return `<button type="button" class="btn btn-gold btn-block" data-subscription-action="subscribe" data-plan="${esc(plan.key)}">Start monthly plan</button>`;
    }
    if (status.canRenew) {
      return `<button type="button" class="btn btn-gold btn-block" data-subscription-action="renew" data-plan="${esc(plan.key)}">Renew for a month</button>`;
    }
    return `<button type="button" class="btn btn-outline btn-block" disabled>${status.current?.plan === plan.key ? 'Current plan' : 'Plan unavailable'}</button>`;
  }

  function renderPlans() {
    plansElement.innerHTML = plans.map((plan) => `
      <article class="plan-card ${status.current?.plan === plan.key ? 'current' : ''}">
        <div class="plan-card__details">
          <div class="plan-card__offer">
            <h4>${esc(plan.name)}</h4>
            <div class="price">${money(plan.fee)} <span class="muted">/ ${plan.months} month${plan.months === 1 ? '' : 's'}</span></div>
            <p class="plan-card__description muted">${esc(plan.description)}</p>
          </div>
        </div>
        <div class="plan-card__action">${planAction(plan)}</div>
      </article>`).join('');
  }

  function renderHistory() {
    if (!history.length) {
      historyBody.innerHTML = '<tr><td colspan="6" class="muted">No subscription history yet.</td></tr>';
      return;
    }

    historyBody.innerHTML = history.map((item) => `
      <tr>
        <td>${esc(item.planName)}</td>
        <td>${dateLabel(item.startDate)} – ${dateLabel(item.endDate)}</td>
        <td>${money(item.fee)}</td>
        <td>${badge(item.paymentStatus)}</td>
        <td>${badge(item.status)}</td>
        <td>${item.receiptNumber ? esc(item.receiptNumber) : '—'}</td>
      </tr>`).join('');
  }

  async function load() {
    loading.style.display = '';
    content.style.display = 'none';
    errorState.style.display = 'none';
    try {
      await gp.session;
      const [plansResult, statusResult, historyResult] = await Promise.all([
        apiFetch('/subscriptions/plans'),
        apiFetch('/subscriptions/status'),
        apiFetch('/subscriptions'),
      ]);
      plans = plansResult.data ?? [];
      status = statusResult.data;
      history = historyResult.data ?? [];
      renderStatus();
      renderPlans();
      renderHistory();
      content.style.display = '';
    } catch (error) {
      errorMessage.textContent = error.message;
      errorState.style.display = '';
    } finally {
      loading.style.display = 'none';
    }
  }

  plansElement.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-subscription-action]');
    if (!button || busy) return;

    busy = true;
    button.disabled = true;
    const action = button.dataset.subscriptionAction;
    try {
      if (action === 'subscribe') {
        await apiFetch('/subscriptions', {
          method: 'POST',
          body: JSON.stringify({ plan: button.dataset.plan }),
        });
        window.gpToast('Subscription created. Complete payment to activate it.');
      } else if (action === 'renew') {
        await apiFetch('/subscriptions/renew', {
          method: 'POST',
          body: JSON.stringify({ plan: button.dataset.plan }),
        });
        window.gpToast('Renewal created. Complete payment to activate it.');
      } else if (action === 'pay') {
        const result = await apiFetch(`/subscriptions/${button.dataset.subscriptionId}/pay`, {
          method: 'POST',
          body: JSON.stringify({ method: 'card' }),
        });
        const receipt = result.data?.receiptNumber ? ` Receipt ${result.data.receiptNumber}.` : '';
        window.gpToast(`Subscription payment successful.${receipt}`);
      }
      await load();
    } catch (error) {
      window.gpToast(error.message, 'error');
      button.disabled = false;
    } finally {
      busy = false;
    }
  });

  load();
});
