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
 * Stripe credentials from Amelia (Settings → Payments), for the custom booking wizard.
 *
 * @return array{ enabled: bool, publishable_key: string, test_mode: bool }
 */
function hughalroztatoo_amelia_stripe_frontend_config() {
	$out = array(
		'enabled'         => false,
		'publishable_key' => '',
		'test_mode'       => false,
		'currency'        => 'cad',
	);
	if ( ! hughalroztatoo_amelia_active() ) {
		return $out;
	}
	$amelia_settings = json_decode( (string) get_option( 'amelia_settings', '{}' ), true );
	if ( empty( $amelia_settings ) || ! is_array( $amelia_settings ) ) {
		return $out;
	}
	$stripe = isset( $amelia_settings['payments']['stripe'] ) ? $amelia_settings['payments']['stripe'] : array();
	if ( empty( $stripe['enabled'] ) ) {
		return $out;
	}
	$test_mode = ! empty( $stripe['testMode'] );
	$pk        = $test_mode ? (string) ( isset( $stripe['testPublishableKey'] ) ? $stripe['testPublishableKey'] : '' ) : (string) ( isset( $stripe['livePublishableKey'] ) ? $stripe['livePublishableKey'] : '' );
	$sk        = $test_mode ? (string) ( isset( $stripe['testSecretKey'] ) ? $stripe['testSecretKey'] : '' ) : (string) ( isset( $stripe['liveSecretKey'] ) ? $stripe['liveSecretKey'] : '' );
	$pk        = trim( $pk );
	$sk        = trim( $sk );
	if ( '' === $sk ) {
		return $out;
	}
	if ( ! apply_filters( 'hughalroztatoo_booking_stripe_active', true, $stripe ) ) {
		return $out;
	}
	$out['enabled']          = true;
	$out['publishable_key']   = $pk;
	$out['test_mode']        = $test_mode;
	$out['currency']         = strtolower( (string) ( isset( $amelia_settings['payments']['currency'] ) ? $amelia_settings['payments']['currency'] : 'cad' ) );
	return $out;
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
 * Resolve the Thank-you page by dedicated template.
 *
 * @return int Post ID or 0.
 */
function hughalroztatoo_thank_you_page_id() {
	static $memo = false;
	if ( false !== $memo ) {
		return (int) $memo;
	}
	$memo = 0;

	$post_id = (int) get_queried_object_id();
	if ( $post_id > 0 ) {
		$tpl = get_page_template_slug( $post_id );
		if ( 'page-thank-you.php' === (string) $tpl ) {
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
			'meta_value'             => 'page-thank-you.php',
		)
	);

	if ( ! empty( $page[0] ) && isset( $page[0]->ID ) ) {
		$memo = (int) $page[0]->ID;
	}

	return (int) apply_filters( 'hughalroztatoo_thank_you_page_id', (int) $memo );
}

/**
 * Thank-you page URL for booking success redirects.
 *
 * @return string Absolute URL.
 */
function hughalroztatoo_booking_thank_you_url() {
	$page_id = hughalroztatoo_thank_you_page_id();
	$url     = '';

	if ( $page_id > 0 ) {
		$localized_id = $page_id;
		if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
			$current_lang = (string) pll_current_language( 'slug' );
			if ( '' !== $current_lang ) {
				$translated = (int) pll_get_post( $page_id, $current_lang );
				if ( $translated > 0 ) {
					$localized_id = $translated;
				}
			}
		}
		$url = (string) get_permalink( $localized_id );
	}

	if ( '' === $url ) {
		$url = hughalroztatoo_booking_thank_you_fallback_url();
	}

	return (string) apply_filters( 'hughalroztatoo_booking_thank_you_url', $url, $page_id );
}

/**
 * Thank-you page slug for a Polylang language slug (e.g. EN uses thank-you-en).
 *
 * @param string $lang_slug Polylang language slug or empty.
 * @return string Post slug without slashes.
 */
function hughalroztatoo_thank_you_slug_for_language( $lang_slug ) {
	$lang_slug = is_string( $lang_slug ) ? trim( $lang_slug ) : '';
	$default   = ( 'en' === $lang_slug ) ? 'thank-you-en' : 'thank-you';

	return (string) apply_filters( 'hughalroztatoo_booking_thank_you_slug', $default, $lang_slug );
}

/**
 * All thank-you URL slugs we treat as the booking success route (templates + redirects).
 *
 * @return string[]
 */
