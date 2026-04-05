<?php
/**
 * Front page: idea / CTA block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-idea hat-section" id="idea" aria-labelledby="hat-idea-title">
	<div class="hat-container hat-idea__inner">
		<h2 class="hat-idea__title" id="hat-idea-title"><?php esc_html_e( 'Have an idea?', 'hughalroztatoo' ); ?></h2>
		<p class="hat-idea__text"><?php esc_html_e( 'Placeholder — invite visitors to share references or book a consult.', 'hughalroztatoo' ); ?></p>
		<a class="hat-idea__cta" href="<?php echo esc_url( home_url( '/' ) ); ?>#contact"><?php esc_html_e( 'Get in touch', 'hughalroztatoo' ); ?></a>
	</div>
</section>
