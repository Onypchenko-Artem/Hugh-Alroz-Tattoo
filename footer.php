<?php
/**
 * Footer template
 *
 * @package Hugh_Alroz_Tattoo
 */
?>
</main><!-- #content -->

<footer class="hat-footer" id="contact">
	<div class="hat-container hat-footer__inner">
		<div class="hat-footer__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<strong><?php bloginfo( 'name' ); ?></strong>
			<?php endif; ?>
		</div>
		<?php
		wp_nav_menu( array(
			'theme_location' => 'footer',
			'container'      => 'nav',
			'container_class' => 'hat-footer__nav',
			'menu_class'     => '',
			'depth'          => 1,
		) );
		?>
		<div class="hat-footer__copy">
			&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'hughalroztatoo' ); ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