function hughalroztatoo_thank_you_slug_variants() {
	$variants = array( 'thank-you', 'thank-you-en' );

	return (array) apply_filters( 'hughalroztatoo_thank_you_slug_variants', $variants );
}

/**
 * Whether a page slug is the thank-you success route.
 *
 * @param string $slug Page post_name.
 * @return bool
 */
function hughalroztatoo_is_thank_you_page_slug( $slug ) {
	return in_array( (string) $slug, hughalroztatoo_thank_you_slug_variants(), true );
}

/**
 * Fallback thank-you URL when no page uses template page-thank-you.php yet.
 *
 * @return string Absolute URL, trailing slash.
 */
function hughalroztatoo_booking_thank_you_fallback_url() {
	$lang = '';
	if ( function_exists( 'pll_current_language' ) ) {
		$lang = (string) pll_current_language( 'slug' );
	}
	$slug = hughalroztatoo_thank_you_slug_for_language( $lang );
	if ( function_exists( 'pll_home_url' ) && '' !== $lang ) {
		$base = trailingslashit( (string) pll_home_url( $lang ) );

		return trailingslashit( $base . $slug );
	}

	return trailingslashit( home_url( '/' . $slug . '/' ) );
}

/**
 * Base URL for Stripe cancel_url (return to the booking page wizard).
 * Success uses the thank-you page URL; that template embeds a hidden `[hugh_amelia_booking]` bridge for callback + redirect.
 *
 * @return string Absolute URL.
 */
function hughalroztatoo_booking_stripe_return_base_url() {
	$booking_src_id = (int) hughalroztatoo_booking_acf_source_post_id();
	if ( $booking_src_id <= 0 ) {
		return (string) apply_filters( 'hughalroztatoo_booking_stripe_return_base_url', home_url( '/' ), 0 );
	}
	$localized_id = $booking_src_id;
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
		$lang = (string) pll_current_language( 'slug' );
		if ( '' !== $lang ) {
			$translated = (int) pll_get_post( $booking_src_id, $lang );
			if ( $translated > 0 ) {
				$localized_id = $translated;
			}
		}
	}
	$url = (string) get_permalink( $localized_id );
	if ( '' === $url ) {
		$url = home_url( '/' );
	}
	return (string) apply_filters( 'hughalroztatoo_booking_stripe_return_base_url', $url, $localized_id );
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
	if ( is_page() && 'page-thank-you.php' === (string) get_page_template_slug( (int) get_queried_object_id() ) ) {
		return true;
	}
	return false;
}

/**
 * Whether the current request renders the thank-you booking screen.
 *
 * @return bool
 */
function hughalroztatoo_is_booking_thank_you_request() {
	if ( is_admin() ) {
		return false;
	}
	if ( is_page() ) {
		$tpl = (string) get_page_template_slug( (int) get_queried_object_id() );
		if ( 'page-thank-you.php' === $tpl ) {
			return true;
		}
		$slug = (string) get_post_field( 'post_name', (int) get_queried_object_id() );
		if ( hughalroztatoo_is_thank_you_page_slug( $slug ) ) {
			return true;
		}
	}
	if ( isset( $_GET['hat_format'] ) || isset( $_GET['hat_date'] ) || isset( $_GET['hat_slot'] ) || isset( $_GET['hat_paid'] ) ) {
		return true;
	}
	return false;
}

