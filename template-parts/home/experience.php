<?php
/**
 * Front page: experience block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-experience hat-section" id="experience" aria-labelledby="hat-experience-title">
	<div class="hat-container hat-experience__inner">
		<div class="hat-experience__content">
			<h2 class="hat-section__title hat-experience__title" id="hat-experience-title"><?php esc_html_e( 'Experience', 'hughalroztatoo' ); ?></h2>
			<p class="hat-experience__text"><?php esc_html_e( 'Placeholder for your years in the industry, style focus, and studio approach. Replace with real copy.', 'hughalroztatoo' ); ?></p>
		</div>
		<ul class="hat-experience__stats">
			<li class="hat-experience__stat">
				<strong class="hat-experience__stat-value">—</strong>
				<span class="hat-experience__stat-label"><?php esc_html_e( 'Years', 'hughalroztatoo' ); ?></span>
			</li>
			<li class="hat-experience__stat">
				<strong class="hat-experience__stat-value">—</strong>
				<span class="hat-experience__stat-label"><?php esc_html_e( 'Sessions', 'hughalroztatoo' ); ?></span>
			</li>
			<li class="hat-experience__stat">
				<strong class="hat-experience__stat-value">—</strong>
				<span class="hat-experience__stat-label"><?php esc_html_e( 'Styles', 'hughalroztatoo' ); ?></span>
			</li>
		</ul>
	</div>
</section>
