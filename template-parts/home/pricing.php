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
	if ( ! function_exists( 'hughalroztatoo_amelia_locale_candidates' ) ) {
		/**
		 * Build locale candidates to match Amelia translation keys.
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

				$expanded[] = $candidate;
				$expanded[] = str_replace( '-', '_', $candidate );
				$expanded[] = str_replace( '_', '-', $candidate );
				$expanded[] = strtolower( $candidate );

				$short = strtok( str_replace( '-', '_', strtolower( $candidate ) ), '_' );
				if ( is_string( $short ) && '' !== $short ) {
					$expanded[] = $short;
				}
			}

			return array_values( array_unique( array_filter( $expanded ) ) );
		}
	}

	/**
	 * Extract translated service field from Amelia translations payload.
	 *
	 * Supports both common Amelia shapes:
	 * - {"name":{"en_US":"..."}, "description":{"en_US":"..."}}
	 * - {"en_US":{"name":"...","description":"..."}}
	 *
	 * @param mixed    $translations_raw Raw JSON/text from Amelia translations column.
	 * @param string   $field            Field name (name|description).
	 * @param string[] $locale_candidates Ordered locale candidates.
	 * @return string
	 */
	function hughalroztatoo_amelia_get_translated_service_field( $translations_raw, $field, $locale_candidates ) {
		if ( '' === trim( (string) $translations_raw ) ) {
			return '';
		}

		$translations = json_decode( (string) $translations_raw, true );
		if ( ! is_array( $translations ) || empty( $translations ) ) {
			return '';
		}

		foreach ( (array) $locale_candidates as $locale ) {
			$locale = (string) $locale;
			if ( '' === $locale ) {
				continue;
			}

			if ( isset( $translations[ $field ][ $locale ] ) && is_string( $translations[ $field ][ $locale ] ) ) {
				$value = trim( $translations[ $field ][ $locale ] );
				if ( '' !== $value ) {
					return $value;
				}
			}

			if ( isset( $translations[ $locale ][ $field ] ) && is_string( $translations[ $locale ][ $field ] ) ) {
				$value = trim( $translations[ $locale ][ $field ] );
				if ( '' !== $value ) {
					return $value;
				}
			}
		}

		return '';
	}

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
			"SELECT id, name, duration, price, description, translations FROM {$table_name} WHERE id IN ({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$ids
		);
		$rows         = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$map = array();
		$locale_candidates = hughalroztatoo_amelia_locale_candidates();
		foreach ( $rows as $row ) {
			$service_id = isset( $row['id'] ) ? (int) $row['id'] : 0;
			if ( $service_id <= 0 ) {
				continue;
			}

			$translated_name = hughalroztatoo_amelia_get_translated_service_field(
				$row['translations'] ?? '',
				'name',
				$locale_candidates
			);
			$translated_description = hughalroztatoo_amelia_get_translated_service_field(
				$row['translations'] ?? '',
				'description',
				$locale_candidates
			);
			if ( '' !== $translated_name ) {
				$row['name'] = $translated_name;
			}
			if ( '' !== $translated_description ) {
				$row['description'] = $translated_description;
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

if ( ! function_exists( 'hughalroztatoo_get_localized_field_fallback' ) ) {
	/**
	 * Read an ACF field from current post with translation fallback.
	 *
	 * @param string $field_name ACF field name.
	 * @param int    $post_id Current post ID.
	 * @return mixed
	 */
	function hughalroztatoo_get_localized_field_fallback( $field_name, $post_id ) {
		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}

		$value = get_field( $field_name, $post_id );
		if ( ! empty( $value ) ) {
			return $value;
		}

		if ( ! function_exists( 'pll_get_post_translations' ) ) {
			return $value;
		}

		$translations = pll_get_post_translations( (int) $post_id );
		if ( ! is_array( $translations ) || empty( $translations ) ) {
			return $value;
		}

		foreach ( $translations as $translation_post_id ) {
			$translation_post_id = (int) $translation_post_id;
			if ( $translation_post_id <= 0 || $translation_post_id === (int) $post_id ) {
				continue;
			}

			$fallback_value = get_field( $field_name, $translation_post_id );
			if ( ! empty( $fallback_value ) ) {
				return $fallback_value;
			}
		}

		return $value;
	}
}

$home_post_id = get_queried_object_id();

$pricing_eyebrow       = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_eyebrow', $home_post_id ) : '';
$pricing_title_main_1  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_1', $home_post_id ) : '';
$pricing_title_prefix1 = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_prefix_1', $home_post_id ) : '';
$pricing_title_main_2  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_2', $home_post_id ) : '';
$pricing_title_prefix2 = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_prefix_2', $home_post_id ) : '';
$pricing_title_main_3  = function_exists( 'get_field' ) ? (string) get_field( 'home_pricing_title_main_3', $home_post_id ) : '';

$pricing_cards = array();
$booking_base_url_raw = hughalroztatoo_get_localized_field_fallback( 'home_hero_cta_url', $home_post_id );
$booking_base_url = is_array( $booking_base_url_raw ) ? (string) ( $booking_base_url_raw['url'] ?? '' ) : (string) $booking_base_url_raw;

if ( function_exists( 'get_field' ) ) {
	$pricing_cards = get_field( 'home_pricing_cards', $home_post_id );
}

$pricing_cards = is_array( $pricing_cards ) ? $pricing_cards : array();
$booking_base_url = trim( $booking_base_url );
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
$pricing_has_heading = '' !== $pricing_eyebrow || '' !== $pricing_title_main_1 || '' !== $pricing_title_prefix1 || '' !== $pricing_title_main_2 || '' !== $pricing_title_prefix2 || '' !== $pricing_title_main_3;

if ( ! $pricing_has_heading && empty( $pricing_cards ) ) {
	return;
}
?>

<section class="hat-pricing" id="pricing"<?php echo ( '' !== $pricing_title_main_1 || '' !== $pricing_title_prefix1 || '' !== $pricing_title_main_2 || '' !== $pricing_title_prefix2 || '' !== $pricing_title_main_3 ) ? ' aria-labelledby="hat-pricing-title"' : ''; ?>>
	<div class="hat-container">
		<?php if ( $pricing_has_heading ) : ?>
		<div class="hat-pricing__heading">
			<?php if ( '' !== $pricing_eyebrow ) : ?>
			<p class="hat-pricing__eyebrow">
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-pricing__eyebrow-word"><?php echo esc_html( $pricing_eyebrow ); ?></span>
				<span class="hat-pricing__eyebrow-bracket" aria-hidden="true">)</span>
			</p>
			<?php endif; ?>

			<?php if ( '' !== $pricing_title_main_1 || '' !== $pricing_title_prefix1 || '' !== $pricing_title_main_2 || '' !== $pricing_title_prefix2 || '' !== $pricing_title_main_3 ) : ?>
			<h2 class="hat-pricing__title" id="hat-pricing-title">
				<?php if ( '' !== $pricing_title_main_1 ) : ?>
					<span class="hat-pricing__title-prefix" aria-hidden="true"></span>
					<span class="hat-pricing__title-main hat-pricing__title-main--gradient"><?php echo esc_html( $pricing_title_main_1 ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $pricing_title_prefix1 ) : ?>
				<span class="hat-pricing__title-prefix hat-pricing__title-prefix--with-start-blur">
					<span class="hat-pricing__title-blur hat-pricing__title-blur--start" aria-hidden="true"></span>
					<?php echo esc_html( $pricing_title_prefix1 ); ?>
				</span>
				<?php endif; ?>
				<?php if ( '' !== $pricing_title_main_2 ) : ?>
					<span class="hat-pricing__title-main hat-pricing__title-main--with-wire"><?php echo esc_html( $pricing_title_main_2 ); ?><img class="hat-pricing__title-wire" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/wire.svg' ); ?>" alt="" aria-hidden="true" width="126" height="33"></span>
				<?php endif; ?>

				<?php if ( '' !== $pricing_title_prefix2 ) : ?>
					<span class="hat-pricing__title-prefix"><?php echo esc_html( $pricing_title_prefix2 ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $pricing_title_main_3 ) : ?>
				<span class="hat-pricing__title-main hat-pricing__title-main--gradient hat-pricing__title-main--with-end-blur">
					<?php echo esc_html( $pricing_title_main_3 ); ?>
					<span class="hat-pricing__title-blur" aria-hidden="true"></span>
				</span>
				<?php endif; ?>
			</h2>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $pricing_cards ) ) : ?>
		<ul class="hat-pricing__grid">
			<?php foreach ( $pricing_cards as $index => $card ) : ?>
				<?php
				$duration = isset( $card['duration'] ) ? (string) $card['duration'] : '';
				$title    = isset( $card['title'] ) ? (string) $card['title'] : '';
				$features = isset( $card['features'] ) ? (string) $card['features'] : '';
				$price    = isset( $card['price'] ) ? (string) $card['price'] : '';
				$popular  = ! empty( $card['popular'] );
				$card_tag = isset( $card['tag'] ) ? trim( (string) $card['tag'] ) : '';
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

				if ( '' === $duration && '' === $title && '' === $features && '' === $price ) {
					continue;
				}

				$lines = preg_split( '/\r\n|\r|\n/', $features );
				$card_url = '';
				if ( '' !== $booking_base_url ) {
					$query_args = array_filter(
						array(
							'hat_service'    => $title,
							'hat_duration'   => $duration,
							'hat_service_id' => $service_id > 0 ? $service_id : '',
						)
					);
					$card_url   = add_query_arg( $query_args, $booking_base_url );
				}
				?>
				<li class="hat-pricing__card<?php echo $popular ? ' hat-pricing__card--popular' : ''; ?>">
					<?php if ( '' !== $card_url ) : ?>
					<a class="hat-pricing__card-link" href="<?php echo esc_url( $card_url ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Choisir le format %1$s (%2$s)', 'hughalroztatoo' ), $title, $duration ) ); ?>">
					<?php else : ?>
					<div class="hat-pricing__card-link">
					<?php endif; ?>
						<?php if ( '' !== $duration ) : ?>
							<p class="hat-pricing__duration">
								<span class="hat-pricing__duration-label"><?php esc_html_e( '(T)', 'hughalroztatoo' ); ?></span>
								<span class="hat-pricing__duration-value"><?php echo esc_html( $duration ); ?></span>
							</p>
						<?php endif; ?>
						<?php if ( $popular ) : ?>
							<p class="hat-pricing__badge">
								<img class="hat-pricing__badge-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/mdi_fire.svg' ); ?>" alt="" aria-hidden="true" width="20" height="20">
								<span class="hat-pricing__badge-text"><?php echo esc_html( hughalroztatoo_pll__( 'le plus populaire' ) ); ?></span>
							</p>
						<?php endif; ?>
						<span class="hat-pricing__bg-index" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<?php if ( '' !== $card_tag ) : ?>
							<p class="hat-pricing__card-tag"><?php echo esc_html( $card_tag ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $title ) : ?>
							<h3 class="hat-pricing__card-title"><?php echo esc_html( $title ); ?></h3>
						<?php endif; ?>
						<?php if ( is_array( $lines ) && '' !== trim( $features ) ) : ?>
						<ul class="hat-pricing__features">
							<?php foreach ( $lines as $line ) : ?>
								<?php if ( '' === trim( $line ) ) : ?>
									<?php continue; ?>
								<?php endif; ?>
								<li><?php echo esc_html( trim( $line ) ); ?></li>
							<?php endforeach; ?>
						</ul>
						<?php endif; ?>
						<?php if ( '' !== $price ) : ?>
							<p class="hat-pricing__price"><?php echo esc_html( $price ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $card_url ) : ?>
							<span class="hat-pricing__cta" aria-hidden="true">
								<span class="hat-pricing__cta-text"><?php echo esc_html( hughalroztatoo_pll__( 'RÉSERVER CE FORMAT' ) ); ?></span>
								<span class="hat-pricing__cta-icon" aria-hidden="true">
									<svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
										<path d="M7 18.2083H25.8173M15.9342 28.4167L26.125 18.2083L15.9342 8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</span>
						<?php endif; ?>
					<?php if ( '' !== $card_url ) : ?>
					</a>
					<?php else : ?>
					</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