/**
 * Body class on the booking thank-you screen (document-flow layout, not the global footer modal).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function hughalroztatoo_booking_thank_you_body_class( $classes ) {
	if ( ! is_page() ) {
		return $classes;
	}
	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 ) {
		return $classes;
	}
	$tpl  = (string) get_page_template_slug( $page_id );
	$slug = (string) get_post_field( 'post_name', $page_id );
	if ( 'page-thank-you.php' === $tpl || hughalroztatoo_is_thank_you_page_slug( $slug ) ) {
		$classes[] = 'hat-booking-thank-you';
	}
	return $classes;
}
add_filter( 'body_class', 'hughalroztatoo_booking_thank_you_body_class' );

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
		'payMethod'          => 'Moyen de paiement',
		'payStripeCardLabel' => 'Carte bancaire',
		'errorStripeUnavailable' => 'Le paiement par carte est indisponible. Réessayez plus tard.',
		'errorStripeCard' => 'Impossible de lire les informations de la carte.',
		'errorPayTerms'      => 'Veuillez accepter les conditions pour continuer.',
		'thankYouTitle'      => 'Réservation confirmée',
		'thankYouMessage'    => 'Confirmation envoyée par email. Rappel automatique 24h avant la séance.',
		'doneRowFormat'      => 'Format',
		'doneRowDate'        => 'Date',
		'doneRowSlot'        => 'Créneau',
		'doneRowPaid'        => 'Acompte payé',
		'doneClose'          => 'Fermer',
		'next'               => 'continuer',
		'back'               => 'Retour',
		'bodySideToggle'     => 'Vue du corps',
		'bodySideFront'      => 'Devant',
		'bodySideBack'       => 'Dos',
		'stepZoneDescription'=> 'Appuyez sur n’importe quelle zone du silhouette et choisissez l’emplacement pour votre futur tatouage.',
		'zonePickedPrefix'   => 'Vous avez choisi ',
		'bodyZoneHead'       => 'Tête',
		'bodyZoneLeftShoulder' => 'Épaule gauche',
		'bodyZoneTorso'      => 'Torse',
		'bodyZoneRightShoulder' => 'Épaule droite',
		'bodyZoneLeftForearm' => 'Avant-bras gauche',
		'bodyZoneRightForearm' => 'Avant-bras droit',
		'bodyZoneLeftPalm'   => 'Main gauche',
		'bodyZoneRightPalm'  => 'Main droite',
		'bodyZoneLeftLeg'    => 'Jambe gauche',
		'bodyZoneRightLeg'   => 'Jambe droite',
		'bodyZoneLeftFoot'   => 'Pied gauche',
		'bodyZoneRightFoot'  => 'Pied droit',
		'bodyZoneBack'       => 'Dos',
		'bodyZoneButtocks'    => 'Fesses',
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
 * Booking template post ID for the current Polylang language (for ACF strings on localized pages).
 *
 * @return int
 */
function hughalroztatoo_booking_acf_post_id_for_current_language() {
	$id = (int) hughalroztatoo_booking_acf_source_post_id();
	if ( $id <= 0 ) {
		return 0;
	}
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
		$lang = (string) pll_current_language( 'slug' );
		if ( '' !== $lang ) {
			$translated = (int) pll_get_post( $id, $lang );
			if ( $translated > 0 ) {
				return $translated;
			}
		}
	}

	return $id;
}

/**
 * Plain-text ACF field from the Booking page in the current language (thank-you screen).
 *
 * @param string $field_name ACF field name.
 * @param string $default    Fallback (e.g. Polylang string).
 * @return string
 */
