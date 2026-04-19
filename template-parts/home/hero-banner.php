<?php
/**
 * Front page: main banner (Figma hero)
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();
$hero_cta_url_raw = function_exists( 'get_field' ) ? get_field( 'home_hero_cta_url', $home_post_id ) : '';

$hero_hugh         = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_hugh', $home_post_id ) : '';
$hero_alroz        = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_alroz', $home_post_id ) : '';
$hero_studio_line1 = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_studio_line_1', $home_post_id ) : '';
$hero_studio_line2 = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_studio_line_2', $home_post_id ) : '';
$hero_lede         = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_lede', $home_post_id ) : '';
$hero_cta_label    = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_cta_label', $home_post_id ) : '';
$hero_cta_url      = is_array( $hero_cta_url_raw ) ? (string) ( $hero_cta_url_raw['url'] ?? '' ) : (string) $hero_cta_url_raw;
$hero_cta_target   = is_array( $hero_cta_url_raw ) ? (string) ( $hero_cta_url_raw['target'] ?? '' ) : '';
$hero_cta_title    = is_array( $hero_cta_url_raw ) ? (string) ( $hero_cta_url_raw['title'] ?? '' ) : '';
$hero_strip_left   = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_strip_left', $home_post_id ) : '';
$hero_strip_center = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_strip_center', $home_post_id ) : '';
$hero_strip_right  = function_exists( 'get_field' ) ? (string) get_field( 'home_hero_strip_right', $home_post_id ) : '';

$hero_hugh         = '' !== $hero_hugh ? $hero_hugh : 'HUGH';
$hero_alroz        = '' !== $hero_alroz ? $hero_alroz : 'ALROZ';
$hero_studio_line1 = '' !== $hero_studio_line1 ? $hero_studio_line1 : '( ) TATOO';
$hero_studio_line2 = '' !== $hero_studio_line2 ? $hero_studio_line2 : 'STUDIO PRIVÉ';
$hero_lede         = '' !== $hero_lede ? $hero_lede : "Nous travaillons uniquement sur rendez-vous afin d'accorder toute notre attention à chaque projet. Réservez votre séance à l'avance et choisissez le moment qui vous convient.";
$hero_cta_label    = '' !== $hero_cta_label ? $hero_cta_label : ( '' !== $hero_cta_title ? $hero_cta_title : 'RÉSERVER UNE SÉANCE' );
$hero_cta_url      = '' !== $hero_cta_url ? $hero_cta_url : '#contact';
$hero_cta_target   = '' !== $hero_cta_target ? $hero_cta_target : '_self';
$hero_strip_left   = '' !== $hero_strip_left ? $hero_strip_left : 'TATTOO.RESERVE';
$hero_strip_center = '' !== $hero_strip_center ? $hero_strip_center : '(&nbsp;R&nbsp;)';
$hero_strip_right  = '' !== $hero_strip_right ? $hero_strip_right : 'SS26';

?>

<section
	class="hat-hero"
	id="hero"
	aria-labelledby="hat-hero-heading"
>
	<div class="hat-hero__media" aria-hidden="true"></div>

	<div class="hat-container hat-hero__body">
		<div class="hat-hero__heading-top">
			<h1 class="hat-hero__heading-block" id="hat-hero-heading">
				<span class="hat-hero__name-line hat-hero__name-line--hugh"><?php echo esc_html( $hero_hugh ); ?></span>
				<span class="hat-hero__heading-bottom">
					<span class="hat-hero__name-line hat-hero__name-line--alroz">
						<?php echo esc_html( $hero_alroz ); ?>
						<span class="hat-hero__studio-tag" aria-label="<?php esc_attr_e( 'Tattoo Studio Prive', 'hughalroztatoo' ); ?>">
							<span class="hat-hero__studio-tag-line"><?php echo esc_html( $hero_studio_line1 ); ?></span>
							<span class="hat-hero__studio-tag-line"><?php echo esc_html( $hero_studio_line2 ); ?></span>
						</span>
					</span>
				</span>
			</h1>
		</div>

		<div class="hat-hero__action-group">
			<p class="hat-hero__lede">
				<?php echo esc_html( $hero_lede ); ?>
			</p>

			<a class="hat-hero__cta" href="<?php echo esc_url( $hero_cta_url ); ?>" target="<?php echo esc_attr( $hero_cta_target ); ?>">
				<span class="hat-hero__cta-label"><?php echo esc_html( $hero_cta_label ); ?></span>
				<span class="hat-hero__cta-arrow" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" width="35" height="35">
				</span>
			</a>
		</div>
	</div>

	<div class="hat-hero__strip">
		<div class="hat-container hat-hero__strip-inner">
			<span class="hat-hero__strip-item"><?php echo esc_html( $hero_strip_left ); ?></span>
			<span class="hat-hero__strip-item hat-hero__strip-item--center"><?php echo wp_kses_post( $hero_strip_center ); ?></span>
			<span class="hat-hero__strip-item"><?php echo esc_html( $hero_strip_right ); ?></span>
		</div>
	</div>
</section>
