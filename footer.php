<?php
/**
 * Footer template
 *
 * @package Hugh_Alroz_Tattoo
 */

$footer_contact_title    = (string) hughalroztatoo_get_site_option( 'footer_contact_title' );
$footer_phone            = (string) hughalroztatoo_get_site_option( 'footer_phone' );
$footer_phone_href       = preg_replace( '/[^0-9+]/', '', $footer_phone );
$footer_email            = (string) hughalroztatoo_get_site_option( 'footer_email' );
$footer_social_title     = (string) hughalroztatoo_get_site_option( 'footer_social_title' );
$footer_instagram_label  = (string) hughalroztatoo_get_site_option( 'footer_instagram_label' );
$footer_instagram_url    = (string) hughalroztatoo_get_site_option( 'footer_instagram_url' );
$footer_facebook_label   = (string) hughalroztatoo_get_site_option( 'footer_facebook_label' );
$footer_facebook_url     = (string) hughalroztatoo_get_site_option( 'footer_facebook_url' );
$footer_to_top_label     = (string) hughalroztatoo_get_site_option( 'footer_to_top_label' );
$footer_copyright_name   = (string) hughalroztatoo_get_site_option( 'footer_copyright_name' );
$footer_copyright_text   = (string) hughalroztatoo_get_site_option( 'footer_copyright_text' );
$footer_legal_links      = hughalroztatoo_get_site_option( 'footer_legal_links', array() );
$footer_legal_links      = is_array( $footer_legal_links ) ? $footer_legal_links : array();
$footer_legal_links      = array_filter(
	$footer_legal_links,
	static function ( $footer_legal_link ) {
		return is_array( $footer_legal_link ) && ! empty( $footer_legal_link['label'] ) && ! empty( $footer_legal_link['url'] );
	}
);

$footer_has_contact = '' !== $footer_contact_title || '' !== $footer_phone || '' !== $footer_email;
$footer_has_social  = '' !== $footer_social_title || ( '' !== $footer_instagram_label && '' !== $footer_instagram_url ) || ( '' !== $footer_facebook_label && '' !== $footer_facebook_url );
$footer_has_top     = $footer_has_contact || $footer_has_social || '' !== $footer_to_top_label;
$footer_copy_parts  = array_filter( array( $footer_copyright_name, $footer_copyright_text ) );
?>
</main><!-- #content -->

<footer class="hat-footer" id="contact">
	<div class="hat-container hat-footer__inner">
		<?php if ( $footer_has_top ) : ?>
		<div class="hat-footer__top">
			<?php if ( $footer_has_contact ) : ?>
			<section class="hat-footer__column"<?php echo '' !== $footer_contact_title ? ' aria-labelledby="hat-footer-contact-title"' : ''; ?>>
				<?php if ( '' !== $footer_contact_title ) : ?>
					<p class="hat-footer__title" id="hat-footer-contact-title"><?php echo esc_html( $footer_contact_title ); ?></p>
				<?php endif; ?>
				<ul class="hat-footer__list">
					<?php if ( '' !== $footer_phone && '' !== $footer_phone_href ) : ?>
					<li>
						<a href="<?php echo esc_url( 'tel:' . $footer_phone_href ); ?>" class="hat-footer__link"><?php echo esc_html( $footer_phone ); ?></a>
					</li>
					<?php endif; ?>
					<?php if ( '' !== $footer_email ) : ?>
					<li>
						<a href="<?php echo esc_url( 'mailto:' . sanitize_email( $footer_email ) ); ?>" class="hat-footer__link"><?php echo esc_html( $footer_email ); ?></a>
					</li>
					<?php endif; ?>
				</ul>
			</section>
			<?php endif; ?>

			<?php if ( $footer_has_social ) : ?>
			<section class="hat-footer__column"<?php echo '' !== $footer_social_title ? ' aria-labelledby="hat-footer-social-title"' : ''; ?>>
				<?php if ( '' !== $footer_social_title ) : ?>
					<p class="hat-footer__title hat-footer__title--mirror" id="hat-footer-social-title"><?php echo esc_html( $footer_social_title ); ?></p>
				<?php endif; ?>
				<ul class="hat-footer__list">
					<?php if ( '' !== $footer_instagram_label && '' !== $footer_instagram_url ) : ?>
					<li>
						<a class="hat-footer__social hat-footer__social--instagram" href="<?php echo esc_url( $footer_instagram_url ); ?>">
							<span class="hat-footer__social-icon" aria-hidden="true">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/instagram.svg' ); ?>" alt="" width="18" height="18">
							</span>
							<span><?php echo esc_html( $footer_instagram_label ); ?></span>
						</a>
					</li>
					<?php endif; ?>
					<?php if ( '' !== $footer_facebook_label && '' !== $footer_facebook_url ) : ?>
					<li>
						<a class="hat-footer__link" href="<?php echo esc_url( $footer_facebook_url ); ?>"><?php echo esc_html( $footer_facebook_label ); ?></a>
					</li>
					<?php endif; ?>
				</ul>
			</section>
			<?php endif; ?>

			<?php if ( '' !== $footer_to_top_label ) : ?>
			<div class="hat-footer__to-top-wrap">
				<a class="hat-footer__to-top" href="#"><?php echo esc_html( $footer_to_top_label ); ?></a>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $footer_copy_parts ) || ! empty( $footer_legal_links ) ) : ?>
		<div class="hat-footer__bottom">
			<?php if ( ! empty( $footer_copy_parts ) ) : ?>
				<p class="hat-footer__copy">&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( implode( ' - ', $footer_copy_parts ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $footer_legal_links ) ) : ?>
			<nav class="hat-footer__legal" aria-label="<?php esc_attr_e( 'Informations légales', 'hughalroztatoo' ); ?>">
				<ul>
					<?php foreach ( $footer_legal_links as $footer_legal_link ) : ?>
						<?php
						$footer_legal_label = isset( $footer_legal_link['label'] ) ? (string) $footer_legal_link['label'] : '';
						$footer_legal_url   = isset( $footer_legal_link['url'] ) ? (string) $footer_legal_link['url'] : '#';

						if ( '' === $footer_legal_label ) {
							continue;
						}
						?>
						<li><a href="<?php echo esc_url( $footer_legal_url ); ?>"><?php echo esc_html( $footer_legal_label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
