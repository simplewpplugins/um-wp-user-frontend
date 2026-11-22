/**
 * Custom Layout Designer for WP User Frontend Integration for Ultimate Member.
 *
 * Model: { version: 2, card: {...}, rows: [ { widths: [fr|0=auto], columns: [ { elements: [...] } ], ... } ] }
 * The canvas uses the same classes and inline styles as the PHP renderer
 * (includes/class-loop-builder.php); keep styles.* below in sync with it.
 */
(function () {
	'use strict';

	var root = document.getElementById('umwpuf-designer');
	if (!root) { return; }

	var field = root.querySelector('input[name="umwpuf_loop_builder"]');
	var boot = JSON.parse(document.getElementById('umwpuf-designer-data').textContent);
	var adminEl = document.getElementById('umwpuf-admin-data');
	var adminData = adminEl ? JSON.parse(adminEl.textContent) : { forms: {}, postTaxonomies: {} };

	function t(s) { return (boot.strings && boot.strings[s]) || s; }
	function esc(s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
		});
	}
	function clone(o) { return JSON.parse(JSON.stringify(o)); }
	/** Mirrors PHP: is_numeric() ? clamp((int) v) : fallback. */
	function int(v, min, max, fallback) {
		if (v === '' || v === null || v === undefined || typeof v === 'boolean' || !isFinite(Number(v))) { return fallback; }
		return Math.max(min, Math.min(max, Math.trunc(Number(v))));
	}
	function color(v, fallback) { return typeof v === 'string' && /^#([0-9a-f]{3}){1,2}$/i.test(v) ? v : fallback; } // Same as sanitize_hex_color(): case kept.
	function choice(v, allowed, fallback) { return allowed.indexOf(v) !== -1 ? v : fallback; }

	/* ------------------------------------------------------------------
	 * Element catalogue
	 * ---------------------------------------------------------------- */
	var TYPES = {
		title: { label: t('Title'), icon: 'H', group: 'content' },
		excerpt: { label: t('Excerpt'), icon: '¶', group: 'content' },
		meta: { label: t('Custom field'), icon: '⌗', group: 'content' },
		taxonomy: { label: t('Taxonomy terms'), icon: '#', group: 'content' },
		text: { label: t('Static text'), icon: 'T', group: 'content' },
		image: { label: t('Featured image'), icon: '▧', group: 'media' },
		avatar: { label: t('Author avatar'), icon: '◉', group: 'media' },
		date: { label: t('Publish date'), icon: '◷', group: 'info' },
		modified: { label: t('Last updated'), icon: '↻', group: 'info' },
		author: { label: t('Author name'), icon: '☺', group: 'info' },
		status: { label: t('Status (owner only)'), icon: '●', group: 'info' },
		comments: { label: t('Comment count'), icon: '❝', group: 'info' },
		button: { label: t('Button'), icon: '▭', group: 'layout' },
		divider: { label: t('Divider'), icon: '—', group: 'layout' },
		spacer: { label: t('Spacer'), icon: '↕', group: 'layout' }
	};
	var GROUPS = [['content', t('Content')], ['media', t('Media')], ['info', t('Details')], ['layout', t('Layout & actions')]];
	var LAYOUTS = [[1], [1, 1], [1, 2], [2, 1], [1, 3], [3, 1], [0, 1], [1, 0], [1, 1, 1], [1, 2, 1], [0, 1, 0], [1, 1, 1, 1]];
	var LIMITS = { rows: 12, columns: 4, elements: 40 };
	var DEVICES = { desktop: { width: 1040, label: t('Desktop') }, tablet: { width: 720, label: t('Tablet') }, mobile: { width: 360, label: t('Phone') } };

	var STYLE_DEFAULTS = { color: '', background: '', border_color: '', size: 16, weight: 400, padding: 0, margin: 0, radius: 0, border_width: 0, gap: 8, align: 'left', shadow: 'none' };
	var STYLE_BY_TYPE = {
		title: { size: 19, weight: 700 }, date: { size: 13 }, modified: { size: 13 }, excerpt: { size: 14 },
		author: { size: 14, weight: 600 }, avatar: { size: 40 }, status: { size: 12 }, comments: { size: 13 },
		taxonomy: { size: 13 }, meta: { size: 14 }, button: { size: 14, weight: 600 }, divider: { border_width: 1, border_color: '#e5e7eb' }
	};

	function elementDefaults(type) {
		return {
			type: type, label: '', key: '',
			link: ['image', 'title', 'button'].indexOf(type) !== -1 ? 'post' : 'none',
			text: '', words: 25, tag: 'h3', ratio: 'auto', placeholder: false, height: 16,
			style: Object.assign({}, STYLE_DEFAULTS, STYLE_BY_TYPE[type] || {})
		};
	}
	function el(type, over) {
		var e = elementDefaults(type);
		Object.keys(over || {}).forEach(function (k) {
			if (k === 'style') { Object.assign(e.style, over.style); } else { e[k] = over[k]; }
		});
		return e;
	}
	function rowDefaults(widths) {
		return { widths: widths.slice(), gap: 12, valign: 'start', stack: 'narrow', padding: 0, radius: 0, background: '',
			columns: widths.map(function () { return { elements: [] }; }) };
	}
	function row(widths, columns, over) {
		var r = Object.assign(rowDefaults(widths), over || {});
		r.columns = widths.map(function (w, i) { return { elements: (columns[i] || []) }; });
		return r;
	}

	/** Client-side mirror of Loop_Builder::sanitize() so presets and old data stay valid. */
	function normalize(raw) {
		var d = clone(boot.defaults);
		if (!raw || typeof raw !== 'object') { return d; }
		var c = raw.card || {}, out = { version: 2, card: {}, rows: [] };
		['background', 'color', 'border_color'].forEach(function (k) { out.card[k] = color(c[k], d.card[k]); });
		var ranges = { border_width: [0, 12], radius: [0, 100], padding: [0, 120], gap: [0, 100], columns: [1, 6], columns_tablet: [1, 4], columns_mobile: [1, 2], grid_gap: [0, 80] };
		Object.keys(ranges).forEach(function (k) { out.card[k] = int(c[k], ranges[k][0], ranges[k][1], d.card[k]); });
		out.card.columns_tablet = Math.min(out.card.columns_tablet, out.card.columns);
		out.card.columns_mobile = Math.min(out.card.columns_mobile, out.card.columns_tablet);
		out.card.shadow = choice(c.shadow, ['none', 'soft', 'medium', 'strong'], 'soft');
		out.card.hover = choice(c.hover, ['none', 'lift', 'glow'], 'none');
		var rows = Array.isArray(raw.rows) ? raw.rows : [{ widths: [1], columns: [{ elements: Array.isArray(raw.elements) ? raw.elements : [] }] }];
		var budget = LIMITS.elements;
		rows.slice(0, LIMITS.rows).forEach(function (r) {
			if (!r || typeof r !== 'object') { return; }
			var widths = (Array.isArray(r.widths) ? r.widths : [1]).slice(0, LIMITS.columns).map(function (w) { return int(w, 0, 12, 1); });
			if (!widths.length) { widths = [1]; }
			var nr = { widths: widths, gap: int(r.gap, 0, 80, 12), valign: choice(r.valign, ['start', 'center', 'end', 'stretch'], 'start'),
				stack: choice(r.stack, ['narrow', 'mobile', 'never'], 'narrow'), padding: int(r.padding, 0, 80, 0), radius: int(r.radius, 0, 60, 0),
				background: color(r.background, ''), columns: [] };
			widths.forEach(function (w, i) {
				var src = r.columns && r.columns[i] && Array.isArray(r.columns[i].elements) ? r.columns[i].elements : [];
				var list = [];
				src.forEach(function (item) {
					if (budget <= 0 || !item || !TYPES[item.type]) { return; }
					var base = elementDefaults(item.type), s = item.style || {}, ns = {};
					['color', 'background', 'border_color'].forEach(function (k) { ns[k] = color(s[k], k in s ? '' : base.style[k]); });
					var sr = { size: [9, 72], weight: [100, 900], padding: [0, 100], margin: [0, 100], radius: [0, 100], border_width: [0, 12], gap: [0, 100] };
					Object.keys(sr).forEach(function (k) { ns[k] = int(s[k], sr[k][0], sr[k][1], base.style[k]); });
					ns.weight = Math.round(ns.weight / 100) * 100;
					ns.align = choice(s.align, ['left', 'center', 'right'], 'left');
					ns.shadow = choice(s.shadow, ['none', 'soft', 'medium', 'strong'], 'none');
					list.push({ type: item.type, label: String(item.label || '').slice(0, 120), key: String(item.key || '').toLowerCase().replace(/[^a-z0-9_\-]/g, ''),
						link: choice(item.link, ['none', 'post', 'archive', 'filter'], base.link), text: String(item.text || '').slice(0, 200),
						words: int(item.words, 5, 80, 25), tag: choice(item.tag, ['h2', 'h3', 'h4', 'p', 'div'], 'h3'),
						ratio: choice(item.ratio, ['auto', '16/9', '4/3', '1/1', '3/4', '21/9'], 'auto'), placeholder: !!item.placeholder && item.placeholder !== 'false',
						height: int(item.height, 0, 200, 16), style: ns });
					budget--;
				});
				nr.columns.push({ elements: list });
			});
			out.rows.push(nr);
		});
		return out;
	}

	/* ------------------------------------------------------------------
	 * Style builders: keep identical to Loop_Builder::*_style() in PHP
	 * ---------------------------------------------------------------- */
	var CARD_SHADOWS = { none: 'none', soft: '0 4px 16px rgba(15,23,42,.06)', medium: '0 10px 28px rgba(15,23,42,.12)', strong: '0 18px 40px rgba(15,23,42,.2)' };
	var EL_SHADOWS = { none: 'none', soft: '0 3px 12px rgba(0,0,0,.08)', medium: '0 8px 22px rgba(0,0,0,.13)', strong: '0 12px 30px rgba(0,0,0,.2)' };
	var styles = {
		card: function (c) {
			return 'background:' + c.background + ';color:' + c.color + ';border:' + c.border_width + 'px solid ' + c.border_color + ';border-radius:' + c.radius + 'px;padding:' + c.padding + 'px;gap:' + c.gap + 'px;box-shadow:' + CARD_SHADOWS[c.shadow] + ';';
		},
		grid: function (c) {
			return '--umwpuf-columns:' + c.columns + ';--umwpuf-columns-tablet:' + c.columns_tablet + ';--umwpuf-columns-mobile:' + c.columns_mobile + ';--umwpuf-grid-gap:' + c.grid_gap + 'px;';
		},
		rowTemplate: function (widths) {
			return widths.map(function (w) { return w > 0 ? 'minmax(0,' + w + 'fr)' : 'auto'; }).join(' ');
		},
		row: function (r) {
			var out = '--umwpuf-row-cols:' + styles.rowTemplate(r.widths) + ';gap:' + r.gap + 'px;align-items:' + r.valign + ';';
			if (r.padding) { out += 'padding:' + r.padding + 'px;'; }
			if (r.radius) { out += 'border-radius:' + r.radius + 'px;'; }
			if (r.background) { out += 'background:' + r.background + ';'; }
			return out;
		},
		element: function (e) {
			var s = e.style;
			if (e.type === 'spacer') { return 'height:' + e.height + 'px;'; }
			if (e.type === 'divider') { return 'border-top:' + Math.max(1, s.border_width) + 'px solid ' + (s.border_color || 'currentColor') + ';margin:' + s.margin + 'px 0;'; }
			var out = 'font-size:' + s.size + 'px;font-weight:' + s.weight + ';padding:' + s.padding + 'px;margin:' + s.margin + 'px;border-radius:' + s.radius + 'px;border:' + s.border_width + 'px solid ' + (s.border_color || 'transparent') + ';text-align:' + s.align + ';gap:' + s.gap + 'px;';
			if (e.type === 'button') {
				if (s.color) { out += '--umwpuf-btn-color:' + s.color + ';'; }
				if (s.background) { out += '--umwpuf-btn-bg:' + s.background + ';'; }
			} else {
				if (s.color) { out += 'color:' + s.color + ';'; }
				if (s.background) { out += 'background:' + s.background + ';'; }
			}
			if (e.type === 'image' && e.ratio !== 'auto') { out += '--umwpuf-ratio:' + e.ratio.replace('/', ' / ') + ';'; }
			return out + 'box-shadow:' + EL_SHADOWS[s.shadow] + ';';
		}
	};
	// Exposed for automated parity tests against the PHP renderer.
	window.umwpufDesignerStyles = styles;
	window.umwpufDesignerNormalize = function (raw) { return normalize(raw); };

	/* ------------------------------------------------------------------
	 * State, selection, history
	 * ---------------------------------------------------------------- */
	var state;
	try { state = normalize(JSON.parse(field.value)); } catch (err) { state = normalize(null); }
	var savedJSON = JSON.stringify(state);
	var sel = { kind: 'card' };          // {kind:'card'} | {kind:'row', r, c} | {kind:'el', r, c, i}
	var ui = { device: 'desktop', tab: 'content', preview: 'sample', layoutMenu: null };
	var past = [], future = [], lastSnap = { key: '', at: 0 };

	function snapshot(key) {
		var now = Date.now();
		// Coalesce rapid edits of the same control (typing, spinner) into one undo step.
		if (key && key === lastSnap.key && now - lastSnap.at < 1000) { lastSnap.at = now; return; }
		lastSnap = { key: key || '', at: now };
		past.push(JSON.stringify({ state: state, sel: sel }));
		if (past.length > 100) { past.shift(); }
		future = [];
	}
	function undo() { if (!past.length) { return; } future.push(JSON.stringify({ state: state, sel: sel })); restore(past.pop()); }
	function redo() { if (!future.length) { return; } past.push(JSON.stringify({ state: state, sel: sel })); restore(future.pop()); }
	function restore(json) { var o = JSON.parse(json); state = o.state; sel = o.sel; lastSnap = { key: '', at: 0 }; renderAll(); }

	function countElements() { return state.rows.reduce(function (n, r) { return n + r.columns.reduce(function (m, c) { return m + c.elements.length; }, 0); }, 0); }
	function getRow() { return sel.kind !== 'card' ? state.rows[sel.r] : null; }
	function getEl() { return sel.kind === 'el' ? state.rows[sel.r].columns[sel.c].elements[sel.i] : null; }
	function validSel() {
		if (sel.kind === 'card') { return; }
		var r = state.rows[sel.r];
		if (!r) { sel = { kind: 'card' }; return; }
		if (sel.c === undefined || !r.columns[sel.c]) { sel.c = 0; }
		if (sel.kind === 'el' && !r.columns[sel.c].elements[sel.i]) { sel = { kind: 'row', r: sel.r, c: sel.c }; }
	}

	/* ------------------------------------------------------------------
	 * Context from the settings metabox (form, post type, mode)
	 * ---------------------------------------------------------------- */
	function formId() { var f = document.getElementById('umwpuf__post_form'); return f ? f.value : ''; }
	function mode() { var m = document.getElementById('umwpuf__mode'); return m ? m.value : 'workspace'; }
	function formInfo() { return adminData.forms[formId()] || { taxonomies: {}, fields: [] }; }
	function taxonomies() { return mode() === 'workspace' ? (formInfo().taxonomies || {}) : (adminData.postTaxonomies || {}); }
	function metaFields() { return mode() === 'workspace' ? (formInfo().fields || []) : []; }

	/* ------------------------------------------------------------------
	 * Sample content
	 * ---------------------------------------------------------------- */
	var SAMPLE_TITLES = [t('Sunny two-bedroom apartment'), t('Quiet cottage with a garden'), t('Modern loft in the old town'), t('Family house near the lake'), t('Studio with mountain views'), t('Renovated flat by the river')];
	var SAMPLE_TEXT = t('Bright corner unit with a balcony, wooden floors and an open kitchen. Close to shops, schools and the park, with easy access to public transport and plenty of natural light throughout the day.');
	function sampleImage(n) {
		var hue = [210, 160, 25, 280, 340, 190][n % 6];
		var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="hsl(' + hue + ',70%,78%)"/><stop offset="1" stop-color="hsl(' + (hue + 40) + ',60%,52%)"/></linearGradient></defs><rect width="800" height="500" fill="url(#g)"/><circle cx="610" cy="140" r="60" fill="rgba(255,255,255,.55)"/><path d="M0 400 L220 230 L380 360 L520 260 L800 430 L800 500 L0 500Z" fill="rgba(255,255,255,.35)"/></svg>';
		return 'data:image/svg+xml,' + encodeURIComponent(svg);
	}
	var AVATAR = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80"><rect width="80" height="80" fill="#6366f1"/><text x="40" y="52" font-family="sans-serif" font-size="30" fill="#fff" text-anchor="middle">AM</text></svg>');
	function trimWords(text, n) { var w = text.split(/\s+/); return w.length > n ? w.slice(0, n).join(' ') + '…' : text; }
	function sampleDate(offset) { var d = new Date(); d.setDate(d.getDate() - offset); return d.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }); }

	/** Inner HTML for one element, or null when it would render nothing on the frontend. */
	function sampleInner(e, n, editing) {
		var link = function (html) { return e.link === 'post' ? '<a href="#" tabindex="-1">' + html + '</a>' : html; };
		var label = e.label && e.type !== 'button' ? '<span class="umwpuf-loop-label">' + esc(e.label) + '</span> ' : '';
		var warn = function (msg) { return editing ? '<span class="umwpuf-d-warn">⚠ ' + esc(msg) + '</span>' : null; };
		switch (e.type) {
			case 'image': return link('<img src="' + sampleImage(n) + '" alt="">');
			case 'avatar': return '<img class="umwpuf-avatar" src="' + AVATAR + '" width="' + e.style.size + '" height="' + e.style.size + '" alt="">';
			case 'title': return '<' + e.tag + ' class="umwpuf-loop-heading">' + link(esc(SAMPLE_TITLES[n % SAMPLE_TITLES.length])) + '</' + e.tag + '>';
			case 'excerpt': return label + link(esc(trimWords(SAMPLE_TEXT, e.words)));
			case 'date': return label + link(esc(sampleDate(n + 2)));
			case 'modified': return label + link(esc(sampleDate(n)));
			case 'author': return label + link(esc('Alex Morgan'));
			case 'status': return label + link(esc(t('Published')));
			case 'comments': return label + link(esc((n % 4 + 1) + ' ' + t('comments')));
			case 'button': return link(esc(e.label || t('View post')));
			case 'text': return e.text ? label + link(esc(e.text)) : warn(t('Type some text'));
			case 'taxonomy': {
				var tax = taxonomies();
				if (!e.key) { return warn(t('Choose a taxonomy')); }
				if (!tax[e.key]) { return warn(t('Taxonomy not available for this form')); }
				var terms = [tax[e.key] + ' A', tax[e.key] + ' B'].slice(0, n % 2 + 1);
				return label + terms.map(function (term) { return e.link === 'none' ? '<span>' + esc(term) + '</span>' : '<a href="#" tabindex="-1">' + esc(term) + '</a>'; }).join('');
			}
			case 'meta': {
				if (!e.key) { return warn(t('Choose a custom field')); }
				var f = metaFields().filter(function (x) { return x.key === e.key; })[0];
				if (f && (f.type === 'image_upload' || f.type === 'file_upload')) { return label + '<img src="' + sampleImage(n + 3) + '" alt="">'; }
				var value = f && f.type === 'numeric_text' ? (125000 + n * 7500).toLocaleString() : (f && /date/.test(f.type) ? sampleDate(n + 30) : t('Sample text'));
				return label + link(esc(value));
			}
		}
		return null;
	}

	function elementHTML(e, n, editing, path) {
		var attrs = editing
			? ' data-r="' + path[0] + '" data-c="' + path[1] + '" data-i="' + path[2] + '" draggable="true" tabindex="0" role="button" aria-label="' + esc(TYPES[e.type].label) + '"'
			: '';
		var selected = editing && sel.kind === 'el' && sel.r === path[0] && sel.c === path[1] && sel.i === path[2];
		var cls = 'umwpuf-loop-element umwpuf-loop-' + e.type + (editing ? ' umwpuf-d-el' : '') + (selected ? ' is-selected' : '');
		if (e.type === 'divider') { return '<hr class="' + cls + '" style="' + esc(styles.element(e)) + '"' + attrs + '>'; }
		if (e.type === 'spacer') { return '<div class="' + cls + '" style="' + esc(styles.element(e)) + '"' + attrs + '></div>'; }
		var inner = sampleInner(e, n, editing);
		if (inner === null) { return ''; }
		if (e.type === 'image' && e.ratio !== 'auto') { cls += ' umwpuf-has-ratio'; }
		return '<div class="' + cls + '" style="' + esc(styles.element(e)) + '"' + attrs + '>' + inner + '</div>';
	}

	function cardHTML(n, editing) {
		var c = state.card;
		var html = '<article class="umwpuf-post umwpuf-custom-card umwpuf-entry-card' + (c.hover !== 'none' ? ' umwpuf-hover-' + c.hover : '') + (editing ? ' umwpuf-d-card' + (sel.kind === 'card' ? ' is-selected' : '') : '') + '" style="' + esc(styles.card(c)) + '">';
		state.rows.forEach(function (r, ri) {
			var cols = r.columns.map(function (col, ci) {
				var inner = col.elements.map(function (e, ei) { return elementHTML(e, n, editing, [ri, ci, ei]); }).join('');
				if (editing && !col.elements.length) { inner = '<div class="umwpuf-d-empty">' + esc(t('Drop elements here')) + '</div>'; }
				return { html: inner, empty: !col.elements.length };
			});
			if (!editing && cols.every(function (x) { return x.html.trim() === ''; })) { return; }
			var rowSel = editing && sel.kind !== 'card' && sel.r === ri;
			html += '<div class="umwpuf-row umwpuf-stack-' + r.stack + (r.columns.length > 1 ? ' umwpuf-row-multi' : '') + (editing ? ' umwpuf-d-row' + (rowSel ? ' is-selected' : '') : '') + '" style="' + esc(styles.row(r)) + '"' + (editing ? ' data-r="' + ri + '"' : '') + '>';
			if (editing) {
				html += '<div class="umwpuf-d-rowbar"><span>' + esc(t('Row')) + ' ' + (ri + 1) + '</span></div>';
			}
			cols.forEach(function (col, ci) {
				var colSel = rowSel && sel.c === ci;
				html += '<div class="umwpuf-col' + (editing ? ' umwpuf-d-col' + (colSel ? ' is-active' : '') : '') + '"' + (editing ? ' data-r="' + ri + '" data-c="' + ci + '"' : '') + '>' + col.html + '</div>';
			});
			html += '</div>';
		});
		if (editing && !state.rows.length) { html += '<div class="umwpuf-d-empty umwpuf-d-empty-card">' + esc(t('Add a row to start designing')) + '</div>'; }
		return html + '</article>';
	}

	/* ------------------------------------------------------------------
	 * Mutations
	 * ---------------------------------------------------------------- */
	function notify(msg) {
		var live = root.querySelector('.umwpuf-d-live');
		live.textContent = '';
		window.setTimeout(function () { live.textContent = msg; }, 30);
	}
	function insertTarget() {
		if (!state.rows.length) { state.rows.push(rowDefaults([1])); return { r: 0, c: 0, i: 0 }; }
		if (sel.kind === 'el') { return { r: sel.r, c: sel.c, i: sel.i + 1 }; }
		if (sel.kind === 'row') { return { r: sel.r, c: sel.c || 0, i: state.rows[sel.r].columns[sel.c || 0].elements.length }; }
		var r = state.rows.length - 1, c = state.rows[r].columns.length - 1;
		return { r: r, c: c, i: state.rows[r].columns[c].elements.length };
	}
	function addElement(type, target) {
		if (countElements() >= LIMITS.elements) { notify(t('Element limit reached (40).')); return; }
		snapshot();
		target = target || insertTarget();
		var e = elementDefaults(type);
		if (type === 'button') { e.label = t('View post'); }
		if (type === 'meta' && metaFields().length) { e.key = metaFields()[0].key; e.label = metaFields()[0].label; }
		if (type === 'taxonomy') { var k = Object.keys(taxonomies())[0]; if (k) { e.key = k; } }
		state.rows[target.r].columns[target.c].elements.splice(target.i, 0, e);
		sel = { kind: 'el', r: target.r, c: target.c, i: target.i };
		ui.tab = 'content';
		notify(TYPES[type].label + ' ' + t('added'));
		renderAll();
	}
	function moveElement(from, to) {
		var list = state.rows[from.r].columns[from.c].elements;
		var item = list.splice(from.i, 1)[0];
		var dest = state.rows[to.r].columns[to.c].elements;
		var i = to.i;
		if (from.r === to.r && from.c === to.c && from.i < to.i) { i--; }
		i = Math.max(0, Math.min(dest.length, i));
		dest.splice(i, 0, item);
		sel = { kind: 'el', r: to.r, c: to.c, i: i };
	}
	function setRowLayout(r, widths) {
		snapshot();
		var rowObj = state.rows[r];
		var cols = rowObj.columns;
		// Never lose elements: when columns are removed, their content moves to the last kept column.
		while (cols.length > widths.length) {
			var removed = cols.pop();
			cols[cols.length - 1].elements = cols[cols.length - 1].elements.concat(removed.elements);
		}
		while (cols.length < widths.length) { cols.push({ elements: [] }); }
		rowObj.widths = widths.slice();
		sel = { kind: 'row', r: r, c: Math.min(sel.c || 0, widths.length - 1) };
		ui.layoutMenu = null;
		renderAll();
	}

	/* ------------------------------------------------------------------
	 * Presets
	 * ---------------------------------------------------------------- */
	function presets() {
		var f = metaFields(), tax = Object.keys(taxonomies())[0] || '';
		var f1 = f[0] ? f[0].key : '', f2 = f[1] ? f[1].key : '';
		var l1 = f[0] ? f[0].label : '', l2 = f[1] ? f[1].label : '';
		var base = clone(boot.defaults.card);
		return {
			classic: { label: t('Classic card'), build: function () { return clone(boot.defaults); } },
			horizontal: { label: t('Horizontal (image beside text)'), build: function () {
				return { card: Object.assign(base, { columns: 1, columns_tablet: 1, columns_mobile: 1 }), rows: [
					row([2, 3], [[el('image', { ratio: '4/3', placeholder: true, style: { radius: 10 } })], [el('title'), el('date'), el('excerpt', { words: 18 }), el('button', { label: t('View post') })]], { valign: 'center', gap: 18 })
				] };
			} },
			listing: { label: t('Listing with details'), build: function () {
				return { card: Object.assign(base, { padding: 0, gap: 0, radius: 16 }), rows: [
					row([1], [[el('image', { ratio: '16/9', placeholder: true })]]),
					row([1], [[el('taxonomy', { key: tax, style: { size: 12 } }), el('title', { style: { size: 18 } })]], { padding: 16 }),
					row([1, 1], [[el('meta', { key: f1, label: l1, style: { weight: 700 } })], [el('meta', { key: f2, label: l2, style: { align: 'right' } })]], { padding: 16 }),
					row([1, 0], [[el('date')], [el('button', { label: t('Details') })]], { valign: 'center', padding: 16, background: '#f8fafc' })
				] };
			} },
			author: { label: t('Author spotlight'), build: function () {
				return { card: base, rows: [
					row([0, 1], [[el('avatar', { style: { size: 44 } })], [el('author'), el('date')]], { valign: 'center', stack: 'never', gap: 10 }),
					row([1], [[el('title'), el('excerpt', { words: 20 })]]),
					row([1, 0], [[el('comments')], [el('button', { label: t('Read more') })]], { valign: 'center', stack: 'never' })
				] };
			} },
			compact: { label: t('Compact list rows'), build: function () {
				return { card: Object.assign(base, { columns: 1, columns_tablet: 1, columns_mobile: 1, padding: 12, grid_gap: 12, shadow: 'none' }), rows: [
					row([1, 4, 0], [[el('image', { ratio: '1/1', placeholder: true, style: { radius: 8 } })], [el('title', { tag: 'h4', style: { size: 16 } }), el('date'), el('status')], [el('button', { label: t('Open') })]], { valign: 'center', stack: 'mobile', gap: 14 })
				] };
			} },
			minimal: { label: t('Minimal text'), build: function () {
				return { card: Object.assign(base, { border_width: 0, shadow: 'none', padding: 0, background: '#ffffff' }), rows: [
					row([1], [[el('date', { style: { size: 12, color: '#64748b' } }), el('title', { style: { size: 20 } }), el('excerpt', { words: 30 }), el('divider')]])
				] };
			} }
		};
	}

	/* ------------------------------------------------------------------
	 * Rendering: toolbar, palette, canvas, inspector, preview grid
	 * ---------------------------------------------------------------- */
	var $ = function (s) { return root.querySelector(s); };

	function renderToolbar() {
		$('[data-act="undo"]').disabled = !past.length;
		$('[data-act="redo"]').disabled = !future.length;
		root.querySelectorAll('[data-act="device"]').forEach(function (b) { b.setAttribute('aria-pressed', String(b.dataset.value === ui.device)); });
		root.querySelectorAll('[data-act="preview-mode"]').forEach(function (b) { b.setAttribute('aria-pressed', String(b.dataset.value === ui.preview)); });
	}

	function renderPalette() {
		var html = GROUPS.map(function (g) {
			var items = Object.keys(TYPES).filter(function (k) { return TYPES[k].group === g[0]; }).map(function (k) {
				return '<button type="button" class="umwpuf-d-chip" draggable="true" data-act="add" data-type="' + k + '" title="' + esc(t('Click to add, or drag onto the card')) + '"><span class="umwpuf-d-chip-icon" aria-hidden="true">' + esc(TYPES[k].icon) + '</span>' + esc(TYPES[k].label) + '</button>';
			}).join('');
			return '<div class="umwpuf-d-group"><h4>' + esc(g[1]) + '</h4>' + items + '</div>';
		}).join('');
		$('.umwpuf-d-palette-items').innerHTML = html;
	}

	function cardWidth(device) {
		var c = state.card, n = { desktop: c.columns, tablet: c.columns_tablet, mobile: c.columns_mobile }[device];
		return Math.floor((DEVICES[device].width - (n - 1) * c.grid_gap) / n);
	}

	function renderCanvas() {
		var stage = $('.umwpuf-d-stage');
		var w = cardWidth(ui.device);
		stage.style.width = w + 'px';
		stage.innerHTML = cardHTML(0, true);
		var c = state.card, n = { desktop: c.columns, tablet: c.columns_tablet, mobile: c.columns_mobile }[ui.device];
		$('.umwpuf-d-canvas-meta').textContent = DEVICES[ui.device].label + ' · ' + n + ' ' + (n === 1 ? t('card per row') : t('cards per row')) + ' · ' + t('card width') + ' ≈ ' + w + 'px';
		stage.className = 'umwpuf-d-stage umwpuf-sim-' + ui.device;
	}

	function layoutIcon(widths) {
		return '<span class="umwpuf-d-layout-icon" aria-hidden="true">' + widths.map(function (w) { return '<i style="flex:' + (w || 0.45) + '"></i>'; }).join('') + '</span>';
	}
	function layoutName(widths) { return widths.map(function (w) { return w ? w : t('auto'); }).join(' : '); }

	/* Control builders ------------------------------------------------- */
	function num(bind, label, value, min, max, unit) {
		return '<label class="umwpuf-d-field"><span>' + esc(label) + '</span><span class="umwpuf-d-num"><input type="number" data-bind="' + bind + '" data-type="int" min="' + min + '" max="' + max + '" value="' + esc(value) + '">' + (unit ? '<em>' + esc(unit) + '</em>' : '') + '</span></label>';
	}
	function sel_(bind, label, value, options) {
		return '<label class="umwpuf-d-field"><span>' + esc(label) + '</span><select data-bind="' + bind + '">' + options.map(function (o) {
			return '<option value="' + esc(o[0]) + '"' + (String(o[0]) === String(value) ? ' selected' : '') + '>' + esc(o[1]) + '</option>';
		}).join('') + '</select></label>';
	}
	function txt(bind, label, value, placeholder, list) {
		return '<label class="umwpuf-d-field umwpuf-d-wide"><span>' + esc(label) + '</span><input type="text" data-bind="' + bind + '" value="' + esc(value) + '" placeholder="' + esc(placeholder || '') + '"' + (list ? ' list="' + list + '"' : '') + ' maxlength="200"></label>';
	}
	function col_(bind, label, value, clearable) {
		return '<div class="umwpuf-d-field"><span>' + esc(label) + '</span><span class="umwpuf-d-color" data-empty="' + (!value) + '"><input type="color" data-bind="' + bind + '" data-type="color" value="' + esc(value || '#ffffff') + '" aria-label="' + esc(label) + '">' +
			(clearable ? '<button type="button" class="umwpuf-d-clear" data-act="clear" data-bind="' + bind + '" aria-label="' + esc(t('Clear') + ' ' + label) + '" title="' + esc(t('Use inherited colour')) + '">×</button>' : '') + '<code>' + esc(value || t('inherit')) + '</code></span></div>';
	}
	function check(bind, label, value) {
		return '<label class="umwpuf-d-check umwpuf-d-wide"><input type="checkbox" data-bind="' + bind + '" data-type="bool"' + (value ? ' checked' : '') + '> ' + esc(label) + '</label>';
	}
	function seg(bind, label, value, options) {
		return '<div class="umwpuf-d-field umwpuf-d-wide"><span>' + esc(label) + '</span><span class="umwpuf-d-seg" role="group" aria-label="' + esc(label) + '">' + options.map(function (o) {
			return '<button type="button" data-act="set" data-bind="' + bind + '" data-value="' + esc(o[0]) + '" aria-pressed="' + (o[0] === value) + '">' + esc(o[1]) + '</button>';
		}).join('') + '</span></div>';
	}
	function section(title, body, open) { return '<details class="umwpuf-d-section"' + (open ? ' open' : '') + '><summary>' + esc(title) + '</summary><div class="umwpuf-d-fields">' + body + '</div></details>'; }
	var SHADOW_OPTS = [['none', t('None')], ['soft', t('Soft')], ['medium', t('Medium')], ['strong', t('Strong')]];

	function breadcrumb() {
		var parts = ['<button type="button" data-act="select-card"' + (sel.kind === 'card' ? ' aria-current="true"' : '') + '>' + esc(t('Card')) + '</button>'];
		if (sel.kind !== 'card') {
			parts.push('<button type="button" data-act="select-row" data-r="' + sel.r + '"' + (sel.kind === 'row' ? ' aria-current="true"' : '') + '>' + esc(t('Row')) + ' ' + (sel.r + 1) + '</button>');
			if (state.rows[sel.r].columns.length > 1) { parts.push('<span>' + esc(t('Column')) + ' ' + (sel.c + 1) + '</span>'); }
		}
		if (sel.kind === 'el') { parts.push('<span aria-current="true">' + esc(TYPES[getEl().type].label) + '</span>'); }
		return '<nav class="umwpuf-d-crumbs" aria-label="' + esc(t('Selection')) + '">' + parts.join('<i aria-hidden="true">›</i>') + '</nav>';
	}

	function inspectCard() {
		var c = state.card;
		return section(t('Grid'), num('card.columns', t('Desktop columns'), c.columns, 1, 6) + num('card.columns_tablet', t('Tablet columns'), c.columns_tablet, 1, 4) + num('card.columns_mobile', t('Phone columns'), c.columns_mobile, 1, 2) + num('card.grid_gap', t('Space between cards'), c.grid_gap, 0, 80, 'px'), true) +
			section(t('Card style'), col_('card.background', t('Background'), c.background) + col_('card.color', t('Text colour'), c.color) + col_('card.border_color', t('Border colour'), c.border_color) + num('card.border_width', t('Border width'), c.border_width, 0, 12, 'px') +
				num('card.radius', t('Corner radius'), c.radius, 0, 100, 'px') + num('card.padding', t('Padding'), c.padding, 0, 120, 'px') + num('card.gap', t('Space between rows'), c.gap, 0, 100, 'px') +
				sel_('card.shadow', t('Shadow'), c.shadow, SHADOW_OPTS) + sel_('card.hover', t('Hover effect'), c.hover, [['none', t('None')], ['lift', t('Lift')], ['glow', t('Glow')]]), true) +
			'<p class="umwpuf-d-hint">' + esc(t('Tip: click a row or element on the card to edit it. Drag elements from the left between columns.')) + '</p>';
	}

	function inspectRow() {
		var r = getRow();
		var layouts = '<div class="umwpuf-d-layouts" role="group" aria-label="' + esc(t('Column layout')) + '">' + LAYOUTS.map(function (w) {
			return '<button type="button" data-act="row-layout" data-r="' + sel.r + '" data-widths="' + w.join(',') + '" aria-pressed="' + (w.join(',') === r.widths.join(',')) + '" title="' + esc(layoutName(w)) + '">' + layoutIcon(w) + '</button>';
		}).join('') + '</div>';
		var widthOpts = [[0, t('Auto (fit content)')]].concat([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(function (n) { return [n, n + ' fr']; }));
		var widths = r.widths.length > 1 ? r.widths.map(function (w, i) { return sel_('row.widths.' + i, t('Column') + ' ' + (i + 1) + ' ' + t('width'), w, widthOpts); }).join('') : '';
		return '<div class="umwpuf-d-actions">' + actionBtn('row-up', '↑', t('Move row up'), sel.r === 0) + actionBtn('row-down', '↓', t('Move row down'), sel.r === state.rows.length - 1) + actionBtn('row-dup', '⧉', t('Duplicate row'), state.rows.length >= LIMITS.rows) + actionBtn('row-del', '🗑', t('Delete row'), false, true) + '</div>' +
			section(t('Columns'), '<div class="umwpuf-d-wide">' + layouts + '</div>' + widths +
				sel_('row.stack', t('Stack columns'), r.stack, [['narrow', t('When the card is narrow')], ['mobile', t('On phones only')], ['never', t('Never')]]) + '<p class="umwpuf-d-hint umwpuf-d-wide">' + esc(t('“When the card is narrow” (under 420px) adapts to any grid and theme, and is recommended.')) + '</p>' +
				sel_('row.valign', t('Vertical alignment'), r.valign, [['start', t('Top')], ['center', t('Middle')], ['end', t('Bottom')], ['stretch', t('Stretch')]]) +
				num('row.gap', t('Column gap'), r.gap, 0, 80, 'px'), true) +
			section(t('Row style'), col_('row.background', t('Background'), r.background, true) + num('row.padding', t('Padding'), r.padding, 0, 80, 'px') + num('row.radius', t('Corner radius'), r.radius, 0, 60, 'px'), false);
	}

	function actionBtn(act, icon, label, disabled, danger) {
		return '<button type="button" class="umwpuf-d-action' + (danger ? ' is-danger' : '') + '" data-act="' + act + '" title="' + esc(label) + '" aria-label="' + esc(label) + '"' + (disabled ? ' disabled' : '') + '><span aria-hidden="true">' + icon + '</span></button>';
	}

	function inspectElement() {
		var e = getEl(), type = e.type, r = state.rows[sel.r], col = r.columns[sel.c];
		var actions = '<div class="umwpuf-d-actions">' +
			actionBtn('el-up', '↑', t('Move up'), sel.i === 0) + actionBtn('el-down', '↓', t('Move down'), sel.i === col.elements.length - 1) +
			actionBtn('el-left', '←', t('Move to previous column'), sel.c === 0) + actionBtn('el-right', '→', t('Move to next column'), sel.c === r.columns.length - 1) +
			actionBtn('el-dup', '⧉', t('Duplicate'), countElements() >= LIMITS.elements) + actionBtn('el-del', '🗑', t('Delete'), false, true) + '</div>';
		var tabs = type === 'spacer' ? '' : '<div class="umwpuf-d-tabs" role="tablist">' + ['content', 'style'].map(function (k) {
			return '<button type="button" role="tab" data-act="tab" data-value="' + k + '" aria-selected="' + (ui.tab === k) + '">' + esc(k === 'content' ? t('Content') : t('Style')) + '</button>';
		}).join('') + '</div>';
		var content = '', style = '';
		var linkPost = [['none', t('No link')], ['post', t('Link to entry')]];
		if (['title', 'excerpt', 'date', 'modified', 'author', 'status', 'comments', 'meta', 'taxonomy', 'text'].indexOf(type) !== -1) {
			content += txt('el.label', t('Label (shown before the value)'), e.label, t('Optional'));
		}
		if (type === 'title') { content += sel_('el.tag', t('HTML tag'), e.tag, [['h2', 'H2'], ['h3', 'H3'], ['h4', 'H4'], ['p', 'p'], ['div', 'div']]); }
		if (type === 'excerpt') { content += num('el.words', t('Length (words)'), e.words, 5, 80); }
		if (type === 'text') { content += txt('el.text', t('Text'), e.text, t('e.g. Featured')); }
		if (type === 'button') { content += txt('el.label', t('Button text'), e.label, t('View post')); }
		if (type === 'spacer') { content += num('el.height', t('Height'), e.height, 0, 200, 'px'); }
		if (type === 'image') {
			content += sel_('el.ratio', t('Aspect ratio'), e.ratio, [['auto', t('Original')], ['16/9', '16:9'], ['4/3', '4:3'], ['1/1', '1:1'], ['3/4', '3:4'], ['21/9', '21:9']]) + check('el.placeholder', t('Show a placeholder when an entry has no image'), e.placeholder);
		}
		if (type === 'avatar') { content += num('el.style.size', t('Size'), e.style.size, 9, 72, 'px'); }
		if (type === 'taxonomy') {
			var tx = taxonomies(), keys = Object.keys(tx);
			content += keys.length
				? sel_('el.key', t('Taxonomy'), e.key, [['', t('— Choose —')]].concat(keys.map(function (k) { return [k, tx[k]]; })))
				: '<p class="umwpuf-d-hint umwpuf-d-wide">' + esc(t('The selected form’s post type has no public taxonomies.')) + '</p>';
			content += sel_('el.link', t('Terms link to'), e.link, [['none', t('Nothing')], ['archive', t('Term archive page')], ['filter', t('Filter this tab’s entries')]]);
		} else if (['image', 'title', 'excerpt', 'date', 'modified', 'author', 'meta', 'text', 'button'].indexOf(type) !== -1) {
			content += sel_('el.link', t('Link'), e.link, linkPost);
		}
		if (type === 'meta') {
			var fields = metaFields();
			var opts = '<datalist id="umwpuf-d-fields">' + fields.map(function (f) { return '<option value="' + esc(f.key) + '">' + esc(f.label) + '</option>'; }).join('') + '</datalist>';
			content += txt('el.key', t('Field (meta key)'), e.key, fields.length ? t('Pick a form field') : t('meta_key'), 'umwpuf-d-fields') + opts +
				'<p class="umwpuf-d-hint umwpuf-d-wide">' + esc(fields.length ? t('Suggestions come from the selected form. Hidden and password fields are never shown.') : t('Select a form in Tab Settings to list its custom fields.')) + '</p>';
		}
		if (type === 'status') { content += '<p class="umwpuf-d-hint umwpuf-d-wide">' + esc(t('Only the entry’s owner sees the status.')) + '</p>'; }

		var s = e.style;
		if (type === 'divider') {
			style = col_('el.style.border_color', t('Colour'), s.border_color, true) + num('el.style.border_width', t('Thickness'), s.border_width, 0, 12, 'px') + num('el.style.margin', t('Vertical spacing'), s.margin, 0, 100, 'px');
		} else if (type !== 'spacer') {
			style = (type === 'avatar' || type === 'image' ? '' : num('el.style.size', t('Font size'), s.size, 9, 72, 'px') +
					sel_('el.style.weight', t('Font weight'), s.weight, [100, 200, 300, 400, 500, 600, 700, 800, 900].map(function (w) { return [w, String(w)]; })) +
					seg('el.style.align', t('Alignment'), s.align, [['left', t('Left')], ['center', t('Center')], ['right', t('Right')]])) +
				col_('el.style.color', type === 'button' ? t('Button text colour') : t('Text colour'), s.color, true) +
				col_('el.style.background', type === 'button' ? t('Button colour') : t('Background'), s.background, true) +
				col_('el.style.border_color', t('Border colour'), s.border_color, true) + num('el.style.border_width', t('Border width'), s.border_width, 0, 12, 'px') +
				num('el.style.padding', t('Padding'), s.padding, 0, 100, 'px') + num('el.style.margin', t('Margin'), s.margin, 0, 100, 'px') +
				num('el.style.radius', t('Corner radius'), s.radius, 0, 100, 'px') + (type === 'taxonomy' ? num('el.style.gap', t('Space between terms'), s.gap, 0, 100, 'px') : '') +
				sel_('el.style.shadow', t('Shadow'), s.shadow, SHADOW_OPTS);
		}
		var body = type === 'spacer' || ui.tab === 'content' ? content : style;
		return actions + tabs + '<div class="umwpuf-d-fields umwpuf-d-panel">' + (body || '<p class="umwpuf-d-hint umwpuf-d-wide">' + esc(t('Nothing to configure here.')) + '</p>') + '</div>';
	}

	function renderInspector() {
		validSel();
		var title = sel.kind === 'card' ? t('Card & grid') : sel.kind === 'row' ? t('Row') + ' ' + (sel.r + 1) : TYPES[getEl().type].label;
		var body = sel.kind === 'card' ? inspectCard() : sel.kind === 'row' ? inspectRow() : inspectElement();
		var insp = $('.umwpuf-d-inspector');
		var focusedBind = document.activeElement && insp.contains(document.activeElement) ? document.activeElement.dataset.bind : null;
		insp.innerHTML = breadcrumb() + '<h3>' + esc(title) + '</h3>' + body;
		if (focusedBind) { var again = insp.querySelector('[data-bind="' + focusedBind + '"]'); if (again) { again.focus(); } }
	}

	/* Preview grid ----------------------------------------------------- */
	var previewTimer = null, previewReq = 0;
	function renderPreview() {
		var c = state.card, n = { desktop: c.columns, tablet: c.columns_tablet, mobile: c.columns_mobile }[ui.device];
		var inner = $('.umwpuf-d-grid-inner');
		inner.style.width = DEVICES[ui.device].width + 'px';
		inner.className = 'umwpuf-d-grid-inner umwpuf-sim-' + ui.device;
		var open = '<div class="umwpuf-posts umwpuf-layout-custom umwpuf-designer-grid" style="' + esc(styles.grid(c)) + 'grid-template-columns:repeat(' + n + ',minmax(0,1fr));gap:' + c.grid_gap + 'px">';
		var note = $('.umwpuf-d-preview-note');
		if (ui.preview === 'real') {
			if (!formId()) { note.textContent = t('Select a form in Tab Settings to preview real entries.'); ui.preview = 'sample'; renderToolbar(); }
			else {
				window.clearTimeout(previewTimer);
				previewTimer = window.setTimeout(function () {
					var req = ++previewReq, body = new URLSearchParams({ action: 'umwpuf_designer_preview', nonce: boot.nonce, form: formId(), config: JSON.stringify(state) });
					note.textContent = t('Loading real entries…');
					fetch(boot.ajax, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (json) {
						if (req !== previewReq || ui.preview !== 'real') { return; }
						if (!json.success || !json.data.html) { note.textContent = t('No published entries for this form yet. Showing sample content.'); inner.innerHTML = open + sampleCards(n) + '</div>'; }
						else { note.textContent = t('Real published entries of the selected form, rendered by the frontend template (unsaved changes included).'); inner.innerHTML = open + json.data.html + '</div>'; }
						inner.querySelectorAll('a').forEach(function (a) { a.setAttribute('tabindex', '-1'); });
						fitPreview();
					}).catch(function () { if (req === previewReq) { note.textContent = t('Could not load the preview.'); } });
				}, 350);
				return;
			}
		}
		note.textContent = t('Sample content. Switch to “Real entries” to check actual posts.');
		inner.innerHTML = open + sampleCards(n) + '</div>';
		fitPreview();
	}
	function sampleCards(n) { var out = ''; for (var i = 0; i < Math.max(n, 1) * 2 && i < 6; i++) { out += cardHTML(i, false); } return out; }
	function fitPreview() {
		var vp = $('.umwpuf-d-grid-viewport'), inner = $('.umwpuf-d-grid-inner');
		var avail = vp.clientWidth, w = DEVICES[ui.device].width + 32; // + inner padding
		if (!avail) { return; }
		var scale = Math.min(1, avail / w);
		inner.style.transform = 'scale(' + scale + ')';
		vp.style.height = Math.ceil(inner.offsetHeight * scale) + 'px';
		$('.umwpuf-d-scale').textContent = scale < 1 ? Math.round(scale * 100) + '%' : '';
	}

	function sync() {
		field.value = JSON.stringify(state);
		root.classList.toggle('is-dirty', field.value !== savedJSON);
	}
	function renderAll() {
		validSel();
		renderToolbar();
		renderCanvas();
		renderInspector();
		renderPreview();
		sync();
	}
	/** Light render for live typing: keeps inspector inputs (and focus) untouched. */
	function renderLive() { renderCanvas(); renderPreview(); renderToolbar(); sync(); }

	/* ------------------------------------------------------------------
	 * Binding inspector controls to the model
	 * ---------------------------------------------------------------- */
	function bindTarget(bind) {
		var parts = bind.split('.'), scope = parts.shift(), obj;
		if (scope === 'card') { obj = state.card; } else if (scope === 'row') { obj = getRow(); } else { obj = getEl(); }
		while (parts.length > 1) { obj = obj[parts.shift()]; }
		return { obj: obj, key: parts[0] };
	}
	function applyBind(input, live) {
		var bind = input.dataset.bind, target = bindTarget(bind), value;
		if (!target.obj) { return; }
		if (input.dataset.type === 'int') {
			value = int(input.value, Number(input.min), Number(input.max), target.obj[target.key]);
			if (!live) { input.value = value; }
		} else if (input.dataset.type === 'bool') { value = input.checked; }
		else if (input.dataset.type === 'color') { value = input.value.toLowerCase(); }
		else if (/\.widths\.\d+$/.test(bind)) { value = int(input.value, 0, 12, 1); }
		else if (bind === 'el.style.weight') { value = int(input.value, 100, 900, 400); }
		else if (bind === 'el.key') { value = input.value.toLowerCase().replace(/[^a-z0-9_\-]/g, ''); }
		else { value = input.value; }
		if (target.obj[target.key] === value) { return; }
		snapshot(bind + JSON.stringify(sel));
		target.obj[target.key] = value;
		if (bind.indexOf('card.columns') === 0) {
			state.card.columns_tablet = Math.min(state.card.columns_tablet, state.card.columns);
			state.card.columns_mobile = Math.min(state.card.columns_mobile, state.card.columns_tablet);
		}
		if (input.dataset.type === 'color') {
			var wrap = input.closest('.umwpuf-d-color');
			if (wrap) { wrap.dataset.empty = 'false'; wrap.querySelector('code').textContent = value; }
		}
		// Meta field picked from suggestions: pre-fill an empty label with the field's name.
		if (bind === 'el.key' && getEl().type === 'meta' && !getEl().label && !live) {
			var f = metaFields().filter(function (x) { return x.key === value; })[0];
			if (f) { getEl().label = f.label; }
		}
		if (live) { renderLive(); } else { renderAll(); }
	}
	root.addEventListener('input', function (e) {
		var input = e.target.closest('[data-bind]');
		if (input && input.tagName !== 'SELECT' && input.type !== 'checkbox') { applyBind(input, true); }
	});
	root.addEventListener('change', function (e) {
		var input = e.target.closest('[data-bind]');
		if (input) { applyBind(input, false); }
	});

	/* ------------------------------------------------------------------
	 * Clicks: toolbar, palette, canvas selection, inspector actions
	 * ---------------------------------------------------------------- */
	root.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-act]');
		var inCanvas = e.target.closest('.umwpuf-d-stage');
		if (inCanvas && e.target.closest('a')) { e.preventDefault(); }
		if (!btn && inCanvas) { selectFromCanvas(e.target); return; }
		if (!btn) { return; }
		var act = btn.dataset.act, r, list;
		switch (act) {
			case 'undo': undo(); return;
			case 'redo': redo(); return;
			case 'device': ui.device = btn.dataset.value; renderAll(); return;
			case 'preview-mode': ui.preview = btn.dataset.value; renderToolbar(); renderPreview(); return;
			case 'add': addElement(btn.dataset.type); return;
			case 'add-row':
				if (state.rows.length >= LIMITS.rows) { notify(t('Row limit reached (12).')); return; }
				snapshot();
				r = sel.kind === 'card' ? state.rows.length : sel.r + 1;
				state.rows.splice(r, 0, rowDefaults(btn.dataset.widths ? btn.dataset.widths.split(',').map(Number) : [1]));
				sel = { kind: 'row', r: r, c: 0 };
				$('.umwpuf-d-addrow-menu').hidden = true;
				notify(t('Row added'));
				renderAll(); return;
			case 'toggle-addrow': $('.umwpuf-d-addrow-menu').hidden = !$('.umwpuf-d-addrow-menu').hidden; return;
			case 'preset':
				var key = $('.umwpuf-d-preset').value, p = presets()[key];
				if (!p) { return; }
				if (countElements() && !window.confirm(t('Replace the current design with this preset? You can undo this.'))) { return; }
				snapshot();
				var built = p.build();
				state = normalize({ card: built.card, rows: built.rows });
				sel = { kind: 'card' };
				$('.umwpuf-d-preset').value = '';
				notify(p.label + ' ' + t('applied'));
				renderAll(); return;
			case 'select-card': sel = { kind: 'card' }; renderAll(); return;
			case 'select-row': sel = { kind: 'row', r: Number(btn.dataset.r), c: 0 }; renderAll(); return;
			case 'tab': ui.tab = btn.dataset.value; renderInspector(); return;
			case 'row-layout': setRowLayout(Number(btn.dataset.r), btn.dataset.widths.split(',').map(Number)); return;
			case 'set': {
				var tg = bindTarget(btn.dataset.bind);
				snapshot(); tg.obj[tg.key] = btn.dataset.value; renderAll(); return;
			}
			case 'clear': {
				var tc = bindTarget(btn.dataset.bind);
				snapshot(); tc.obj[tc.key] = ''; renderAll(); return;
			}
		}
		// Structural actions on the current selection.
		snapshot();
		if (act.indexOf('row-') === 0 && sel.kind !== 'card') {
			r = sel.r;
			if (act === 'row-up' && r > 0) { state.rows.splice(r - 1, 0, state.rows.splice(r, 1)[0]); sel.r = r - 1; }
			if (act === 'row-down' && r < state.rows.length - 1) { state.rows.splice(r + 1, 0, state.rows.splice(r, 1)[0]); sel.r = r + 1; }
			if (act === 'row-dup' && state.rows.length < LIMITS.rows) {
				var copy = clone(state.rows[r]), n = 0;
				copy.columns.forEach(function (c) { c.elements = c.elements.filter(function () { return countElements() + (++n) <= LIMITS.elements; }); });
				state.rows.splice(r + 1, 0, copy); sel = { kind: 'row', r: r + 1, c: 0 };
			}
			if (act === 'row-del') {
				var has = state.rows[r].columns.some(function (c) { return c.elements.length; });
				if (has && !window.confirm(t('Delete this row and its elements?'))) { past.pop(); return; }
				state.rows.splice(r, 1); sel = state.rows.length ? { kind: 'row', r: Math.max(0, r - 1), c: 0 } : { kind: 'card' };
				notify(t('Row deleted'));
			}
		}
		if (act.indexOf('el-') === 0 && sel.kind === 'el') {
			list = state.rows[sel.r].columns[sel.c].elements;
			var cols = state.rows[sel.r].columns;
			if (act === 'el-up' && sel.i > 0) { moveElement(sel, { r: sel.r, c: sel.c, i: sel.i - 1 }); }
			if (act === 'el-down' && sel.i < list.length - 1) { moveElement(sel, { r: sel.r, c: sel.c, i: sel.i + 2 }); }
			if (act === 'el-left' && sel.c > 0) { moveElement(sel, { r: sel.r, c: sel.c - 1, i: cols[sel.c - 1].elements.length }); }
			if (act === 'el-right' && sel.c < cols.length - 1) { moveElement(sel, { r: sel.r, c: sel.c + 1, i: cols[sel.c + 1].elements.length }); }
			if (act === 'el-dup' && countElements() < LIMITS.elements) { list.splice(sel.i + 1, 0, clone(list[sel.i])); sel.i++; }
			if (act === 'el-del') { notify(TYPES[list[sel.i].type].label + ' ' + t('deleted')); list.splice(sel.i, 1); sel = { kind: 'row', r: sel.r, c: sel.c }; }
		}
		renderAll();
		var again = root.querySelector('.umwpuf-d-inspector [data-act="' + act + '"]:not([disabled])');
		if (again) { again.focus(); }
	});

	function selectFromCanvas(target) {
		var elNode = target.closest('.umwpuf-d-el'), colNode = target.closest('.umwpuf-d-col'), rowNode = target.closest('.umwpuf-d-row');
		if (elNode) { sel = { kind: 'el', r: Number(elNode.dataset.r), c: Number(elNode.dataset.c), i: Number(elNode.dataset.i) }; }
		else if (colNode) { sel = { kind: 'row', r: Number(colNode.dataset.r), c: Number(colNode.dataset.c) }; }
		else if (rowNode) { sel = { kind: 'row', r: Number(rowNode.dataset.r), c: 0 }; }
		else { sel = { kind: 'card' }; }
		renderAll();
		if (elNode) { var n = root.querySelector('.umwpuf-d-el.is-selected'); if (n) { n.focus({ preventScroll: true }); } }
	}

	/* ------------------------------------------------------------------
	 * Drag and drop (palette -> column, element -> column)
	 * ---------------------------------------------------------------- */
	var drag = null, indicator = document.createElement('div');
	indicator.className = 'umwpuf-d-indicator';
	root.addEventListener('dragstart', function (e) {
		var chip = e.target.closest('.umwpuf-d-chip'), node = e.target.closest('.umwpuf-d-el');
		if (chip) { drag = { kind: 'new', type: chip.dataset.type }; }
		else if (node) { drag = { kind: 'move', from: { r: Number(node.dataset.r), c: Number(node.dataset.c), i: Number(node.dataset.i) } }; node.classList.add('is-dragging'); }
		else { return; }
		e.dataTransfer.effectAllowed = drag.kind === 'new' ? 'copy' : 'move';
		e.dataTransfer.setData('text/plain', 'umwpuf');
		root.classList.add('is-dragging');
	});
	function dropIndex(col, y) {
		var items = Array.prototype.filter.call(col.children, function (c) { return c.classList.contains('umwpuf-d-el'); });
		for (var i = 0; i < items.length; i++) {
			var b = items[i].getBoundingClientRect();
			if (y < b.top + b.height / 2) { return { i: i, before: items[i] }; }
		}
		return { i: items.length, before: null };
	}
	root.addEventListener('dragover', function (e) {
		if (!drag) { return; }
		var col = e.target.closest('.umwpuf-d-col');
		if (!col) { indicator.remove(); return; }
		e.preventDefault();
		e.dataTransfer.dropEffect = drag.kind === 'new' ? 'copy' : 'move';
		var pos = dropIndex(col, e.clientY);
		var empty = col.querySelector('.umwpuf-d-empty');
		if (empty) { empty.classList.add('is-over'); }
		if (pos.before) { col.insertBefore(indicator, pos.before); } else { col.appendChild(indicator); }
	});
	root.addEventListener('dragleave', function (e) {
		var col = e.target.closest('.umwpuf-d-col');
		if (col && !col.contains(e.relatedTarget)) { var em = col.querySelector('.umwpuf-d-empty'); if (em) { em.classList.remove('is-over'); } }
	});
	root.addEventListener('drop', function (e) {
		var col = e.target.closest('.umwpuf-d-col');
		if (!drag || !col) { return; }
		e.preventDefault();
		indicator.remove();
		var to = { r: Number(col.dataset.r), c: Number(col.dataset.c), i: dropIndex(col, e.clientY).i };
		if (drag.kind === 'new') { var type = drag.type; drag = null; addElement(type, to); }
		else { snapshot(); moveElement(drag.from, to); drag = null; notify(t('Element moved')); renderAll(); }
	});
	root.addEventListener('dragend', function () {
		drag = null; indicator.remove(); root.classList.remove('is-dragging');
		root.querySelectorAll('.is-dragging').forEach(function (n) { n.classList.remove('is-dragging'); });
	});

	/* ------------------------------------------------------------------
	 * Keyboard
	 * ---------------------------------------------------------------- */
	root.addEventListener('keydown', function (e) {
		var tag = e.target.tagName, typing = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
		// Enter inside a designer input must not submit the whole post form.
		if (e.key === 'Enter' && tag === 'INPUT') { e.preventDefault(); e.target.dispatchEvent(new Event('change', { bubbles: true })); return; }
		var node = e.target.closest('.umwpuf-d-el');
		if (!node) { return; }
		if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectFromCanvas(node); return; }
		if (sel.kind !== 'el') { return; }
		var map = { Delete: 'el-del', Backspace: 'el-del' };
		if (e.altKey) { map = { ArrowUp: 'el-up', ArrowDown: 'el-down', ArrowLeft: 'el-left', ArrowRight: 'el-right' }; }
		var act = map[e.key];
		if (!act) { return; }
		e.preventDefault();
		var b = root.querySelector('.umwpuf-d-inspector [data-act="' + act + '"]');
		if (b && !b.disabled) {
			b.click();
			var n = root.querySelector('.umwpuf-d-el.is-selected');
			if (n) { n.focus(); } else { var s = root.querySelector('.umwpuf-d-stage'); if (s) { s.focus(); } }
		}
	});

	// Undo/redo shortcuts: also when focus fell back to <body> (e.g. after a
	// toolbar button became disabled), but never while typing in any field.
	document.addEventListener('keydown', function (e) {
		var mod = e.ctrlKey || e.metaKey, key = (e.key || '').toLowerCase();
		if (!mod || (key !== 'z' && key !== 'y') || root.hidden || !root.offsetParent) { return; }
		var a = document.activeElement;
		var inDesigner = a && root.contains(a), onBody = !a || a === document.body;
		if (!inDesigner && !onBody) { return; }
		if (a && /^(INPUT|TEXTAREA|SELECT)$/.test(a.tagName) || (a && a.isContentEditable)) { return; }
		e.preventDefault();
		if (key === 'y' || e.shiftKey) { redo(); } else { undo(); }
	});

	/* ------------------------------------------------------------------
	 * Boot
	 * ---------------------------------------------------------------- */
	root.querySelector('.umwpuf-d-addrow-menu').innerHTML = LAYOUTS.map(function (w) {
		return '<button type="button" data-act="add-row" data-widths="' + w.join(',') + '" title="' + esc(layoutName(w)) + '">' + layoutIcon(w) + '<span>' + esc(layoutName(w)) + '</span></button>';
	}).join('');
	var presetSelect = root.querySelector('.umwpuf-d-preset');
	var ps = presets();
	presetSelect.insertAdjacentHTML('beforeend', Object.keys(ps).map(function (k) { return '<option value="' + k + '">' + esc(ps[k].label) + '</option>'; }).join(''));
	presetSelect.addEventListener('change', function () { if (presetSelect.value) { root.querySelector('[data-act="preset"]').click(); } });

	['umwpuf__post_form', 'umwpuf__mode'].forEach(function (id) {
		var node = document.getElementById(id);
		if (!node) { return; }
		if (window.jQuery) { window.jQuery(node).on('change', function () { renderAll(); }); } else { node.addEventListener('change', function () { renderAll(); }); }
	});
	if (window.ResizeObserver) { new ResizeObserver(function () { fitPreview(); }).observe(root.querySelector('.umwpuf-d-grid-viewport')); }
	root.querySelector('.umwpuf-d-grid-inner').addEventListener('load', fitPreview, true);
	document.addEventListener('umwpuf:designer-shown', renderAll);

	// Warn before leaving with unsaved design changes; saving the post clears it.
	var form = document.getElementById('post');
	var submitting = false;
	if (form) { form.addEventListener('submit', function () { submitting = true; sync(); }); }
	window.addEventListener('beforeunload', function (e) {
		if (!submitting && field.value !== savedJSON) { e.preventDefault(); e.returnValue = ''; }
	});

	renderPalette();
	renderAll();
}());
