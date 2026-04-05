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

<section class="hat-portfolio hat-section hat-section--alt" id="portfolio" aria-labelledby="hat-portfolio-title">
	<div class="hat-container">
		<h2 class="hat-section__title" id="hat-portfolio-title"><?php esc_html_e( 'Portfolio', 'hughalroztatoo' ); ?></h2>
		<ul class="hat-portfolio__grid">
			<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
				<li class="hat-portfolio__item">
					<div class="hat-portfolio__placeholder" aria-hidden="true"></div>
					<span class="hat-portfolio__label"><?php echo esc_html( sprintf( __( 'Work %d', 'hughalroztatoo' ), $i ) ); ?></span>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>
