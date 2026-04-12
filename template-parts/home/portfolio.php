<?php
/**
 * Front page: portfolio block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-portfolio" id="portfolio" aria-labelledby="hat-portfolio-title">
	<div class="hat-container">
		<div class="hat-portfolio__header">
			<p class="hat-portfolio__eyebrow">
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">[</span>
				<span class="hat-portfolio__eyebrow-word"><?php esc_html_e( 'PORTFOLIO', 'hughalroztatoo' ); ?></span>
				<span class="hat-portfolio__eyebrow-bracket" aria-hidden="true">]</span>
			</p>

			<div class="hat-portfolio__title-row">
				<h2 class="hat-portfolio__title" id="hat-portfolio-title">
					<span class="hat-portfolio__title-line"><?php esc_html_e( "L'ENCRE", 'hughalroztatoo' ); ?></span>
					<span class="hat-portfolio__title-line"><?php esc_html_e( 'NE MENT PAS', 'hughalroztatoo' ); ?></span>
				</h2>

				<span class="hat-portfolio__ornament" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/ornament.svg' ); ?>" alt="">
				</span>
			</div>
		</div>
	</div>

	<div class="hat-portfolio__grid">
		<figure class="hat-portfolio__item hat-portfolio__item--wide">
			<div class="hat-portfolio__img-wrap">
				<img class="hat-portfolio__img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/photo 1.jpg' ); ?>" alt="<?php esc_attr_e( 'Tattoo work 1', 'hughalroztatoo' ); ?>" loading="lazy" width="1920" height="1080">
			</div>
		</figure>

		<figure class="hat-portfolio__item hat-portfolio__item--half">
			<div class="hat-portfolio__img-wrap">
				<img class="hat-portfolio__img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/photo 2.jpg' ); ?>" alt="<?php esc_attr_e( 'Tattoo work 2', 'hughalroztatoo' ); ?>" loading="lazy" width="960" height="720">
			</div>
		</figure>

		<figure class="hat-portfolio__item hat-portfolio__item--half">
			<div class="hat-portfolio__img-wrap">
				<img class="hat-portfolio__img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/photo 3.jpg' ); ?>" alt="<?php esc_attr_e( 'Tattoo work 3', 'hughalroztatoo' ); ?>" loading="lazy" width="960" height="720">
			</div>
		</figure>

		<figure class="hat-portfolio__item hat-portfolio__item--wide">
			<div class="hat-portfolio__img-wrap">
				<img class="hat-portfolio__img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/photo 4.jpg' ); ?>" alt="<?php esc_attr_e( 'Tattoo work 4', 'hughalroztatoo' ); ?>" loading="lazy" width="1920" height="1080">
			</div>
		</figure>
	</div>

	<div class="hat-container">
		<div class="hat-portfolio__footer">
			<a class="hat-portfolio__more" href="#portfolio">
				<span class="hat-portfolio__more-label"><?php esc_html_e( 'VOIR PLUS', 'hughalroztatoo' ); ?></span>
				<img class="hat-portfolio__more-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/voir-plus.svg' ); ?>" alt="" aria-hidden="true" width="22" height="23">
			</a>
		</div>
	</div>
</section>
