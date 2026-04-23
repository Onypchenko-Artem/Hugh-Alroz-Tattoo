"use strict";

(function () {
	var slider = document.querySelector(".hat-faq__slider");
	if (!slider) return;

	var cards = Array.prototype.slice.call(
		slider.querySelectorAll(".hat-faq__card")
	);
	var prevBtn = document.querySelector(".hat-faq__nav-btn--prev");
	var nextBtn = document.querySelector(".hat-faq__nav-btn--next");
	var total = cards.length;

	if (total < 2) return;

	var current = 0;
	var DURATION = 560;
	var isAnimating = false;
	var fallbackTimer = null;
	var queuedStep = 0;

	function setCardOpen(card, shouldOpen) {
		var answerWrap = card.querySelector(".hat-faq__card-answer-wrap");
		card.classList.toggle("is-open", shouldOpen);
		card.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
		if (answerWrap) {
			answerWrap.setAttribute("aria-hidden", shouldOpen ? "false" : "true");
		}
	}

	function mod(n, m) {
		return ((n % m) + m) % m;
	}

	function relativePos(cardIndex) {
		var diff = cardIndex - current;
		if (diff > total / 2) diff -= total;
		if (diff < -total / 2) diff += total;
		return diff;
	}

	function update() {
		cards.forEach(function (card, i) {
			var pos = relativePos(i);
			if (pos >= -2 && pos <= 2) {
				card.setAttribute("data-pos", String(pos));
				card.setAttribute("aria-hidden", pos === 0 ? "false" : "true");
				card.tabIndex = pos === 0 ? 0 : -1;

				if (pos !== 0 && card.classList.contains("is-open")) {
					setCardOpen(card, false);
				}
			} else {
				card.removeAttribute("data-pos");
				card.setAttribute("aria-hidden", "true");
				card.tabIndex = -1;
				setCardOpen(card, false);
			}
		});
	}

	function finishAnimation() {
		isAnimating = false;
		if (fallbackTimer) {
			window.clearTimeout(fallbackTimer);
			fallbackTimer = null;
		}

		if (queuedStep !== 0) {
			var step = queuedStep > 0 ? 1 : -1;
			queuedStep -= step;
			goTo(current + step);
		}
	}

	function goTo(index) {
		if (isAnimating) {
			var delta = index > current ? 1 : -1;
			queuedStep += delta;
			if (queuedStep > total) queuedStep = total;
			if (queuedStep < -total) queuedStep = -total;
			return;
		}

		current = mod(index, total);
		isAnimating = true;
		update();

		if (fallbackTimer) {
			window.clearTimeout(fallbackTimer);
		}
		fallbackTimer = window.setTimeout(finishAnimation, DURATION + 100);
	}

	function flashPressed(btn) {
		if (!btn) return;
		btn.classList.add("is-pressed");
		window.setTimeout(function () {
			btn.classList.remove("is-pressed");
		}, 90);
	}

	if (prevBtn) {
		prevBtn.addEventListener("click", function () {
			flashPressed(prevBtn);
			goTo(current - 1);
		});
	}

	if (nextBtn) {
		nextBtn.addEventListener("click", function () {
			flashPressed(nextBtn);
			goTo(current + 1);
		});
	}

	cards.forEach(function (card, i) {
		card.setAttribute("aria-expanded", "false");
		card.addEventListener("click", function () {
			var pos = card.getAttribute("data-pos");

			if (pos !== "0") {
				goTo(i);
				return;
			}

			setCardOpen(card, !card.classList.contains("is-open"));
		});

		card.addEventListener("keydown", function (e) {
			if (e.key !== "Enter" && e.key !== " ") return;
			e.preventDefault();
			card.click();
		});
	});

	var touchStartX = 0;
	var touchDelta = 0;
	var SWIPE_THRESHOLD = 50;

	slider.addEventListener("touchstart", function (e) {
		touchStartX = e.touches[0].clientX;
		touchDelta = 0;
	}, { passive: true });

	slider.addEventListener("touchmove", function (e) {
		touchDelta = e.touches[0].clientX - touchStartX;
	}, { passive: true });

	slider.addEventListener("touchend", function () {
		if (Math.abs(touchDelta) > SWIPE_THRESHOLD) {
			goTo(touchDelta > 0 ? current - 1 : current + 1);
		}
		touchDelta = 0;
	});

	slider.addEventListener("transitionend", function (e) {
		if (!isAnimating) return;
		if (!e.target.classList.contains("hat-faq__card")) return;
		if (e.propertyName !== "transform") return;
		finishAnimation();
	});

	window.addEventListener("resize", function () {
		if (isAnimating) {
			return;
		}
		update();
	});

	update();
})();
