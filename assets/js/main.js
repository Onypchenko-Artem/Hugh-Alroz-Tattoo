"use strict";

// Global theme entry point.
(function () {
	const toggle = document.querySelector(".hat-header__menu-toggle");
	const desktopMenu = document.getElementById("hat-desktop-menu");

	if (!toggle || !desktopMenu) {
		return;
	}

	const closeButton = desktopMenu.querySelector("[data-menu-close]");
	const links = desktopMenu.querySelectorAll("a");
	const desktopMedia = window.matchMedia("(min-width: 1025px)");
	const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

	let closeFallbackTimerId = 0;
	let onCloseTransitionEnd = null;

	const isMenuOpen = () => desktopMenu.classList.contains("is-open");

	const clearCloseAnimation = () => {
		if (closeFallbackTimerId) {
			window.clearTimeout(closeFallbackTimerId);
			closeFallbackTimerId = 0;
		}
		if (onCloseTransitionEnd) {
			desktopMenu.removeEventListener("transitionend", onCloseTransitionEnd);
			onCloseTransitionEnd = null;
		}
	};

	const getScrollbarGap = () =>
		Math.max(0, window.innerWidth - document.documentElement.clientWidth);

	const openMenu = () => {
		if (!desktopMedia.matches || isMenuOpen()) {
			return;
		}

		clearCloseAnimation();

		document.documentElement.style.setProperty("--hat-scrollbar-gap", `${getScrollbarGap()}px`);

		desktopMenu.hidden = false;
		desktopMenu.setAttribute("aria-hidden", "false");
		toggle.setAttribute("aria-expanded", "true");
		document.body.classList.add("hat-menu-open");

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				desktopMenu.classList.add("is-open");
			});
		});
	};

	const finishClose = () => {
		clearCloseAnimation();
		document.body.classList.remove("hat-menu-open");
		document.documentElement.style.removeProperty("--hat-scrollbar-gap");
		desktopMenu.hidden = true;
		desktopMenu.setAttribute("aria-hidden", "true");
	};

	const closeMenu = () => {
		if (!isMenuOpen()) {
			return;
		}

		clearCloseAnimation();

		desktopMenu.classList.remove("is-open");
		toggle.setAttribute("aria-expanded", "false");
		desktopMenu.setAttribute("aria-hidden", "true");

		if (prefersReducedMotion.matches) {
			finishClose();
			return;
		}

		onCloseTransitionEnd = (event) => {
			if (event.target !== desktopMenu || event.propertyName !== "opacity") {
				return;
			}
			finishClose();
		};

		desktopMenu.addEventListener("transitionend", onCloseTransitionEnd);
		closeFallbackTimerId = window.setTimeout(finishClose, 500);
	};

	toggle.addEventListener("click", () => {
		if (isMenuOpen()) {
			closeMenu();
			return;
		}
		openMenu();
	});

	if (closeButton) {
		closeButton.addEventListener("click", closeMenu);
	}

	links.forEach((link) => {
		link.addEventListener("click", closeMenu);
	});

	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape" && isMenuOpen()) {
			closeMenu();
		}
	});

	desktopMedia.addEventListener("change", (event) => {
		if (!event.matches) {
			clearCloseAnimation();
			desktopMenu.classList.remove("is-open");
			document.body.classList.remove("hat-menu-open");
			document.documentElement.style.removeProperty("--hat-scrollbar-gap");
			toggle.setAttribute("aria-expanded", "false");
			desktopMenu.hidden = true;
			desktopMenu.setAttribute("aria-hidden", "true");
		}
	});
})();
