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
$hero_cta_label    = '' !== $hero_cta_label ? $hero_cta_label : $hero_cta_title;
$hero_cta_target   = '' !== $hero_cta_target ? $hero_cta_target : '_self';

$hero_heading_image        = function_exists( 'get_field' ) ? get_field( 'home_hero_heading_image', $home_post_id ) : null;
$hero_heading_image_url    = '';
$hero_heading_image_width  = '';
$hero_heading_image_height = '';
if ( is_array( $hero_heading_image ) ) {
	$hero_heading_image_url    = (string) ( $hero_heading_image['url'] ?? '' );
	$hero_heading_image_width  = (string) ( $hero_heading_image['width'] ?? '' );
	$hero_heading_image_height = (string) ( $hero_heading_image['height'] ?? '' );
} elseif ( is_numeric( $hero_heading_image ) && $hero_heading_image ) {
	$hero_heading_image_url = (string) ( wp_get_attachment_image_url( (int) $hero_heading_image, 'full' ) ?: '' );
}

$hero_has_studio_info = '' !== $hero_studio_line1 || '' !== $hero_studio_line2;
$hero_has_heading     = '' !== $hero_heading_image_url;
$hero_has_action      = '' !== $hero_lede || ( '' !== $hero_cta_label && '' !== $hero_cta_url );
$hero_has_strip       = '' !== $hero_strip_left || '' !== $hero_strip_center || '' !== $hero_strip_right;

$hero_cta_is_booking = function_exists( 'hughalroztatoo_url_is_booking_modal_cta' )
	&& hughalroztatoo_url_is_booking_modal_cta( $hero_cta_url );

/** Для модалки не даём открыть «бронирование» в новой вкладке из ACF. */
$hero_cta_target_final = $hero_cta_is_booking ? '_self' : $hero_cta_target;

if ( ! $hero_has_studio_info && ! $hero_has_heading && ! $hero_has_action && ! $hero_has_strip ) {
	return;
}
?>

<section
	class="hat-hero"
	id="hero"
>
	<div class="hat-hero__media" aria-hidden="true"></div>

	<div class="hat-container hat-hero__body">
		<?php if ( $hero_has_studio_info || $hero_has_heading ) : ?>
		<div class="hat-hero__heading-top">
			<?php if ( $hero_has_studio_info ) : ?>
			<div class="hat-hero__studio-info">
				<?php if ( '' !== $hero_studio_line1 ) : ?>
					<span class="hat-hero__studio-tag-line"><?php echo esc_html( $hero_studio_line1 ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $hero_studio_line2 ) : ?>
					<span class="hat-hero__studio-tag-line"><?php echo esc_html( $hero_studio_line2 ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php if ( $hero_heading_image_url ) : ?>
			<div class="hat-hero__heading-image" aria-hidden="true">
				<img
					src="<?php echo esc_url( $hero_heading_image_url ); ?>"
					alt=""
					<?php echo $hero_heading_image_width ? 'width="' . esc_attr( $hero_heading_image_width ) . '"' : ''; ?>
					<?php echo $hero_heading_image_height ? 'height="' . esc_attr( $hero_heading_image_height ) . '"' : ''; ?>
				>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( $hero_has_action ) : ?>
		<div class="hat-hero__action-group">
			<?php if ( '' !== $hero_lede ) : ?>
			<p class="hat-hero__lede">
				<?php echo esc_html( $hero_lede ); ?>
			</p>
			<?php endif; ?>

			<?php if ( '' !== $hero_cta_label && '' !== $hero_cta_url ) : ?>
			<a class="hat-hero__cta<?php echo $hero_cta_is_booking ? ' hat-js-booking-trigger' : ''; ?>" href="<?php echo esc_url( $hero_cta_url ); ?>" target="<?php echo esc_attr( $hero_cta_target_final ); ?>">
				<span class="hat-hero__cta-label"><?php echo esc_html( $hero_cta_label ); ?></span>
				<span class="hat-hero__cta-arrow" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" width="35" height="35">
				</span>
			</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

	<?php if ( $hero_has_strip ) : ?>
	<div class="hat-hero__strip">
		<div class="hat-container hat-hero__strip-inner">
			<?php if ( '' !== $hero_strip_left ) : ?>
				<span class="hat-hero__strip-item"><?php echo esc_html( $hero_strip_left ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $hero_strip_center ) : ?>
				<span class="hat-hero__strip-item hat-hero__strip-item--center"><?php echo wp_kses_post( $hero_strip_center ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $hero_strip_right ) : ?>
				<span class="hat-hero__strip-item"><?php echo esc_html( $hero_strip_right ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
</section>
