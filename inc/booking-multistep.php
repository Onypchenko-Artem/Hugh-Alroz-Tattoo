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
				'stepDate'        => __( 'Choisir une date', 'hughalroztatoo' ),
				'stepFormat'      => __( 'Choisir un format', 'hughalroztatoo' ),
				'stepTime'        => __( 'Choisir un créneau', 'hughalroztatoo' ),
				'stepPhoto'       => __( 'Photos & références', 'hughalroztatoo' ),
				'stepInfo'        => __( 'Vos informations', 'hughalroztatoo' ),
				'stepPay'         => __( 'Paiement / acompte', 'hughalroztatoo' ),
				'stepDone'        => __( 'Réservation confirmée', 'hughalroztatoo' ),
				'next'            => __( 'Suivant', 'hughalroztatoo' ),
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
			'categoryIds'  => $ids,
			'photoFieldId' => $photo_field_id ? $photo_field_id : null,
		)
	);

	return '<div class="hugh-ms-booking" data-hugh-ms-config="' . esc_attr( $data ) . '" role="region" aria-live="polite"></div>';
}
add_shortcode( 'hugh_amelia_booking', 'hughalroztatoo_shortcode_amelia_booking' );
