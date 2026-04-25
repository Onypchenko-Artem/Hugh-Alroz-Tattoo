<?php
/**
 * Front page: portfolio block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();

$portfolio_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'home_portfolio_eyebrow', $home_post_id ) : '';
$portfolio_title_1 = function_exists( 'get_field' ) ? (string) get_field( 'home_portfolio_title_line_1', $home_post_id ) : '';
$portfolio_title_2 = function_exists( 'get_field' ) ? (string) get_field( 'home_portfolio_title_line_2', $home_post_id ) : '';
$portfolio_cta     = function_exists( 'get_field' ) ? (string) get_field( 'home_portfolio_cta_label', $home_post_id ) : '';
$portfolio_items   = function_exists( 'get_field' ) ? get_field( 'home_portfolio_items', $home_post_id ) : array();

$portfolio_items   = is_array( $portfolio_items ) ? $portfolio_items : array();

$portfolio_entries = array();

foreach ( $portfolio_items as $item ) {
	$image = isset( $item['image'] ) ? $item['image'] : '';
	$alt   = isset( $item['alt'] ) ? (string) $item['alt'] : '';
	$size  = isset( $item['size'] ) && 'half' === $item['size'] ? 'half' : 'wide';

	if ( is_array( $image ) ) {
		$image_url = isset( $image['url'] ) ? (string) $image['url'] : '';
		$image_alt = isset( $image['alt'] ) && '' !== (string) $image['alt'] ? (string) $image['alt'] : $alt;
	} else {
		$image_url = (string) $image;
		$image_alt = $alt;
	}

	if ( '' === $image_url ) {
		continue;
	}

	$portfolio_entries[] = array(
		'url'  => $image_url,
		'alt'  => $image_alt,
		'size' => $size,
	);
}

$portfolio_initial_count = 4;
$portfolio_step_count    = 4;
$portfolio_total_count   = count( $portfolio_entries );
$portfolio_has_more      = $portfolio_total_count > $portfolio_initial_count;
$portfolio_less_label    = __( 'VOIR MOINS', 'hughalroztatoo' );
$portfolio_has_heading   = '' !== $portfolio_eyebrow || '' !== $portfolio_title_1 || '' !== $portfolio_title_2;
$portfolio_has_cta       = $portfolio_has_more && '' !== $portfolio_cta;

if ( ! $portfolio_has_heading && empty( $portfolio_entries ) ) {
	return;
}
?>

<section
	class="hat-portfolio"
	id="portfolio"
	<?php echo ( '' !== $portfolio_title_1 || '' !== $portfolio_title_2 ) ? 'aria-labelledby="hat-portfolio-title"' : ''; ?>
	data-portfolio-root
	data-portfolio-initial="<?php echo esc_attr( (string) $portfolio_initial_count ); ?>"
	data-portfolio-step="<?php echo esc_attr( (string) $portfolio_step_count ); ?>"
>
	<?php if ( $portfolio_has_heading ) : ?>
	<div class="hat-container">
		<div class="hat-portfolio__header">
			<?php if ( '' !== $portfolio_eyebrow ) : ?>
			<p class="hat-portfolio__eyebrow">
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-portfolio__eyebrow-word"><?php echo esc_html( $portfolio_eyebrow ); ?></span>
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">)</span>
			</p>
			<?php endif; ?>

			<?php if ( '' !== $portfolio_title_1 || '' !== $portfolio_title_2 ) : ?>
			<div class="hat-portfolio__title-row">
				<h2 class="hat-portfolio__title" id="hat-portfolio-title">
					<?php if ( '' !== $portfolio_title_1 ) : ?>
						<span class="hat-portfolio__title-line"><?php echo esc_html( $portfolio_title_1 ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $portfolio_title_2 ) : ?>
						<span class="hat-portfolio__title-line"><?php echo esc_html( $portfolio_title_2 ); ?></span>
					<?php endif; ?>
				</h2>

				<span class="hat-portfolio__ornament" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/ornament.svg' ); ?>" alt="">
				</span>
			</div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $portfolio_entries ) ) : ?>
	<div class="hat-portfolio__grid" data-portfolio-grid>
		<?php foreach ( $portfolio_entries as $index => $entry ) : ?>
			<?php
			$hidden_class = $index >= $portfolio_initial_count ? ' is-hidden' : '';
			?>
			<figure class="hat-portfolio__item hat-portfolio__item--<?php echo esc_attr( $entry['size'] ); ?><?php echo esc_attr( $hidden_class ); ?>" data-portfolio-item>
				<div class="hat-portfolio__img-wrap">
					<img class="hat-portfolio__img" src="<?php echo esc_url( $entry['url'] ); ?>" alt="<?php echo esc_attr( $entry['alt'] ); ?>" loading="lazy">
				</div>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( $portfolio_has_cta ) : ?>
	<div class="hat-container">
		<div class="hat-portfolio__footer" data-portfolio-footer>
			<button class="hat-portfolio__more" type="button" data-portfolio-more data-portfolio-less-label="<?php echo esc_attr( $portfolio_less_label ); ?>" aria-expanded="false">
				<span class="hat-portfolio__more-label" data-portfolio-more-label><?php echo esc_html( $portfolio_cta ); ?></span>
				<img class="hat-portfolio__more-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/voir-plus.svg' ); ?>" alt="" aria-hidden="true" width="22" height="23">
			</button>
		</div>
	</div>
	<?php endif; ?>
</section>