function hughalroztatoo_thank_you_field( $field_name, $default = '' ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}
	$post_id = hughalroztatoo_booking_acf_post_id_for_current_language();
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

	$stripe_fe = hughalroztatoo_amelia_stripe_frontend_config();

	$css_path = $theme_dir . '/assets/css/booking-multistep.css';
	$js_path  = $theme_dir . '/assets/js/booking-multistep.js';

	$booking_js_deps = array();

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
			$booking_js_deps,
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

	$stripe_fe = hughalroztatoo_amelia_stripe_frontend_config();

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
			/** Stripe Payment Element/card step (requires Amelia Stripe enabled + publishable key). */
			'stripeEnabled'     => ! empty( $stripe_fe['enabled'] ),
			'stripePublishableKey' => $stripe_fe['enabled'] ? (string) $stripe_fe['publishable_key'] : '',
			'stripeTestMode'    => ! empty( $stripe_fe['test_mode'] ),
			'paymentCurrency'   => ! empty( $stripe_fe['currency'] ) ? (string) $stripe_fe['currency'] : 'cad',
			'depositPercent'   => (int) apply_filters( 'hughalroztatoo_booking_deposit_percent', 30 ),
			'thankYouUrl'          => (string) hughalroztatoo_booking_thank_you_url(),
			'thankYouFallbackUrl'  => (string) hughalroztatoo_booking_thank_you_fallback_url(),
			/**
			 * Stripe cancel_url base (booking page). Success uses thankYouUrl when the thank-you template
			 * includes a hidden booking bridge for /payments/callback.
			 */
			'stripeReturnBaseUrl' => (string) hughalroztatoo_booking_stripe_return_base_url(),
			'payUrls'          => array(
				'conditions' => (string) apply_filters( 'hughalroztatoo_booking_pay_url_conditions', '' ),
				'privacy'    => (string) apply_filters( 'hughalroztatoo_booking_pay_url_privacy', function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '' ),
				'refund'     => (string) apply_filters( 'hughalroztatoo_booking_pay_url_refund', '' ),
			),
			'humanImagesUrl'         => get_template_directory_uri() . '/assets/images/human',
			'zoneOptions'            => hughalroztatoo_get_booking_zone_options(),
			'bodyZoneFieldId'        => (int) apply_filters( 'hughalroztatoo_booking_body_zone_field_id', 5 ),
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
				'payMethod'       => hughalroztatoo_booking_t( 'payMethod' ),
				'payStripeCardLabel' => hughalroztatoo_booking_t( 'payStripeCardLabel' ),
				'errorStripeUnavailable' => hughalroztatoo_booking_t( 'errorStripeUnavailable' ),
				'errorStripeCard' => hughalroztatoo_booking_t( 'errorStripeCard' ),
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
				'bodySideToggle'  => hughalroztatoo_booking_t( 'bodySideToggle' ),
				'bodySideFront'   => hughalroztatoo_booking_t( 'bodySideFront' ),
				'bodySideBack'    => hughalroztatoo_booking_t( 'bodySideBack' ),
				'stepZoneDescription' => hughalroztatoo_booking_t( 'stepZoneDescription' ),
				'zonePickedPrefix' => hughalroztatoo_booking_t( 'zonePickedPrefix' ),
				'bodyZoneHead'    => hughalroztatoo_booking_t( 'bodyZoneHead' ),
				'bodyZoneLeftShoulder' => hughalroztatoo_booking_t( 'bodyZoneLeftShoulder' ),
				'bodyZoneTorso'   => hughalroztatoo_booking_t( 'bodyZoneTorso' ),
				'bodyZoneRightShoulder' => hughalroztatoo_booking_t( 'bodyZoneRightShoulder' ),
				'bodyZoneLeftForearm' => hughalroztatoo_booking_t( 'bodyZoneLeftForearm' ),
				'bodyZoneRightForearm' => hughalroztatoo_booking_t( 'bodyZoneRightForearm' ),
				'bodyZoneLeftPalm' => hughalroztatoo_booking_t( 'bodyZoneLeftPalm' ),
				'bodyZoneRightPalm' => hughalroztatoo_booking_t( 'bodyZoneRightPalm' ),
				'bodyZoneLeftLeg' => hughalroztatoo_booking_t( 'bodyZoneLeftLeg' ),
				'bodyZoneRightLeg' => hughalroztatoo_booking_t( 'bodyZoneRightLeg' ),
				'bodyZoneLeftFoot' => hughalroztatoo_booking_t( 'bodyZoneLeftFoot' ),
				'bodyZoneRightFoot' => hughalroztatoo_booking_t( 'bodyZoneRightFoot' ),
				'bodyZoneBack'    => hughalroztatoo_booking_t( 'bodyZoneBack' ),
				'bodyZoneButtocks' => hughalroztatoo_booking_t( 'bodyZoneButtocks' ),
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
 * Map multistep visit type (category) to Amelia appointment internal notes. Project note and body zone
 * stay in dedicated Amelia custom fields; they are not duplicated here.
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
	unset( $appointment_data['bookings'][0]['customer']['hughBodyZoneLabel'] );

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
 * Force dedicated thank-you template for the booking success route.
 *
 * Prevents accidental rendering of booking flow when a page with slug thank-you / thank-you-en
 * exists but has a different assigned template/content.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function hughalroztatoo_force_booking_thank_you_template( $template ) {
	if ( is_admin() || ! is_page() ) {
		return $template;
	}

	$page_id = (int) get_queried_object_id();
	if ( $page_id <= 0 ) {
		return $template;
	}

	$is_thank_you_slug = hughalroztatoo_is_thank_you_page_slug( (string) get_post_field( 'post_name', $page_id ) );
	$has_booking_done_params = isset( $_GET['hat_format'] ) || isset( $_GET['hat_date'] ) || isset( $_GET['hat_slot'] ) || isset( $_GET['hat_paid'] );

	if ( ! $is_thank_you_slug && ! $has_booking_done_params ) {
		return $template;
	}

	$thank_you_template = get_template_directory() . '/page-thank-you.php';
	if ( file_exists( $thank_you_template ) ) {
		return $thank_you_template;
	}

	return $template;
}
add_filter( 'template_include', 'hughalroztatoo_force_booking_thank_you_template', 99 );

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
	if ( hughalroztatoo_is_booking_thank_you_request() ) {
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
 * Validate front redirect URL for Stripe Checkout return.
 *
 * @param string $url Candidate absolute URL.
 * @return string Safe URL or empty string.
 */
function hughalroztatoo_booking_validate_return_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	$sanitized = esc_url_raw( $url );
	if ( '' === $sanitized ) {
		return '';
	}
	$parts = wp_parse_url( $sanitized );
	if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return '';
	}
	$scheme = strtolower( (string) $parts['scheme'] );
	if ( 'http' !== $scheme && 'https' !== $scheme ) {
		return '';
	}
	$home_host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host      = isset( $parts['host'] ) ? (string) $parts['host'] : '';
	if ( ! $home_host || '' === $host || strtolower( (string) $home_host ) !== strtolower( $host ) ) {
		return '';
	}
	return $sanitized;
}

