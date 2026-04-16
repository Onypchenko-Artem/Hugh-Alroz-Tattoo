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
			};
		} catch (e) {
			return { categoryIds: [], categoryDescriptions: {}, photoFieldId: null };
		}
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
			const name = cat.name || '';
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
		return new Date(y, m, d).toLocaleDateString(calendarLocale, {
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
			return String(minutes / 60) + 'h de tatouage';
		}
		return String(minutes) + ' min de tatouage';
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
				esc(S.photoUploaded || 'Fichier(s) chargé(s).') +
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
				const cf = photoFileCustomField(state.service.id, state.customFields, ui.photoFieldId);
				if (cf && !allPhotoFiles().length) {
					setError(S.photoRequired || S.errorGeneric);
					render();
					return;
				}
				if (!cf && allPhotoFiles().length) {
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
					setError(S.errorGeneric);
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
							const cust = {
								firstName: state.customer.firstName,
								lastName: state.customer.lastName,
								email: state.customer.email,
								phone: state.customer.phone || null,
							};
							const nt = state.customer.note != null && String(state.customer.note).trim();
							if (nt) {
								cust.note = String(state.customer.note).trim();
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
			const cfSubmit = photoFileCustomField(state.service.id, state.customFields, ui.photoFieldId);
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
			const photosForSubmit = allPhotoFiles();
			const useMultipart = cfSubmit && photosForSubmit.length > 0;
			let ok;
			let json;
			if (useMultipart) {
				const fid = String(cfSubmit.id);
				const label = cfSubmit.label != null ? String(cfSubmit.label) : '';
				const cfPayload = {
					type: 'file',
					label: label,
					value: photosForSubmit.map(function (file) {
						return { name: file.name };
					}),
				};
				bookingCustomFields[fid] = cfPayload;
				body.bookings[0].customFields = bookingCustomFields;
				if (token) {
					body.recaptcha = token;
				}
				const fd = new FormData();
				appendFormData(fd, body, '');
				photosForSubmit.forEach(function (file, idx) {
					fd.append('files[' + fid + '][' + idx + ']', file, file.name);
				});
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
						esc(entry.service.name) +
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
				'<button type="button" class="hugh-ms__step2-back" data-act="back">↶ ' +
				esc(S.back) +
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
			const monthLabel = new Date(y, m, 1)
				.toLocaleString(calendarLocale, { month: 'long', year: 'numeric' })
				.toUpperCase();
			const first = new Date(y, m, 1);
			const startWeekday = (first.getDay() + 6) % 7;
			const daysInMonth = new Date(y, m + 1, 0).getDate();
			const daysInPrevMonth = new Date(y, m, 0).getDate();
			const weekdays = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
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
				'<button type="button" class="hugh-ms__step2-back" data-act="back">↶ ' +
				esc(S.back) +
				'</button>' +
				'</div>' +
				'<div class="hugh-ms__cal-wrap">' +
				'<div class="hugh-ms__cal-nav">' +
				'<span class="hugh-ms__cal-month">' +
				esc(monthLabel) +
				'</span>' +
				'<div class="hugh-ms__cal-arrows">' +
				'<button type="button" class="hugh-ms__cal-arrow" data-cal="prev" aria-label="Previous month">‹</button>' +
				'<button type="button" class="hugh-ms__cal-arrow" data-cal="next" aria-label="Next month">›</button>' +
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
						esc(x.name) +
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
			const formatLabel = state.selectedExtra ? state.selectedExtra.name : state.service && state.service.name;
			const metaParts = [dateLabel, formatLabel].filter(Boolean);
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
				'<button type="button" class="hugh-ms__step2-back" data-act="back">↶ ' +
				esc(S.back) +
				'</button>' +
				'</div>' +
				(metaParts.length ? '<p class="hugh-ms__time-meta">' + esc(metaParts.join(' • ')) + '</p>' : '') +
				'<h3 class="hugh-ms__time-sub">' +
				esc('Créneaux' + (durationLabel ? ' — ' + durationLabel : '')) +
				'</h3><div class="hugh-ms__slots">' +
				(buttons || '<p class="hugh-ms__muted">' + esc(S.errorSlots) + '</p>') +
				'</div></div>'
			);
		}

		function renderPhoto() {
			const cf = photoFileCustomField(state.service.id, state.customFields, ui.photoFieldId);
			const zoneField = customFieldById(state.customFields, 3);
			const zoneFieldLabel = zoneField && zoneField.label ? String(zoneField.label) : 'Zone à tatouer';
			const zoneOptions = customFieldOptions(zoneField);
			const fallbackZoneOptions = [
				{ value: 'bras', label: 'Bras' },
				{ value: 'jambe', label: 'Jambe' },
				{ value: 'dos', label: 'Dos' },
				{ value: 'torse', label: 'Torse' },
				{ value: 'cou', label: 'Cou' },
				{ value: 'autre', label: 'Autre' },
			];
			const usableZoneOptions = zoneOptions.length ? zoneOptions : fallbackZoneOptions;
			const selectedZone = usableZoneOptions.find(function (opt) {
				return state.tattooZone === opt.value;
			});
			const zoneLabel = selectedZone ? selectedZone.label : 'Sélectionner...';
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
			const warn = cf ? '' : '<p class="hugh-ms__warn">' + esc(S.photoNoField) + '</p>';
			const zoneFeedback = renderPhotoSlotFeedback(state.photoFilesZone);
			const refFeedback = renderPhotoSlotFeedback(state.photoFilesReference);
			return (
				'<div class="hugh-ms__panel">' +
				'<div class="hugh-ms__step2-head">' +
				'<h2 class="hugh-ms__title">' +
				esc(S.stepPhoto) +
				'</h2>' +
				'<button type="button" class="hugh-ms__step2-back" data-act="back">↶ ' +
				esc(S.back) +
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
				'<span class="hugh-ms__photo-upload-text">3 photos de la zone à tatouer</span>' +
				zoneFeedback +
				'</label>' +
				'<label class="hugh-ms__photo-upload-card">' +
				'<input type="file" class="hugh-ms__file-input" name="bookingPhotos" data-photo-slot="reference" accept="image/*" multiple>' +
				'<span class="hugh-ms__photo-upload-icon" aria-hidden="true"></span>' +
				'<span class="hugh-ms__photo-upload-text">1+ photo de référence ( style )</span>' +
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
			const extraLine =
				state.selectedExtra ?
					'<li>' + esc(state.selectedExtra.name) + '</li>' :
					'';
			return (
				'<div class="hugh-ms__panel"><h2 class="hugh-ms__title">' +
				esc(S.stepPay) +
				'</h2>' +
				'<div class="hugh-ms__pay">' +
				'<div><h3 class="hugh-ms__sub">' +
				esc(S.paySummary) +
				'</h3><ul class="hugh-ms__summary">' +
				'<li><strong>' +
				esc(state.service.name) +
				'</strong></li>' +
				extraLine +
				'<li>' +
				esc(state.date) +
				' ' +
				esc(state.time) +
				'</li>' +
				'</ul></div>' +
				'<div class="hugh-ms__pay-aside"><p class="hugh-ms__muted">' +
				esc(S.payOnSite) +
				'</p></div>' +
				'</div></div>'
			);
		}

		function renderDone() {
			const d = state.resultData || {};
			return (
				'<div class="hugh-ms__panel hugh-ms__panel--center"><div class="hugh-ms__ok">✓</div><h2 class="hugh-ms__title">' +
				esc(S.stepDone) +
				'</h2>' +
				'<p class="hugh-ms__muted">' +
				esc(d.bookingStart || state.date + ' ' + state.time) +
				'</p></div>'
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
			const nextLabel = state.step === 8 ? S.submit : S.next;
			const nextDisabled = state.step === 2 && !state.date;
			const nextBtn =
				showNext ?
					'<button type="button" class="hugh-ms__btn hugh-ms__btn--primary" data-act="next"' +
					(nextDisabled ? ' disabled aria-disabled="true"' : '') +
					'>' +
					esc(nextLabel) +
					'</button>' :
					'';
			const step7 = state.step === 7;
			const footerBack =
				showBack && !step7 ?
					'<button type="button" class="hugh-ms__btn hugh-ms__btn--ghost" data-act="back">' + esc(S.back) + '</button>' :
					'';
			const footerLeft =
				step7 ?
					'<label class="hugh-ms__age-row">' +
					'<input type="checkbox" class="hugh-ms__age-input" name="ageConfirm" value="1"' +
					(state.ageConfirmed ? ' checked' : '') +
					'>' +
					'<span class="hugh-ms__age-text">' +
					esc(S.ageCheckbox || '') +
					'</span>' +
					'</label>' :
					footerBack ?
						footerBack :
						'<span></span>';
			el.innerHTML =
				'<div class="hugh-ms__inner hugh-ms__inner--step-' +
				state.step +
				'">' +
				renderHeader() +
				err +
				main +
				'<div class="hugh-ms__footer' +
				(step7 ? ' hugh-ms__footer--info-step' : '') +
				'">' +
				footerLeft +
				nextBtn +
				'</div></div>';
		}

		function bind() {
			el.addEventListener('click', function (e) {
				const t = e.target;
				if (!(t instanceof Element)) {
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
							state.photoFilesZone = list;
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
				},
				true
			);
		}

		function syncFieldsFromDom() {
			const zIn = el.querySelector('input[name="bookingPhotos"][data-photo-slot="zone"]');
			const rIn = el.querySelector('input[name="bookingPhotos"][data-photo-slot="reference"]');
			if (zIn && zIn.files && zIn.files.length) {
				state.photoFilesZone = Array.prototype.slice.call(zIn.files, 0);
			}
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
						return { id: cid, name: cat.name || '', description: description, servicesCount: servicesCount };
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
