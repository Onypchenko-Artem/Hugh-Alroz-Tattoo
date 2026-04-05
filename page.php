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
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="hat-section__title"><?php the_title(); ?></h1>
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
