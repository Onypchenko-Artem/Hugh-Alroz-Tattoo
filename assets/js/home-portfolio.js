"use strict";

// Reveal portfolio images in batches of 4.
(function () {
	const roots = document.querySelectorAll("[data-portfolio-root]");

	roots.forEach((root) => {
		const moreLink = root.querySelector("[data-portfolio-more]");
		const footer = root.querySelector("[data-portfolio-footer]");
		if (!moreLink || !footer) {
			return;
		}

		const allItems = Array.from(root.querySelectorAll("[data-portfolio-item]"));
		const labelNode = moreLink.querySelector("[data-portfolio-more-label]");
		const moreLabel = labelNode ? labelNode.textContent.trim() : "VOIR PLUS";
		const lessLabel = moreLink.getAttribute("data-portfolio-less-label") || "VOIR MOINS";
		const initialRaw = parseInt(root.getAttribute("data-portfolio-initial") || "4", 10);
		const initial = Number.isFinite(initialRaw) && initialRaw > 0 ? initialRaw : 4;
		const stepRaw = parseInt(root.getAttribute("data-portfolio-step") || "4", 10);
		const step = Number.isFinite(stepRaw) && stepRaw > 0 ? stepRaw : 4;

		function hiddenItems() {
			return Array.from(root.querySelectorAll("[data-portfolio-item].is-hidden"));
		}

		function setExpandedState(expanded) {
			if (labelNode) {
				labelNode.textContent = expanded ? lessLabel : moreLabel;
			}
			moreLink.setAttribute("aria-expanded", expanded ? "true" : "false");
		}

		function collapseToInitial() {
			allItems.forEach((item, index) => {
				if (index >= initial) {
					item.classList.add("is-hidden");
					item.classList.remove("is-revealing");
				}
			});
			setExpandedState(false);
		}

		function revealNextBatch() {
			const items = hiddenItems();
			if (!items.length) {
				footer.classList.add("is-hidden");
				return 0;
			}
			const count = Math.min(step, items.length);
			for (let i = 0; i < count; i += 1) {
				items[i].classList.remove("is-hidden");
				items[i].classList.add("is-revealing");
				items[i].addEventListener(
					"animationend",
					() => {
						items[i].classList.remove("is-revealing");
					},
					{ once: true }
				);
			}
			if (hiddenItems().length === 0) {
				setExpandedState(true);
			}
			return count;
		}

		moreLink.addEventListener("click", (event) => {
			if (hiddenItems().length === 0) {
				event.preventDefault();
				collapseToInitial();
				return;
			}
			const revealed = revealNextBatch();
			if (revealed > 0) {
				event.preventDefault();
			}
		});
	});
})();