/**
 * AJAX: Create Stripe Checkout Session (hosted page) for already-created Amelia booking payment.
 */
function hughalroztatoo_booking_create_stripe_checkout_session() {
	check_ajax_referer( 'ajax-nonce', 'nonce' );

	if ( ! hughalroztatoo_amelia_active() ) {
		wp_send_json_error( array( 'message' => 'Amelia is not active.' ), 400 );
	}

	$amelia_settings = json_decode( (string) get_option( 'amelia_settings', '{}' ), true );
	$stripe          = isset( $amelia_settings['payments']['stripe'] ) && is_array( $amelia_settings['payments']['stripe'] )
		? $amelia_settings['payments']['stripe']
		: array();

	if ( empty( $stripe['enabled'] ) ) {
		wp_send_json_error( array( 'message' => 'Stripe is disabled.' ), 400 );
	}

	$test_mode = ! empty( $stripe['testMode'] );
	$secret    = $test_mode
		? (string) ( isset( $stripe['testSecretKey'] ) ? $stripe['testSecretKey'] : '' )
		: (string) ( isset( $stripe['liveSecretKey'] ) ? $stripe['liveSecretKey'] : '' );
	$secret    = trim( $secret );
	if ( '' === $secret ) {
		wp_send_json_error( array( 'message' => 'Stripe secret key is missing.' ), 400 );
	}

	if ( ! class_exists( '\\AmeliaVendor\\Stripe\\StripeClient' ) ) {
		wp_send_json_error( array( 'message' => 'Stripe SDK is unavailable.' ), 500 );
	}

	$amount_major = isset( $_POST['amount'] ) ? (float) wp_unslash( $_POST['amount'] ) : 0.0;
	$amount_cents = (int) round( $amount_major * 100 );
	if ( $amount_cents <= 0 ) {
		wp_send_json_error( array( 'message' => 'Invalid payment amount.' ), 400 );
	}

	$currency = isset( $_POST['currency'] ) ? sanitize_key( wp_unslash( $_POST['currency'] ) ) : '';
	if ( '' === $currency ) {
		$currency = strtolower( (string) ( isset( $amelia_settings['payments']['currency'] ) ? $amelia_settings['payments']['currency'] : 'cad' ) );
	}

	$success_url = isset( $_POST['success_url'] ) ? hughalroztatoo_booking_validate_return_url( wp_unslash( $_POST['success_url'] ) ) : '';
	$cancel_url  = isset( $_POST['cancel_url'] ) ? hughalroztatoo_booking_validate_return_url( wp_unslash( $_POST['cancel_url'] ) ) : '';
	if ( '' === $success_url || '' === $cancel_url ) {
		wp_send_json_error( array( 'message' => 'Invalid return URL.' ), 400 );
	}

	$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
	if ( '' === $description ) {
		$description = __( 'Tattoo booking deposit', 'hughalroztatoo' );
	}

	$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';

	$payment_amelia_id = isset( $_POST['payment_amelia_id'] ) ? absint( wp_unslash( $_POST['payment_amelia_id'] ) ) : 0;
	if ( $payment_amelia_id <= 0 ) {
		wp_send_json_error( array( 'message' => 'Missing payment id.' ), 400 );
	}
	if ( ! hughalroztatoo_booking_payment_prepare_stripe_checkout( $payment_amelia_id ) ) {
		wp_send_json_error( array( 'message' => 'This payment cannot be sent to Stripe Checkout.' ), 400 );
	}

	try {
		$stripe_client = new \AmeliaVendor\Stripe\StripeClient( $secret );
		$session_data  = array(
			'mode'       => 'payment',
			'success_url'=> $success_url,
			'cancel_url' => $cancel_url,
			'line_items' => array(
				array(
					'price_data' => array(
						'currency'     => $currency,
						'unit_amount'  => $amount_cents,
						'product_data' => array(
							'name' => $description,
						),
					),
					'quantity'   => 1,
				),
			),
			'metadata'   => array(
				'source'            => 'hugh_booking_multistep',
				'payment_amelia_id' => $payment_amelia_id > 0 ? (string) $payment_amelia_id : '',
			),
		);

		if ( is_email( $customer_email ) ) {
			$session_data['customer_email'] = $customer_email;
		}

		$session = $stripe_client->checkout->sessions->create( $session_data );

		if ( empty( $session['url'] ) ) {
			wp_send_json_error( array( 'message' => 'Stripe checkout URL was not returned.' ), 500 );
		}

		wp_send_json_success(
			array(
				'url'       => (string) $session['url'],
				'sessionId' => ! empty( $session['id'] ) ? (string) $session['id'] : '',
			)
		);
	} catch ( \Exception $e ) {
		wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
	}
}
add_action( 'wp_ajax_hughalroztatoo_create_stripe_checkout_session', 'hughalroztatoo_booking_create_stripe_checkout_session' );
add_action( 'wp_ajax_nopriv_hughalroztatoo_create_stripe_checkout_session', 'hughalroztatoo_booking_create_stripe_checkout_session' );

