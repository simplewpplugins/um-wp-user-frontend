(function () {
	'use strict';
	var cfg = window.umwpufDashboard || {};
	var i18n = cfg.i18n || {};

	// Saved/added notice: dismissible, and removed from the URL so a refresh doesn't repeat it.
	document.querySelectorAll('.umwpuf-success').forEach(function (notice) {
		var btn = notice.querySelector('.umwpuf-dismiss');
		if (btn) { btn.addEventListener('click', function () { notice.remove(); }); }
	});
	if (window.history && history.replaceState && /[?&]umwpuf_notice=/.test(location.search)) {
		var url = new URL(location.href);
		url.searchParams.delete('umwpuf_notice');
		history.replaceState(history.state, '', url.toString());
	}

	document.querySelectorAll('.umwpuf-dashboard').forEach(function (root) {
		var results = root.querySelector('.umwpuf-results');
		if (!results) { return; }
		var search = root.querySelector('.umwpuf-search');
		var filters = root.querySelectorAll('.umwpuf-taxonomy-filter');
		var reset = root.querySelector('.umwpuf-reset');
		var controller = null, timer = null, activeTaxonomy = '', activeTerm = 0, lastPage = 1;

		function isFiltered() {
			var any = (search && search.value.trim() !== '') || activeTerm > 0;
			filters.forEach(function (el) { if (el.value !== '0') { any = true; } });
			return any;
		}
		function updateReset() { if (reset) { reset.hidden = !isFiltered(); } }

		function load(page, scroll) {
			lastPage = page;
			if (controller) { controller.abort(); }
			controller = new AbortController();
			var request = controller;
			results.setAttribute('aria-busy', 'true');
			root.classList.add('umwpuf-loading');
			var data = new URLSearchParams({
				action: 'umwpuf_posts', nonce: cfg.nonce, tab: root.dataset.tab, user: root.dataset.user,
				page: String(page), search: search ? search.value : '', taxonomy: activeTaxonomy, term: String(activeTerm)
			});
			filters.forEach(function (el) { data.append('filters[' + el.dataset.taxonomy + ']', el.value); });
			updateReset();
			fetch(cfg.ajax, { method: 'POST', body: data, credentials: 'same-origin', signal: request.signal })
				.then(function (r) { return r.json().then(function (json) { return { ok: r.ok, json: json }; }); })
				.then(function (res) {
					if (!res.ok || !res.json.success || typeof res.json.data.html !== 'string') {
						throw new Error(res.json && res.json.data && res.json.data.message ? res.json.data.message : '');
					}
					results.innerHTML = res.json.data.html;
					if (scroll) {
						var top = root.getBoundingClientRect().top;
						if (top < 0) { root.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
						var count = results.querySelector('.umwpuf-result-count');
						if (count) { count.setAttribute('tabindex', '-1'); count.focus({ preventScroll: true }); }
					}
				})
				.catch(function (e) {
					if (e.name === 'AbortError') { return; }
					results.innerHTML = '';
					var box = document.createElement('div');
					box.className = 'umwpuf-error'; box.setAttribute('role', 'alert');
					var msg = document.createElement('span');
					msg.textContent = e.message || i18n.error || 'Unable to load entries. Please try again.';
					var retry = document.createElement('button');
					retry.type = 'button'; retry.textContent = i18n.retry || 'Retry';
					retry.addEventListener('click', function () { load(lastPage, false); });
					box.append(msg, retry);
					results.appendChild(box);
				})
				.finally(function () {
					if (!request.signal.aborted) { results.setAttribute('aria-busy', 'false'); root.classList.remove('umwpuf-loading'); }
				});
		}

		if (search) {
			search.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { activeTaxonomy = ''; activeTerm = 0; load(1, false); }, 300); });
			search.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); clearTimeout(timer); load(1, false); } });
		}
		filters.forEach(function (el) { el.addEventListener('change', function () { activeTaxonomy = ''; activeTerm = 0; load(1, false); }); });
		if (reset) {
			reset.addEventListener('click', function () {
				if (search) { search.value = ''; }
				filters.forEach(function (el) { el.value = '0'; });
				activeTaxonomy = ''; activeTerm = 0;
				load(1, false);
				(search || filters[0] || results).focus();
			});
		}
		root.addEventListener('click', function (e) {
			var termLink = e.target.closest('[data-umwpuf-taxonomy][data-umwpuf-term]');
			if (termLink && root.contains(termLink)) {
				e.preventDefault();
				activeTaxonomy = termLink.dataset.umwpufTaxonomy;
				activeTerm = parseInt(termLink.dataset.umwpufTerm, 10) || 0;
				// Reflect the badge choice in the matching dropdown when one exists.
				filters.forEach(function (el) {
					if (el.dataset.taxonomy === activeTaxonomy && el.querySelector('option[value="' + activeTerm + '"]')) {
						el.value = String(activeTerm); activeTaxonomy = ''; activeTerm = 0;
					}
				});
				load(1, true);
				return;
			}
			var btn = e.target.closest('.umwpuf-pagination button');
			if (btn && root.contains(btn) && !btn.disabled && !btn.hasAttribute('aria-current')) {
				e.preventDefault();
				load(parseInt(btn.dataset.page, 10) || 1, true);
			}
		});
	});
}());
