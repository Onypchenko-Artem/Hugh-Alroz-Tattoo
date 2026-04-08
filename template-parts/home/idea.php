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
		<p class="hat-idea__eyebrow">
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">(</span>
			<span class="hat-idea__eyebrow-word"><?php esc_html_e( 'RESERVATION', 'hughalroztatoo' ); ?></span>
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">)</span>
		</p>

		<div class="hat-idea__title-row">
			<h2 class="hat-idea__title" id="hat-idea-title">
				<span class="hat-idea__title-line"><?php esc_html_e( "L'IDEE. L'ENCRE.", 'hughalroztatoo' ); ?></span>
				<span class="hat-idea__title-line"><?php esc_html_e( 'VOUS.', 'hughalroztatoo' ); ?></span>
			</h2>

			<span class="hat-idea__ornament" aria-hidden="true">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/idea-element.svg' ); ?>" alt="" width="98" height="55">
			</span>
		</div>

		<div class="hat-idea__action-group">
			<p class="hat-idea__text">
				<?php esc_html_e( "Sur rendez-vous uniquement afin d'offrir une experience entierement personnalisee et dediee a chaque projet. Reservation en ligne disponible 24/7 pour organiser votre seance en toute simplicite, a l'heure qui vous convient.", 'hughalroztatoo' ); ?>
			</p>

			<a class="hat-idea__cta" href="#contact">
				<span class="hat-idea__cta-label"><?php esc_html_e( 'RESERVER UNE SEANCE', 'hughalroztatoo' ); ?></span>
				<span class="hat-idea__cta-arrow" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" width="35" height="35">
				</span>
			</a>
		</div>

		<div class="hat-idea__strip" aria-hidden="true">
			<span class="hat-idea__strip-item"><?php esc_html_e( 'RDV//', 'hughalroztatoo' ); ?></span>
			<span class="hat-idea__strip-item"><?php esc_html_e( '/ 20.2026 /', 'hughalroztatoo' ); ?></span>
		</div>
	</div>
</section>
