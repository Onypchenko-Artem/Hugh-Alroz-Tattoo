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
$portfolio_url     = function_exists( 'get_field' ) ? (string) get_field( 'home_portfolio_cta_url', $home_post_id ) : '';
$portfolio_items   = function_exists( 'get_field' ) ? get_field( 'home_portfolio_items', $home_post_id ) : array();

$portfolio_eyebrow = '' !== $portfolio_eyebrow ? $portfolio_eyebrow : 'PORTFOLIO';
$portfolio_title_1 = '' !== $portfolio_title_1 ? $portfolio_title_1 : "L'ENCRE";
$portfolio_title_2 = '' !== $portfolio_title_2 ? $portfolio_title_2 : 'NE MENT PAS';
$portfolio_cta     = '' !== $portfolio_cta ? $portfolio_cta : 'VOIR PLUS';
$portfolio_url     = '' !== $portfolio_url ? $portfolio_url : '#portfolio';

if ( ! is_array( $portfolio_items ) || empty( $portfolio_items ) ) {
	$portfolio_items = array(
		array(
			'image' => get_template_directory_uri() . '/assets/images/photo 1.jpg',
			'alt'   => 'Tattoo work 1',
			'size'  => 'wide',
		),
		array(
			'image' => get_template_directory_uri() . '/assets/images/photo 2.jpg',
			'alt'   => 'Tattoo work 2',
			'size'  => 'half',
		),
		array(
			'image' => get_template_directory_uri() . '/assets/images/photo 3.jpg',
			'alt'   => 'Tattoo work 3',
			'size'  => 'half',
		),
		array(
			'image' => get_template_directory_uri() . '/assets/images/photo 4.jpg',
			'alt'   => 'Tattoo work 4',
			'size'  => 'wide',
		),
	);
}
?>

<section class="hat-portfolio" id="portfolio" aria-labelledby="hat-portfolio-title">
	<div class="hat-container">
		<div class="hat-portfolio__header">
			<p class="hat-portfolio__eyebrow">
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">[</span>
				<span class="hat-portfolio__eyebrow-word"><?php echo esc_html( $portfolio_eyebrow ); ?></span>
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">]</span>
			</p>

			<div class="hat-portfolio__title-row">
				<h2 class="hat-portfolio__title" id="hat-portfolio-title">
					<span class="hat-portfolio__title-line"><?php echo esc_html( $portfolio_title_1 ); ?></span>
					<span class="hat-portfolio__title-line"><?php echo esc_html( $portfolio_title_2 ); ?></span>
				</h2>

				<span class="hat-portfolio__ornament" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/ornament.svg' ); ?>" alt="">
				</span>
			</div>
		</div>
	</div>

	<div class="hat-portfolio__grid">
		<?php foreach ( $portfolio_items as $item ) : ?>
			<?php
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
			?>
			<figure class="hat-portfolio__item hat-portfolio__item--<?php echo esc_attr( $size ); ?>">
				<div class="hat-portfolio__img-wrap">
					<img class="hat-portfolio__img" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
				</div>
			</figure>
		<?php endforeach; ?>
	</div>

	<div class="hat-container">
		<div class="hat-portfolio__footer">
			<a class="hat-portfolio__more" href="<?php echo esc_url( $portfolio_url ); ?>">
				<span class="hat-portfolio__more-label"><?php echo esc_html( $portfolio_cta ); ?></span>
				<img class="hat-portfolio__more-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/voir-plus.svg' ); ?>" alt="" aria-hidden="true" width="22" height="23">
			</a>
		</div>
	</div>
</section>
