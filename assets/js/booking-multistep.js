/**
 * Amelia custom multistep booking (public).
 */
(function () {
	'use strict';

	const cfg = typeof hughAmeliaBooking === 'undefined' ? null : hughAmeliaBooking;
	if (!cfg) {
		return;
	}

	const S = cfg.strings || {};

	/** WordPress uses ru_RU; Intl / toLocaleString needs BCP 47 (ru-RU). */
	function intlLocaleTag(wpLocale) {
		if (!wpLocale || typeof wpLocale !== 'string') {
			return undefined;
		}
		const tag = wpLocale.replace(/_/g, '-');
		if (!tag.trim()) {
			return undefined;
		}
		try {
			if (typeof Intl !== 'undefined' && Intl.getCanonicalLocales) {
				Intl.getCanonicalLocales(tag);
			}
			return tag;
		} catch (e) {
			return undefined;
		}
	}

	const calendarLocale = intlLocaleTag(cfg.calendarLocale) || 'fr-FR';
	const ameliaLocaleCandidates = Array.isArray(cfg.ameliaLocaleCandidates) ? cfg.ameliaLocaleCandidates : [];

	/** 12 noms de mois (ordre janvier…décembre) depuis Polylang / traduction. */
	function parseMonthCsv(s) {
		if (!s || typeof s !== 'string') {
			return null;
		}
		const arr = s.split(',').map(function (x) {
			return x.trim();
		});
		return arr.length === 12 ? arr : null;
	}

	/**
	 * Resolve Amelia translations JSON (same shapes as home pricing: field→locale and locale→field).
	 */
	function ameliaPickTranslatedField(entity, field, fallback) {
		if (!entity) {
			return fallback || '';
		}
		let tr = entity.translations;
		if (tr == null || tr === '') {
			return fallback || '';
		}
		if (typeof tr === 'string') {
			try {
				tr = JSON.parse(tr);
			} catch (e) {
				return fallback || '';
			}
		}
		if (!tr || typeof tr !== 'object') {
			return fallback || '';
		}
		for (let i = 0; i < ameliaLocaleCandidates.length; i++) {
			const loc = String(ameliaLocaleCandidates[i] || '');
			if (!loc) {
				continue;
			}
			if (tr[field] && tr[field][loc] != null && String(tr[field][loc]).trim() !== '') {
				return String(tr[field][loc]).trim();
			}
			if (tr[loc] && tr[loc][field] != null && String(tr[loc][field]).trim() !== '') {
				return String(tr[loc][field]).trim();
			}
		}
		return fallback || '';
	}

	function ameliaEntityName(entity) {
		const fb = entity && entity.name != null ? String(entity.name) : '';
		return ameliaPickTranslatedField(entity, 'name', fb) || fb;
	}

	function apiUrl(path, query) {
		// Amelia parses `call` from the raw query: the value must be a real path like /entities.
		// encodeURIComponent('/entities') → %2Fentities → Slim gets wrong path → HTTP 404.
		const route = path.charAt(0) === '/' ? path : '/' + path;
		let base = cfg.ajaxUrl + '?action=wpamelia_api&call=' + route;
		if (cfg.nonce) {
			base += '&ameliaNonce=' + encodeURIComponent(cfg.nonce);
		}
		if (!query) {
			return base;
		}
		const q = new URLSearchParams(query);
		return base + '&' + q.toString();
	}

	async function ameliaGet(path, query) {
		const res = await fetch(apiUrl(path, query), {
			method: 'GET',
			credentials: 'same-origin',
			headers: { Accept: 'application/json' },
		});
		const raw = await res.text();
		let json = {};
		try {
			json = raw ? JSON.parse(raw) : {};
		} catch (e) {
			json = { message: raw ? raw.slice(0, 200) : S.errorGeneric };
		}
		if (!res.ok) {
			throw new Error(
				json.message || (res.status === 403 ? '403 Forbidden' : S.errorGeneric) || 'Error'
			);
		}
		return json;
	}

	async function ameliaPost(path, body) {
		const res = await fetch(apiUrl(path), {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
			body: JSON.stringify(body),
		});
		const raw = await res.text();
		let json = {};
		try {
			json = raw ? JSON.parse(raw) : {};
		} catch (e) {
			json = { message: raw ? raw.slice(0, 200) : '' };
		}
		return { ok: res.ok, status: res.status, json };
	}

	async function ameliaPostForm(path, formData) {
		const res = await fetch(apiUrl(path), {
			method: 'POST',
			credentials: 'same-origin',
			headers: { Accept: 'application/json' },
			body: formData,
		});
		const raw = await res.text();
		let json = {};
		try {
			json = raw ? JSON.parse(raw) : {};
		} catch (e) {
			json = { message: raw ? raw.slice(0, 200) : '' };
		}
		return { ok: res.ok, status: res.status, json };
	}

	/** Flatten nested objects/arrays into PHP-style keys (same idea as Amelia stepForm L$). */
	function appendFormData(fd, val, key) {
		if (val === undefined) {
			return;
		}
		if (val === null) {
			fd.append(key, '');
			return;
		}
		if (
			typeof val === 'object' &&
			!(val instanceof Date) &&
			!(typeof File !== 'undefined' && val instanceof File) &&
			!(typeof Blob !== 'undefined' && val instanceof Blob)
		) {
			if (Array.isArray(val)) {
				if (val.length === 0) {
					return;
				}
				val.forEach(function (item, idx) {
					appendFormData(fd, item, key + '[' + idx + ']');
				});
				return;
			}
			Object.keys(val).forEach(function (k) {
				appendFormData(fd, val[k], key ? key + '[' + k + ']' : k);
			});
			return;
		}
		fd.append(key, val);
	}

	function photoFileCustomField(serviceId, customFieldsList, forcedId) {
		const sid = parseInt(serviceId, 10);
		const forced = forcedId != null && forcedId !== '' ? parseInt(forcedId, 10) : NaN;
		const list = customFieldsList || [];
		if (!isNaN(forced) && forced > 0) {
			for (let i = 0; i < list.length; i++) {
				const f = list[i];
				if (f && parseInt(f.id, 10) === forced && f.type === 'file') {
					return f;
				}
			}
		}
		for (let j = 0; j < list.length; j++) {
			const f = list[j];
			if (!f || f.type !== 'file') {
				continue;
			}
			if (f.allServices) {
				return f;
			}
			const svcs = valuesMap(f.services || f.serviceList);
			for (let k = 0; k < svcs.length; k++) {
				if (parseInt(svcs[k].id, 10) === sid) {
					return f;
				}
			}
		}
		return null;
	}

	function customFieldById(customFieldsList, id) {
		const wanted = parseInt(id, 10);
		if (isNaN(wanted) || wanted <= 0) {
			return null;
		}
		const list = customFieldsList || [];
		for (let i = 0; i < list.length; i++) {
			const f = list[i];
			if (f && parseInt(f.id, 10) === wanted) {
				return f;
			}
		}
		return null;
	}

	function customFieldOptions(field) {
		if (!field) {
			return [];
		}
		let source =
			field.options != null ? field.options :
			field.selectOptions != null ? field.selectOptions :
			field.items != null ? field.items :
			field.valueOptions != null ? field.valueOptions :
			null;
		// Amelia may return options as JSON-encoded string.
		if (typeof source === 'string') {
			try {
				source = JSON.parse(source);
			} catch (e) {
				source = [];
			}
		}
		const raw = valuesMap(source);
		return raw
			.map(function (opt) {
				if (opt == null) {
					return null;
				}
				if (typeof opt === 'string') {
					return { value: opt, label: opt };
				}
				const val =
					opt.value != null ? String(opt.value) :
					opt.label != null ? String(opt.label) :
					opt.name != null ? String(opt.name) :
					'';
				const label =
					opt.label != null ? String(opt.label) :
					opt.name != null ? String(opt.name) :
					val;
				if (!val && !label) {
					return null;
				}
				return { value: val || label, label: label || val };
			})
			.filter(Boolean);
	}

	function parseConfig(el) {
		try {
			const o = JSON.parse(el.getAttribute('data-hugh-ms-config') || '{}');
			return {
				categoryIds: o.categoryIds || [],
				categoryDescriptions: o.categoryDescriptions || {},
				photoFieldId: o.photoFieldId != null && o.photoFieldId !== '' ? o.photoFieldId : null,
				photoRefFieldId: o.photoRefFieldId != null && o.photoRefFieldId !== '' ? o.photoRefFieldId : null,
				projectNoteFieldId: o.projectNoteFieldId != null && o.projectNoteFieldId !== '' ? o.projectNoteFieldId : null,
			};
		} catch (e) {
			return { categoryIds: [], categoryDescriptions: {}, photoFieldId: null, photoRefFieldId: null, projectNoteFieldId: null };
		}
	}

	/**
	 * Champs « fichier » liés à la prestation, triés par id (pour couple zone / référence).
	 */
	function getFileCustomFieldsForService(serviceId, customFieldsList) {
		const sid = parseInt(serviceId, 10);
		if (isNaN(sid)) {
			return [];
		}
		const list = customFieldsList || [];
		const out = [];
		const seen = Object.create(null);
		for (let j = 0; j < list.length; j++) {
			const f = list[j];
			if (!f || f.type !== 'file') {
				continue;
			}
			const fid = parseInt(f.id, 10);
			if (isNaN(fid) || seen[fid]) {
				continue;
			}
			if (f.allServices) {
				seen[fid] = true;
				out.push(f);
				continue;
			}
			const svcs = valuesMap(f.services || f.serviceList);
			let forService = false;
			for (let k = 0; k < svcs.length; k++) {
				if (parseInt(svcs[k].id, 10) === sid) {
					forService = true;
					break;
				}
			}
			if (forService) {
				seen[fid] = true;
				out.push(f);
			}
		}
		out.sort(function (a, b) {
			return parseInt(a.id, 10) - parseInt(b.id, 10);
		});
		return out;
	}

	/**
	 * Couple zone / référence Amelia, ou un seul champ fichier.
	 * - Si photo_ref_field est défini : zone = photo_field ou l’autre champ fichier que le ref.
	 * - Si exactement 2 champs fichier pour la prestation (sans ref en shortcode) : split auto (ids triés).
	 * - Sinon : un seul envoi (comportement historique).
	 */
	function resolveAmeliaFileFieldsForBooking(serviceId, customFieldsList, ui) {
		const fileFields = getFileCustomFieldsForService(serviceId, customFieldsList);
		const refIdRaw = ui && ui.photoRefFieldId != null && ui.photoRefFieldId !== '' ? parseInt(String(ui.photoRefFieldId), 10) : NaN;
		const zoneIdRaw = ui && ui.photoFieldId != null && ui.photoFieldId !== '' ? parseInt(String(ui.photoFieldId), 10) : NaN;

		if (!isNaN(refIdRaw) && refIdRaw > 0) {
			const refCf = customFieldById(customFieldsList, refIdRaw);
			if (!refCf || refCf.type !== 'file') {
				return { split: false, zoneCf: null, refCf: null, singleCf: null };
			}
			let zoneCf = null;
			if (!isNaN(zoneIdRaw) && zoneIdRaw > 0 && zoneIdRaw !== refIdRaw) {
				const z = customFieldById(customFieldsList, zoneIdRaw);
				if (z && z.type === 'file') {
					zoneCf = z;
				}
			}
			if (!zoneCf) {
				for (let i = 0; i < fileFields.length; i++) {
					if (parseInt(fileFields[i].id, 10) !== refIdRaw) {
						zoneCf = fileFields[i];
						break;
					}
				}
			}
			if (!zoneCf) {
				return { split: false, zoneCf: null, refCf: null, singleCf: null };
			}
			return { split: true, zoneCf: zoneCf, refCf: refCf, singleCf: null };
		}

		if (fileFields.length === 2) {
			return {
				split: true,
				zoneCf: fileFields[0],
				refCf: fileFields[1],
				singleCf: null,
			};
		}

		return {
			split: false,
			zoneCf: null,
			refCf: null,
			singleCf: photoFileCustomField(serviceId, customFieldsList, ui && ui.photoFieldId ? ui.photoFieldId : null),
		};
	}

	/** Amelia JSON often uses objects { "1": row, "2": row } instead of arrays. */
	function valuesMap(obj) {
		if (!obj) {
			return [];
		}
		if (Array.isArray(obj)) {
			return obj;
		}
		if (typeof obj === 'object') {
			return Object.keys(obj).map(function (k) {
				return obj[k];
			});
		}
		return [];
	}

	function flattenServices(categories, allowedCatIds) {
		const list = [];
		const allow = allowedCatIds && allowedCatIds.length ? new Set(allowedCatIds) : null;
		valuesMap(categories).forEach(function (cat) {
			if (!cat) {
				return;
			}
			const cid = parseInt(cat.id, 10);
			if (allow && !allow.has(cid)) {
				return;
			}
			const name = ameliaEntityName(cat);
			const services = valuesMap(cat.serviceList || cat.services);
			services.forEach(function (svc) {
				if (!svc) {
					return;
				}
				list.push({
					service: svc,
					categoryName: name,
					categoryId: cid,
				});
			});
		});
		return list;
	}

	function monthStart(d) {
		const x = new Date(d.getFullYear(), d.getMonth(), 1);
		return formatYmd(x) + ' 00:00:00';
	}

	function formatYmd(d) {
		const y = d.getFullYear();
		const m = String(d.getMonth() + 1).padStart(2, '0');
		const day = String(d.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + day;
	}

	function formatDateLabel(ymd) {
		if (!ymd) {
			return '';
		}
		const parts = ymd.split('-');
		if (parts.length !== 3) {
			return ymd;
		}
		const y = parseInt(parts[0], 10);
		const m = parseInt(parts[1], 10) - 1;
		const d = parseInt(parts[2], 10);
		if (isNaN(y) || isNaN(m) || isNaN(d)) {
			return ymd;
		}
		const dObj = new Date(y, m, d);
		const customMonths = parseMonthCsv(S.monthsShort);
		if (customMonths) {
			try {
				const fParts = new Intl.DateTimeFormat(calendarLocale, {
					day: 'numeric',
					month: 'short',
				}).formatToParts(dObj);
				return fParts
					.map(function (p) {
						if (p.type === 'month') {
							return customMonths[m];
						}
						return p.value;
					})
					.join('');
			} catch (e) {
				// fallback: toLocaleDateString
			}
		}
		return dObj.toLocaleDateString(calendarLocale, {
			day: 'numeric',
			month: 'short',
		});
	}

	function serviceDurationLabel(service) {
		if (!service || service.duration == null) {
			return '';
		}
		const raw = parseInt(service.duration, 10);
		if (isNaN(raw) || raw <= 0) {
			return '';
		}
		const minutes = raw > 480 ? Math.round(raw / 60) : raw;
		if (minutes <= 0) {
			return '';
		}
		if (minutes >= 60 && minutes % 60 === 0) {
			const h = String(minutes / 60);
			return (S.tattooDurationHours || '%sh de tatouage').replace(/%s/g, h);
		}
		return (S.tattooDurationMinutes || '%s min de tatouage').replace(
			/%s/g,
			String(minutes)
		);
	}

	function parsePriceNumber(raw) {
		if (raw == null || raw === '') {
			return null;
		}
		const s = String(raw).replace(/[^\d.,]/g, '').replace(',', '.');
		const n = parseFloat(s);
		return isNaN(n) ? null : n;
	}

	function cadMoneyLabel(raw) {
		if (raw == null || raw === '') {
			return '—';
		}
		const p = String(raw).trim();
		if (/[€$]|\bCAD\b|\bEUR\b/i.test(p)) {
			return p;
		}
		return p + ' CAD';
	}

	function bookingSessionPriceNumber(service, extra) {
		if (!service) {
			return null;
		}
		const sn = parsePriceNumber(service.price);
		const en = extra != null ? parsePriceNumber(extra.price) : null;
		if (sn != null && en != null) {
			return sn + en;
		}
		if (sn != null) {
			return sn;
		}
		return en;
	}

	function durationShortH(service) {
		if (!service || service.duration == null) {
			return '';
		}
		const raw = parseInt(service.duration, 10);
		if (isNaN(raw) || raw <= 0) {
			return '';
		}
		const minutes = raw > 480 ? Math.round(raw / 60) : raw;
		if (minutes >= 60 && minutes % 60 === 0) {
			return String(minutes / 60) + 'H';
		}
		if (minutes >= 60) {
			return String(Math.floor(minutes / 60)) + 'H';
		}
		return String(minutes) + 'MIN';
	}

	function normalizeLookupToken(value) {
		return String(value == null ? '' : value)
			.toLowerCase()
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.replace(/[^a-z0-9]+/g, ' ')
			.trim();
	}

	function preselectedServiceQuery() {
		if (typeof window === 'undefined' || !window.location || !window.location.search) {
			return null;
		}
		const q = new URLSearchParams(window.location.search);
		const serviceIdRaw = q.get('hat_service_id') || q.get('hatServiceId') || q.get('serviceId') || '';
		const serviceId = parseInt(serviceIdRaw, 10);
		const serviceName = q.get('hat_service') || q.get('hatService') || q.get('service') || '';
		const duration = q.get('hat_duration') || q.get('hatDuration') || q.get('duration') || '';
		if (isNaN(serviceId) && !serviceName && !duration) {
			return null;
		}
		return {
			serviceId: !isNaN(serviceId) && serviceId > 0 ? serviceId : null,
			serviceName: normalizeLookupToken(serviceName),
			duration: normalizeLookupToken(duration),
		};
	}

	function matchPreselectedService(flat, preselected) {
		if (!preselected || !flat || !flat.length) {
			return null;
		}
		if (preselected.serviceId) {
			const exact = flat.find(function (entry) {
				return entry && entry.service && parseInt(entry.service.id, 10) === preselected.serviceId;
			});
			if (exact) {
				return exact;
			}
		}
		let best = null;
		let bestScore = 0;
		flat.forEach(function (entry) {
			if (!entry || !entry.service) {
				return;
			}
			const svcName = normalizeLookupToken(ameliaEntityName(entry.service) || '');
			const svcDuration = normalizeLookupToken(durationShortH(entry.service));
			let score = 0;
			if (preselected.serviceName) {
				if (svcName === preselected.serviceName) {
					score += 5;
				} else if (svcName.indexOf(preselected.serviceName) !== -1 || preselected.serviceName.indexOf(svcName) !== -1) {
					score += 2;
				}
			}
			if (preselected.duration) {
				if (svcDuration === preselected.duration) {
					score += 3;
				} else if (
					svcDuration.indexOf(preselected.duration) !== -1 ||
					preselected.duration.indexOf(svcDuration) !== -1
				) {
					score += 1;
				}
			}
			if (score > bestScore) {
				best = entry;
				bestScore = score;
			}
		});
		return bestScore > 0 ? best : null;
	}

	function displayTimesForDate(allSlots, date) {
		const daySlots = allSlots && allSlots[date] ? allSlots[date] : {};
		const dayTimes = Object.keys(daySlots || {});
		const monthTimesMap = {};
		Object.keys(allSlots || {}).forEach(function (ymd) {
			const slots = allSlots[ymd];
			Object.keys(slots || {}).forEach(function (time) {
				monthTimesMap[time] = true;
			});
		});
		const monthTimes = Object.keys(monthTimesMap);
		return (monthTimes.length ? monthTimes : dayTimes).sort();
	}

	function pickProviderFromSlot(cell, depth) {
		if (depth == null) {
			depth = 0;
		}
		if (depth > 6 || cell == null) {
			return null;
		}
		if (typeof cell === 'number') {
			return cell;
		}
		if (typeof cell === 'string') {
			const n = parseInt(cell, 10);
			return isNaN(n) ? null : n;
		}
		if (Array.isArray(cell)) {
			for (let i = 0; i < cell.length; i++) {
				const found = pickProviderFromSlot(cell[i], depth + 1);
				if (found) {
					return found;
				}
			}
			return null;
		}
		if (typeof cell === 'object') {
			if (cell.id != null) {
				return parseInt(cell.id, 10);
			}
			if (cell.providerId != null) {
				return parseInt(cell.providerId, 10);
			}
			const keys = Object.keys(cell);
			for (let j = 0; j < keys.length; j++) {
				const found = pickProviderFromSlot(cell[keys[j]], depth + 1);
				if (found) {
					return found;
				}
			}
		}
		return null;
	}

	async function loadEntities() {
		const q = new URLSearchParams();
		q.append('types[]', 'categories');
		q.append('types[]', 'customFields');
		q.append('source', 'booking');
		const r = await ameliaGet('/entities', q);
		return r.data || {};
	}

	async function loadSlots(params) {
		const q = new URLSearchParams();
		q.set('serviceId', String(params.serviceId));
		q.set('page', 'booking');
		q.set('monthsLoad', String(params.monthsLoad || 1));
		if (params.startDateTime) {
			q.set('startDateTime', params.startDateTime);
		}
		if (cfg.timeZone) {
			q.set('timeZone', cfg.timeZone);
			q.set('queryTimeZone', cfg.timeZone);
		}
		if (params.extras && params.extras.length) {
			q.set('extras', JSON.stringify(params.extras));
		}
		const r = await ameliaGet('/slots', q);
		const data = r.data || {};
		return data.slots || {};
	}

	async function recaptchaToken() {
		if (!cfg.recaptchaOn || !cfg.recaptchaSiteKey || typeof grecaptcha === 'undefined') {
			return '';
		}
		return new Promise(function (resolve) {
			grecaptcha.ready(function () {
				grecaptcha
					.execute(cfg.recaptchaSiteKey, { action: 'hugh_amelia_booking' })
					.then(resolve)
					.catch(function () {
						resolve('');
					});
			});
		});
	}

	function esc(s) {
		const d = document.createElement('div');
		d.textContent = s == null ? '' : String(s);
		return d.innerHTML;
	}

	function initRoot(el) {
		const ui = parseConfig(el);
		const projectNoteFieldId = (function () {
			const fromUi = ui.projectNoteFieldId != null && ui.projectNoteFieldId !== '' ? parseInt(ui.projectNoteFieldId, 10) : NaN;
			const fromCfg = cfg && cfg.projectNoteFieldId != null && cfg.projectNoteFieldId !== '' ? parseInt(cfg.projectNoteFieldId, 10) : NaN;
			const n = !isNaN(fromUi) && fromUi > 0 ? fromUi : !isNaN(fromCfg) && fromCfg > 0 ? fromCfg : 4;
			return n > 0 ? n : 4;
		})();
		const state = {
			step: 1,
			categories: [],
			categoryEntries: [],
			flat: [],
			selectedCategoryId: null,
			serviceEntry: null,
			service: null,
			categoryName: '',
			date: '',
			slotsMonth: new Date(),
			slotsFinal: {},
			extras: [],
			selectedExtra: null,
			time: '',
			providerId: null,
			photoFilesZone: [],
			photoFilesReference: [],
			tattooZone: '',
			tattooZoneOpen: false,
			customFields: [],
			customer: { firstName: '', lastName: '', email: '', phone: '', note: '' },
			ageConfirmed: false,
			payTermsAccepted: false,
			loading: false,
			error: '',
			resultData: null,
		};

		function setError(msg) {
			state.error = msg || '';
		}

		function allPhotoFiles() {
			return state.photoFilesZone.concat(state.photoFilesReference);
		}

		/** Dédoublonnage par nom+taille+lastModified, puis union (zone : ajouts successifs). */
		function fileIdentity(f) {
			if (!f) {
				return '';
			}
			return String(f.name || '') + '\u0000' + String(f.size) + '\u0000' + String(f.lastModified);
		}
		function mergeZonePhotoFiles(existing, added) {
			const seen = Object.create(null);
			const out = [];
			function pushUnique(arr) {
				(arr || []).forEach(function (f) {
					const id = fileIdentity(f);
					if (id && !seen[id]) {
						seen[id] = true;
						out.push(f);
					}
				});
			}
			pushUnique(existing);
			pushUnique(added);
			return out;
		}

		/** Placeholders {{current}} {{needed}} {{missing}} from Polylang strings. */
		function fillBookingTemplate(tpl, vals) {
			if (!tpl || typeof tpl !== 'string') {
				return '';
			}
			return tpl.replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, function (_, name) {
				return vals[name] != null ? String(vals[name]) : '';
			});
		}

		function renderPhotoProgressLine(tpl, vals, isOk) {
			const text = fillBookingTemplate(tpl, vals);
			if (!text.trim()) {
				return '';
			}
			const cls =
				'hugh-ms__photo-progress' +
				(isOk ? ' hugh-ms__photo-progress--ok' : ' hugh-ms__photo-progress--pending');
			return '<p class="' + cls + '" role="status">' + esc(text) + '</p>';
		}

		function renderPhotoSlotFeedback(files) {
			if (!files || !files.length) {
				return '';
			}
			const names = files
				.map(function (f) {
					return '<li class="hugh-ms__photo-upload-name">' + esc(f.name) + '</li>';
				})
				.join('');
			return (
				'<div class="hugh-ms__photo-upload-feedback">' +
				'<p class="hugh-ms__photo-upload-status">' +
				esc(S.photoUploadStatus || 'Fichier(s) chargé(s).') +
				'</p>' +
				'<ul class="hugh-ms__photo-upload-names">' +
				names +
				'</ul>' +
				'</div>'
			);
		}

		function serviceExtras() {
			const s = state.service;
			if (!s) {
				return [];
			}
			return valuesMap(s.extras || s.extraList);
		}

		function needsFormatStep() {
			return serviceExtras().length > 0;
		}

		function stepsMeta() {
			const steps = [
				{ step: 1, label: S.stepCategory || S.stepType },
				{ step: 2, label: S.stepDate },
				{ step: 3, label: S.stepService || S.stepType },
			];
			if (needsFormatStep()) {
				steps.push({ step: 4, label: S.stepFormat });
			}
			steps.push(
				{ step: 5, label: S.stepTime },
				{ step: 6, label: S.stepPhoto },
				{ step: 7, label: S.stepInfo },
				{ step: 8, label: S.stepPay },
				{ step: 9, label: S.stepDone }
			);
			return steps;
		}

		async function goNext() {
			setError('');
			if (state.step === 1) {
				if (!state.selectedCategoryId) {
					setError(S.pickCategory || S.errorGeneric);
					render();
					return;
				}
				state.step = 2;
				render();
				return;
			}
			if (state.step === 2) {
				if (!state.date) {
					return;
				}
				state.step = 3;
				render();
				return;
			}
			if (state.step === 3) {
				if (!state.service) {
					setError(S.pickService);
					render();
					return;
				}
				if (needsFormatStep()) {
					state.step = 4;
					render();
					return;
				}
				state.step = 5;
				await afterDateNext();
				return;
			}
			if (state.step === 4) {
				if (state.selectedExtra == null) {
					setError(S.pickFormat);
					render();
					return;
				}
				state.step = 5;
				await afterDateNext();
				return;
			}
			if (state.step === 5) {
				if (!state.time || !state.providerId) {
					setError(S.pickTime);
					render();
					return;
				}
				state.step = 6;
				render();
				return;
			}
			if (state.step === 6) {
				const nZone = state.photoFilesZone.length;
				const nRef = state.photoFilesReference.length;
				if (nZone === 0) {
					setError(S.photoErrorZoneNone || S.photoErrorZoneMin || S.errorGeneric);
					render();
					return;
				}
				if (nZone < 3) {
					setError(S.photoErrorZoneMin || S.errorGeneric);
					render();
					return;
				}
				if (nRef === 0) {
					setError(S.photoErrorRefNone || S.photoErrorRefMin || S.errorGeneric);
					render();
					return;
				}
				const ameliaFiles = resolveAmeliaFileFieldsForBooking(
					state.service.id,
					state.customFields,
					ui
				);
				if (ameliaFiles.split) {
					if (!ameliaFiles.zoneCf || !ameliaFiles.refCf) {
						setError(S.photoNoField || S.errorGeneric);
						render();
						return;
					}
				} else if (!ameliaFiles.singleCf) {
					setError(S.photoNoField || S.errorGeneric);
					render();
					return;
				}
				state.step = 7;
				render();
				return;
			}
			if (state.step === 7) {
				syncFieldsFromDom();
				const c = state.customer;
				if (!c.firstName || !c.lastName || !c.email) {
					setError(S.errorCustomerIdentity || S.errorGeneric);
					render();
					return;
				}
				if (!c.phone || !String(c.phone).trim()) {
					setError(S.errorPhone || S.errorGeneric);
					render();
					return;
				}
				if (!state.ageConfirmed) {
					setError(S.errorAge || S.errorGeneric);
					render();
					return;
				}
				state.step = 8;
				render();
				return;
			}
			if (state.step === 8) {
				syncFieldsFromDom();
				if (!state.payTermsAccepted) {
					setError(S.errorPayTerms || S.errorGeneric);
					render();
					return;
				}
				await submitBooking();
			}
		}

		function goBack() {
			setError('');
			if (state.step <= 1) {
				return;
			}
			state.step -= 1;
			if (!needsFormatStep() && state.step === 4) {
				state.step = 3;
			}
			state.tattooZoneOpen = false;
			render();
		}

		async function submitBooking() {
			state.loading = true;
			state.error = '';
			render();
			const token = await recaptchaToken();
			if (cfg.recaptchaOn && !token) {
				state.loading = false;
				setError(S.recaptcha || S.errorGeneric);
				render();
				return;
			}
			const bookingStart = state.date + ' ' + state.time + ':00';
			const extrasPayload = [];
			if (state.selectedExtra != null) {
				extrasPayload.push({ extraId: parseInt(state.selectedExtra.id, 10), quantity: 1 });
			}
			const body = {
				type: 'appointment',
				bookingStart: bookingStart,
				notifyParticipants: true,
				serviceId: parseInt(state.service.id, 10),
				providerId: parseInt(state.providerId, 10),
				locale: cfg.locale || '',
				timeZone: cfg.timeZone || '',
				recaptcha: token || undefined,
				payment: { gateway: 'onSite' },
				bookings: [
					{
						customer: (function () {
							const cat = state.categoryName != null && String(state.categoryName).trim();
							const cust = {
								firstName: state.customer.firstName,
								lastName: state.customer.lastName,
								email: state.customer.email,
								phone: state.customer.phone || null,
							};
							if (cat) {
								cust.hughCategoryName = String(state.categoryName).trim();
							}
							return cust;
						})(),
						persons: 1,
						extras: extrasPayload,
					},
				],
			};
			const loc = state.service.locationId;
			if (loc != null && loc !== '' && parseInt(loc, 10) > 0) {
				body.locationId = parseInt(loc, 10);
			}
			const ameliaFiles = resolveAmeliaFileFieldsForBooking(
				state.service.id,
				state.customFields,
				ui
			);
			const splitAmelia = ameliaFiles.split;
			const cfSubmit = splitAmelia ? ameliaFiles.zoneCf : ameliaFiles.singleCf;
			const cfSubmitRef = splitAmelia ? ameliaFiles.refCf : null;
			const zoneFieldSubmit = customFieldById(state.customFields, 3);
			const bookingCustomFields = {};
			if (zoneFieldSubmit && state.tattooZone) {
				const zid = String(zoneFieldSubmit.id);
				bookingCustomFields[zid] = {
					type: zoneFieldSubmit.type || 'text',
					label: zoneFieldSubmit.label != null ? String(zoneFieldSubmit.label) : 'Zone à tatouer',
					value: state.tattooZone,
				};
			}
			const noteRaw = state.customer.note != null && String(state.customer.note).trim();
			if (noteRaw) {
				const nField = customFieldById(state.customFields, projectNoteFieldId);
				bookingCustomFields[String(projectNoteFieldId)] = {
					type: nField && nField.type ? nField.type : 'text-area',
					label: nField && nField.label != null ? String(nField.label) : (S.labelProjectNote || 'Note sur le projet'),
					value: String(state.customer.note).trim(),
				};
			}
			const zonePhotos = state.photoFilesZone;
			const refPhotos = state.photoFilesReference;
			const photosForSubmit = allPhotoFiles();
			const useMultipart = splitAmelia
				? (cfSubmit && zonePhotos.length > 0) || (cfSubmitRef && refPhotos.length > 0)
				: cfSubmit && photosForSubmit.length > 0;
			let ok;
			let json;
			if (useMultipart) {
				if (splitAmelia) {
					if (cfSubmit && zonePhotos.length > 0) {
						bookingCustomFields[String(cfSubmit.id)] = {
							type: 'file',
							label: cfSubmit.label != null ? String(cfSubmit.label) : '',
							value: zonePhotos.map(function (file) {
								return { name: file.name };
							}),
						};
					}
					if (cfSubmitRef && refPhotos.length > 0) {
						bookingCustomFields[String(cfSubmitRef.id)] = {
							type: 'file',
							label: cfSubmitRef.label != null ? String(cfSubmitRef.label) : '',
							value: refPhotos.map(function (file) {
								return { name: file.name };
							}),
						};
					}
				} else {
					const fid = String(cfSubmit.id);
					const label = cfSubmit.label != null ? String(cfSubmit.label) : '';
					bookingCustomFields[fid] = {
						type: 'file',
						label: label,
						value: photosForSubmit.map(function (file) {
							return { name: file.name };
						}),
					};
				}
				body.bookings[0].customFields = bookingCustomFields;
				if (token) {
					body.recaptcha = token;
				}
				const fd = new FormData();
				appendFormData(fd, body, '');
				if (splitAmelia) {
					if (cfSubmit) {
						zonePhotos.forEach(function (file, idx) {
							fd.append('files[' + String(cfSubmit.id) + '][' + idx + ']', file, file.name);
						});
					}
					if (cfSubmitRef) {
						refPhotos.forEach(function (file, idx) {
							fd.append('files[' + String(cfSubmitRef.id) + '][' + idx + ']', file, file.name);
						});
					}
				} else {
					const fid = String(cfSubmit.id);
					photosForSubmit.forEach(function (file, idx) {
						fd.append('files[' + fid + '][' + idx + ']', file, file.name);
					});
				}
				const postRes = await ameliaPostForm('/bookings', fd);
				ok = postRes.ok;
				json = postRes.json;
			} else {
				body.bookings[0].customFields =
					Object.keys(bookingCustomFields).length ? bookingCustomFields : null;
				const postRes = await ameliaPost('/bookings', body);
				ok = postRes.ok;
				json = postRes.json;
			}
			state.loading = false;
			const d = json && json.data;
			const failed =
				!ok ||
				(d && (d.timeSlotUnavailable || d.recaptchaError || d.emailError || d.customerBlocked));
			if (!failed && d) {
				state.resultData = d;
				state.step = 9;
			} else {
				const msg =
					(d && d.message) ||
					(json && json.message) ||
					S.errorBooking;
				setError(typeof msg === 'string' ? msg : S.errorBooking);
			}
			render();
		}

		function renderHeader() {
			const steps = stepsMeta();
			const parts = steps.map(function (st) {
				const active = st.step === state.step ? ' is-active' : '';
				return '<li class="hugh-ms__crumb' + active + '"><span>' + esc(st.label) + '</span></li>';
			});
			return (
				'<ol class="hugh-ms__crumbs">' +
				parts.join('') +
				'</ol>'
			);
		}

		function renderCategory() {
			const items = state.categoryEntries
				.map(function (entry, idx) {
					const sel = state.selectedCategoryId === entry.id ? ' is-selected' : '';
					const metaText = entry.description || ((S.stepService || S.stepType) + (entry.servicesCount > 0 ? ' — ' + String(entry.servicesCount) : ''));
					return (
						'<button type="button" class="hugh-ms__card' +
						sel +
						'" data-cat-idx="' +
						idx +
						'">' +
						'<span class="hugh-ms__card-title">' +
						esc(entry.name) +
						'</span>' +
						'<span class="hugh-ms__card-meta">' +
						esc(metaText) +
						'</span>' +
						'</button>'
					);
				})
				.join('');
			return (
				'<div class="hugh-ms__panel"><h2 class="hugh-ms__title">' +
				esc(S.stepCategory || S.stepType) +
				'</h2>' +
				'<p class="hugh-ms__lead">' +
				esc(S.stepCategoryHint || '') +
				'</p><div class="hugh-ms__grid">' +
				items +
				'</div></div>'
			);
		}

		function renderService() {
			const items = state.flat
				.filter(function (entry) {
					return parseInt(entry.categoryId, 10) === parseInt(state.selectedCategoryId, 10);
				})
				.map(function (entry) {
					const sel = state.service && state.service.id === entry.service.id ? ' is-selected' : '';
					const rawPrice = entry.service.price;
					let metaLine = '';
					if (rawPrice != null && String(rawPrice).trim() !== '') {
						const p = String(rawPrice).trim();
						metaLine =
							/[€$]|\bCAD\b|\bEUR\b/i.test(p) ? esc(p) : esc(p) + ' CAD';
					} else {
						metaLine = esc(entry.categoryName || '');
					}
					return (
						'<button type="button" class="hugh-ms__card' +
						sel +
						'" data-svc-id="' +
						esc(String(entry.service.id)) +
						'">' +
						'<span class="hugh-ms__card-title">' +
						esc(ameliaEntityName(entry.service)) +
						'</span>' +
						'<span class="hugh-ms__card-meta hugh-ms__card-meta--price">' +
						metaLine +
						'</span>' +
						'</button>'
					);
				})
				.join('');
			return (
				'<div class="hugh-ms__panel">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepService || S.stepType) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				'<div class="hugh-ms__grid">' +
				items +
				'</div></div>'
			);
		}

		function renderDate() {
			const y = state.slotsMonth.getFullYear();
			const m = state.slotsMonth.getMonth();
			const todayYmd = formatYmd(new Date());
			const monthsL = parseMonthCsv(S.monthsLong);
			const monthLabel = monthsL
				? (monthsL[m] + ' ' + y).toUpperCase()
				: new Date(y, m, 1)
						.toLocaleString(calendarLocale, { month: 'long', year: 'numeric' })
						.toUpperCase();
			const first = new Date(y, m, 1);
			const startWeekday = (first.getDay() + 6) % 7;
			const daysInMonth = new Date(y, m + 1, 0).getDate();
			const daysInPrevMonth = new Date(y, m, 0).getDate();
			const weekdays = S.calWeekdays ? S.calWeekdays.split(',').map(function (s) { return s.trim(); }) : ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
			let cells = '';

			// Leading cells from previous month.
			for (let i = 0; i < startWeekday; i++) {
				const d = daysInPrevMonth - startWeekday + i + 1;
				cells +=
					'<button type="button" class="hugh-ms__cal-cell hugh-ms__cal-cell--outside is-disabled" disabled>' +
					d +
					'</button>';
			}

			// Current month cells.
			for (let d = 1; d <= daysInMonth; d++) {
				const ymd = formatYmd(new Date(y, m, d));
				const has = ymd >= todayYmd;
				const sel = state.date === ymd ? ' is-selected' : '';
				const dis = has ? '' : ' is-disabled';
				cells +=
					'<button type="button" class="hugh-ms__cal-cell' +
					sel +
					dis +
					'" data-date="' +
					ymd +
					'" ' +
					(has ? '' : 'disabled') +
					'">' +
					d +
					'</button>';
			}

			// Trailing cells from next month up to 6 full weeks.
			const totalCells = startWeekday + daysInMonth;
			const trailing = (7 - (totalCells % 7)) % 7;
			for (let nd = 1; nd <= trailing; nd++) {
				cells +=
					'<button type="button" class="hugh-ms__cal-cell hugh-ms__cal-cell--outside is-disabled" disabled>' +
					nd +
					'</button>';
			}
			const weekdaysHtml = weekdays
				.map(function (day) {
					return '<span class="hugh-ms__cal-weekday">' + esc(day) + '</span>';
				})
				.join('');
			return (
				'<div class="hugh-ms__panel">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepDate) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				'<div class="hugh-ms__cal-wrap">' +
				'<div class="hugh-ms__cal-nav">' +
				'<span class="hugh-ms__cal-month">' +
				esc(monthLabel) +
				'</span>' +
				'<div class="hugh-ms__cal-arrows">' +
				'<button type="button" class="hugh-ms__cal-arrow" data-cal="prev" aria-label="' +
				esc(S.calAriaPrevMonth || '') +
				'">‹</button>' +
				'<button type="button" class="hugh-ms__cal-arrow" data-cal="next" aria-label="' +
				esc(S.calAriaNextMonth || '') +
				'">›</button>' +
				'</div>' +
				'</div>' +
				'<div class="hugh-ms__cal-weekdays">' +
				weekdaysHtml +
				'</div>' +
				'<div class="hugh-ms__cal-grid">' +
				cells +
				'</div>' +
				'</div></div>'
			);
		}

		function renderFormat() {
			const ex = serviceExtras();
			const items = ex
				.map(function (x, idx) {
					const sel = state.selectedExtra && state.selectedExtra.id === x.id ? ' is-selected' : '';
					return (
						'<button type="button" class="hugh-ms__card' +
						sel +
						'" data-ex-idx="' +
						idx +
						'">' +
						'<span class="hugh-ms__card-title">' +
						esc(ameliaEntityName(x)) +
						'</span>' +
						(x.description ? '<span class="hugh-ms__card-meta">' + esc(x.description) + '</span>' : '') +
						'</button>'
					);
				})
				.join('');
			return (
				'<div class="hugh-ms__panel"><h2 class="hugh-ms__title">' +
				esc(S.stepFormat) +
				'</h2><div class="hugh-ms__grid">' +
				items +
				'</div></div>'
			);
		}

		function renderTime() {
			const daySlots = state.slotsFinal[state.date] || {};
			const times = displayTimesForDate(state.slotsFinal, state.date);
			const dateLabel = formatDateLabel(state.date);
			const formatLabel = state.selectedExtra
				? ameliaEntityName(state.selectedExtra)
				: state.service
					? ameliaEntityName(state.service)
					: '';
			const metaHtml = dateLabel || formatLabel
				? '<p class="hugh-ms__time-meta">' +
					(dateLabel ? '<span class="hugh-ms__time-meta-date">' + esc(dateLabel) + '</span>' : '') +
					(dateLabel && formatLabel ? '<span class="hugh-ms__time-meta-sep"> • </span>' : '') +
					(formatLabel ? '<span class="hugh-ms__time-meta-format">' + esc(formatLabel) + '</span>' : '') +
					'</p>'
				: '';
			const durationLabel = serviceDurationLabel(state.service);
			const buttons = times
				.sort()
				.map(function (t) {
					const cell = daySlots[t];
					const providerId = pickProviderFromSlot(cell);
					const unavailable = !providerId;
					const sel = !unavailable && state.time === t ? ' is-selected' : '';
					const dis = unavailable ? ' disabled aria-disabled="true"' : '';
					const unavailableClass = unavailable ? ' is-unavailable' : '';
					return (
						'<button type="button" class="hugh-ms__slot' +
						sel +
						unavailableClass +
						'" data-time="' +
						esc(t) +
						'"' +
						dis +
						'">' +
						esc(t) +
						'</button>'
					);
				})
				.join('');
			return (
				'<div class="hugh-ms__panel">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepTime) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				metaHtml +
				'<h3 class="hugh-ms__time-sub">' +
				esc((S.timeSub || 'Créneaux') + (durationLabel ? ' — ' + durationLabel : '')) +
				'</h3><div class="hugh-ms__slots">' +
				(buttons || '<p class="hugh-ms__muted">' + esc(S.errorSlots) + '</p>') +
				'</div></div>'
			);
		}

		function renderPhoto() {
			const ameliaF = resolveAmeliaFileFieldsForBooking(
				state.service.id,
				state.customFields,
				ui
			);
			const photoCfsOk = ameliaF.split
				? !!(ameliaF.zoneCf && ameliaF.refCf)
				: !!ameliaF.singleCf;
			const zoneField = customFieldById(state.customFields, 3);
			const zoneFieldLabel = S.photoZoneLabel || (zoneField && zoneField.label ? String(zoneField.label) : 'Zone à tatouer');
			const zoneOptions = customFieldOptions(zoneField);
			const acfZoneOptions = Array.isArray(cfg.zoneOptions) && cfg.zoneOptions.length ? cfg.zoneOptions : [];
			const fallbackZoneOptions = [
				{ value: 'bras', label: 'Bras' },
				{ value: 'jambe', label: 'Jambe' },
				{ value: 'dos', label: 'Dos' },
				{ value: 'torse', label: 'Torse' },
				{ value: 'cou', label: 'Cou' },
				{ value: 'autre', label: 'Autre' },
			];
			const usableZoneOptions = acfZoneOptions.length ? acfZoneOptions : (zoneOptions.length ? zoneOptions : fallbackZoneOptions);
			const selectedZone = usableZoneOptions.find(function (opt) {
				return state.tattooZone === opt.value;
			});
			const zoneLabel = selectedZone ? selectedZone.label : (S.photoZonePh || 'Sélectionner...');
			const zoneOptionsHtml = usableZoneOptions
				.map(function (opt) {
					const selected = state.tattooZone === opt.value ? ' is-selected' : '';
					return (
						'<button type="button" class="hugh-ms__photo-zone-option' +
						selected +
						'" data-zone-option="' +
						esc(opt.value) +
						'">' +
						esc(opt.label) +
						'</button>'
					);
				})
				.join('');
			const warn = photoCfsOk ? '' : '<p class="hugh-ms__warn">' + esc(S.photoNoField) + '</p>';
			const reqMark = ' <span class="hugh-ms__req" aria-hidden="true">*</span>';
			const ZONE_MIN = 3;
			const REF_MIN = 1;
			const nz = state.photoFilesZone.length;
			const nr = state.photoFilesReference.length;
			const zMiss = Math.max(0, ZONE_MIN - nz);
			const rMiss = Math.max(0, REF_MIN - nr);
			const zoneProgressHtml =
				zMiss > 0
					? renderPhotoProgressLine(S.photoZoneProgressIncomplete, { current: nz, needed: ZONE_MIN, missing: zMiss }, false)
					: renderPhotoProgressLine(S.photoZoneProgressComplete, { current: nz, needed: ZONE_MIN, missing: 0 }, true);
			const refProgressHtml =
				rMiss > 0
					? renderPhotoProgressLine(S.photoRefProgressIncomplete, { current: nr, needed: REF_MIN, missing: rMiss }, false)
					: renderPhotoProgressLine(S.photoRefProgressComplete, { current: nr, needed: REF_MIN, missing: 0 }, true);
			const zoneFeedback = renderPhotoSlotFeedback(state.photoFilesZone);
			const refFeedback = renderPhotoSlotFeedback(state.photoFilesReference);
			return (
				'<div class="hugh-ms__panel">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepPhoto) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				'<p class="hugh-ms__photo-guidelines">' +
				esc(S.photoHint) +
				'</p>' +
				warn +
				'<div class="hugh-ms__photo-upload-grid">' +
				'<label class="hugh-ms__photo-upload-card">' +
				'<input type="file" class="hugh-ms__file-input" name="bookingPhotos" data-photo-slot="zone" accept="image/*" multiple>' +
				'<span class="hugh-ms__photo-upload-icon" aria-hidden="true"></span>' +
				'<span class="hugh-ms__photo-upload-text">' +
				esc(S.photoUploadZone || '3 photos de la zone à tatouer') +
				reqMark +
				'</span>' +
				zoneProgressHtml +
				zoneFeedback +
				'</label>' +
				'<label class="hugh-ms__photo-upload-card">' +
				'<input type="file" class="hugh-ms__file-input" name="bookingPhotos" data-photo-slot="reference" accept="image/*" multiple>' +
				'<span class="hugh-ms__photo-upload-icon" aria-hidden="true"></span>' +
				'<span class="hugh-ms__photo-upload-text">' +
				esc(S.photoUploadRef || '1+ photo de référence ( style )') +
				reqMark +
				'</span>' +
				refProgressHtml +
				refFeedback +
				'</label>' +
				'</div>' +
				'<div class="hugh-ms__photo-zone-wrap">' +
				'<label class="hugh-ms__photo-zone-label">' +
				esc(zoneFieldLabel) +
				'</label>' +
				'<div class="hugh-ms__photo-zone-field' +
				(state.tattooZoneOpen ? ' is-open' : '') +
				'">' +
				'<input type="hidden" name="tattooZone" value="' +
				esc(state.tattooZone) +
				'">' +
				'<button type="button" class="hugh-ms__photo-zone-trigger" data-zone-toggle="1" aria-haspopup="listbox" aria-expanded="' +
				(state.tattooZoneOpen ? 'true' : 'false') +
				'">' +
				'<span class="hugh-ms__photo-zone-value">' +
				esc(zoneLabel) +
				'</span>' +
				'<span class="hugh-ms__photo-zone-arrow" aria-hidden="true"></span>' +
				'</button>' +
				'<div class="hugh-ms__photo-zone-menu' +
				(state.tattooZoneOpen ? ' is-open' : '') +
				'" role="listbox">' +
				zoneOptionsHtml +
				'</div>' +
				'</div>' +
				'</div>' +
				'<label class="hugh-ms__file-label hugh-ms__file-label--fallback">' +
				'<input type="file" class="hugh-ms__file-input" name="bookingPhotos" accept="image/*" multiple>' +
				'<span class="hugh-ms__file-btn">' +
				esc(S.photoChoose) +
				'</span>' +
				'</label>' +
				'</div>'
			);
		}

		function renderInfo() {
			const c = state.customer;
			const req = ' <span class="hugh-ms__req" aria-hidden="true">*</span>';
			const lbl = function (key, showReq) {
				return esc(S[key] || key) + (showReq ? req : '');
			};
			return (
				'<div class="hugh-ms__panel hugh-ms__panel--info">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepInfo) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				'<div class="hugh-ms__info-form">' +
				'<div class="hugh-ms__info-row">' +
				'<label class="hugh-ms__field hugh-ms__field--half">' +
				'<span class="hugh-ms__field-label">' +
				lbl('labelFirstName', true) +
				'</span>' +
				'<input class="hugh-ms__field-input" type="text" name="firstName" autocomplete="given-name" placeholder="" value="' +
				esc(c.firstName) +
				'" required>' +
				'</label>' +
				'<label class="hugh-ms__field hugh-ms__field--half">' +
				'<span class="hugh-ms__field-label">' +
				lbl('labelLastName', true) +
				'</span>' +
				'<input class="hugh-ms__field-input" type="text" name="lastName" autocomplete="family-name" placeholder="" value="' +
				esc(c.lastName) +
				'" required>' +
				'</label>' +
				'</div>' +
				'<label class="hugh-ms__field hugh-ms__field--full">' +
				'<span class="hugh-ms__field-label">' +
				lbl('labelEmail', true) +
				'</span>' +
				'<input class="hugh-ms__field-input" type="email" name="email" autocomplete="email" placeholder="' +
				esc(S.phEmail || '') +
				'" value="' +
				esc(c.email) +
				'" required>' +
				'</label>' +
				'<label class="hugh-ms__field hugh-ms__field--full">' +
				'<span class="hugh-ms__field-label">' +
				lbl('labelPhone', true) +
				'</span>' +
				'<input class="hugh-ms__field-input" type="tel" name="phone" autocomplete="tel" placeholder="' +
				esc(S.phPhone || '') +
				'" value="' +
				esc(c.phone) +
				'" required>' +
				'</label>' +
				'<label class="hugh-ms__field hugh-ms__field--full">' +
				'<span class="hugh-ms__field-label">' +
				lbl('labelProjectNote', false) +
				'</span>' +
				'<textarea class="hugh-ms__field-input hugh-ms__field-input--note" name="note" rows="1" placeholder="' +
				esc(S.phNote || '') +
				'">' +
				esc(c.note || '') +
				'</textarea>' +
				'</label>' +
				'</div>' +
				'</div>'
			);
		}

		function renderPay() {
			const svc = state.service;
			const extra = state.selectedExtra;
			const urls = cfg.payUrls || {};
			function payUrl(key) {
				const u = urls[key];
				return u && String(u).trim() ? String(u).trim() : '#';
			}
			const formatName = extra ? ameliaEntityName(extra) : svc ? ameliaEntityName(svc) : '';
			const dh = durationShortH(svc);
			const formatVal =
				formatName && dh ? formatName + ' — ' + dh : formatName || dh || '—';
			const dateVal = formatDateLabel(state.date) || '—';
			const slotVal = state.time ? state.time : '—';
			const totalNum = bookingSessionPriceNumber(svc, extra);
			const totalDisplay =
				totalNum != null ? String(Math.round(totalNum)) + ' CAD' : svc ? cadMoneyLabel(svc.price) : '—';
			const pctRaw = cfg.depositPercent != null ? parseInt(cfg.depositPercent, 10) : 30;
			const pct = isNaN(pctRaw) ? 30 : pctRaw;
			const depLabel = (S.payRowDepositFmt || 'Acompte %d%%')
				.replace('%d', String(pct))
				.replace(/%%/g, '%');
			let depDisplay = '—';
			if (totalNum != null) {
				depDisplay = String(Math.round((totalNum * pct) / 100)) + ' CAD';
			}
			function payRow(label, value) {
				return (
					'<div class="hugh-ms__pay-row">' +
					'<span class="hugh-ms__pay-k">' +
					esc(label || '') +
					'</span>' +
					'<span class="hugh-ms__pay-v">' +
					esc(value || '') +
					'</span>' +
					'</div>'
				);
			}
			const customConsent =
				typeof cfg.payConsentHtml === 'string' && String(cfg.payConsentHtml).trim() !== ''
					? String(cfg.payConsentHtml).trim()
					: '';
			const consentHtml = customConsent
				? '<div class="hugh-ms__pay-terms-line hugh-ms__pay-terms-line--html">' +
				  customConsent +
				  '</div>'
				: '<span class="hugh-ms__pay-terms-line">' +
				  esc(S.payConsentBefore || '') +
				  '<a href="' +
				  esc(payUrl('conditions')) +
				  '" class="hugh-ms__pay-link" target="_blank" rel="noopener noreferrer">' +
				  esc(S.payTermsConditions || '') +
				  '</a>' +
				  esc(S.payConsentMid || '') +
				  '<a href="' +
				  esc(payUrl('privacy')) +
				  '" class="hugh-ms__pay-link" target="_blank" rel="noopener noreferrer">' +
				  esc(S.payTermsPrivacy || '') +
				  '</a>' +
				  esc(S.payConsentAnd || '') +
				  '<a href="' +
				  esc(payUrl('refund')) +
				  '" class="hugh-ms__pay-link" target="_blank" rel="noopener noreferrer">' +
				  esc(S.payTermsRefund || '') +
				  '</a>' +
				  esc(S.payConsentAfter || '') +
				  '</span>';
			const payTermsTextTagOpen = customConsent ? '<div class="hugh-ms__pay-terms-text">' : '<span class="hugh-ms__pay-terms-text">';
			const payTermsTextTagClose = customConsent ? '</div>' : '</span>';
			return (
				'<div class="hugh-ms__panel hugh-ms__panel--pay">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepPay) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back hugh-ms__step2-back--figma" data-act="back">' +
				'<span class="hugh-ms__step2-back-ico" aria-hidden="true"></span>' +
				'<span class="hugh-ms__step2-back-txt">' +
				esc(S.back) +
				'</span>' +
				'</button>' +
				'</div>' +
				'<div class="hugh-ms__pay-layout">' +
				'<div class="hugh-ms__pay-sheet">' +
				payRow(S.payRowFormat, formatVal) +
				payRow(S.payRowDate, dateVal) +
				payRow(S.payRowSlot, slotVal) +
				payRow(S.payRowTotal, totalDisplay) +
				'<div class="hugh-ms__pay-sep" role="presentation"></div>' +
				payRow(depLabel, depDisplay) +
				'</div>' +
				'<p class="hugh-ms__pay-disclaimer">' +
				esc(S.payDisclaimer || '') +
				'</p>' +
				'<label class="hugh-ms__pay-terms' +
				(customConsent ? ' hugh-ms__pay-terms--consent-html' : '') +
				'">' +
				'<input type="checkbox" class="hugh-ms__cb-outline hugh-ms__pay-terms-input" name="payTerms" value="1"' +
				(state.payTermsAccepted ? ' checked' : '') +
				'>' +
				payTermsTextTagOpen +
				consentHtml +
				payTermsTextTagClose +
				'</label>' +
				'</div>' +
				'</div>'
			);
		}

		function renderDone() {
			const svc = state.service;
			const extra = state.selectedExtra;
			const formatName = extra ? ameliaEntityName(extra) : svc ? ameliaEntityName(svc) : '';
			const dh = durationShortH(svc);
			const formatVal = formatName && dh ? formatName + '- ' + dh : formatName || dh || '—';
			const dateVal = formatDateLabel(state.date) || '—';
			const slotVal = state.time || '—';
			const totalNum = bookingSessionPriceNumber(svc, extra);
			const pctRaw = cfg.depositPercent != null ? parseInt(cfg.depositPercent, 10) : 30;
			const pct = isNaN(pctRaw) ? 30 : pctRaw;
			let paidVal = '—';
			if (totalNum != null) {
				paidVal = String(Math.round((totalNum * pct) / 100)) + ' CAD';
			}
			function doneRow(label, value, strong) {
				return (
					'<div class="hugh-ms__done-row' +
					(strong ? ' is-strong' : '') +
					'">' +
					'<span class="hugh-ms__done-k">' +
					esc(label || '') +
					'</span>' +
					'<span class="hugh-ms__done-v">' +
					esc(value || '') +
					'</span>' +
					'</div>'
				);
			}
			return (
				'<div class="hugh-ms__panel hugh-ms__panel--done">' +
				'<div class="hugh-ms__done-wrap">' +
				'<div class="hugh-ms__done-icon" aria-hidden="true"></div>' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepDone || '') +
				'</h2>' +
				'<div class="hugh-ms__done-sheet">' +
				doneRow(S.doneRowFormat, formatVal, false) +
				doneRow(S.doneRowDate, dateVal, false) +
				doneRow(S.doneRowSlot, slotVal, false) +
				doneRow(S.doneRowPaid, paidVal, true) +
				'</div>' +
				'<p class="hugh-ms__done-note">' +
				esc(S.doneMessage || '') +
				'</p>' +
				'<button type="button" class="hugh-ms__done-close" data-act="close">' +
				esc(S.doneClose || 'Fermer') +
				'</button>' +
				'</div>' +
				'</div>'
			);
		}

		function render() {
			let main = '';
			if (state.loading) {
				main = '<div class="hugh-ms__loading">' + esc(S.loading) + '</div>';
			} else {
				switch (state.step) {
					case 1:
						main = renderCategory();
						break;
					case 2:
						main = renderDate();
						break;
					case 3:
						main = renderService();
						break;
					case 4:
						main = renderFormat();
						break;
					case 5:
						main = renderTime();
						break;
					case 6:
						main = renderPhoto();
						break;
					case 7:
						main = renderInfo();
						break;
					case 8:
						main = renderPay();
						break;
					case 9:
						main = renderDone();
						break;
					default:
						main = '';
				}
			}
			const err = state.error ? '<p class="hugh-ms__error">' + esc(state.error) + '</p>' : '';
			const showNext = state.step < 9 && !state.loading;
			const showBack = state.step > 1 && state.step < 9 && !state.loading;
			let nextLabel = S.next;
			if (state.step === 8) {
				const totalNum = bookingSessionPriceNumber(state.service, state.selectedExtra);
				const pctRaw = cfg.depositPercent != null ? parseInt(cfg.depositPercent, 10) : 30;
				const pct = isNaN(pctRaw) ? 30 : pctRaw;
				let depStr = '—';
				if (totalNum != null) {
					depStr = String(Math.round((totalNum * pct) / 100)) + ' CAD';
				}
				const tmpl = S.payViaStripe || 'Payer %s via Stripe';
				nextLabel = tmpl.indexOf('%s') !== -1 ? tmpl.replace('%s', depStr) : tmpl + ' ' + depStr;
			}
			const nextDisabled =
				(state.step === 2 && !state.date) ||
				(state.step === 6 &&
					(state.photoFilesZone.length < 3 || state.photoFilesReference.length < 1));
			const nextBtn =
				showNext ?
					'<button type="button" class="hugh-ms__btn hugh-ms__btn--primary" data-act="next"' +
					(nextDisabled ? ' disabled aria-disabled="true"' : '') +
					'>' +
					esc(nextLabel) +
					'</button>' :
					'';
			const step7 = state.step === 7;
			const step8 = state.step === 8;
			const step9 = state.step === 9;
			const footerBack =
				showBack && !step7 && !step8 ?
					'<button type="button" class="hugh-ms__btn hugh-ms__btn--ghost" data-act="back">' + esc(S.back) + '</button>' :
					'';
			const footerLeft =
				step7 ?
					'<label class="hugh-ms__age-row">' +
					'<input type="checkbox" class="hugh-ms__cb-outline hugh-ms__age-input" name="ageConfirm" value="1"' +
					(state.ageConfirmed ? ' checked' : '') +
					'>' +
					'<span class="hugh-ms__age-text">' +
					esc(S.ageCheckbox || '') +
					'</span>' +
					'</label>' :
					footerBack;
			const footerPrimary =
				step8 && nextBtn ?
					'<div class="hugh-ms__pay-footer-stack">' +
					'<p class="hugh-ms__pay-secure">' +
					esc(S.paySecureLine || '') +
					'</p>' +
					nextBtn +
					'</div>' :
					nextBtn;
			const panelPrimary = footerPrimary ? '<div class="hugh-ms__panel-actions">' + footerPrimary + '</div>' : '';
			const panelControlsClass =
				'hugh-ms__panel-controls' +
				(step7 ? ' hugh-ms__panel-controls--info-step' : '') +
				(step8 ? ' hugh-ms__panel-controls--pay-step' : '');
			const panelControls =
				!step9 && (footerLeft || panelPrimary) ?
					'<div class="' + panelControlsClass + '">' + (footerLeft || '') + panelPrimary + '</div>' :
					'';
			const mainHtml =
				panelControls && /<\/div>\s*$/.test(main) ?
					main.replace(/<\/div>\s*$/, panelControls + '</div>') :
					main + panelControls;
			el.innerHTML =
				'<div class="hugh-ms__inner hugh-ms__inner--step-' +
				state.step +
				'">' +
				renderHeader() +
				err +
				mainHtml +
				'</div>';
		}

		function bind() {
			el.addEventListener('click', function (e) {
				const t = e.target;
				if (!(t instanceof Element)) {
					return;
				}
				if (t instanceof HTMLAnchorElement && t.classList.contains('hugh-ms__pay-link') && t.getAttribute('href') === '#') {
					e.preventDefault();
				}
				if (t.closest('[data-act="close"]')) {
					window.location.href = '/';
					return;
				}
				if (state.step === 6 && state.tattooZoneOpen && !t.closest('.hugh-ms__photo-zone-field')) {
					setTimeout(function () {
						if (state.step === 6 && state.tattooZoneOpen) {
							state.tattooZoneOpen = false;
							render();
						}
					}, 0);
				}
				const cat = t.closest('[data-cat-idx]');
				if (cat) {
					const idx = parseInt(cat.getAttribute('data-cat-idx'), 10);
					const picked = state.categoryEntries[idx];
					if (picked) {
						state.selectedCategoryId = parseInt(picked.id, 10);
						state.serviceEntry = null;
						state.service = null;
						state.selectedExtra = null;
						state.time = '';
						state.providerId = null;
						state.slotsFinal = {};
						state.photoFilesZone = [];
						state.photoFilesReference = [];
					}
					render();
					return;
				}
				const svc = t.closest('[data-svc-id]');
				if (svc) {
					const serviceId = parseInt(svc.getAttribute('data-svc-id'), 10);
					const pickedServiceEntry = state.flat.find(function (entry) {
						return parseInt(entry.service.id, 10) === serviceId;
					});
					if (pickedServiceEntry) {
						state.serviceEntry = pickedServiceEntry;
						state.service = pickedServiceEntry.service;
						state.categoryName = pickedServiceEntry.categoryName;
						state.photoFilesZone = [];
						state.photoFilesReference = [];
						state.selectedExtra = null;
					}
					render();
					return;
				}
				if (t.closest('[data-zone-toggle]')) {
					state.tattooZoneOpen = !state.tattooZoneOpen;
					render();
					return;
				}
				const zoneOpt = t.closest('[data-zone-option]');
				if (zoneOpt) {
					state.tattooZone = zoneOpt.getAttribute('data-zone-option') || '';
					state.tattooZoneOpen = false;
					render();
					return;
				}
				const ex = t.closest('[data-ex-idx]');
				if (ex) {
					const idx = parseInt(ex.getAttribute('data-ex-idx'), 10);
					state.selectedExtra = serviceExtras()[idx];
					render();
					return;
				}
				const slot = t.closest('[data-time]');
				if (slot && !slot.disabled) {
					const time = slot.getAttribute('data-time');
					state.time = time;
					const cell = state.slotsFinal[state.date][time];
					state.providerId = pickProviderFromSlot(cell);
					render();
					return;
				}
				const day = t.closest('[data-date]');
				if (day && !day.disabled) {
					state.date = day.getAttribute('data-date');
					render();
					return;
				}
				const cal = t.closest('[data-cal]');
				if (cal) {
					const dir = cal.getAttribute('data-cal');
					state.slotsMonth.setMonth(state.slotsMonth.getMonth() + (dir === 'next' ? 1 : -1));
					render();
					return;
				}
				if (t.matches('[data-act="next"]')) {
					syncFieldsFromDom();
					goNext().catch(function () {
						setError(S.errorGeneric);
						state.loading = false;
						render();
					});
					return;
				}
				if (t.closest('[data-act="back"]')) {
					syncFieldsFromDom();
					goBack();
					render();
				}
			});
			el.addEventListener(
				'change',
				function (e) {
					const t = e.target;
					if (
						!(t instanceof HTMLInputElement) &&
						!(t instanceof HTMLSelectElement) &&
						!(t instanceof HTMLTextAreaElement)
					) {
						return;
					}
					if (t instanceof HTMLInputElement && t.name === 'bookingPhotos' && t.type === 'file' && t.files) {
						const list = Array.prototype.slice.call(t.files, 0);
						const slot = t.getAttribute('data-photo-slot');
						if (slot === 'reference') {
							state.photoFilesReference = list;
						} else {
							state.photoFilesZone = mergeZonePhotoFiles(state.photoFilesZone, list);
						}
						t.value = '';
						if (state.step === 6) {
							setError('');
						}
						render();
						return;
					}
					if (t.name === 'tattooZone') {
						state.tattooZone = t.value || '';
						return;
					}
					if (t.matches('.hugh-ms__fields input, .hugh-ms__info-form input, .hugh-ms__info-form textarea')) {
						state.customer[t.name] = t.value;
					}
					if (t.matches('.hugh-ms__age-input')) {
						state.ageConfirmed = !!t.checked;
					}
					if (t.matches('.hugh-ms__pay-terms-input')) {
						state.payTermsAccepted = !!t.checked;
					}
				},
				true
			);
		}

		function syncFieldsFromDom() {
			const rIn = el.querySelector('input[name="bookingPhotos"][data-photo-slot="reference"]');
			// Zone : l’input est vidé à chaque render / après choix; la pile est dans state (merge au change).
			if (rIn && rIn.files && rIn.files.length) {
				state.photoFilesReference = Array.prototype.slice.call(rIn.files, 0);
			}
			const zone = el.querySelector('input[name="tattooZone"]');
			if (zone) {
				state.tattooZone = zone.value || '';
			}
			['firstName', 'lastName', 'email', 'phone'].forEach(function (n) {
				const inp = el.querySelector('.hugh-ms__info-form input[name="' + n + '"]');
				if (inp) {
					state.customer[n] = inp.value;
				}
			});
			const noteTa = el.querySelector('.hugh-ms__info-form textarea[name="note"]');
			if (noteTa) {
				state.customer.note = noteTa.value;
			}
			const ageIn = el.querySelector('.hugh-ms__age-input');
			if (ageIn) {
				state.ageConfirmed = !!ageIn.checked;
			}
			const payTermsIn = el.querySelector('.hugh-ms__pay-terms-input');
			if (payTermsIn) {
				state.payTermsAccepted = !!payTermsIn.checked;
			}
		}

		async function afterDateNext() {
			state.loading = true;
			render();
			try {
				const extrasQ = [];
				if (state.selectedExtra) {
					extrasQ.push({ id: state.selectedExtra.id, quantity: 1 });
				}
				state.slotsFinal = await loadSlots({
					serviceId: state.service.id,
					startDateTime: monthStart(new Date(state.date + 'T12:00:00')),
					monthsLoad: 1,
					extras: extrasQ,
				});
			} catch (err) {
				setError(S.errorSlots);
				state.slotsFinal = {};
			}
			state.loading = false;
			state.time = '';
			state.providerId = null;
			render();
		}

		async function boot() {
			bind();
			state.loading = true;
			render();
			try {
				const data = await loadEntities();
				state.categories = valuesMap(data.categories || []);
				state.customFields = valuesMap(data.customFields);
				state.flat = flattenServices(state.categories, ui.categoryIds || []);
				const preselectedEntry = matchPreselectedService(state.flat, preselectedServiceQuery());
				if (preselectedEntry) {
					state.serviceEntry = preselectedEntry;
					state.service = preselectedEntry.service;
					state.categoryName = preselectedEntry.categoryName;
					state.selectedCategoryId = parseInt(preselectedEntry.categoryId, 10);
				}
				const allow = ui.categoryIds && ui.categoryIds.length ? new Set(ui.categoryIds.map(function (id) { return parseInt(id, 10); })) : null;
				state.categoryEntries = state.categories
					.map(function (cat) {
						const cid = parseInt(cat.id, 10);
						if (allow && !allow.has(cid)) {
							return null;
						}
						const servicesCount = valuesMap(cat.serviceList || cat.services).length;
						if (!servicesCount) {
							return null;
						}
						const description = ui.categoryDescriptions && ui.categoryDescriptions[cid] ? String(ui.categoryDescriptions[cid]) : '';
						return { id: cid, name: ameliaEntityName(cat) || '', description: description, servicesCount: servicesCount };
					})
					.filter(Boolean);
				if (!state.selectedCategoryId && state.categoryEntries.length) {
					state.selectedCategoryId = parseInt(state.categoryEntries[0].id, 10);
				}
				if (!state.flat.length) {
					setError(S.errorNoServices);
				}
			} catch (err) {
				if (typeof console !== 'undefined' && console.error) {
					console.error('[hugh_amelia_booking]', err);
				}
				setError(S.errorGeneric);
			}
			state.loading = false;
			render();
		}

		boot();
	}

	document.querySelectorAll('.hugh-ms-booking').forEach(initRoot);
})();
