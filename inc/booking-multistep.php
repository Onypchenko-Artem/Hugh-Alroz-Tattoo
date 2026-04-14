<?php
/**
 * Custom Amelia multistep booking (shortcode + assets).
 *
 * Usage: add [hugh_amelia_booking] to a page. Requires Amelia plugin.
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option key for custom category descriptions.
 */
const HUGHALROZTATOO_BOOKING_CATEGORY_DESCRIPTIONS_OPTION = 'hughalroztatoo_booking_category_descriptions';

/**
 * Whether Amelia is active.
 */
function hughalroztatoo_amelia_active() {
	return class_exists( 'AmeliaBooking\\Plugin', false ) || defined( 'AMELIA_PATH' );
}

/**
 * Detect shortcode in singular content (Classic Editor).
 *
 * @param string|null $content Post content.
 * @return bool
 */
function hughalroztatoo_content_has_amelia_booking_shortcode( $content ) {
	return is_string( $content ) && has_shortcode( $content, 'hugh_amelia_booking' );
}

/**
 * Get saved booking category descriptions.
 *
 * @return array<int, string>
 */
function hughalroztatoo_get_booking_category_descriptions() {
	$raw = get_option( HUGHALROZTATOO_BOOKING_CATEGORY_DESCRIPTIONS_OPTION, array() );
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$out = array();
	foreach ( $raw as $cat_id => $description ) {
		$id = absint( $cat_id );
		if ( ! $id ) {
			continue;
		}
		$text = is_string( $description ) ? trim( $description ) : '';
		if ( '' !== $text ) {
			$out[ $id ] = $text;
		}
	}

	return $out;
}

/**
 * Load Amelia categories for admin settings page.
 *
 * @return array<int, array<string, mixed>>
 */
function hughalroztatoo_get_amelia_categories_for_admin() {
	global $wpdb;

	$table = $wpdb->prefix . 'amelia_categories';
	$like  = $wpdb->esc_like( $table );
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );
	if ( $exists !== $table ) {
		return array();
	}

	$rows = $wpdb->get_results( "SELECT id, name, status FROM {$table} ORDER BY position ASC, id ASC", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return is_array( $rows ) ? $rows : array();
}

/**
 * Register admin page for booking category descriptions.
 */
function hughalroztatoo_register_booking_category_descriptions_page() {
	add_submenu_page(
		'themes.php',
		__( 'Booking Categories', 'hughalroztatoo' ),
		__( 'Booking Categories', 'hughalroztatoo' ),
		'manage_options',
		'hughalroztatoo-booking-categories',
		'hughalroztatoo_render_booking_category_descriptions_page'
	);
}
add_action( 'admin_menu', 'hughalroztatoo_register_booking_category_descriptions_page' );

/**
 * Render admin page for booking category descriptions.
 */
