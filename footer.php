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
		<div class="hat-footer__top">
			<section class="hat-footer__column" aria-labelledby="hat-footer-contact-title">
				<p class="hat-footer__title" id="hat-footer-contact-title"><?php esc_html_e( 'CONTACT', 'hughalroztatoo' ); ?></p>
				<ul class="hat-footer__list">
					<li>
						<a href="tel:+18195986128" class="hat-footer__link">+1 (819) 598-6128</a>
					</li>
					<li>
						<a href="mailto:alroztattoos@gmail.com" class="hat-footer__link">alroztattoos@gmail.com</a>
					</li>
				</ul>
			</section>

			<section class="hat-footer__column" aria-labelledby="hat-footer-social-title">
				<p class="hat-footer__title hat-footer__title--mirror" id="hat-footer-social-title"><?php esc_html_e( 'RÉSEAUX SOCIAUX', 'hughalroztatoo' ); ?></p>
				<ul class="hat-footer__list">
					<li>
						<a class="hat-footer__social hat-footer__social--instagram" href="#">
							<span class="hat-footer__social-icon" aria-hidden="true">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/instagram.svg' ); ?>" alt="" width="18" height="18">
							</span>
							<span><?php esc_html_e( 'INSTAGRAM', 'hughalroztatoo' ); ?></span>
						</a>
					</li>
					<li>
						<a class="hat-footer__link" href="#"><?php esc_html_e( 'FACEBOOK', 'hughalroztatoo' ); ?></a>
					</li>
				</ul>
			</section>

			<div class="hat-footer__to-top-wrap">
				<a class="hat-footer__to-top" href="#"><?php esc_html_e( 'RETOUR EN HAUT', 'hughalroztatoo' ); ?></a>
			</div>
		</div>

		<div class="hat-footer__bottom">
			<p class="hat-footer__copy">&copy; <?php echo esc_html( date( 'Y' ) ); ?> HughAlrozTattoo - <?php esc_html_e( 'Tous Droits Réservés', 'hughalroztatoo' ); ?></p>
			<nav class="hat-footer__legal" aria-label="<?php esc_attr_e( 'Informations légales', 'hughalroztatoo' ); ?>">
				<ul>
					<li><a href="#"><?php esc_html_e( 'Confidentialité', 'hughalroztatoo' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Conditions', 'hughalroztatoo' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Remboursement', 'hughalroztatoo' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Données Personnelles', 'hughalroztatoo' ); ?></a></li>
				</ul>
			</nav>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
