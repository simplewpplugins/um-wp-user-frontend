/**
 * Tab settings screen: show only the settings that matter for the chosen
 * tab purpose, drive the taxonomy picker, and toggle the layout designer.
 */
(function () {
	'use strict';
	var modeSel = document.getElementById('umwpuf__mode');
	if (!modeSel) { return; }
	var dataEl = document.getElementById('umwpuf-admin-data');
	var data = dataEl ? JSON.parse(dataEl.textContent) : { forms: {}, savedFilters: [], postTaxonomies: {} };
	var formSel = document.getElementById('umwpuf__post_form');
	var layoutSel = document.getElementById('umwpuf__layout');
	var checked = (data.savedFilters || []).slice();
	// UM may enhance selects with select2, which fires jQuery-only change events.
	function onChange(el, fn) { if (!el) { return; } if (window.jQuery) { window.jQuery(el).on('change', fn); } else { el.addEventListener('change', fn); } }

	// Which modes use each field (field id => modes).
	var FIELDS = {
		umwpuf__post_form: ['submit', 'workspace'],
		umwpuf__layout: ['manage', 'public', 'workspace'],
		umwpuf__per_page: ['manage', 'public', 'workspace'],
		umwpuf__search: ['manage', 'public', 'workspace']
	};

	function line(id) {
		var input = document.getElementById(id) || document.querySelector('[name="umwpuf[' + id.replace('umwpuf_', '') + ']"]');
		return input ? input.closest('.um-forms-line') : null;
	}

	function renderTaxonomies() {
		var holder = document.getElementById('umwpuf-taxonomy-options');
		var empty = document.getElementById('umwpuf-taxonomy-empty');
		if (!holder) { return; }
		holder.querySelectorAll('input:checked').forEach(function (c) { if (checked.indexOf(c.value) === -1) { checked.push(c.value); } });
		holder.querySelectorAll('input:not(:checked)').forEach(function (c) { checked = checked.filter(function (v) { return v !== c.value; }); });
		var available = modeSel.value === 'workspace' ? ((data.forms[formSel && formSel.value] || {}).taxonomies || {}) : (data.postTaxonomies || {});
		var keys = Object.keys(available);
		holder.replaceChildren();
		keys.forEach(function (key) {
			var label = document.createElement('label');
			label.className = 'umwpuf-tax-option';
			var box = document.createElement('input');
			box.type = 'checkbox'; box.name = 'umwpuf_taxonomy_filters[]'; box.value = key;
			box.checked = checked.indexOf(key) !== -1;
			label.append(box, document.createTextNode(' ' + available[key]));
			holder.append(label);
		});
		if (empty) { empty.hidden = keys.length > 0; }
	}

	function update() {
		var mode = modeSel.value;
		Object.keys(FIELDS).forEach(function (id) {
			var row = line(id);
			if (row) { row.hidden = FIELDS[id].indexOf(mode) === -1; row.style.display = row.hidden ? 'none' : ''; }
		});
		document.querySelectorAll('[data-umwpuf-show]').forEach(function (node) {
			node.hidden = node.dataset.umwpufShow.split(' ').indexOf(mode) === -1;
		});
		var box = document.getElementById('umwpuf-layout-designer');
		var designer = document.getElementById('umwpuf-designer');
		var off = document.getElementById('umwpuf-designer-off');
		if (box) { box.hidden = mode === 'submit'; box.style.display = mode === 'submit' ? 'none' : ''; }
		if (designer && off) {
			var custom = layoutSel && layoutSel.value === 'custom';
			var wasHidden = designer.hidden;
			designer.hidden = !custom;
			off.hidden = custom;
			if (custom && wasHidden) { document.dispatchEvent(new CustomEvent('umwpuf:designer-shown')); }
		}
		renderTaxonomies();
	}

	onChange(modeSel, update);
	onChange(formSel, renderTaxonomies);
	if (layoutSel) {
		onChange(layoutSel, function () {
			update();
			if (layoutSel.value === 'custom') {
				var box = document.getElementById('umwpuf-layout-designer');
				if (box) { box.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
			}
		});
	}

	var icon = document.getElementById('umwpuf__tabicon');
	if (icon) {
		onChange(icon, function () {
			var i = document.querySelector('#umwpuf-icon-preview i');
			if (i) { i.className = icon.value; }
		});
	}
	update();
}());
