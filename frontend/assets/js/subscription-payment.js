document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('subscriptionPaymentForm');
  if (!form) return;

  const loading = document.getElementById('subscriptionPaymentLoading');
  const unavailable = document.getElementById('subscriptionPaymentUnavailable');
  const layout = document.getElementById('subscriptionPaymentLayout');
  const error = document.getElementById('subscriptionPaymentError');
  const submit = document.getElementById('subscriptionPayNow');
  const subscriptionId = Number(window._paymentSubscriptionId);
  const fields = {
    name: document.getElementById('subscriptionCardholder'),
    number: document.getElementById('subscriptionCardNumber'),
    expiry: document.getElementById('subscriptionCardExpiry'),
    cvc: document.getElementById('subscriptionCardCvc'),
  };
  let submitted = false;

  function showUnavailable(message) {
    loading.style.display = 'none';
    layout.style.display = 'none';
    document.getElementById('subscriptionPaymentUnavailableMessage').textContent = message;
    unavailable.style.display = '';
  }

  fields.number.addEventListener('input', () => {
    const digits = fields.number.value.replace(/\D/g, '').slice(0, 19);
    fields.number.value = digits.replace(/(.{4})/g, '$1 ').trim();
  });
  fields.expiry.addEventListener('input', (event) => {
    let digits = fields.expiry.value.replace(/\D/g, '').slice(0, 4);
    if (digits.length === 1 && digits > '1') digits = `0${digits}`;
    if (digits.length >= 3) digits = `${digits.slice(0, 2)} / ${digits.slice(2)}`;
    else if (digits.length === 2 && event.inputType !== 'deleteContentBackward') digits += ' / ';
    fields.expiry.value = digits;
  });
  fields.cvc.addEventListener('input', () => {
    fields.cvc.value = fields.cvc.value.replace(/\D/g, '').slice(0, 4);
  });

  function luhnValid(number) {
    let sum = 0;
    let double = false;
    for (let index = number.length - 1; index >= 0; index--) {
      let digit = Number(number[index]);
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
    if (!/^\d{13,19}$/.test(number) || !luhnValid(number)) errors.number = 'Enter a valid card number.';

    const expiry = fields.expiry.value.match(/^(\d{2}) \/ (\d{2})$/);
    if (!expiry || Number(expiry[1]) < 1 || Number(expiry[1]) > 12) {
      errors.expiry = 'Enter the expiry date as MM / YY.';
    } else {
      const expiryEnd = new Date(2000 + Number(expiry[2]), Number(expiry[1]), 0, 23, 59, 59);
      if (expiryEnd < new Date()) errors.expiry = 'This card has expired.';
    }

    if (!/^\d{3,4}$/.test(fields.cvc.value)) errors.cvc = 'Enter a 3- or 4-digit security code.';
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

  Object.values(fields).forEach((input) => input.addEventListener('input', () => {
    if (submitted) showErrors(validate());
  }));

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    submitted = true;
    error.style.display = 'none';
    const errors = validate();
    showErrors(errors);
    const firstInvalid = Object.keys(errors)[0];
    if (firstInvalid) { fields[firstInvalid].focus(); return; }

    submit.disabled = true;
    submit.textContent = 'Processing…';
    try {
      const result = await apiFetch(`/subscriptions/${subscriptionId}/pay`, {
        method: 'POST',
        body: JSON.stringify({ method: 'card' }),
      });
      form.reset();
      window.gpToast(result.data?.receiptNumber ? `Payment successful. Receipt ${result.data.receiptNumber}` : 'Payment successful.');
      window.location.href = `${BASE_URL_JS}/rider/subscription.php`;
    } catch (paymentError) {
      error.textContent = paymentError.message;
      error.style.display = '';
      submit.disabled = false;
      submit.textContent = 'Pay now';
    }
  });

  (async () => {
    if (!subscriptionId) { showUnavailable('No subscription was selected for payment.'); return; }
    try {
      await gp.session;
      const pending = (await apiFetch('/subscriptions/status')).data.pending;
      if (!pending || Number(pending.id) !== subscriptionId) {
        showUnavailable('This subscription is not awaiting payment.');
        return;
      }
      document.getElementById('subscriptionPaymentNumber').textContent = `#${pending.id}`;
      document.getElementById('subscriptionPaymentPlan').innerHTML = `<span>${gp.esc(pending.planName)} <span class="muted">· ${Number(pending.periodMonths)} ${Number(pending.periodMonths) === 1 ? 'month' : 'months'}</span></span><strong>${gp.money(pending.fee)}</strong>`;
      document.getElementById('subscriptionPaymentTotal').textContent = gp.money(pending.fee);
      loading.style.display = 'none';
      layout.style.display = '';
    } catch (loadError) {
      showUnavailable(loadError.message);
    }
  })();
});