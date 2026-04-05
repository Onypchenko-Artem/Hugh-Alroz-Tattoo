<?php
/**
 * Front page: services block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-services hat-section" id="services" aria-labelledby="hat-services-title">
	<div class="hat-container">
		<h2 class="hat-section__title" id="hat-services-title"><?php esc_html_e( 'Services', 'hughalroztatoo' ); ?></h2>
		<ul class="hat-services__grid">
			<li class="hat-services__item">
				<h3 class="hat-services__name"><?php esc_html_e( 'Custom design', 'hughalroztatoo' ); ?></h3>
				<p class="hat-services__text"><?php esc_html_e( 'Placeholder description for this service.', 'hughalroztatoo' ); ?></p>
			</li>
			<li class="hat-services__item">
				<h3 class="hat-services__name"><?php esc_html_e( 'Cover-ups', 'hughalroztatoo' ); ?></h3>
				<p class="hat-services__text"><?php esc_html_e( 'Placeholder description for this service.', 'hughalroztatoo' ); ?></p>
			</li>
			<li class="hat-services__item">
				<h3 class="hat-services__name"><?php esc_html_e( 'Consultation', 'hughalroztatoo' ); ?></h3>
				<p class="hat-services__text"><?php esc_html_e( 'Placeholder description for this service.', 'hughalroztatoo' ); ?></p>
			</li>
		</ul>
	</div>
</section>
