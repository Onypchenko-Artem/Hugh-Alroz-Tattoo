<?php
/**
 * Hugh Alroz Tattoo theme functions and definitions
 *
 * @package Hugh_Alroz_Tattoo
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HUGHALROZTATOO_VERSION', '1.0' );

/**
 * Preconnect to Google Fonts (Inter Tight)
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function hughalroztatoo_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'hughalroztatoo_resource_hints', 10, 2 );

/**
 * Theme setup
 */
function hughalroztatoo_setup() {
	load_theme_textdomain( 'hughalroztatoo', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => array( 'site-title', 'site-description' ),
	) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
}
add_action( 'after_setup_theme', 'hughalroztatoo_setup' );

/**
 * Register menus
 */
function hughalroztatoo_menus() {
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'hughalroztatoo' ),
		'footer'  => __( 'Footer Menu', 'hughalroztatoo' ),
	) );
}
add_action( 'init', 'hughalroztatoo_menus' );

/**
 * Enqueue scripts and styles
 */
function hughalroztatoo_assets() {
	$css_path      = get_template_directory() . '/assets/css/main.css';
	$js_path       = get_template_directory() . '/assets/js/main.js';

	wp_enqueue_style(
		'hughalroztatoo-font-inter-tight',
		'https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,400..700;1,400..700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'hughalroztatoo-style', get_stylesheet_uri(), array( 'hughalroztatoo-font-inter-tight' ), HUGHALROZTATOO_VERSION );

	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'hughalroztatoo-main',
			get_template_directory_uri() . '/assets/css/main.css',
			array( 'hughalroztatoo-style' ),
			(string) filemtime( $css_path )
		);
	}

	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'hughalroztatoo-main',
			get_template_directory_uri() . '/assets/js/main.js',
			array(),
			(string) filemtime( $js_path ),
			true
		);
	}

	if ( is_front_page() ) {
		$home_styles = array(
			'hero-banner' => 'home-hero-banner.css',
			'services'    => 'home-services.css',
			'portfolio'   => 'home-portfolio.css',
			'experience'  => 'home-experience.css',
			'faq'         => 'home-faq.css',
			'idea'        => 'home-idea.css',
		);

		$style_deps = file_exists( $css_path ) ? array( 'hughalroztatoo-main' ) : array( 'hughalroztatoo-style' );

		foreach ( $home_styles as $suffix => $filename ) {
			$home_css_path = get_template_directory() . '/assets/css/' . $filename;
			if ( ! file_exists( $home_css_path ) ) {
				continue;
			}
			wp_enqueue_style(
				'hughalroztatoo-home-' . $suffix,
				get_template_directory_uri() . '/assets/css/' . $filename,
				$style_deps,
				(string) filemtime( $home_css_path )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hughalroztatoo_assets' );
