<?php
/**
 * Custom Amelia multistep booking (shortcode + assets).
 *
 * Usage: wizard is injected in the footer as a popup. Optional shortcode [hugh_amelia_booking] on the Booking template
 * remains supported for admins; frontend output strips it (see hughalroztatoo_strip_booking_shortcode_from_booking_template). Requires Amelia.
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
 * Resolve the WordPress page that holds Booking ACF (template page-booking.php).
 *
 * Used when the multistep renders in the global footer modal (queried object is not that page).
 *
 * @return int Post ID or 0.
 */
function hughalroztatoo_booking_acf_source_post_id() {
	static $memo = false;
	if ( false !== $memo ) {
		return (int) $memo;
	}
	$memo = 0;

	$post_id = (int) get_queried_object_id();
	if ( $post_id > 0 ) {
		$tpl = get_page_template_slug( $post_id );
		if ( 'page-booking.php' === (string) $tpl ) {
			return $memo = $post_id;
		}
	}

	$page = get_posts(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'suppress_filters'       => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'page-booking.php',
		)
	);

	if ( ! empty( $page[0] ) && isset( $page[0]->ID ) ) {
		$memo = (int) $page[0]->ID;
	}

	return (int) apply_filters( 'hughalroztatoo_booking_acf_source_post_id', (int) $memo );
}

/**
 * Whether a frontend URL targets the Booking page template (opens modal via .hat-js-booking-trigger).
 *
 * Matches by template slug (incl. Polylang locales), canonical booking post ID, or normalized URLs.
 *
 * @param string $cta_url Link from ACF or markup.
 * @return bool
 */
