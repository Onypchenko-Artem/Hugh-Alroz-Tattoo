<?php
/**
 * Front page: FAQ block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-faq hat-section hat-section--alt" id="faq" aria-labelledby="hat-faq-title">
	<div class="hat-container">
		<h2 class="hat-section__title" id="hat-faq-title"><?php esc_html_e( 'FAQ', 'hughalroztatoo' ); ?></h2>
		<div class="hat-faq__list">
			<details class="hat-faq__item">
				<summary class="hat-faq__question"><?php esc_html_e( 'How do I book a session?', 'hughalroztatoo' ); ?></summary>
				<p class="hat-faq__answer"><?php esc_html_e( 'Placeholder answer — add your booking process here.', 'hughalroztatoo' ); ?></p>
			</details>
			<details class="hat-faq__item">
				<summary class="hat-faq__question"><?php esc_html_e( 'What should I bring to the appointment?', 'hughalroztatoo' ); ?></summary>
				<p class="hat-faq__answer"><?php esc_html_e( 'Placeholder answer — ID, deposit policy, aftercare info, etc.', 'hughalroztatoo' ); ?></p>
			</details>
			<details class="hat-faq__item">
				<summary class="hat-faq__question"><?php esc_html_e( 'Do you work with my idea or only custom designs?', 'hughalroztatoo' ); ?></summary>
				<p class="hat-faq__answer"><?php esc_html_e( 'Placeholder answer — describe how you collaborate with clients.', 'hughalroztatoo' ); ?></p>
			</details>
		</div>
	</div>
</section>
