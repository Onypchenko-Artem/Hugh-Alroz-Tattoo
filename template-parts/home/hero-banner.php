<?php
/**
 * Front page: main banner (Figma hero)
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero_bg_url = esc_url_raw( get_template_directory_uri() . '/assets/images/video-hero.jpg' );
$hero_style    = sprintf( "--hat-hero-bg-image: url('%s');", $hero_bg_url );
?>

<section
	class="hat-hero"
	id="hero"
	aria-labelledby="hat-hero-heading"
	style="<?php echo esc_attr( $hero_style ); ?>"
>
	<div class="hat-hero__media" aria-hidden="true"></div>

	<div class="hat-container hat-hero__body">
		<h1 class="hat-hero__heading-block" id="hat-hero-heading">
			<span class="hat-hero__heading-top">
				<span class="hat-hero__name-line hat-hero__name-line--hugh"><?php esc_html_e( 'HUGH', 'hughalroztatoo' ); ?></span>
				<span class="hat-hero__studio-tag"><?php esc_html_e( '() TATTOO STUDIO PRIVÉ', 'hughalroztatoo' ); ?></span>
			</span>
			<span class="hat-hero__heading-bottom">
				<span class="hat-hero__name-line hat-hero__name-line--alroz"><?php esc_html_e( 'ALROZ', 'hughalroztatoo' ); ?></span>
			</span>
		</h1>

		<p class="hat-hero__lede">
			<?php esc_html_e( "Nous travaillons uniquement sur rendez-vous afin d'accorder toute notre attention à chaque projet. Réservez votre séance à l'avance et choisissez le moment qui vous convient.", 'hughalroztatoo' ); ?>
		</p>

		<a class="hat-hero__cta" href="#contact">
			<span class="hat-hero__cta-label"><?php esc_html_e( 'RÉSERVER UNE SÉANCE', 'hughalroztatoo' ); ?></span>
			<span class="hat-hero__cta-arrow" aria-hidden="true">&rarr;</span>
		</a>
	</div>

	<div class="hat-hero__strip">
		<div class="hat-container hat-hero__strip-inner">
			<span class="hat-hero__strip-item"><?php esc_html_e( 'TATTOO.RESERVE', 'hughalroztatoo' ); ?></span>
			<span class="hat-hero__strip-item hat-hero__strip-item--center"><?php esc_html_e( '( R )', 'hughalroztatoo' ); ?></span>
			<span class="hat-hero__strip-item"><?php esc_html_e( 'SS26', 'hughalroztatoo' ); ?></span>
		</div>
	</div>
</section>