/**
 * Stripe secret key from Amelia settings (same logic as checkout session AJAX).
 *
 * @return string Empty if unavailable.
 */
function hughalroztatoo_booking_amelia_stripe_secret_key() {
	if ( ! hughalroztatoo_amelia_active() ) {
		return '';
	}
	$amelia_settings = json_decode( (string) get_option( 'amelia_settings', '{}' ), true );
	$stripe          = isset( $amelia_settings['payments']['stripe'] ) && is_array( $amelia_settings['payments']['stripe'] )
		? $amelia_settings['payments']['stripe']
		: array();
	if ( empty( $stripe['enabled'] ) ) {
		return '';
	}
	$test_mode = ! empty( $stripe['testMode'] );
	$secret    = $test_mode
		? (string) ( isset( $stripe['testSecretKey'] ) ? $stripe['testSecretKey'] : '' )
		: (string) ( isset( $stripe['liveSecretKey'] ) ? $stripe['liveSecretKey'] : '' );

	return trim( $secret );
}

/**
 * Redirect to thank-you URL with only hat_* summary params (strip Stripe return params).
 *
 * @return void
 */
function hughalroztatoo_booking_redirect_thank_you_clean_from_request() {
	$base = hughalroztatoo_booking_thank_you_url();
	$params = array();
	foreach ( array( 'hat_format', 'hat_date', 'hat_slot', 'hat_paid' ) as $k ) {
		if ( isset( $_GET[ $k ] ) ) {
			$params[ $k ] = sanitize_text_field( wp_unslash( $_GET[ $k ] ) );
		}
	}
	$url = $params ? add_query_arg( $params, $base ) : $base;
	wp_safe_redirect( $url, 302 );
	exit;
}

/**
 * Relabel a pending Amelia payment from on-site to Stripe when starting hosted Checkout.
 *
 * Bookings are submitted with gateway `onSite` so Amelia does not run in-app Stripe
 * before we redirect; the admin would otherwise show "on site / cash" until the return handler runs.
 *
 * @param int $payment_id Amelia `amelia_payments.id`.
 * @return bool True when the row is suitable and now shows gateway stripe (or already did).
 */
function hughalroztatoo_booking_payment_prepare_stripe_checkout( $payment_id ) {
	global $wpdb;
	$payment_id = (int) $payment_id;
	if ( $payment_id <= 0 ) {
		return false;
	}

	$table = $wpdb->prefix . 'amelia_payments';
	$row   = $wpdb->get_row(
		$wpdb->prepare( "SELECT id, status, gateway FROM {$table} WHERE id = %d LIMIT 1", $payment_id ),
		ARRAY_A
	);
	if ( ! $row ) {
		return false;
	}

	$status = isset( $row['status'] ) ? (string) $row['status'] : '';
	$gateway = isset( $row['gateway'] ) ? (string) $row['gateway'] : '';

	if ( 'paid' === $status ) {
		return 'stripe' === $gateway;
	}

	if ( ! in_array( $status, array( 'pending', 'partiallyPaid' ), true ) ) {
		return false;
	}

	if ( 'stripe' === $gateway ) {
		return true;
	}

	if ( 'onSite' !== $gateway ) {
		return false;
	}

	$result = $wpdb->update(
		$table,
		array(
			'gateway'      => 'stripe',
			'gatewayTitle' => 'Stripe',
		),
		array( 'id' => $payment_id ),
		array( '%s', '%s' ),
		array( '%d' )
	);

	return false !== $result;
}

