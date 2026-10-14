document.addEventListener('DOMContentLoaded', async () => {
  const phoneLink = document.getElementById('helpdeskPhoneLink');
  const emailLink = document.getElementById('helpdeskEmailLink');
  const phoneLinks = document.querySelectorAll('[data-support-phone]');
  if (!phoneLink || !emailLink || !phoneLinks.length) return;

  try {
    const { data } = await apiFetch('/support-contact');
    const telephoneUrl = `tel:${data.phone.replace(/[^+\d]/g, '')}`;
    phoneLinks.forEach((link) => { link.href = telephoneUrl; });
    phoneLink.textContent = data.phone;
    emailLink.textContent = data.email;
    emailLink.href = `mailto:${data.email}`;
  } catch (err) {
    window.gpToast('Could not load support contact details: ' + err.message, 'error');
  }
});