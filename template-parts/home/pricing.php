<?php
/**
 * Front page: pricing formats block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hughalroztatoo_amelia_services_by_ids' ) ) {
	/**
	 * Load Amelia services by IDs.
	 *
	 * @param int[] $service_ids Service IDs.
	 * @return array<int,array<string,mixed>>
	 */
	function hughalroztatoo_amelia_services_by_ids( $service_ids ) {
		global $wpdb;

		if ( ! $wpdb || empty( $service_ids ) || ! function_exists( 'hughalroztatoo_amelia_active' ) || ! hughalroztatoo_amelia_active() ) {
			return array();
		}

		$ids = array_values(
			array_filter(
				array_map( 'intval', (array) $service_ids ),
				static function ( $id ) {
					return $id > 0;
				}
			)
		);
		if ( empty( $ids ) ) {
			return array();
		}

		$table_name = $wpdb->prefix . 'amelia_services';
		$like       = $wpdb->esc_like( $table_name );
		$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $table_name !== $exists ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$query        = $wpdb->prepare(
			"SELECT id, name, duration, price, description FROM {$table_name} WHERE id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$ids
		);
		$rows         = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$map = array();
		foreach ( $rows as $row ) {
			$service_id = isset( $row['id'] ) ? (int) $row['id'] : 0;
			if ( $service_id <= 0 ) {
				continue;
			}
			$map[ $service_id ] = $row;
		}

		return $map;
	}
}

if ( ! function_exists( 'hughalroztatoo_format_service_duration' ) ) {
	/**
	 * Convert Amelia duration to readable card duration.
	 *
	 * @param mixed $duration_raw Raw duration from Amelia.
	 * @return string
	 */
	function hughalroztatoo_format_service_duration( $duration_raw ) {
		$raw = (int) $duration_raw;
		if ( $raw <= 0 ) {
			return '';
		}

		$minutes = $raw > 480 ? (int) round( $raw / 60 ) : $raw;
		if ( $minutes >= 60 && 0 === $minutes % 60 ) {
			return (string) ( $minutes / 60 ) . 'h';
		}
		if ( $minutes >= 60 ) {
			return (string) floor( $minutes / 60 ) . 'h';
		}

		return (string) $minutes . ' min';
	}
}

if ( ! function_exists( 'hughalroztatoo_format_service_price' ) ) {
	/**
	 * Convert Amelia price to card display.
	 *
	 * @param mixed $price_raw Raw price from Amelia.
	 * @return string
	 */
	function hughalroztatoo_format_service_price( $price_raw ) {
		if ( null === $price_raw || '' === trim( (string) $price_raw ) ) {
			return '';
		}

		$price = (float) $price_raw;
		if ( abs( $price - round( $price ) ) < 0.001 ) {
			$display = (string) (int) round( $price );
		} else {
			$display = rtrim( rtrim( number_format( $price, 2, '.', '' ), '0' ), '.' );
		}

		return $display . ' CAD';
	}
}

if ( ! function_exists( 'hughalroztatoo_service_description_to_features' ) ) {
	/**
	 * Convert Amelia HTML/plain description to multiline feature list.
	 *
	 * @param string $description_raw Service description.
	 * @return string
	 */
	function hughalroztatoo_service_description_to_features( $description_raw ) {
		$description = (string) $description_raw;
		if ( '' === trim( $description ) ) {
			return '';
		}

		$text = str_ireplace(
			array( '</li>', '<br>', '<br/>', '<br />', '</p>', '</div>' ),
			"\n",
			$description
		);
		$text = preg_replace( '/<li[^>]*>/i', '', $text );
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( "/\r\n|\r/u", "\n", $text );
		$text = preg_replace( "/[ \t]+\n/u", "\n", $text );
		$text = preg_replace( "/\n{2,}/u", "\n", $text );

		return trim( $text );
	}
}

$home_post_id = get_queried_object_id();

$pricing_eyebrow       = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_eyebrow', $home_post_id ) : '';
$pricing_title_main_1  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_1', $home_post_id ) : '';
$pricing_title_prefix1 = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_prefix_1', $home_post_id ) : '';
$pricing_title_main_2  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_2', $home_post_id ) : '';
$pricing_title_prefix2 = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_prefix_2', $home_post_id ) : '';
$pricing_title_main_3  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_3', $home_post_id ) : '';

