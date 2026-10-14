/**
 * Govi Paura — profile.js
 * Loaded by footer.php via $pageScript = 'profile' on all three portal profile pages.
 * Relies on apiFetch() and gp.esc() already being in scope from api.js / main.js.
 */
document.addEventListener('DOMContentLoaded', () => {
  const { esc } = gp;

  const profileForm    = document.getElementById('profileForm');
  const profileMessage = document.getElementById('profileMessage');

  if (!profileForm) return;

  /* ── helpers ─────────────────────────────────────────────────────── */

  function showMessage(text, isError = false) {
    if (!profileMessage) return;
    profileMessage.textContent = text;
    profileMessage.className   = 'form-message ' + (isError ? 'form-message--error' : 'form-message--success');
    profileMessage.style.display = '';
  }

  function hideMessage() {
    if (profileMessage) profileMessage.style.display = 'none';
  }

  /** Set the value of a named form field (works for inputs, textareas, and selects). */
  function setField(name, value) {
    const field = profileForm.elements.namedItem(name);
    if (field && value != null) {
      field.value = value;
    }
  }

  /* ── load & autofill ─────────────────────────────────────────────── */

  async function loadProfile() {
    try {
      await gp.session;  // wait for the session / token to be confirmed
      const result  = await apiFetch('/profile');
      const profile = result.data;

      // Fill every key the API returned into the matching form field
      for (const [name, value] of Object.entries(profile)) {
        setField(name, value);
      }

      // Update the display name shown in the page header (if the element exists)
      const displayName = document.getElementById('profileDisplayName');
      if (displayName && profile.full_name) {
        displayName.textContent = profile.full_name;
      }

      // Update the business / region subheading (if present)
      const business = document.getElementById('profileBusiness');
      if (business) {
        business.textContent = profile.business_name || profile.region || '';
      }
    } catch (err) {
      showMessage(err.message, true);
    }
  }

  /* ── save ────────────────────────────────────────────────────────── */

  // Fields each role is allowed to send — mirrors backend validation
  const ALLOWED_FIELDS = {
    buyer:  ['full_name', 'business_name', 'business_type', 'address', 'region'],
    farmer: ['full_name', 'address', 'region'],
    rider:  ['full_name', 'category', 'region'],
  };

  profileForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideMessage();

    const role      = profileForm.dataset.role;
    const permitted = ALLOWED_FIELDS[role];

    if (!permitted) {
      showMessage('Unsupported profile type.', true);
      return;
    }

    // Build payload from FormData, keeping only the permitted keys
    const raw     = Object.fromEntries(new FormData(profileForm).entries());
    const payload = Object.fromEntries(permitted.map((key) => [key, raw[key] ?? '']));

    const saveBtn = profileForm.querySelector('[type="submit"]');
    if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving…'; }

    try {
      const result = await apiFetch('/profile', {
        method: 'PATCH',
        body:   JSON.stringify(payload),
      });
      showMessage(result.message || 'Profile updated successfully.');
      await loadProfile();   // re-autofill so the heading etc. refresh
    } catch (err) {
      showMessage(err.message, true);
    } finally {
      if (saveBtn) {
        saveBtn.disabled    = false;
        saveBtn.textContent = 'Save Changes';
      }
    }
  });

  /* ── init ────────────────────────────────────────────────────────── */
  loadProfile();
});