/**
 * Update Amelia payment row directly so the admin reflects Stripe (not "on-site") immediately.
 *
 * @param int    $payment_id      Amelia payment row id.
 * @param float  $charged         Amount paid (major units).
 * @param string $transaction_id  Stripe payment_intent id (optional).
 * @return bool True when row was updated.
 */
function hughalroztatoo_booking_payment_mark_stripe_paid( $payment_id, $charged, $transaction_id = '' ) {
	global $wpdb;
	$table = $wpdb->prefix . 'amelia_payments';

	$existing = $wpdb->get_row(
		$wpdb->prepare( "SELECT id, status, gateway, amount FROM {$table} WHERE id = %d LIMIT 1", $payment_id ),
		ARRAY_A
	);
	if ( ! $existing ) {
		return false;
	}

	$now = gmdate( 'Y-m-d H:i:s' );
	$data = array(
		'amount'       => (float) $charged,
		'gateway'      => 'stripe',
		'gatewayTitle' => 'Stripe',
		'status'       => 'paid',
		'dateTime'     => $now,
	);
	$format = array( '%f', '%s', '%s', '%s', '%s' );

	if ( '' !== (string) $transaction_id ) {
		$data['transactionId'] = (string) $transaction_id;
		$format[]              = '%s';
	}

	$result = $wpdb->update( $table, $data, array( 'id' => (int) $payment_id ), $format, array( '%d' ) );
	if ( false === $result ) {
		return false;
	}

	do_action( 'hughalroztatoo_booking_payment_marked_stripe', (int) $payment_id, (float) $charged, (string) $transaction_id, $existing );

	return true;
}

/**
 * After Stripe Checkout, mark the pending Amelia payment as paid via Stripe.
 *
 * Verifies the Checkout Session, updates the payment row directly so admin shows Stripe + paid amount,
 * then redirects to a clean thank-you URL. The internal Amelia callback would otherwise try to insert
 * a duplicate payment row when the existing one is no longer pending, so we deliberately skip it here.
 *
 * @return void
 */