function hughalroztatoo_render_booking_category_descriptions_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if (
		isset( $_POST['hughalroztatoo_booking_category_descriptions_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hughalroztatoo_booking_category_descriptions_nonce'] ) ), 'hughalroztatoo_save_booking_category_descriptions' )
	) {
		$descriptions = array();
		$incoming     = isset( $_POST['category_descriptions'] ) ? (array) wp_unslash( $_POST['category_descriptions'] ) : array();

		foreach ( $incoming as $cat_id => $description ) {
			$id   = absint( $cat_id );
			$text = is_string( $description ) ? sanitize_textarea_field( $description ) : '';
			if ( $id && '' !== trim( $text ) ) {
				$descriptions[ $id ] = $text;
			}
		}

		update_option( HUGHALROZTATOO_BOOKING_CATEGORY_DESCRIPTIONS_OPTION, $descriptions );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Booking category descriptions saved.', 'hughalroztatoo' ) . '</p></div>';
	}

	$categories   = hughalroztatoo_get_amelia_categories_for_admin();
	$descriptions = hughalroztatoo_get_booking_category_descriptions();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Booking Categories', 'hughalroztatoo' ); ?></h1>
		<p><?php esc_html_e( 'Add custom descriptions for Amelia categories shown on step 1 of booking.', 'hughalroztatoo' ); ?></p>
		<?php if ( empty( $categories ) ) : ?>
			<p><?php esc_html_e( 'No Amelia categories found.', 'hughalroztatoo' ); ?></p>
		<?php else : ?>
			<form method="post">
				<?php wp_nonce_field( 'hughalroztatoo_save_booking_category_descriptions', 'hughalroztatoo_booking_category_descriptions_nonce' ); ?>
				<table class="form-table" role="presentation">
					<tbody>
						<?php foreach ( $categories as $category ) : ?>
							<?php
							$cat_id   = isset( $category['id'] ) ? absint( $category['id'] ) : 0;
							$name     = isset( $category['name'] ) ? (string) $category['name'] : '';
							$status   = isset( $category['status'] ) ? (string) $category['status'] : '';
							$current  = isset( $descriptions[ $cat_id ] ) ? $descriptions[ $cat_id ] : '';
							$field_id = 'hat_booking_cat_desc_' . $cat_id;
							?>
							<tr>
								<th scope="row">
									<label for="<?php echo esc_attr( $field_id ); ?>">
										<?php echo esc_html( $name ); ?>
										<?php if ( $status ) : ?>
											<small style="display:block;color:#777;"><?php echo esc_html( $status ); ?></small>
										<?php endif; ?>
									</label>
								</th>
								<td>
									<textarea id="<?php echo esc_attr( $field_id ); ?>" name="category_descriptions[<?php echo esc_attr( (string) $cat_id ); ?>]" rows="4" class="large-text"><?php echo esc_textarea( $current ); ?></textarea>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button( __( 'Save descriptions', 'hughalroztatoo' ) ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Register scripts/styles (loaded only when shortcode is present).
 */
function hughalroztatoo_register_amelia_multistep_assets() {
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();

	$css_path = $theme_dir . '/assets/css/booking-multistep.css';
	$js_path  = $theme_dir . '/assets/js/booking-multistep.js';

	if ( file_exists( $css_path ) ) {
		wp_register_style(
			'hughalroztatoo-booking-ms',
			$theme_uri . '/assets/css/booking-multistep.css',
			array(),
			(string) filemtime( $css_path )
		);
	}

	if ( file_exists( $js_path ) ) {
		wp_register_script(
			'hughalroztatoo-booking-ms',
			$theme_uri . '/assets/js/booking-multistep.js',
			array(),
			(string) filemtime( $js_path ),
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hughalroztatoo_register_amelia_multistep_assets', 5 );

/**
 * Attach config to booking multistep script (once per request).
 */
function hughalroztatoo_localize_amelia_multistep_script() {
	static $done = false;
	if ( $done || ! wp_script_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		return;
	}
	$done = true;

	$amelia_settings = json_decode( (string) get_option( 'amelia_settings', '{}' ), true );
	$grecap          = isset( $amelia_settings['general']['googleRecaptcha'] ) ? $amelia_settings['general']['googleRecaptcha'] : array();
	$recaptcha_on    = ! empty( $grecap['siteKey'] ) && ! empty( $grecap['secret'] );

	$tz = function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : 'UTC';

	wp_localize_script(
		'hughalroztatoo-booking-ms',
		'hughAmeliaBooking',
		array(
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'ajax-nonce' ),
			'timeZone'         => $tz,
			'locale'           => get_locale(),
			/**
			 * BCP 47 tag for calendar month names (independent of WP admin language).
			 * Filter: hughalroztatoo_booking_calendar_locale
			 */
			'calendarLocale'   => apply_filters( 'hughalroztatoo_booking_calendar_locale', 'fr-FR' ),
			'recaptchaSiteKey' => $recaptcha_on ? (string) $grecap['siteKey'] : '',
			'recaptchaOn'      => $recaptcha_on,
			'strings'          => array(
				'loading'         => __( 'Chargement…', 'hughalroztatoo' ),
				'errorGeneric'    => __( 'Une erreur est survenue. Réessayez.', 'hughalroztatoo' ),
				'errorNoServices' => __( 'Aucun service Amelia disponible. Vérifiez les catégories et la visibilité des services.', 'hughalroztatoo' ),
				'errorSlots'      => __( 'Impossible de charger les créneaux pour cette date.', 'hughalroztatoo' ),
				'errorBooking'    => __( 'La réservation a échoué.', 'hughalroztatoo' ),
				'recaptcha'       => __( 'Vérification anti-robot requise.', 'hughalroztatoo' ),
				'stepType'        => __( 'Type de visite', 'hughalroztatoo' ),
				'stepCategory'    => __( 'Type de visite', 'hughalroztatoo' ),
				'stepCategoryHint'=> __( 'Nouveau projet ou suite ?', 'hughalroztatoo' ),
				'stepDate'        => __( 'Choisir une date', 'hughalroztatoo' ),
				'stepService'     => __( 'Choisir un format', 'hughalroztatoo' ),
				'stepFormat'      => __( 'Choisir un format', 'hughalroztatoo' ),
				'stepTime'        => __( 'Choisir un créneau', 'hughalroztatoo' ),
				'stepPhoto'       => __( 'Photos & références', 'hughalroztatoo' ),
				'stepInfo'        => __( 'Vos informations', 'hughalroztatoo' ),
				'stepPay'         => __( 'Paiement / acompte', 'hughalroztatoo' ),
				'stepDone'        => __( 'Réservation confirmée', 'hughalroztatoo' ),
				'next'            => __( 'continuer', 'hughalroztatoo' ),
				'back'            => __( 'Retour', 'hughalroztatoo' ),
				'submit'          => __( 'Confirmer la réservation', 'hughalroztatoo' ),
				'photoHint'       => __( 'Ajoutez une ou plusieurs photos (zone à tatouer, références). Les fichiers sont enregistrés dans Amelia comme pièces jointes du champ personnalisé « fichier » du service.', 'hughalroztatoo' ),
				'photoRequired'   => __( 'Veuillez joindre au moins une photo.', 'hughalroztatoo' ),
				'photoNoField'    => __( 'Aucun champ « fichier » Amelia n’est lié à ce service : ajoutez-en un dans Amelia (Personnalisé → champs du service) ou précisez l’ID du champ dans le shortcode : photo_field="ID".', 'hughalroztatoo' ),
				'photoClear'      => __( 'Retirer les fichiers', 'hughalroztatoo' ),
				'photoChoose'     => __( 'Choisir des images', 'hughalroztatoo' ),
				'paySummary'      => __( 'Récapitulatif', 'hughalroztatoo' ),
				'payOnSite'       => __( 'Paiement sur place (réglé dans Amelia : acompte en ligne si activé).', 'hughalroztatoo' ),
				'noExtras'        => __( 'Aucune option de format pour ce service — étape ignorée.', 'hughalroztatoo' ),
				'pickService'     => __( 'Choisissez une prestation', 'hughalroztatoo' ),
				'pickCategory'    => __( 'Choisissez une catégorie', 'hughalroztatoo' ),
				'pickDate'        => __( 'Sélectionnez un jour disponible', 'hughalroztatoo' ),
				'pickTime'        => __( 'Sélectionnez une heure', 'hughalroztatoo' ),
				'pickFormat'      => __( 'Choisissez un format / taille', 'hughalroztatoo' ),
			),
		)
	);

	if ( $recaptcha_on ) {
		wp_enqueue_script(
			'google-recaptcha-v3',
			'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( (string) $grecap['siteKey'] ),
			array(),
			null,
			true
		);
		wp_script_add_data( 'google-recaptcha-v3', 'async', true );
		wp_script_add_data( 'google-recaptcha-v3', 'defer', true );
	}
}

/**
 * Enqueue when post content contains shortcode.
 */
function hughalroztatoo_maybe_enqueue_amelia_multistep() {
	if ( is_admin() || ! hughalroztatoo_amelia_active() ) {
		return;
	}

	global $post;
	if ( ! $post || ! hughalroztatoo_content_has_amelia_booking_shortcode( $post->post_content ) ) {
		return;
	}

	if ( wp_style_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_style( 'hughalroztatoo-booking-ms' );
	}
	if ( wp_script_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-ms' );
		hughalroztatoo_localize_amelia_multistep_script();
	}
}
add_action( 'wp_enqueue_scripts', 'hughalroztatoo_maybe_enqueue_amelia_multistep', 20 );

/**
 * Shortcode callback.
 *
 * Attributes:
 * - category: comma-separated Amelia category IDs to limit the list (optional).
 * - photo_field: Amelia custom field ID (type « fichier ») if it cannot be detected automatically (optional).
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function hughalroztatoo_shortcode_amelia_booking( $atts ) {
	if ( ! hughalroztatoo_amelia_active() ) {
		return '<p class="hugh-ms-booking__notice">' . esc_html__( 'Amelia n’est pas activé.', 'hughalroztatoo' ) . '</p>';
	}

	$atts = shortcode_atts(
		array(
			'category'    => '',
			'photo_field' => '',
		),
		$atts,
		'hugh_amelia_booking'
	);

	$ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) $atts['category'] ) ) ) );

	$photo_field_id = absint( $atts['photo_field'] );

	if ( wp_style_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_style( 'hughalroztatoo-booking-ms' );
	}
	if ( wp_script_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-ms' );
		hughalroztatoo_localize_amelia_multistep_script();
	}

	$data = wp_json_encode(
		array(
			'categoryIds'          => $ids,
			'categoryDescriptions' => hughalroztatoo_get_booking_category_descriptions(),
			'photoFieldId'         => $photo_field_id ? $photo_field_id : null,
		)
	);

	return '<div class="hugh-ms-booking" data-hugh-ms-config="' . esc_attr( $data ) . '" role="region" aria-live="polite"></div>';
}
add_shortcode( 'hugh_amelia_booking', 'hughalroztatoo_shortcode_amelia_booking' );
