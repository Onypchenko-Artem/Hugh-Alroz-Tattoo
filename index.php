<?php
/**
 * Main template fallback
 *
 * @package Hugh_Alroz_Tattoo
 */

get_header();
?>

<main class="hat-container hat-section">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<?php the_content(); ?>
			</article>
			<?php
		endwhile;
	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'hughalroztatoo' ); ?></p>
		<?php
	endif;
	?>
</main>

<?php
get_footer();