function hughalroztatoo_booking_server_complete_stripe_checkout() {
	if ( is_admin() || wp_doing_ajax() || ! hughalroztatoo_amelia_active() ) {
		return;
	}
	if ( empty( $_GET['session_id'] ) ) {
		return;
	}
	$session_id = sanitize_text_field( wp_unslash( $_GET['session_id'] ) );
	if ( ! is_string( $session_id ) || strncmp( $session_id, 'cs_', 3 ) !== 0 ) {
		return;
	}

	$status_hint = isset( $_GET['hat_stripe_status'] ) ? strtolower( (string) wp_unslash( $_GET['hat_stripe_status'] ) ) : '';
	if ( '' !== $status_hint && 'success' !== $status_hint && 'paid' !== $status_hint ) {
		return;
	}

	$done_key = 'hughalroztatoo_cs_done_' . md5( $session_id );
	if ( get_transient( $done_key ) ) {
		hughalroztatoo_booking_redirect_thank_you_clean_from_request();
	}

	$secret = hughalroztatoo_booking_amelia_stripe_secret_key();
	if ( '' === $secret || ! class_exists( '\\AmeliaVendor\\Stripe\\StripeClient' ) ) {
		return;
	}

	try {
		$stripe_client = new \AmeliaVendor\Stripe\StripeClient( $secret );
		$session       = $stripe_client->checkout->sessions->retrieve( $session_id );
		$row           = is_array( $session ) ? $session : ( method_exists( $session, 'toArray' ) ? $session->toArray() : array() );
	} catch ( \Exception $e ) {
		return;
	}

	$pay_status = isset( $row['payment_status'] ) ? (string) $row['payment_status'] : '';
	if ( 'paid' !== $pay_status ) {
		return;
	}

	$meta_raw = isset( $row['metadata'] ) ? $row['metadata'] : array();
	if ( is_object( $meta_raw ) && method_exists( $meta_raw, 'toArray' ) ) {
		$meta_raw = $meta_raw->toArray();
	}
	if ( ! is_array( $meta_raw ) ) {
		$meta_raw = array();
	}

	$payment_id = isset( $_GET['hat_payment_id'] ) ? absint( wp_unslash( $_GET['hat_payment_id'] ) ) : 0;
	if ( $payment_id <= 0 && ! empty( $meta_raw['payment_amelia_id'] ) ) {
		$payment_id = absint( $meta_raw['payment_amelia_id'] );
	}
	if ( $payment_id <= 0 ) {
		return;
	}

	$meta_pid = ! empty( $meta_raw['payment_amelia_id'] ) ? absint( $meta_raw['payment_amelia_id'] ) : 0;
	if ( $meta_pid > 0 && $meta_pid !== $payment_id ) {
		return;
	}

	$amount_cents = isset( $row['amount_total'] ) ? (int) $row['amount_total'] : 0;
	$charged      = $amount_cents > 0 ? round( $amount_cents / 100, 2 ) : 0.0;
	if ( $charged <= 0 && isset( $_GET['hat_charged_amount'] ) ) {
		$charged = (float) wp_unslash( $_GET['hat_charged_amount'] );
	}
	if ( $charged <= 0 ) {
		global $wpdb;
		$table_pay = $wpdb->prefix . 'amelia_payments';
		$row_amt   = $wpdb->get_var( $wpdb->prepare( "SELECT amount FROM {$table_pay} WHERE id = %d LIMIT 1", $payment_id ) );
		if ( null !== $row_amt && (float) $row_amt > 0 ) {
			$charged = (float) $row_amt;
		}
	}
	if ( $charged <= 0 ) {
		return;
	}

	$payment_intent = '';
	if ( ! empty( $row['payment_intent'] ) ) {
		$payment_intent = is_string( $row['payment_intent'] ) ? $row['payment_intent'] : '';
		if ( '' === $payment_intent && is_object( $row['payment_intent'] ) && isset( $row['payment_intent']->id ) ) {
			$payment_intent = (string) $row['payment_intent']->id;
		}
	}

	$updated = hughalroztatoo_booking_payment_mark_stripe_paid( $payment_id, $charged, $payment_intent );
	if ( ! $updated ) {
		return;
	}

	set_transient( $done_key, 1, DAY_IN_SECONDS );
	hughalroztatoo_booking_redirect_thank_you_clean_from_request();
}
add_action( 'init', 'hughalroztatoo_booking_server_complete_stripe_checkout', 1 );

/**
 * Shortcode callback.
 *
 * Attributes:
 * - category: comma-separated Amelia category IDs to limit the list (optional).
 * - photo_field: ID du champ fichier « zone » (1er bloc), optionnel si un seul champ ou couple auto.
 * - photo_ref_field: ID du champ « référence (style) » (2e bloc). Si absent et la prestation a exactement 2 champs fichier Amelia, couplage auto (ids croissants : zone puis ref).
 * - body_zone_field: ID du champ personnalisé Amelia « Zone à tatouer » (défaut: filtre hughalroztatoo_booking_body_zone_field_id, 5).
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
			'body_zone_field'    => '',
			'project_note_field' => '',
		),
		$atts,
		'hugh_amelia_booking'
	);

	$ids = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', (string) $atts['category'] ) ) ) );

	$photo_field_id     = absint( $atts['photo_field'] );
	$photo_ref_field_id = absint( $atts['photo_ref_field'] );
	$body_zone_fid      = absint( $atts['body_zone_field'] );
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

	$default_note_id      = (int) apply_filters( 'hughalroztatoo_booking_project_note_field_id', 4 );
	$default_body_zone_id = (int) apply_filters( 'hughalroztatoo_booking_body_zone_field_id', 5 );
	$data                 = wp_json_encode(
		array(
			'categoryIds'          => $ids,
			'categoryDescriptions' => hughalroztatoo_get_booking_category_descriptions(),
			'photoFieldId'         => $photo_field_id ? $photo_field_id : null,
			'photoRefFieldId'      => $photo_ref_field_id ? $photo_ref_field_id : null,
			'bodyZoneFieldId'      => $body_zone_fid > 0 ? $body_zone_fid : $default_body_zone_id,
			'projectNoteFieldId'   => $project_note_fid > 0 ? $project_note_fid : $default_note_id,
		)
	);

	return '<div id="hugh-ms-booking-root" class="hugh-ms-booking" data-hugh-ms-config="' . esc_attr( $data ) . '" role="region" aria-live="polite"></div>';
}
add_shortcode( 'hugh_amelia_booking', 'hughalroztatoo_shortcode_amelia_booking' );
