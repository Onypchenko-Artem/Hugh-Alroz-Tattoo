<?php
/**
 * Front page: idea / CTA block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();
$idea_cta_url_raw = function_exists( 'get_field' ) ? get_field( 'home_idea_cta_url', $home_post_id ) : '';

$idea_eyebrow   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_eyebrow', $home_post_id ) : '';
$idea_title_1   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_title_line_1', $home_post_id ) : '';
$idea_title_2   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_title_line_2', $home_post_id ) : '';
$idea_text      = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_text', $home_post_id ) : '';
$idea_cta_label = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_cta_label', $home_post_id ) : '';
$idea_cta_url   = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['url'] ?? '' ) : (string) $idea_cta_url_raw;
$idea_cta_target = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['target'] ?? '' ) : '';
$idea_cta_title = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['title'] ?? '' ) : '';
$idea_strip_1   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_strip_1', $home_post_id ) : '';
$idea_strip_2   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_strip_2', $home_post_id ) : '';

$idea_cta_label = '' !== $idea_cta_label ? $idea_cta_label : $idea_cta_title;
$idea_cta_target = '' !== $idea_cta_target ? $idea_cta_target : '_self';

$idea_cta_is_booking = function_exists( 'hughalroztatoo_url_is_booking_modal_cta' )
	&& hughalroztatoo_url_is_booking_modal_cta( $idea_cta_url );

/** То же поведение, что hero: модалка, без новой вкладки. */
$idea_cta_target_final = $idea_cta_is_booking ? '_self' : $idea_cta_target;

$idea_has_title = '' !== $idea_title_1 || '' !== $idea_title_2;
$idea_has_action = '' !== $idea_text || ( '' !== $idea_cta_label && '' !== $idea_cta_url );
$idea_has_strip = '' !== $idea_strip_1 || '' !== $idea_strip_2;

if ( '' === $idea_eyebrow && ! $idea_has_title && ! $idea_has_action && ! $idea_has_strip ) {
	return;
}
?>

<section class="hat-idea hat-section" id="idea"<?php echo $idea_has_title ? ' aria-labelledby="hat-idea-title"' : ''; ?>>
	<div class="hat-container hat-idea__inner">
		<?php if ( '' !== $idea_eyebrow ) : ?>
		<p class="hat-idea__eyebrow">
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">(</span>
			<span class="hat-idea__eyebrow-word"><?php echo esc_html( $idea_eyebrow ); ?></span>
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">)</span>
		</p>
		<?php endif; ?>

		<?php if ( $idea_has_title ) : ?>
		<div class="hat-idea__title-row">
			<h2 class="hat-idea__title" id="hat-idea-title">
				<?php if ( '' !== $idea_title_1 ) : ?>
					<span class="hat-idea__title-line"><?php echo esc_html( $idea_title_1 ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $idea_title_2 ) : ?>
					<span class="hat-idea__title-line"><?php echo esc_html( $idea_title_2 ); ?></span>
				<?php endif; ?>
			</h2>

			<span class="hat-idea__ornament" aria-hidden="true">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/idea-element.svg' ); ?>" alt="" width="98" height="55">
			</span>
		</div>
		<?php endif; ?>

		<?php if ( $idea_has_action ) : ?>
		<div class="hat-idea__action-group">
			<?php if ( '' !== $idea_text ) : ?>
			<p class="hat-idea__text">
				<?php echo esc_html( $idea_text ); ?>
			</p>
			<?php endif; ?>

			<?php if ( '' !== $idea_cta_label && '' !== $idea_cta_url ) : ?>
			<a class="hat-idea__cta<?php echo $idea_cta_is_booking ? ' hat-js-booking-trigger' : ''; ?>" href="<?php echo esc_url( $idea_cta_url ); ?>" target="<?php echo esc_attr( $idea_cta_target_final ); ?>">
				<span class="hat-idea__cta-label"><?php echo esc_html( $idea_cta_label ); ?></span>
				<span class="hat-idea__cta-arrow" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" width="35" height="35">
				</span>
			</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( $idea_has_strip ) : ?>
		<div class="hat-idea__strip" aria-hidden="true">
			<?php if ( '' !== $idea_strip_1 ) : ?>
				<span class="hat-idea__strip-item"><?php echo esc_html( $idea_strip_1 ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $idea_strip_2 ) : ?>
				<span class="hat-idea__strip-item"><?php echo esc_html( $idea_strip_2 ); ?></span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