function hughalroztatoo_url_is_booking_modal_cta( $cta_url ) {
	if ( '' === trim( (string) $cta_url ) ) {
		return false;
	}

	$booking_src_id      = function_exists( 'hughalroztatoo_booking_acf_source_post_id' )
		? (int) hughalroztatoo_booking_acf_source_post_id()
		: 0;
	$booking_permalink = $booking_src_id > 0 ? (string) get_permalink( $booking_src_id ) : '';

	$cta_id = (int) url_to_postid( esc_url_raw( $cta_url ) );

	if ( $cta_id > 0 ) {
		$cta_tpl = (string) get_page_template_slug( $cta_id );
		if ( 'page-booking.php' === $cta_tpl ) {
			return true;
		}
		if ( $booking_src_id > 0 && $cta_id === $booking_src_id ) {
			return true;
		}
	}

	if ( $booking_src_id > 0 && '' !== $booking_permalink ) {
		$cta_norm     = untrailingslashit(
			strtok( trailingslashit( esc_url_raw( $cta_url ) ), '?' )
		);
		$booking_norm = untrailingslashit(
			strtok( trailingslashit( $booking_permalink ), '?' )
		);
		if ( '' !== $cta_norm && '' !== $booking_norm && $cta_norm === $booking_norm ) {
			return true;
		}
	}

	return false;
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
 * Whether Amelia booking assets should load on this front request.
 *
 * @return bool
 */
function hughalroztatoo_frontend_needs_booking_multistep() {
	global $post;
	if ( ! hughalroztatoo_amelia_active() ) {
		return false;
	}
	if ( hughalroztatoo_booking_acf_source_post_id() > 0 ) {
		return true;
	}
	if ( $post && hughalroztatoo_content_has_amelia_booking_shortcode( $post->post_content ) ) {
		return true;
	}
	return false;
}

if ( ! function_exists( 'hughalroztatoo_amelia_locale_candidates' ) ) {
	/**
	 * Locales to match Amelia `translations` keys (same logic as home pricing block).
	 *
	 * @return string[]
	 */
	function hughalroztatoo_amelia_locale_candidates() {
		$candidates = array();
		if ( function_exists( 'pll_current_language' ) ) {
			$pll_locale = (string) pll_current_language( 'locale' );
			$pll_slug   = (string) pll_current_language( 'slug' );
			if ( '' !== $pll_locale ) {
				$candidates[] = $pll_locale;
			}
			if ( '' !== $pll_slug ) {
				$candidates[] = $pll_slug;
			}
		}
		$wp_locale = (string) get_locale();
		if ( '' !== $wp_locale ) {
			$candidates[] = $wp_locale;
		}
		$expanded = array();
		foreach ( $candidates as $candidate ) {
			$candidate = trim( (string) $candidate );
			if ( '' === $candidate ) {
				continue;
			}
			$expanded[]   = $candidate;
			$expanded[]   = str_replace( '-', '_', $candidate );
			$expanded[]   = str_replace( '_', '-', $candidate );
			$expanded[]   = strtolower( $candidate );
			$short        = strtok( str_replace( '-', '_', strtolower( $candidate ) ), '_' );
			if ( is_string( $short ) && '' !== $short ) {
				$expanded[] = $short;
			}
		}
		return array_values( array_unique( array_filter( $expanded ) ) );
	}
}

/**
 * Polylang string group (Languages → String translations in admin).
 */
const HUGHALROZTATOO_PLL_BOOKING_GROUP = 'Amelia booking';

/**
 * Strings registered in Polylang only when they have no ACF field on the Booking template.
 *
 * @return array<string, string> Key => default-language source string.
 */
function hughalroztatoo_booking_pll_strings() {
	return array(
		'loading'            => 'Chargement…',
		'errorGeneric'       => 'Une erreur est survenue. Réessayez.',
		'errorNoServices'    => 'Aucun service Amelia disponible. Vérifiez les catégories et la visibilité des services.',
		'errorSlots'         => 'Impossible de charger les créneaux pour cette date.',
		'errorBooking'       => 'La réservation a échoué.',
		'recaptcha'          => 'Vérification anti-robot requise.',
		'labelFirstName'     => 'Prénom',
		'labelLastName'      => 'Nom',
		'labelEmail'         => 'Email',
		'labelPhone'         => 'Téléphone',
		'labelProjectNote'   => 'Note sur le projet',
		'phEmail'            => 'email@gmail.com',
		'phPhone'            => '+1 (XXX) XXX-XXXX',
		'phNote'             => 'Note',
		'errorPhone'         => 'Veuillez indiquer votre numéro de téléphone.',
		'errorAge'           => 'Veuillez confirmer que vous avez 18 ans ou plus.',
		'errorCustomerIdentity' => 'Veuillez renseigner le prénom, le nom et l’email.',
		'payRowFormat'       => 'Format',
		'payRowDate'         => 'Date',
		'payRowSlot'         => 'Créneau',
		'payRowTotal'        => 'Total séance',
		'payRowDepositFmt'   => 'Acompte %d%%',
		'payConsentBefore'   => 'En validant, j’accepte les ',
		'payConsentMid'      => ', la ',
		'payConsentAnd'      => ' et la ',
		'payConsentAfter'    => ' relatives à cette réservation.',
		'payTermsConditions' => 'Conditions',
		'payTermsPrivacy'    => 'Politique de confidentialité',
		'payTermsRefund'     => 'Politique de remboursement',
		'paySecureLine'      => 'Paiement sécurisé · CAD',
		'payViaStripe'       => 'Payer %s via Stripe',
		'errorPayTerms'      => 'Veuillez accepter les conditions pour continuer.',
		'doneRowFormat'      => 'Format',
		'doneRowDate'        => 'Date',
		'doneRowSlot'        => 'Créneau',
		'doneRowPaid'        => 'Acompte payé',
		'doneClose'          => 'Fermer',
		'next'               => 'continuer',
		'back'               => 'Retour',
		'submit'             => 'Confirmer la réservation',
		'photoUploadStatus'    => 'Fichier(s) chargé(s).',
		'tattooDurationHours'  => '%sh de tatouage',
		'tattooDurationMinutes'=> '%s min de tatouage',
		'photoRequired'      => 'Veuillez joindre au moins une photo.',
		'photoErrorZoneNone' => 'Aucune photo de la zone à tatouer. Veuillez importer au moins 3 fichiers.',
		'photoErrorZoneMin'  => 'Pas assez de photos : au moins 3 photos de la zone à tatouer sont nécessaires.',
		'photoErrorRefNone'  => 'Aucune photo de référence (style). Veuillez en ajouter au moins une.',
		'photoErrorRefMin'   => 'Veuillez ajouter au moins 1 photo de référence (style).',
		'photoZoneProgressIncomplete' =>
			'Téléchargé(s) {{current}} sur {{needed}} photo(s) pour la zone. Ajoutez encore {{missing}} photo(s).',
		'photoZoneProgressComplete'   => 'Zone : minimum {{needed}} photo(s) atteint ({{current}} fichier(s)).',
		'photoRefProgressIncomplete'  =>
			'Téléchargé(s) {{current}} sur {{needed}} photo(s) de référence (style). Ajoutez encore {{missing}} photo(s).',
		'photoRefProgressComplete'    => 'Référence (style) : minimum {{needed}} fichier(s) — OK ({{current}} envoyé(s)).',
		'photoNoField'       => 'Aucun champ « fichier » Amelia n’est lié à ce service : ajoutez-en un dans Amelia (Personnalisé → champs du service) ou précisez l’ID du champ dans le shortcode : photo_field="ID".',
		'photoClear'         => 'Retirer les fichiers',
		'photoChoose'        => 'Choisir des images',
		'stepPhotoSuiteTitle' => 'PHOTO DU PROJET',
		'stepPhotoSuiteSub'   => 'SUITE DE TRAVAIL',
		'stepPhotoSuiteLead'  => 'Envoyez une photo de votre tatouage existant pour que le studio puisse identifier votre projet et préparer la suite de la session.',
		'photoUploadSuite'    => 'Photo du tatouage existant',
		'photoHintSuite'      => 'Pas besoin de référence ni de photos de zone — uniquement une photo claire du tatouage en cours.',
		'stepCounterFormat'   => 'Étape {{current}} sur {{total}}',
		'paySummary'         => 'Récapitulatif',
		'payOnSite'          => 'Paiement sur place (réglé dans Amelia : acompte en ligne si activé).',
		'noExtras'           => 'Aucune option de format pour ce service — étape ignorée.',
		'pickService'        => 'Choisissez une prestation',
		'pickCategory'       => 'Choisissez une catégorie',
		'pickDate'           => 'Sélectionnez un jour disponible',
		'pickTime'           => 'Sélectionnez une heure',
		'pickFormat'         => 'Choisissez un format / taille',
		'calAriaPrevMonth'   => 'Mois précédent',
		'calAriaNextMonth'   => 'Mois suivant',
		'ameliaInactive'     => 'Amelia n’est pas activé.',
		// Mois 1…12, séparés par des virgules (même ordre que calWeekdays : traductible).
		'monthsLong'         => 'janvier,février,mars,avril,mai,juin,juillet,août,septembre,octobre,novembre,décembre',
		'monthsShort'        => 'janv.,févr.,mars,avr.,mai,juin,juil.,août,sept.,oct.,nov.,déc.',
		'stepZone'           => 'Sélectionnez la zone',
	);
}

/**
 * Translate a Polylang-registered booking string (not covered by ACF). Else gettext.
 *
 * @param string $key Key from hughalroztatoo_booking_pll_strings().
 * @return string
 */
function hughalroztatoo_booking_t( $key ) {
	$d = hughalroztatoo_booking_pll_strings();
	if ( ! isset( $d[ $key ] ) ) {
		return '';
	}
	$s = $d[ $key ];
	if ( function_exists( 'pll__' ) ) {
		return pll__( $s );
	}
	return __( $s, 'hughalroztatoo' );
}

/**
 * Register Polylang strings that are not covered by the Booking ACF template fields.
 */
function hughalroztatoo_register_booking_polylang_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	$group = HUGHALROZTATOO_PLL_BOOKING_GROUP;
	$d     = hughalroztatoo_booking_pll_strings();
	foreach ( $d as $key => $string ) {
		pll_register_string( 'hugh_amelia_booking_' . $key, $string, $group, false );
	}
}
add_action( 'init', 'hughalroztatoo_register_booking_polylang_strings', 20 );

