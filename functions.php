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

require get_template_directory() . '/inc/booking-multistep.php';
require get_template_directory() . '/inc/site-settings.php';

/**
 * Polylang theme string group (Languages → String translations).
 */
const HUGHALROZTATOO_PLL_THEME_GROUP = 'Theme';

/**
 * Polylang group for footer Site settings texts (excluding contact lines and social link labels).
 */
const HUGHALROZTATOO_PLL_FOOTER_GROUP = 'Footer';

/**
 * Translate a theme string registered for Polylang; falls back to gettext without Polylang.
 *
 * @param string $source Default-language string (must match pll_register_string).
 * @return string
 */
function hughalroztatoo_pll__( $source ) {
	if ( function_exists( 'pll__' ) ) {
		return pll__( $source );
	}
	return __( $source, 'hughalroztatoo' );
}

/**
 * Escape attribute text translated via Polylang string registry.
 *
 * @param string $source Default-language string (must match pll_register_string).
 * @return string
 */
function hughalroztatoo_pll_esc_attr__( $source ) {
	if ( function_exists( 'pll_esc_attr__' ) ) {
		return pll_esc_attr__( $source );
	}
	return esc_attr( __( $source, 'hughalroztatoo' ) );
}

/**
 * Translate a Site settings footer text value (same string must be registered for Polylang).
 *
 * @param string $stored Value from ACF options.
 * @return string
 */
function hughalroztatoo_pll_footer_text( $stored ) {
	$stored = (string) $stored;
	if ( '' === $stored ) {
		return '';
	}
	if ( function_exists( 'pll__' ) ) {
		return pll__( $stored );
	}
	return $stored;
}

/**
 * Register UI strings for Polylang String translations.
 */
function hughalroztatoo_register_theme_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	pll_register_string(
		'hat_portfolio_voir_moins',
		'VOIR MOINS',
		HUGHALROZTATOO_PLL_THEME_GROUP,
		false
	);
	pll_register_string(
		'hat_pricing_reserve_format',
		'RÉSERVER CE FORMAT',
		HUGHALROZTATOO_PLL_THEME_GROUP,
		false
	);
	pll_register_string(
		'hat_desktop_menu_close',
		'FERMER',
		HUGHALROZTATOO_PLL_THEME_GROUP,
		false
	);
	pll_register_string(
		'hat_pricing_popular_badge',
		'le plus populaire',
		HUGHALROZTATOO_PLL_THEME_GROUP,
		false
	);
	pll_register_string(
		'hat_footer_legal_nav_aria',
		'Informations légales',
		HUGHALROZTATOO_PLL_FOOTER_GROUP,
		false
	);
}
add_action( 'init', 'hughalroztatoo_register_theme_polylang_strings', 20 );

/**
 * Register current Site settings footer texts for Polylang (translations follow stored French/source strings).
 */
