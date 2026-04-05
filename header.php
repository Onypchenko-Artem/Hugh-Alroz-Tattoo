<?php
/**
 * Header template
 *
 * @package Hugh_Alroz_Tattoo
 */

$hat_is_hero = is_front_page();
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
						<span class="hat-header__brand-line"><?php esc_html_e( 'TATOO', 'hughalroztatoo' ); ?></span>
					</span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>
		<nav class="hat-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'hughalroztatoo' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => '',
					'fallback_cb'    => function () use ( $hat_is_hero ) {
						if ( $hat_is_hero ) {
							echo '<ul><li><a href="#">' . esc_html__( '+ MENU', 'hughalroztatoo' ) . '</a></li></ul>';
						} else {
							echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'hughalroztatoo' ) . '</a></li></ul>';
						}
					},
					'items_wrap'     => '<ul>%3$s</ul>',
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="content" class="hat-main">
