<?php
/**
 * Default page template
 *
 * @package Hugh_Alroz_Tattoo
 */

get_header();
?>

<main class="hat-container hat-section">
	<?php
	while ( have_posts() ) :
		the_post();
		$hat_hide_title = has_shortcode( (string) get_post_field( 'post_content', get_the_ID() ), 'hugh_amelia_booking' );
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( ! $hat_hide_title ) : ?>
				<h1 class="hat-section__title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<div class="hat-page-content">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
