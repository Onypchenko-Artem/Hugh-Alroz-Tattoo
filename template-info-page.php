<?php
/**
 * Template Name: Info page
 * Description: Legal and informational pages (privacy, terms, etc.) with layout matching the site design system.
 *
 * @package Hugh_Alroz_Tattoo
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="hat-info-page hat-container">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'hat-info-page__article' ); ?>>
			<header class="hat-info-page__header">
				<h1 class="hat-info-page__title"><?php the_title(); ?></h1>
			</header>
			<div class="hat-info-page__body">
				<p class="hat-info-page__updated">
					<?php
					printf(
						/* translators: %s: last updated date (localized). */
						esc_html__( 'Dernière mise à jour : %s', 'hughalroztatoo' ),
						esc_html( date_i18n( 'j F Y', (int) get_post_modified_time( 'U', true ) ) )
					);
					?>
				</p>
				<div class="hat-info-page__content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
	</div>
	<?php
endwhile;

get_footer();
