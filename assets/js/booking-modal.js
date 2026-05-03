/**
 * Global booking overlay — opens [hugh_amelia_booking] rendered in footer.
 * На узком экране — bottom sheet со смахиванием вниз по «ручке».
 */
(function () {
	'use strict';

	const cfg = typeof hughAmeliaBooking === 'undefined' ? {} : hughAmeliaBooking;
	const modal = document.getElementById('hugh-ms-booking-modal');
	const root =
		modal && modal.querySelector
			? modal.querySelector('.hugh-ms-booking') ||
			  document.getElementById('hugh-ms-booking-root')
			: document.querySelector('.hugh-ms-booking');

	if (!modal || !root) {
		return;
	}

	const shell = modal.querySelector('.hugh-ms-booking-modal__shell');
	const grab = modal.querySelector('.hugh-ms-booking-modal__grab');
	const mqSheet = typeof window.matchMedia !== 'undefined' ? window.matchMedia('(max-width: 767px)') : null;

	let focusBeforeOpen = null;
	let sheetDragging = false;
	let sheetStartY = 0;
	let sheetLastYMoved = false;
	let sheetDragOffsetPx = 0;
	let sheetPrevTouchY = 0;
	let sheetPrevTouchT = 0;
	let sheetVelPxPerMs = 0;

	let focusCloseBtn = modal.querySelector('.hugh-ms-booking-modal__close');

	function isSheetBreakpoint() {
		return mqSheet && mqSheet.matches;
	}

	function sheetResetTransform() {
		if (!(shell instanceof HTMLElement)) {
			return;
		}
		sheetDragging = false;
		sheetLastYMoved = false;
		shell.classList.remove('is-sheet-dragging');
		shell.style.transform = '';
		shell.style.transition = '';
	}

	function sheetOpenAnimation() {
		if (!(shell instanceof HTMLElement)) {
			return;
		}
		sheetResetTransform();
		shell.style.transform = 'translate3d(0,100vh,0)';
		shell.style.transition = 'none';
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				if (!(shell instanceof HTMLElement) || !modal.classList.contains('is-open')) {
					return;
				}
				shell.style.transition =
					'transform 0.38s cubic-bezier(0.32, 0.72, 0, 1)';
				shell.style.transform = '';
				window.setTimeout(function () {
					if (shell instanceof HTMLElement) {
						shell.style.transition = '';
					}
				}, 420);
			});
		});
	}

	function openUi() {
		focusBeforeOpen = document.activeElement;
		sheetResetTransform();
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.documentElement.classList.add('hat-booking-modal-open');

		focusCloseBtn = modal.querySelector('.hugh-ms-booking-modal__close');

		if (isSheetBreakpoint()) {
			sheetOpenAnimation();
			var dlgEl = modal.querySelector('.hugh-ms-booking-modal__dialog');
			window.setTimeout(function () {
				if (!modal.classList.contains('is-open')) {
					return;
				}
				if (dlgEl instanceof HTMLElement && dlgEl.focus) {
					if (!dlgEl.getAttribute('tabindex')) {
						dlgEl.setAttribute('tabindex', '-1');
					}
					dlgEl.focus({ preventScroll: true });
				}
			}, 380);
			return;
		}

		if (focusCloseBtn instanceof HTMLElement && focusCloseBtn.focus) {
			window.setTimeout(function () {
				focusCloseBtn.focus();
			}, 0);
		}
	}

	function closeUi() {
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.documentElement.classList.remove('hat-booking-modal-open');
		sheetResetTransform();
		if (
			focusBeforeOpen instanceof HTMLElement &&
			typeof focusBeforeOpen.focus === 'function'
		) {
			window.setTimeout(function () {
				focusBeforeOpen.focus();
			}, 0);
		}
	}

	function closeUiSheetSlide() {
		if (!isSheetBreakpoint() || !(shell instanceof HTMLElement) || !modal.classList.contains('is-open')) {
			closeUi();
			return;
		}
		sheetDragging = false;
		sheetDragOffsetPx = 0;
		shell.classList.remove('is-sheet-dragging');
		shell.style.transition =
			'transform 0.32s cubic-bezier(0.4, 0, 1, 0.98)';
		shell.style.transform = 'translate3d(0,100vh,0)';
		var settled = false;
		var fallback = window.setTimeout(function () {
			if (settled) {
				return;
			}
			settled = true;
			closeUi();
		}, 380);
		var onEnd = function (e) {
			if (e && e.propertyName && e.propertyName !== 'transform') {
				return;
			}
			if (settled) {
				return;
			}
			settled = true;
			window.clearTimeout(fallback);
			shell.removeEventListener('transitionend', onEnd);
			closeUi();
		};
		shell.addEventListener('transitionend', onEnd);
	}

	function onGrabTouchStart(e) {
		if (!(shell instanceof HTMLElement)) {
			return;
		}
		if (!isSheetBreakpoint() || !modal.classList.contains('is-open') || !e.touches.length) {
			return;
		}
		sheetDragging = true;
		sheetLastYMoved = false;
		sheetDragOffsetPx = 0;
		sheetStartY = e.touches[0].clientY;
		sheetPrevTouchY = sheetStartY;
		sheetPrevTouchT = Date.now();
		sheetVelPxPerMs = 0;
		shell.classList.add('is-sheet-dragging');
	}

	function onGrabTouchMove(e) {
		if (!(shell instanceof HTMLElement)) {
			return;
		}
		if (!sheetDragging || !isSheetBreakpoint()) {
			return;
		}
		var ty = e.touches[0].clientY - sheetStartY;
		if (ty < 0) {
			ty = 0;
		}
		sheetDragOffsetPx = ty;
		sheetLastYMoved = true;
		var now = Date.now();
		var vy = e.touches[0].clientY;
		var dt = now - sheetPrevTouchT;
		if (dt > 0) {
			sheetVelPxPerMs = Math.max(0, (vy - sheetPrevTouchY) / dt);
		}
		sheetPrevTouchY = vy;
		sheetPrevTouchT = now;
		shell.style.transform = 'translate3d(0,' + ty + 'px,0)';
		e.preventDefault();
	}

	function onGrabTouchEnd() {
		if (!(shell instanceof HTMLElement)) {
			return;
		}
		if (!sheetDragging) {
			return;
		}
		sheetDragging = false;
		var raw = sheetLastYMoved ? sheetDragOffsetPx : 0;
		sheetDragOffsetPx = 0;
		if (!sheetLastYMoved) {
			shell.classList.remove('is-sheet-dragging');
			shell.style.transition = '';
			shell.style.transform = '';
			return;
		}
		var toss = sheetVelPxPerMs > 0.45;
		var far = raw > Math.min(window.innerHeight * 0.2, 160);
		if (toss || far) {
			closeUiSheetSlide();
			return;
		}
		shell.classList.remove('is-sheet-dragging');
		shell.style.transition = 'transform 0.24s cubic-bezier(0.32, 0.72, 0, 1)';
		shell.style.transform = '';
		window.setTimeout(function () {
			if (shell instanceof HTMLElement) {
				shell.style.transition = '';
			}
		}, 260);
	}

	if (grab instanceof HTMLElement && shell instanceof HTMLElement) {
		grab.addEventListener(
			'touchstart',
			function (e) {
				onGrabTouchStart(e);
			},
			{ passive: true }
		);
		grab.addEventListener(
			'touchmove',
			function (e) {
				onGrabTouchMove(e);
			},
			{ passive: false }
		);
		grab.addEventListener('touchend', onGrabTouchEnd);
		grab.addEventListener('touchcancel', onGrabTouchEnd);
	}

	/**
	 * Public: close wizard + overlay (wired from booking-multistep.js end screen).
	 */
	window.hughalroztatooBookingModalClose = function () {
		closeUiSheetSlide();
	};

	function openBookingFromHref(href, openUiFlag) {
		var h = typeof href === 'string' ? href : '';
		if (typeof root._hughHatRebootFromHref === 'function') {
			root._hughHatRebootFromHref(h);
		}
		if (openUiFlag !== false) {
			openUi();
		}
	}

	/**
	 * Opening entry: plain CTA (# or booking permalink) opens modal fresh.
	 */
	window.hughalroztatooOpenBookingModal = function (hrefOpt) {
		openBookingFromHref(typeof hrefOpt === 'string' ? hrefOpt : '', true);
	};

	document.addEventListener(
		'click',
		function (e) {
			const el = e.target;
			if (!(el instanceof Element)) {
				return;
			}

			const trigger = el.closest('a.hat-js-booking-trigger[href]');
			if (trigger instanceof HTMLAnchorElement) {
				e.preventDefault();
				openBookingFromHref(trigger.getAttribute('href') || '', true);
				return;
			}

			if (
				el.closest('[data-hat-booking-modal-close]') &&
				el.closest('[data-hat-booking-modal-close]') instanceof Element
			) {
				e.preventDefault();
				closeUiSheetSlide();
			}
		},
		true
	);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && modal.classList.contains('is-open')) {
			closeUiSheetSlide();
		}
	});

	if (cfg.openBookingModalOnLoad) {
		window.hughalroztatooOpenBookingModal(window.location.href);
	}

	/**
	 * Auto-grow modal mode (desktop):
	 * Если контент не помещается по высоте (или мы на высоких шагах 6/7/8),
	 * модалка переходит в page-scroll режим и растёт по контенту.
	 * Без внутреннего скролла и без выхода контента за рамку попапа.
	 */
	(function () {
		var AUTO_GROW_MODAL_CLASS = 'is-auto-grow';
		var DESKTOP_MQ = typeof window.matchMedia !== 'undefined' ? window.matchMedia('(min-width: 768px)') : null;
		var active = false;
		var rafToken = 0;

		function isDesktop() {
			return DESKTOP_MQ ? DESKTOP_MQ.matches : window.innerWidth >= 768;
		}

		function availableViewportHeight() {
			var modalStyles = window.getComputedStyle(modal);
			var padRaw = modalStyles.getPropertyValue('--hugh-ms-modal-pad') || '0px';
			var pad = parseFloat(padRaw) || 0;
			return Math.max(0, window.innerHeight - (pad * 2));
		}

		function contentHeight() {
			var bookingEl = modal.querySelector('.hugh-ms-booking-modal__body .hugh-ms-booking');
			if (!(bookingEl instanceof HTMLElement)) {
				return 0;
			}
			return bookingEl.scrollHeight;
		}

		function hasTallStep() {
			return !!root.querySelector(
				'.hugh-ms__inner--step-6, .hugh-ms__inner--step-7, .hugh-ms__inner--step-8'
			);
		}

		function shouldEnableAutoGrow() {
			if (!modal.classList.contains('is-open') || !isDesktop()) {
				return false;
			}
			var contentTooTall = contentHeight() > (availableViewportHeight() - 2);
			return hasTallStep() || contentTooTall;
		}

		function enterAutoGrow() {
			if (active) {
				return;
			}
			active = true;
			modal.classList.add(AUTO_GROW_MODAL_CLASS);
		}

		function exitAutoGrow() {
			if (!active) {
				return;
			}
			active = false;
			modal.classList.remove(AUTO_GROW_MODAL_CLASS);
		}

		function syncAutoGrowMode() {
			if (shouldEnableAutoGrow()) {
				enterAutoGrow();
			} else {
				exitAutoGrow();
			}
		}

		function scheduleSync() {
			if (rafToken) {
				return;
			}
			rafToken = window.requestAnimationFrame(function () {
				rafToken = 0;
				syncAutoGrowMode();
			});
		}

		var observer = new MutationObserver(function () {
			scheduleSync();
		});
		observer.observe(root, { childList: true, subtree: true });

		var modalObserver = new MutationObserver(function () {
			scheduleSync();
		});
		modalObserver.observe(modal, { attributes: true, attributeFilter: ['class'] });

		window.addEventListener('resize', scheduleSync);

		if (DESKTOP_MQ && typeof DESKTOP_MQ.addEventListener === 'function') {
			DESKTOP_MQ.addEventListener('change', scheduleSync);
		}
	})();
})();
