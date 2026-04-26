<?php
/**
 * Template Name: Account
 * Description: Account page without site header/footer or default section title.
 *
 * @package Hugh_Alroz_Tattoo
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'hat-page-account' ); ?>>
<?php wp_body_open(); ?>

<main id="content" class="hat-main hat-page-account__main">
	<div class="hat-container hat-section">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="hat-page-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>

<?php wp_footer(); ?>
</body>
</html>
