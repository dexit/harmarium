/* Commission form widget — AJAX submit, no jQuery dependency */
(function () {
	'use strict';

	function initForm(form) {
		var restUrl    = form.dataset.restUrl;
		var nonce      = form.dataset.nonce;
		var successMsg = form.dataset.success;
		var errBox     = form.querySelector('.hm-commission-form__errors');
		var successBox = form.closest('.hm-commission-form-wrap').querySelector('.hm-commission-form__success');
		var submit     = form.querySelector('.hm-commission-form__submit');

		function showError(msg) {
			errBox.textContent = msg;
			errBox.style.display = '';
			errBox.focus();
		}

		function clearError() {
			errBox.style.display = 'none';
			errBox.textContent   = '';
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			clearError();

			var data = {
				name:     form.querySelector('[name="name"]').value.trim(),
				email:    form.querySelector('[name="email"]').value.trim(),
				subject:  form.querySelector('[name="subject"]').value.trim(),
				message:  form.querySelector('[name="message"]').value.trim(),
				budget:   form.querySelector('[name="budget"]').value,
				timeline: form.querySelector('[name="timeline"]').value,
				nonce:    nonce,
				utm_source:   (window._hmUtm && window._hmUtm.source)   || '',
				utm_medium:   (window._hmUtm && window._hmUtm.medium)   || '',
				utm_campaign: (window._hmUtm && window._hmUtm.campaign) || '',
				referrer:     document.referrer || '',
				landing_page: window._hmUtm && window._hmUtm.landing || '',
			};

			if (!data.name || !data.email || !data.subject || !data.message) {
				showError('Please fill in all required fields.');
				return;
			}

			submit.setAttribute('aria-busy', 'true');
			submit.disabled = true;

			fetch(restUrl, {
				method:  'POST',
				headers: { 'Content-Type': 'application/json' },
				body:    JSON.stringify(data),
			})
				.then(function (res) { return res.json().then(function (json) { return { status: res.status, json: json }; }); })
				.then(function (r) {
					if (r.status === 201) {
						form.style.display = 'none';
						successBox.style.display = '';
						successBox.textContent   = successMsg || r.json.message || 'Thank you!';
						successBox.focus();
					} else {
						showError(r.json.message || 'Something went wrong. Please try again.');
						submit.setAttribute('aria-busy', 'false');
						submit.disabled = false;
					}
				})
				.catch(function () {
					showError('Network error. Please check your connection and try again.');
					submit.setAttribute('aria-busy', 'false');
					submit.disabled = false;
				});
		});
	}

	function init() {
		document.querySelectorAll('.hm-commission-form').forEach(initForm);
	}

	if (window.elementorFrontend) {
		window.addEventListener('elementor/frontend/init', init);
	} else {
		document.addEventListener('DOMContentLoaded', init);
	}

	/* Capture UTM params on first page load */
	(function captureUtm() {
		var p    = new URLSearchParams(window.location.search);
		var keys = ['source','medium','campaign','content','term'];
		var utms = {};
		var found = false;
		keys.forEach(function(k) {
			var v = p.get('utm_' + k);
			if (v) { utms[k] = v; found = true; }
		});
		if (found) {
			utms.landing = window.location.pathname;
			window._hmUtm = utms;
			try { sessionStorage.setItem('_hmUtm', JSON.stringify(utms)); } catch(e) {}
		} else {
			try {
				var stored = sessionStorage.getItem('_hmUtm');
				if (stored) window._hmUtm = JSON.parse(stored);
			} catch(e) {}
		}
	})();
})();
