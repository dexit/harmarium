/* Commission form — front-end submit handler */
(function () {
  'use strict';

  const forms = document.querySelectorAll('[data-hm-commission]');
  if (!forms.length) return;

  const cfg = window.HarmariumCommission || {};

  forms.forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }

      const btn      = form.querySelector('[type="submit"]');
      const label    = form.querySelector('.hm-commission-form__label');
      const spinner  = form.querySelector('.hm-commission-form__spinner');
      const feedback = form.querySelector('.hm-commission-form__feedback');

      btn.disabled = true;
      spinner.hidden = false;
      feedback.textContent = '';
      feedback.className = 'hm-commission-form__feedback';

      const data = Object.fromEntries(new FormData(form));
      data.nonce = cfg.nonce || '';

      try {
        const res = await fetch(cfg.restUrl || '/wp-json/harmarium/v1/commission', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce || '' },
          body:    JSON.stringify(data),
        });
        const json = await res.json();
        if (res.ok) {
          form.reset();
          feedback.textContent = json.message || 'Thank you — your request has been received.';
          feedback.classList.add('is-success');
        } else {
          feedback.textContent = json.message || 'Something went wrong. Please try again.';
          feedback.classList.add('is-error');
        }
      } catch {
        feedback.textContent = 'Network error — please check your connection and try again.';
        feedback.classList.add('is-error');
      } finally {
        btn.disabled = false;
        spinner.hidden = true;
      }
    });
  });
})();
