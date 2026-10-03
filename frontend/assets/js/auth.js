document.addEventListener('DOMContentLoaded', () => {
  const roleInputs = document.querySelectorAll('input[name="role"]');
  if (roleInputs.length) {
    const sync = () => {
      const role = document.querySelector('input[name="role"]:checked')?.value || 'buyer';
      document.querySelectorAll('[data-role-section]').forEach((section) => {
        const roles = section.dataset.roleSection.split(',');
        const show = roles.includes(role);
        section.style.display = show ? '' : 'none';
        section.querySelectorAll('[data-required-for-role]').forEach((field) => {
          field.required = show;
        });
      });
    };
    roleInputs.forEach((i) => i.addEventListener('change', sync));
    sync();
  }

  document.querySelectorAll('.radio-card-group').forEach((group) => {
    const sync = () => {
      group.querySelectorAll('.radio-card').forEach((card) => {
        const input = card.querySelector('input[type="radio"]');
        card.classList.toggle('selected', Boolean(input?.checked));
      });
    };
    group.addEventListener('change', sync);
    sync();
  });

  const PATTERNS = {
    phone: /^(?:\+94|0)?7\d{1}[\s-]?\d{3}[\s-]?\d{4}$/,
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    nic: /^(?:\d{9}[VvXx]|\d{12})$/,
  };
  const MESSAGES = {
    phone: 'Enter a valid Sri Lankan mobile number, e.g. 077 123 4567',
    email: 'Enter a valid email address',
    nic: 'Enter a valid NIC — 9 digits + V/X (old) or 12 digits (new)',
  };

  function fieldError(field, message) {
    field.style.borderColor = 'var(--rust)';
    let hint = field.parentElement.querySelector('.field-error');
    if (!hint) {
      hint = document.createElement('div');
      hint.className = 'field-error';
      hint.style.cssText = 'color:var(--rust);font-size:12px;margin-top:4px;';
      field.insertAdjacentElement('afterend', hint);
    }
    hint.textContent = message;
  }
  function clearFieldError(field) {
    field.style.borderColor = '';
    const hint = field.parentElement.querySelector('.field-error');
    if (hint) hint.remove();
  }

  function runValidation(form) {
    let ok = true;
    const validatedRadioGroups = new Set();

    form.querySelectorAll('[required]').forEach((field) => {
      if (field.offsetParent === null) return;
      if (field.type === 'radio') {
        if (validatedRadioGroups.has(field.name)) return;
        validatedRadioGroups.add(field.name);
        const selected = Array.from(form.querySelectorAll('input[type="radio"]'))
          .some((radio) => radio.name === field.name && radio.offsetParent !== null && radio.checked);
        const group = field.closest('.radio-card-group') || field;
        if (!selected) {
          ok = false;
          fieldError(group, 'Choose an option');
        } else {
          clearFieldError(group);
        }
        return;
      }
      if (!field.value.trim()) {
        ok = false;
        fieldError(field, 'This field is required');
      } else {
        clearFieldError(field);
      }
    });

    form.querySelectorAll('[data-format]').forEach((field) => {
      if (field.offsetParent === null || !field.value.trim()) return;
      const pattern = PATTERNS[field.dataset.format];
      if (pattern && !pattern.test(field.value.trim())) {
        ok = false;
        fieldError(field, MESSAGES[field.dataset.format]);
      } else {
        clearFieldError(field);
      }
    });

    if (form.id === 'loginForm') {
      const identifier = form.querySelector('[name="identifier"]');
      if (identifier && identifier.value.trim()) {
        const value = identifier.value.trim();
        if (!PATTERNS.email.test(value) && !PATTERNS.phone.test(value)) {
          ok = false;
          fieldError(identifier, 'Enter a valid email address or Sri Lankan mobile number');
        } else {
          clearFieldError(identifier);
        }
      }
    }

    const pw = form.querySelector('[name="password"]');
    const confirmPw = form.querySelector('[name="confirm_password"]');
    if (pw && confirmPw && confirmPw.value) {
      if (pw.value !== confirmPw.value) {
        ok = false;
        fieldError(confirmPw, 'Passwords do not match');
      } else {
        clearFieldError(confirmPw);
      }
    }

    return ok;
  }

  async function handleLogin(form) {
    const errorEl = document.getElementById('loginError');
    errorEl.style.display = 'none';

    const identifier = document.getElementById('identifier').value;
    const password = document.getElementById('password').value;

    try {
      const result = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({ identifier: identifier, password }),
      });

      localStorage.setItem('token', result.data.token);
      window.location.href = `${BASE_URL_JS}/${result.data.user.role}/dashboard.php`;
    } catch (err) {
      errorEl.textContent = err.message;
      errorEl.style.display = 'block';
    }
  }

  async function handleRegister(form) {
    const errorEl = document.getElementById('registerError');
    errorEl.style.display = 'none';

    const role = form.querySelector('input[name="role"]:checked').value;
    const preferredLang = form.querySelector('input[name="preferred_lang"]:checked')?.value;
    const get = (name) => form.querySelector(`[name="${name}"]`)?.value || null;

    const payload = {
      full_name: get('full_name'),
      email: get('email') || null,
      phone: get('phone'),
      password: get('password'),
      password_confirmation: get('confirm_password'),
      preferred_lang: preferredLang,
      role,
    };

    if (role === 'farmer') {
      payload.nic = get('nic_number');
      payload.address = get('farm_location');
    } else if (role === 'buyer') {
      payload.business_name = get('business_name');
      payload.business_type = get('business_type');
    } else if (role === 'rider') {
      payload.category = form.querySelector('input[name="category"]:checked')?.value;
      payload.vehicles = [
        { vehicle_type: get('vehicle_type'), vehicle_no: get('vehicle_number') },
      ];
    }

    try {
      const result = await apiFetch('/register', {
        method: 'POST',
        body: JSON.stringify(payload),
      });

      if (result.data.token) {
        localStorage.setItem('token', result.data.token);
        window.location.href = `${BASE_URL_JS}/${result.data.user.role}/dashboard.php`;
      } else {
        window.location.href = `${BASE_URL_JS}/auth/pending_verification.php`;
      }
    } catch (err) {
      errorEl.textContent = err.message;
      errorEl.style.display = 'block';
    }
  }

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.noValidate = true;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!runValidation(form)) return;

      if (form.id === 'loginForm') {
        await handleLogin(form);
      } else if (form.id === 'registerForm') {
        await handleRegister(form);
      }
    });
  });
});