function hughalroztatoo_register_footer_option_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}

	$group = HUGHALROZTATOO_PLL_FOOTER_GROUP;

	$reg = static function ( $register_name, $string ) use ( $group ) {
		$string = trim( (string) $string );
		if ( '' === $string ) {
			return;
		}
		pll_register_string( $register_name, $string, $group, false );
	};

	$reg( 'hat_footer_to_top_label', (string) hughalroztatoo_get_site_option( 'footer_to_top_label' ) );
	$reg( 'hat_footer_social_title', (string) hughalroztatoo_get_site_option( 'footer_social_title' ) );
	$reg( 'hat_footer_copyright_name', (string) hughalroztatoo_get_site_option( 'footer_copyright_name' ) );
	$reg( 'hat_footer_copyright_text', (string) hughalroztatoo_get_site_option( 'footer_copyright_text' ) );

	$legal_rows = hughalroztatoo_get_site_option( 'footer_legal_links', array() );
	if ( is_array( $legal_rows ) ) {
		$n = 0;
		foreach ( $legal_rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$label = isset( $row['label'] ) ? trim( (string) $row['label'] ) : '';
			if ( '' === $label ) {
				continue;
			}
			$reg( 'hat_footer_legal_' . (++$n), $label );
		}
	}
}
add_action( 'init', 'hughalroztatoo_register_footer_option_polylang_strings', 25 );

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
		'https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,300..700;1,300..700&display=swap',
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

	$info_css_path = get_template_directory() . '/assets/css/info-page.css';
	if ( is_page_template( 'template-info-page.php' ) && file_exists( $info_css_path ) ) {
		$info_deps = file_exists( $css_path ) ? array( 'hughalroztatoo-main' ) : array( 'hughalroztatoo-style' );
		wp_enqueue_style(
			'hughalroztatoo-info-page',
			get_template_directory_uri() . '/assets/css/info-page.css',
			$info_deps,
			(string) filemtime( $info_css_path )
		);
	}

	if ( is_front_page() ) {
		$home_styles = array(
			'hero-banner' => 'home-hero-banner.css',
			'pricing'     => 'home-pricing.css',
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

		$home_scripts = array(
			'experience' => 'home-experience.js',
			'faq'        => 'home-faq.js',
			'portfolio'  => 'home-portfolio.js',
		);

		foreach ( $home_scripts as $suffix => $filename ) {
			$home_js_path = get_template_directory() . '/assets/js/' . $filename;
			if ( ! file_exists( $home_js_path ) ) {
				continue;
			}
			wp_enqueue_script(
				'hughalroztatoo-home-' . $suffix,
				get_template_directory_uri() . '/assets/js/' . $filename,
				array( 'hughalroztatoo-main' ),
				(string) filemtime( $home_js_path ),
				true
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hughalroztatoo_assets' );

/**
 * Disable Gutenberg editor, use Classic Editor instead
 */
add_filter( 'use_block_editor_for_post', '__return_false' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );

/**
 * Redirect direct hits to the static front page slug (e.g. /home-page/ or /en/home-page/)
 * to the clean language root URL. Keeps the address bar free of /home-page/ on the home.
 */
function hughalroztatoo_redirect_front_page_slug() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return;
	}

	$front_page_id = (int) get_option( 'page_on_front' );
	if ( ! $front_page_id ) {
		return;
	}

	if ( ! is_page() ) {
		return;
	}

	$queried_id = (int) get_queried_object_id();
	if ( ! $queried_id ) {
		return;
	}

	$front_page_ids = array( $front_page_id );

	if ( function_exists( 'pll_get_post_translations' ) ) {
		$translations = pll_get_post_translations( $front_page_id );
		if ( is_array( $translations ) ) {
			$front_page_ids = array_unique( array_merge( $front_page_ids, array_map( 'intval', array_values( $translations ) ) ) );
		}
	}

	if ( ! in_array( $queried_id, $front_page_ids, true ) ) {
		return;
	}

	$target = home_url( '/' );
	if ( function_exists( 'pll_home_url' ) && function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $queried_id );
		if ( $lang ) {
			$target = pll_home_url( $lang );
		}
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . strtok( $_SERVER['REQUEST_URI'], '?' );
	if ( untrailingslashit( $current_url ) === untrailingslashit( $target ) ) {
		return;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'hughalroztatoo_redirect_front_page_slug' );

/**
 * Build a list of Polylang languages for the current request, each carrying the
 * URL of the current page translated into that language. Falls back to the
 * language home URL when a translation does not exist so the switcher always
 * yields a meaningful destination, regardless of where on the site it is used.
 *
 * @return array<int,array<string,mixed>> List of language descriptors.
 */
function hughalroztatoo_get_language_switcher_items() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array();
	}

	$languages = pll_the_languages(
		array(
			'raw'                    => 1,
			'echo'                   => 0,
			'hide_current'           => 0,
			'hide_if_empty'          => 0,
			'hide_if_no_translation' => 0,
		)
	);

	if ( empty( $languages ) || ! is_array( $languages ) ) {
		return array();
	}

	$items = array();

	foreach ( $languages as $language ) {
		if ( empty( $language['slug'] ) ) {
			continue;
		}

		$slug = (string) $language['slug'];
		$url  = ! empty( $language['url'] ) ? (string) $language['url'] : '';

		if ( ( '' === $url || ! empty( $language['no_translation'] ) ) && function_exists( 'pll_home_url' ) ) {
			$url = pll_home_url( $slug );
		}

		if ( '' === $url ) {
			$url = home_url( '/' );
		}

		$locale = ! empty( $language['locale'] ) ? (string) $language['locale'] : $slug;
		$name   = ! empty( $language['name'] ) ? (string) $language['name'] : $slug;

		$items[] = array(
			'slug'    => $slug,
			'locale'  => $locale,
			'name'    => $name,
			'url'     => $url,
			'current' => ! empty( $language['current_lang'] ),
		);
	}

	return $items;
}

/**
 * Render the bespoke Polylang-backed language switcher used inside the burger
 * menu. Outputs nothing when Polylang is unavailable.
 */
function hughalroztatoo_render_lang_switcher() {
	$items = hughalroztatoo_get_language_switcher_items();
	if ( empty( $items ) ) {
		return;
	}

	?>
	<div class="hat-desktop-menu__langs" aria-label="<?php esc_attr_e( 'Language switcher', 'hughalroztatoo' ); ?>">
		<?php foreach ( $items as $index => $item ) : ?>
			<?php if ( $index > 0 ) : ?>
				<span class="hat-desktop-menu__lang-separator" aria-hidden="true"></span>
			<?php endif; ?>
			<a
				href="<?php echo esc_url( $item['url'] ); ?>"
				hreflang="<?php echo esc_attr( $item['locale'] ? str_replace( '_', '-', $item['locale'] ) : $item['slug'] ); ?>"
				lang="<?php echo esc_attr( $item['slug'] ); ?>"
				<?php echo $item['current'] ? 'aria-current="true"' : ''; ?>
			><?php echo esc_html( strtoupper( $item['slug'] ) ); ?></a>
		<?php endforeach; ?>
	</div>
	<?php
}
