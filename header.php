<?php
/**
 * Header template
 *
 * @package Hugh_Alroz_Tattoo
 */

$hat_is_hero        = is_front_page();
$hat_bg_poster_url  = esc_url( get_template_directory_uri() . '/assets/images/video-hero.jpg' );
$hat_bg_video_url   = esc_url( get_template_directory_uri() . '/assets/videos/background-video.mp4' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="hat-site-bg" aria-hidden="true">
	<video
		class="hat-site-bg__video"
		autoplay
		muted
		loop
		playsinline
		preload="auto"
		disablepictureinpicture
		disableremoteplayback
		controlslist="nodownload nofullscreen noplaybackrate noremoteplayback"
		tabindex="-1"
		poster="<?php echo $hat_bg_poster_url; ?>"
	>
		<source src="<?php echo $hat_bg_video_url; ?>" type="video/mp4">
	</video>
</div>

<header class="hat-header<?php echo $hat_is_hero ? ' hat-header--hero' : ''; ?>">
	<div class="hat-container hat-header__inner">
		<div class="hat-header__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php elseif ( $hat_is_hero ) : ?>
				<a class="hat-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="hat-header__brand-mark" aria-hidden="true">A</span>
					<span class="hat-header__brand-text">
						<span class="hat-header__brand-line"><?php esc_html_e( 'ALROZ', 'hughalroztatoo' ); ?></span>
						<span class="hat-header__brand-line"><?php esc_html_e( 'TATTOO', 'hughalroztatoo' ); ?></span>
					</span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>
		<nav class="hat-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'hughalroztatoo' ); ?>">
			<?php if ( $hat_is_hero ) : ?>
				<button
					class="hat-header__menu-toggle"
					type="button"
					aria-expanded="false"
					aria-controls="hat-desktop-menu"
				>
					<span class="hat-header__nav-plus" aria-hidden="true">+</span>
					<?php esc_html_e( 'MENU', 'hughalroztatoo' ); ?>
				</button>
				<div class="hat-header__mobile-nav">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => '',
							'fallback_cb'    => function () {
								echo '<ul><li><a href="#"><span class="hat-header__nav-plus" aria-hidden="true">+</span> ' . esc_html__( 'MENU', 'hughalroztatoo' ) . '</a></li></ul>';
							},
							'items_wrap'     => '<ul>%3$s</ul>',
						)
					);
					?>
				</div>
			<?php else : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => '',
						'fallback_cb'    => function () {
							echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'hughalroztatoo' ) . '</a></li></ul>';
						},
						'items_wrap'     => '<ul>%3$s</ul>',
					)
				);
				?>
			<?php endif; ?>
		</nav>
	</div>

	<?php if ( $hat_is_hero ) : ?>
		<div class="hat-desktop-menu" id="hat-desktop-menu" hidden>
			<div class="hat-desktop-menu__inner">
				<div class="hat-desktop-menu__top">
					<div class="hat-desktop-menu__logo">
						<?php if ( has_custom_logo() ) : ?>
							<?php the_custom_logo(); ?>
						<?php else : ?>
							<a class="hat-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<span class="hat-header__brand-mark" aria-hidden="true">A</span>
								<span class="hat-header__brand-text">
									<span class="hat-header__brand-line"><?php esc_html_e( 'ALROZ', 'hughalroztatoo' ); ?></span>
									<span class="hat-header__brand-line"><?php esc_html_e( 'TATTOO', 'hughalroztatoo' ); ?></span>
								</span>
							</a>
						<?php endif; ?>
					</div>
					<div class="hat-desktop-menu__controls">
						<button class="hat-desktop-menu__close" type="button" data-menu-close>
							<span aria-hidden="true">
								<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/close.svg' ); ?>" alt="" width="13" height="13">
							</span>
							<?php esc_html_e( 'FERMER', 'hughalroztatoo' ); ?>
						</button>
					</div>
				</div>

				<nav class="hat-desktop-menu__nav" aria-label="<?php esc_attr_e( 'Desktop menu', 'hughalroztatoo' ); ?>">
					<span class="hat-desktop-menu__mark" aria-hidden="true">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/spark-70.svg' ); ?>" alt="" width="40" height="40">
					</span>
					<span class="hat-desktop-menu__mode">( A )</span>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'hat-desktop-menu__list',
							'fallback_cb'    => function () {
								echo '<ul class="hat-desktop-menu__list">
									<li><a href="#pricing">' . esc_html__( 'FORMAT', 'hughalroztatoo' ) . '</a></li>
									<li><a href="#portfolio">' . esc_html__( 'PORTFOLIO', 'hughalroztatoo' ) . '</a></li>
									<li><a href="#faq">' . esc_html__( 'FAQ', 'hughalroztatoo' ) . '</a></li>
									<li><a href="#contact">' . esc_html__( 'CONTACTS', 'hughalroztatoo' ) . '</a></li>
								</ul>';
							},
						)
					);
					?>
				</nav>

				<div class="hat-desktop-menu__bottom">
					<div class="hat-desktop-menu__langs" aria-label="<?php esc_attr_e( 'Language switcher', 'hughalroztatoo' ); ?>">
						<a href="#" aria-current="true">FR</a>
						<span class="hat-desktop-menu__lang-separator" aria-hidden="true"></span>
						<a href="#">EN</a>
					</div>
					<a class="hat-desktop-menu__email" href="mailto:alroztattoos@gmail.com">alroztattoos@gmail.com</a>
				</div>
			</div>
		</div>
	<?php endif; ?>
</header>

<main id="content" class="hat-main">