$pricing_eyebrow       = '' !== $pricing_eyebrow ? $pricing_eyebrow : 'FORMATS';
$pricing_title_main_1  = '' !== $pricing_title_main_1 ? $pricing_title_main_1 : 'Choisissez';
$pricing_title_prefix1 = '' !== $pricing_title_prefix1 ? $pricing_title_prefix1 : 'votre';
$pricing_title_main_2  = '' !== $pricing_title_main_2 ? $pricing_title_main_2 : 'format';
$pricing_title_prefix2 = '' !== $pricing_title_prefix2 ? $pricing_title_prefix2 : 'de';
$pricing_title_main_3  = '' !== $pricing_title_main_3 ? $pricing_title_main_3 : 'réservation';

$pricing_cards = array();
$booking_base_url_raw = function_exists( 'get_field' ) ? get_field( 'home_hero_cta_url', $home_post_id ) : '';
$booking_base_url = is_array( $booking_base_url_raw ) ? (string) ( $booking_base_url_raw['url'] ?? '' ) : (string) $booking_base_url_raw;

if ( function_exists( 'get_field' ) ) {
	$pricing_cards = get_field( 'home_pricing_cards', $home_post_id );
}

if ( ! is_array( $pricing_cards ) || empty( $pricing_cards ) ) {
	$pricing_cards = array(
		array(
			'duration' => '2h',
			'title'    => 'SHORT BLOCK',
			'features' => "Option de paiement accessible\nIdeal pour les petits projets\nParfait pour continuation de projet\nEngagement minimal, impact maximal",
			'price'    => '500 CAD',
			'popular'  => 0,
		),
		array(
			'duration' => '4h',
			'title'    => 'HALF DAY',
			'features' => "Designs moyens a grands\nContinuation & nouveaux projets\nEquilibre ideal temps / resultat\nPriorite sur les disponibilites",
			'price'    => '800 CAD',
			'popular'  => 1,
		),
		array(
			'duration' => '7h',
			'title'    => 'FULL DAY',
			'features' => "Pour projets ambitieux et detailles\nProjet boucle en une seule journee\nMeilleur rendement par heure\nQualite optimale sans interruption",
			'price'    => '1200 CAD',
			'popular'  => 0,
		),
	);
}

$booking_base_url = '' !== trim( $booking_base_url ) ? $booking_base_url : home_url( '/booking/' );
$pricing_service_ids = array_values(
	array_filter(
		array_map(
			static function ( $card ) {
				return isset( $card['service_id'] ) ? (int) $card['service_id'] : 0;
			},
			(array) $pricing_cards
		)
	)
);
$pricing_services_map = hughalroztatoo_amelia_services_by_ids( $pricing_service_ids );
?>

