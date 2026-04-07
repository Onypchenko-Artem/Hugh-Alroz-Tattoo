<?php
/**
 * Front page: pricing formats block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-pricing" id="pricing" aria-labelledby="hat-pricing-title">
	<div class="hat-container">
		<div class="hat-pricing__heading">
			<p class="hat-pricing__eyebrow">
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-pricing__eyebrow-word"><?php esc_html_e( 'FORMATS', 'hughalroztatoo' ); ?></span>
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">)</span>
			</p>

			<h2 class="hat-pricing__title" id="hat-pricing-title">
				<span class="hat-pricing__title-prefix" aria-hidden="true"></span>
				<span class="hat-pricing__title-main hat-pricing__title-main--gradient"><?php esc_html_e( 'Choisissez', 'hughalroztatoo' ); ?></span>

				<span class="hat-pricing__title-prefix hat-pricing__title-prefix--with-start-blur">
					<span class="hat-pricing__title-blur hat-pricing__title-blur--start" aria-hidden="true"></span>
					<?php esc_html_e( 'votre', 'hughalroztatoo' ); ?>
				</span>
				<span class="hat-pricing__title-main hat-pricing__title-main--with-wire"><?php esc_html_e( 'format', 'hughalroztatoo' ); ?><img class="hat-pricing__title-wire" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/wire.svg' ); ?>" alt="" aria-hidden="true" width="126" height="33"></span>

				<span class="hat-pricing__title-prefix"><?php esc_html_e( 'de', 'hughalroztatoo' ); ?></span>
				<span class="hat-pricing__title-main hat-pricing__title-main--gradient hat-pricing__title-main--with-end-blur">
					<?php esc_html_e( 'réservation', 'hughalroztatoo' ); ?>
					<span class="hat-pricing__title-blur" aria-hidden="true"></span>
				</span>
			</h2>
		</div>

		<ul class="hat-pricing__grid">
			<li class="hat-pricing__card">
				<p class="hat-pricing__duration">
					<span class="hat-pricing__duration-label"><?php esc_html_e( '(T)', 'hughalroztatoo' ); ?></span>
					<span class="hat-pricing__duration-value"><?php esc_html_e( '2h', 'hughalroztatoo' ); ?></span>
				</p>
				<span class="hat-pricing__bg-index" aria-hidden="true">01</span>
				<h3 class="hat-pricing__card-title"><?php esc_html_e( 'SHORT BLOCK', 'hughalroztatoo' ); ?></h3>
				<ul class="hat-pricing__features">
					<li><?php esc_html_e( 'Option de paiement accessible', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Ideal pour les petits projets', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Parfait pour continuation de projet', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Engagement minimal, impact maximal', 'hughalroztatoo' ); ?></li>
				</ul>
				<p class="hat-pricing__price"><?php esc_html_e( '500 CAD', 'hughalroztatoo' ); ?></p>
			</li>

			<li class="hat-pricing__card">
				<p class="hat-pricing__duration">
					<span class="hat-pricing__duration-label"><?php esc_html_e( '(T)', 'hughalroztatoo' ); ?></span>
					<span class="hat-pricing__duration-value"><?php esc_html_e( '4h', 'hughalroztatoo' ); ?></span>
				</p>
				<p class="hat-pricing__badge">
					<img class="hat-pricing__badge-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/mdi_fire.svg' ); ?>" alt="" aria-hidden="true" width="20" height="20">
					<span class="hat-pricing__badge-text"><?php esc_html_e( 'le plus populaire', 'hughalroztatoo' ); ?></span>
				</p>
				<span class="hat-pricing__bg-index" aria-hidden="true">02</span>
				<h3 class="hat-pricing__card-title"><?php esc_html_e( 'HALF DAY', 'hughalroztatoo' ); ?></h3>
				<ul class="hat-pricing__features">
					<li><?php esc_html_e( 'Designs moyens a grands', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Continuation & nouveaux projets', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Equilibre ideal temps / resultat', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Priorite sur les disponibilites', 'hughalroztatoo' ); ?></li>
				</ul>
				<p class="hat-pricing__price"><?php esc_html_e( '800 CAD', 'hughalroztatoo' ); ?></p>
			</li>

			<li class="hat-pricing__card">
				<p class="hat-pricing__duration">
					<span class="hat-pricing__duration-label"><?php esc_html_e( '(T)', 'hughalroztatoo' ); ?></span>
					<span class="hat-pricing__duration-value"><?php esc_html_e( '7h', 'hughalroztatoo' ); ?></span>
				</p>
				<span class="hat-pricing__bg-index" aria-hidden="true">03</span>
				<h3 class="hat-pricing__card-title"><?php esc_html_e( 'FULL DAY', 'hughalroztatoo' ); ?></h3>
				<ul class="hat-pricing__features">
					<li><?php esc_html_e( 'Pour projets ambitieux et detailles', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Projet boucle en une seule journee', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Meilleur rendement par heure', 'hughalroztatoo' ); ?></li>
					<li><?php esc_html_e( 'Qualite optimale sans interruption', 'hughalroztatoo' ); ?></li>
				</ul>
				<p class="hat-pricing__price"><?php esc_html_e( '1200 CAD', 'hughalroztatoo' ); ?></p>
			</li>
		</ul>
	</div>
</section>