/**
 * Read a booking ACF text field from the current post.
 *
 * @param string $field_name ACF field name.
 * @param string $default    Fallback value.
 * @return string
 */
function hughalroztatoo_booking_field( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$post_id = hughalroztatoo_booking_acf_source_post_id();
	if ( ! $post_id ) {
		return $default;
	}
	$value = get_field( $field_name, $post_id );
	if ( is_string( $value ) && '' !== trim( $value ) ) {
		return trim( $value );
	}
	return $default;
}

/**
 * Read a booking ACF WYSIWYG field (for HTML allowed in front output).
 *
 * @param string $field_name ACF field name.
 * @param string $default    Fallback value.
 * @return string
 */
function hughalroztatoo_booking_field_wysiwyg( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$post_id = hughalroztatoo_booking_acf_source_post_id();
	if ( ! $post_id ) {
		return $default;
	}
	$value = get_field( $field_name, $post_id );
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return $default;
	}
	return wp_kses_post( $value );
}

/**
 * Get booking category descriptions for step 1.
 *
 * @return array<int, string>
 */
function hughalroztatoo_get_booking_category_descriptions() {
	$out = array();
	if ( ! function_exists( 'get_field' ) ) {
		return $out;
	}
	$post_id = hughalroztatoo_booking_acf_source_post_id();
	if ( ! $post_id ) {
		return $out;
	}
	$rows = get_field( 'booking_step1_categories', $post_id );
	if ( ! is_array( $rows ) ) {
		return $out;
	}
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$cat_id = isset( $row['category_id'] ) ? absint( $row['category_id'] ) : 0;
		$desc   = isset( $row['description'] ) && is_string( $row['description'] ) ? trim( $row['description'] ) : '';
		if ( $cat_id && '' !== $desc ) {
			$out[ $cat_id ] = $desc;
		}
	}
	return $out;
}