<section class="hat-pricing" id="pricing" aria-labelledby="hat-pricing-title">
	<div class="hat-container">
		<div class="hat-pricing__heading">
			<p class="hat-pricing__eyebrow">
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-pricing__eyebrow-word"><?php echo esc_html( $pricing_eyebrow ); ?></span>
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">)</span>
			</p>

			<h2 class="hat-pricing__title" id="hat-pricing-title">
				<span class="hat-pricing__title-prefix" aria-hidden="true"></span>
				<span class="hat-pricing__title-main hat-pricing__title-main--gradient"><?php echo esc_html( $pricing_title_main_1 ); ?></span>

				<span class="hat-pricing__title-prefix hat-pricing__title-prefix--with-start-blur">
					<span class="hat-pricing__title-blur hat-pricing__title-blur--start" aria-hidden="true"></span>
					<?php echo esc_html( $pricing_title_prefix1 ); ?>
				</span>
				<span class="hat-pricing__title-main hat-pricing__title-main--with-wire"><?php echo esc_html( $pricing_title_main_2 ); ?><img class="hat-pricing__title-wire" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/wire.svg' ); ?>" alt="" aria-hidden="true" width="126" height="33"></span>

				<span class="hat-pricing__title-prefix"><?php echo esc_html( $pricing_title_prefix2 ); ?></span>
				<span class="hat-pricing__title-main hat-pricing__title-main--gradient hat-pricing__title-main--with-end-blur">
					<?php echo esc_html( $pricing_title_main_3 ); ?>
					<span class="hat-pricing__title-blur" aria-hidden="true"></span>
				</span>
			</h2>
		</div>

		<ul class="hat-pricing__grid">
			<?php
			$pricing_card_tags = array(
				1 => 'nouveau projet',
				2 => 'meilleur qualité / prix',
			);
			?>
			<?php foreach ( $pricing_cards as $index => $card ) : ?>
				<?php
				$duration = isset( $card['duration'] ) ? (string) $card['duration'] : '';
				$title    = isset( $card['title'] ) ? (string) $card['title'] : '';
				$features = isset( $card['features'] ) ? (string) $card['features'] : '';
				$price    = isset( $card['price'] ) ? (string) $card['price'] : '';
				$popular  = ! empty( $card['popular'] );
				$card_tag = isset( $card['tag'] ) ? trim( (string) $card['tag'] ) : '';
				if ( '' === $card_tag && isset( $pricing_card_tags[ $index ] ) ) {
					$card_tag = $pricing_card_tags[ $index ];
				}
				$service_id = isset( $card['service_id'] ) ? (int) $card['service_id'] : 0;
				$service_data = $service_id > 0 && isset( $pricing_services_map[ $service_id ] ) ? $pricing_services_map[ $service_id ] : null;
				if ( is_array( $service_data ) ) {
					$service_name        = isset( $service_data['name'] ) ? trim( (string) $service_data['name'] ) : '';
					$service_duration    = isset( $service_data['duration'] ) ? hughalroztatoo_format_service_duration( $service_data['duration'] ) : '';
					$service_price       = isset( $service_data['price'] ) ? hughalroztatoo_format_service_price( $service_data['price'] ) : '';
					$service_description = isset( $service_data['description'] ) ? (string) $service_data['description'] : '';
					$service_features    = hughalroztatoo_service_description_to_features( $service_description );

					if ( '' !== $service_name ) {
						$title = $service_name;
					}
					if ( '' !== $service_duration ) {
						$duration = $service_duration;
					}
					if ( '' !== $service_price ) {
						$price = $service_price;
					}
					if ( '' !== $service_features ) {
						$features = $service_features;
					}
				}

				$lines = preg_split( '/\r\n|\r|\n/', $features );
				$query_args = array(
					'hat_service'  => $title,
					'hat_duration' => $duration,
				);
				if ( $service_id > 0 ) {
					$query_args['hat_service_id'] = $service_id;
				}
				$card_url = add_query_arg( $query_args, $booking_base_url );
				?>
				<li class="hat-pricing__card<?php echo $popular ? ' hat-pricing__card--popular' : ''; ?>">
					<a class="hat-pricing__card-link" href="<?php echo esc_url( $card_url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Choisir le format %1$s (%2$s)', 'hughalroztatoo' ), $title, $duration ) ); ?>">
						<p class="hat-pricing__duration">
							<span class="hat-pricing__duration-label"><?php esc_html_e( '(T)', 'hughalroztatoo' ); ?></span>
							<span class="hat-pricing__duration-value"><?php echo esc_html( $duration ); ?></span>
						</p>
						<?php if ( $popular ) : ?>
							<p class="hat-pricing__badge">
								<img class="hat-pricing__badge-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/mdi_fire.svg' ); ?>" alt="" aria-hidden="true" width="20" height="20">
								<span class="hat-pricing__badge-text"><?php esc_html_e( 'le plus populaire', 'hughalroztatoo' ); ?></span>
							</p>
						<?php endif; ?>
						<span class="hat-pricing__bg-index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( '' !== $card_tag ) : ?>
							<p class="hat-pricing__card-tag"><?php echo esc_html( $card_tag ); ?></p>
						<?php endif; ?>
						<h3 class="hat-pricing__card-title"><?php echo esc_html( $title ); ?></h3>
						<ul class="hat-pricing__features">
							<?php foreach ( $lines as $line ) : ?>
								<?php if ( '' === trim( $line ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li><?php echo esc_html( trim( $line ) ); ?></li>
							<?php endforeach; ?>
						</ul>
						<p class="hat-pricing__price"><?php echo esc_html( $price ); ?></p>
						<span class="hat-pricing__cta" aria-hidden="true">
							<span class="hat-pricing__cta-text"><?php esc_html_e( 'RÉSERVER CE FORMAT', 'hughalroztatoo' ); ?></span>
							<span class="hat-pricing__cta-icon" aria-hidden="true">
								<svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
									<path d="M7 18.2083H25.8173M15.9342 28.4167L26.125 18.2083L15.9342 8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
