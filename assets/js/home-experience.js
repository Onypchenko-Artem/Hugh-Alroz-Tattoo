"use strict";

(function () {
	var DURATION = 700;
	var EASING = "cubic-bezier(0.16, 1, 0.3, 1)";

	function nextFrame(fn) {
		requestAnimationFrame(function () {
			requestAnimationFrame(fn);
		});
	}

	document.querySelectorAll(".hat-experience__accordion").forEach(function (accordion) {
		var trigger = accordion.querySelector(".hat-experience__summary");
		var panel = accordion.querySelector(".hat-experience__panel");
		var inner = accordion.querySelector(".hat-experience__panel-inner");

		if (!trigger || !panel || !inner) {
			return;
		}

		var running = false;
		var handler = null;

		trigger.addEventListener("click", function () {
			if (running) {
				return;
			}

			if (accordion.classList.contains("is-open")) {
				close();
			} else {
				open();
			}
		});

		function open() {
			running = true;

			panel.style.transition = "none";
			panel.style.height = "0px";

			accordion.classList.add("is-open");
			trigger.setAttribute("aria-expanded", "true");

			// Force layout recalculation so padding/width from .is-open is applied
			// before measuring the real content height.
			void panel.offsetHeight;
			var endHeight = inner.scrollHeight;

			nextFrame(function () {
				panel.style.transition = "height " + DURATION + "ms " + EASING;
				panel.style.height = endHeight + "px";

				handler = function (event) {
					if (event.propertyName !== "height") {
						return;
					}
					panel.removeEventListener("transitionend", handler);
					handler = null;
					panel.style.transition = "";
					panel.style.height = "auto";
					running = false;
				};
				panel.addEventListener("transitionend", handler);
			});
		}

		function close() {
			running = true;
			var startHeight = panel.scrollHeight;

			panel.style.transition = "none";
			panel.style.height = startHeight + "px";

			nextFrame(function () {
				panel.style.transition = "height " + DURATION + "ms " + EASING;
				panel.style.height = "0px";
				accordion.classList.remove("is-open");
				trigger.setAttribute("aria-expanded", "false");

				handler = function (event) {
					if (event.propertyName !== "height") {
						return;
					}
					panel.removeEventListener("transitionend", handler);
					handler = null;
					panel.style.transition = "";
					running = false;
				};
				panel.addEventListener("transitionend", handler);
			});
		}
	});
})();