/**
 * Get zone options repeater for step 5.
 *
 * @return array
 */
function hughalroztatoo_get_booking_zone_options() {
	$out = array();
	if ( ! function_exists( 'get_field' ) ) {
		return $out;
	}
	$post_id = hughalroztatoo_booking_acf_source_post_id();
	if ( ! $post_id ) {
		return $out;
	}
	$rows = get_field( 'booking_step5_zone_options', $post_id );
	if ( ! is_array( $rows ) ) {
		return $out;
	}
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$value = isset( $row['value'] ) && is_string( $row['value'] ) ? trim( $row['value'] ) : '';
		$label = isset( $row['label'] ) && is_string( $row['label'] ) ? trim( $row['label'] ) : '';
		if ( '' !== $value && '' !== $label ) {
			$out[] = array(
				'value' => $value,
				'label' => $label,
			);
		}
	}
	return $out;
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

	$modal_js = $theme_dir . '/assets/js/booking-modal.js';
	if ( file_exists( $modal_js ) ) {
		wp_register_script(
			'hughalroztatoo-booking-modal',
			$theme_uri . '/assets/js/booking-modal.js',
			array( 'hughalroztatoo-booking-ms' ),
			(string) filemtime( $modal_js ),
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
			/** Open fullscreen booking URL in modal overlay (booking template). */
			'openBookingModalOnLoad' => is_page_template( 'page-booking.php' ),
			/**
			 * BCP 47 tag for calendar month names (independent of WP admin language).
			 * Filter: hughalroztatoo_booking_calendar_locale
			 */
			'calendarLocale'   => apply_filters( 'hughalroztatoo_booking_calendar_locale', 'fr-FR' ),
			'recaptchaSiteKey' => $recaptcha_on ? (string) $grecap['siteKey'] : '',
			'recaptchaOn'      => $recaptcha_on,
			'depositPercent'   => (int) apply_filters( 'hughalroztatoo_booking_deposit_percent', 30 ),
			'payUrls'          => array(
				'conditions' => (string) apply_filters( 'hughalroztatoo_booking_pay_url_conditions', '' ),
				'privacy'    => (string) apply_filters( 'hughalroztatoo_booking_pay_url_privacy', function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '' ),
				'refund'     => (string) apply_filters( 'hughalroztatoo_booking_pay_url_refund', '' ),
			),
			'humanImagesUrl'         => get_template_directory_uri() . '/assets/images/human',
			'zoneOptions'            => hughalroztatoo_get_booking_zone_options(),
			/**
			 * Amelia custom field id for “Note sur le projet” (textarea in step 6).
			 *
			 * @since 1.0
			 */
			'projectNoteFieldId'     => (int) apply_filters( 'hughalroztatoo_booking_project_note_field_id', 4 ),
			'ameliaLocaleCandidates' => hughalroztatoo_amelia_locale_candidates(),
			'payConsentHtml'         => hughalroztatoo_booking_field_wysiwyg( 'booking_step7_pay_consent', '' ),
			'strings'                => array(
				'loading'         => hughalroztatoo_booking_t( 'loading' ),
				'errorGeneric'    => hughalroztatoo_booking_t( 'errorGeneric' ),
				'errorNoServices' => hughalroztatoo_booking_t( 'errorNoServices' ),
				'errorSlots'      => hughalroztatoo_booking_t( 'errorSlots' ),
				'errorBooking'    => hughalroztatoo_booking_t( 'errorBooking' ),
				'recaptcha'       => hughalroztatoo_booking_t( 'recaptcha' ),
				'stepZone'        => hughalroztatoo_booking_field( 'booking_step0_title', hughalroztatoo_booking_t( 'stepZone' ) ),
				'stepType'        => hughalroztatoo_booking_field( 'booking_step1_title', __( 'Type de visite', 'hughalroztatoo' ) ),
				'stepCategory'    => hughalroztatoo_booking_field( 'booking_step1_title', __( 'Type de visite', 'hughalroztatoo' ) ),
				'stepCategoryHint'=> hughalroztatoo_booking_field( 'booking_step1_lead', __( 'Nouveau projet ou suite ?', 'hughalroztatoo' ) ),
				'stepDate'        => hughalroztatoo_booking_field( 'booking_step2_title', __( 'Choisir une date', 'hughalroztatoo' ) ),
				'calWeekdays'     => hughalroztatoo_booking_field( 'booking_step2_weekdays', 'L,M,M,J,V,S,D' ),
				'stepService'     => hughalroztatoo_booking_field( 'booking_step3_title', __( 'Choisir un format', 'hughalroztatoo' ) ),
				'stepFormat'      => hughalroztatoo_booking_field( 'booking_step4_title', __( 'Choisir un format', 'hughalroztatoo' ) ),
				'stepTime'        => hughalroztatoo_booking_field( 'booking_step4_time_title', __( 'Choisir un créneau', 'hughalroztatoo' ) ),
				'timeSub'         => hughalroztatoo_booking_field( 'booking_step4_time_sub', __( 'Créneaux', 'hughalroztatoo' ) ),
				'stepPhoto'       => hughalroztatoo_booking_field( 'booking_step5_title', __( 'Photos & références', 'hughalroztatoo' ) ),
				'stepInfo'        => hughalroztatoo_booking_field( 'booking_step6_title', __( 'Vos informations', 'hughalroztatoo' ) ),
				'labelFirstName'  => hughalroztatoo_booking_t( 'labelFirstName' ),
				'labelLastName'   => hughalroztatoo_booking_t( 'labelLastName' ),
				'labelEmail'      => hughalroztatoo_booking_t( 'labelEmail' ),
				'labelPhone'      => hughalroztatoo_booking_t( 'labelPhone' ),
				'labelProjectNote'=> hughalroztatoo_booking_t( 'labelProjectNote' ),
				'phEmail'         => hughalroztatoo_booking_t( 'phEmail' ),
				'phPhone'         => hughalroztatoo_booking_t( 'phPhone' ),
				'phNote'          => hughalroztatoo_booking_t( 'phNote' ),
				'ageCheckbox'     => hughalroztatoo_booking_field( 'booking_step6_age_text', __( 'Je confirme avoir 18 ans ou plus et accepte les conditions du studio.', 'hughalroztatoo' ) ),
				'errorPhone'      => hughalroztatoo_booking_t( 'errorPhone' ),
				'errorAge'        => hughalroztatoo_booking_t( 'errorAge' ),
				'errorCustomerIdentity' => hughalroztatoo_booking_t( 'errorCustomerIdentity' ),
				'stepPay'         => hughalroztatoo_booking_field( 'booking_step7_title', __( 'Paiement de l\'acompte', 'hughalroztatoo' ) ),
				'payRowFormat'    => hughalroztatoo_booking_t( 'payRowFormat' ),
				'payRowDate'      => hughalroztatoo_booking_t( 'payRowDate' ),
				'payRowSlot'      => hughalroztatoo_booking_t( 'payRowSlot' ),
				'payRowTotal'     => hughalroztatoo_booking_t( 'payRowTotal' ),
				'payRowDepositFmt'=> hughalroztatoo_booking_t( 'payRowDepositFmt' ),
				'payDisclaimer'   => hughalroztatoo_booking_field( 'booking_step7_disclaimer', __( 'Le paiement de l’acompte confirme votre créneau. Les montants sont traités de façon sécurisée. Pour toute question, contactez le studio avant de valider.', 'hughalroztatoo' ) ),
				'payConsentBefore'=> hughalroztatoo_booking_t( 'payConsentBefore' ),
				'payConsentMid'   => hughalroztatoo_booking_t( 'payConsentMid' ),
				'payConsentAnd'   => hughalroztatoo_booking_t( 'payConsentAnd' ),
				'payConsentAfter' => hughalroztatoo_booking_t( 'payConsentAfter' ),
				'payTermsConditions' => hughalroztatoo_booking_t( 'payTermsConditions' ),
				'payTermsPrivacy' => hughalroztatoo_booking_t( 'payTermsPrivacy' ),
				'payTermsRefund'  => hughalroztatoo_booking_t( 'payTermsRefund' ),
				'paySecureLine'   => hughalroztatoo_booking_t( 'paySecureLine' ),
				'payViaStripe'    => hughalroztatoo_booking_t( 'payViaStripe' ),
				'errorPayTerms'   => hughalroztatoo_booking_t( 'errorPayTerms' ),
				'stepDone'        => hughalroztatoo_booking_field( 'booking_step8_title', __( 'Réservation confirmée', 'hughalroztatoo' ) ),
				'doneRowFormat'   => hughalroztatoo_booking_t( 'doneRowFormat' ),
				'doneRowDate'     => hughalroztatoo_booking_t( 'doneRowDate' ),
				'doneRowSlot'     => hughalroztatoo_booking_t( 'doneRowSlot' ),
				'doneRowPaid'     => hughalroztatoo_booking_t( 'doneRowPaid' ),
				'doneMessage'     => hughalroztatoo_booking_field( 'booking_step8_message', __( 'Confirmation envoyée par email. Rappel automatique 24h avant la séance.', 'hughalroztatoo' ) ),
				'doneClose'       => hughalroztatoo_booking_t( 'doneClose' ),
				'next'            => hughalroztatoo_booking_t( 'next' ),
				'back'            => hughalroztatoo_booking_t( 'back' ),
				'submit'          => hughalroztatoo_booking_t( 'submit' ),
				'photoHint'       => hughalroztatoo_booking_field( 'booking_step5_guidelines', __( 'Photos claires, sans filtre, cadrage large. Demandez de l\'aide pour les prendre. Minimum 4 fichiers requis.', 'hughalroztatoo' ) ),
				'photoUploadZone' => hughalroztatoo_booking_field( 'booking_step5_upload_zone_text', __( '3 photos de la zone à tatouer', 'hughalroztatoo' ) ),
				'photoUploadRef'  => hughalroztatoo_booking_field( 'booking_step5_upload_ref_text', __( '1+ photo de référence ( style )', 'hughalroztatoo' ) ),
				'photoZoneLabel'  => hughalroztatoo_booking_field( 'booking_step5_zone_label', __( 'Zone à tatouer', 'hughalroztatoo' ) ),
				'photoZonePh'     => hughalroztatoo_booking_field( 'booking_step5_zone_placeholder', __( 'Sélectionner...', 'hughalroztatoo' ) ),
				'photoUploadStatus'  => hughalroztatoo_booking_t( 'photoUploadStatus' ),
				'tattooDurationHours' => hughalroztatoo_booking_t( 'tattooDurationHours' ),
				'tattooDurationMinutes' => hughalroztatoo_booking_t( 'tattooDurationMinutes' ),
				'photoRequired'   => hughalroztatoo_booking_t( 'photoRequired' ),
				'photoErrorZoneNone' => hughalroztatoo_booking_t( 'photoErrorZoneNone' ),
				'photoErrorZoneMin' => hughalroztatoo_booking_t( 'photoErrorZoneMin' ),
				'photoErrorRefNone' => hughalroztatoo_booking_t( 'photoErrorRefNone' ),
				'photoErrorRefMin'  => hughalroztatoo_booking_t( 'photoErrorRefMin' ),
				'photoZoneProgressIncomplete' => hughalroztatoo_booking_t( 'photoZoneProgressIncomplete' ),
				'photoZoneProgressComplete' => hughalroztatoo_booking_t( 'photoZoneProgressComplete' ),
				'photoRefProgressIncomplete' => hughalroztatoo_booking_t( 'photoRefProgressIncomplete' ),
				'photoRefProgressComplete' => hughalroztatoo_booking_t( 'photoRefProgressComplete' ),
				'photoNoField'    => hughalroztatoo_booking_t( 'photoNoField' ),
				'photoClear'      => hughalroztatoo_booking_t( 'photoClear' ),
				'photoChoose'     => hughalroztatoo_booking_t( 'photoChoose' ),
				'stepPhotoSuiteTitle' => hughalroztatoo_booking_t( 'stepPhotoSuiteTitle' ),
				'stepPhotoSuiteSub' => hughalroztatoo_booking_t( 'stepPhotoSuiteSub' ),
				'stepPhotoSuiteLead' => hughalroztatoo_booking_t( 'stepPhotoSuiteLead' ),
				'photoUploadSuite' => hughalroztatoo_booking_t( 'photoUploadSuite' ),
				'photoHintSuite' => hughalroztatoo_booking_t( 'photoHintSuite' ),
				'stepCounterFormat' => hughalroztatoo_booking_t( 'stepCounterFormat' ),
				'paySummary'      => hughalroztatoo_booking_t( 'paySummary' ),
				'payOnSite'       => hughalroztatoo_booking_t( 'payOnSite' ),
				'noExtras'        => hughalroztatoo_booking_t( 'noExtras' ),
				'pickService'     => hughalroztatoo_booking_t( 'pickService' ),
				'pickCategory'    => hughalroztatoo_booking_t( 'pickCategory' ),
				'pickDate'        => hughalroztatoo_booking_t( 'pickDate' ),
				'pickTime'        => hughalroztatoo_booking_t( 'pickTime' ),
				'pickFormat'      => hughalroztatoo_booking_t( 'pickFormat' ),
				'calAriaPrevMonth' => hughalroztatoo_booking_t( 'calAriaPrevMonth' ),
				'calAriaNextMonth' => hughalroztatoo_booking_t( 'calAriaNextMonth' ),
				'monthsLong'      => hughalroztatoo_booking_t( 'monthsLong' ),
				'monthsShort'     => hughalroztatoo_booking_t( 'monthsShort' ),
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
	if ( is_admin() || ! hughalroztatoo_frontend_needs_booking_multistep() ) {
		return;
	}

	if ( wp_style_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_style( 'hughalroztatoo-booking-ms' );
	}
	if ( wp_script_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-ms' );
		hughalroztatoo_localize_amelia_multistep_script();
	}
	if ( wp_script_is( 'hughalroztatoo-booking-modal', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-modal' );
	}
}
add_action( 'wp_enqueue_scripts', 'hughalroztatoo_maybe_enqueue_amelia_multistep', 20 );

/**
 * Map multistep visit type (category) to Amelia appointment internal notes. Project note stays in
 * the dedicated Amelia custom field; it is not duplicated here.
 *
 * The public /bookings endpoint does not set internalNotes; staff often read this field as «Заметка».
 *
 * @param array $appointment_data Appointment payload after BookingApplicationService::getAppointmentData().
 * @return array
 */
function hughalroztatoo_amelia_multistep_internal_notes( $appointment_data ) {
	if ( ! is_array( $appointment_data ) || empty( $appointment_data['bookings'][0] ) || ! is_array( $appointment_data['bookings'][0] ) ) {
		return $appointment_data;
	}
	$customer = isset( $appointment_data['bookings'][0]['customer'] ) && is_array( $appointment_data['bookings'][0]['customer'] )
		? $appointment_data['bookings'][0]['customer']
		: array();

	$category = '';
	if ( isset( $customer['hughCategoryName'] ) && is_string( $customer['hughCategoryName'] ) ) {
		$category = trim( $customer['hughCategoryName'] );
		$category = '' !== $category ? sanitize_text_field( $category ) : '';
	}
	unset( $appointment_data['bookings'][0]['customer']['hughCategoryName'] );

	if ( '' === $category ) {
		return $appointment_data;
	}
	$block = __( 'Type de visite :', 'hughalroztatoo' ) . ' ' . $category;
	$prev  = ! empty( $appointment_data['internalNotes'] ) ? (string) $appointment_data['internalNotes'] : '';
	if ( '' !== $prev ) {
		$block = $prev . "\n\n" . $block;
	}
	$appointment_data['internalNotes'] = $block;
	return $appointment_data;
}
add_filter( 'amelia_before_booking_added_filter', 'hughalroztatoo_amelia_multistep_internal_notes', 10, 1 );

/**
 * Remove processed shortcode from Booking template page output (wizard is rendered globally in footer modal).
 *
 * Keeps optional non-shortcode content (paragraphs, etc.).
 *
 * @param string $content Post content.
 * @return string
 */
function hughalroztatoo_strip_booking_shortcode_from_booking_template( $content ) {
	if ( ! is_singular( 'page' ) ) {
		return $content;
	}
	$pid = (int) get_queried_object_id();
	if ( $pid <= 0 || 'page-booking.php' !== (string) get_page_template_slug( $pid ) ) {
		return $content;
	}
	return (string) preg_replace( '/\[\s*hugh_amelia_booking(?:\s+[^\]]*)?\]\s*/', '', $content );
}
add_filter( 'the_content', 'hughalroztatoo_strip_booking_shortcode_from_booking_template', 9 );

/**
 * Output booking modal shell (shortcode instance) before scripts.
 */
function hughalroztatoo_render_booking_modal() {
	if ( is_admin() || ! hughalroztatoo_amelia_active() ) {
		return;
	}
	if ( hughalroztatoo_booking_acf_source_post_id() <= 0 ) {
		return;
	}
	$label = esc_attr__( 'Réservation', 'hughalroztatoo' );
	$close = esc_attr__( 'Fermer', 'hughalroztatoo' );
	echo '<div id="hugh-ms-booking-modal" class="hugh-ms-booking-modal" aria-hidden="true">';
	echo '<div class="hugh-ms-booking-modal__backdrop" data-hat-booking-modal-close tabindex="-1"></div>';
	echo '<div class="hugh-ms-booking-modal__shell">';
	echo '<div class="hugh-ms-booking-modal__grab" aria-hidden="true"><span class="hugh-ms-booking-modal__grab-bar"></span></div>';
	echo '<div class="hugh-ms-booking-modal__dialog" role="dialog" aria-modal="true" aria-label="' . $label . '">';
	echo '<div class="hugh-ms-booking-modal__body">';
	echo do_shortcode( '[hugh_amelia_booking]' );
	echo '</div></div>';
	$close_icon = esc_url( get_template_directory_uri() . '/assets/images/close.svg' );
	echo '<button type="button" class="hugh-ms-booking-modal__close" data-hat-booking-modal-close aria-label="' . $close . '">';
	echo '<img class="hugh-ms-booking-modal__close-svg" src="' . $close_icon . '" alt="" width="22" height="22" draggable="false" />';
	echo '</button>';
	echo '</div></div>';
}
add_action( 'wp_footer', 'hughalroztatoo_render_booking_modal', 5 );

/**
 * Shortcode callback.
 *
 * Attributes:
 * - category: comma-separated Amelia category IDs to limit the list (optional).
 * - photo_field: ID du champ fichier « zone » (1er bloc), optionnel si un seul champ ou couple auto.
 * - photo_ref_field: ID du champ « référence (style) » (2e bloc). Si absent et la prestation a exactement 2 champs fichier Amelia, couplage auto (ids croissants : zone puis ref).
 * - project_note_field: ID du champ personnalisé « Note sur le projet » (défaut: filtre hughalroztatoo_booking_project_note_field_id, 4).
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function hughalroztatoo_shortcode_amelia_booking( $atts ) {
	if ( ! hughalroztatoo_amelia_active() ) {
		return '<p class="hugh-ms-booking__notice">' . esc_html( hughalroztatoo_booking_t( 'ameliaInactive' ) ) . '</p>';
	}

	$atts = shortcode_atts(
		array(
			'category'           => '',
			'photo_field'        => '',
			'photo_ref_field'    => '',
			'project_note_field' => '',
		),
		$atts,
		'hugh_amelia_booking'
	);

	$ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) $atts['category'] ) ) ) );

	$photo_field_id     = absint( $atts['photo_field'] );
	$photo_ref_field_id = absint( $atts['photo_ref_field'] );
	$project_note_fid   = absint( $atts['project_note_field'] );

	if ( wp_style_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_style( 'hughalroztatoo-booking-ms' );
	}
	if ( wp_script_is( 'hughalroztatoo-booking-ms', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-ms' );
		hughalroztatoo_localize_amelia_multistep_script();
	}
	if ( wp_script_is( 'hughalroztatoo-booking-modal', 'registered' ) ) {
		wp_enqueue_script( 'hughalroztatoo-booking-modal' );
	}

	$default_note_id = (int) apply_filters( 'hughalroztatoo_booking_project_note_field_id', 4 );
	$data            = wp_json_encode(
		array(
			'categoryIds'          => $ids,
			'categoryDescriptions' => hughalroztatoo_get_booking_category_descriptions(),
			'photoFieldId'         => $photo_field_id ? $photo_field_id : null,
			'photoRefFieldId'      => $photo_ref_field_id ? $photo_ref_field_id : null,
			'projectNoteFieldId'   => $project_note_fid > 0 ? $project_note_fid : $default_note_id,
		)
	);

	return '<div id="hugh-ms-booking-root" class="hugh-ms-booking" data-hugh-ms-config="' . esc_attr( $data ) . '" role="region" aria-live="polite"></div>';
}
add_shortcode( 'hugh_amelia_booking', 'hughalroztatoo_shortcode_amelia_booking' );
