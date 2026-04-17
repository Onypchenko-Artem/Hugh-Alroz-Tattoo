<?php
/**
 * Front page: pricing formats block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
			<?php foreach ( $pricing_cards as $index => $card ) : ?>
				<?php
				$duration = isset( $card['duration'] ) ? (string) $card['duration'] : '';
				$title    = isset( $card['title'] ) ? (string) $card['title'] : '';
				$features = isset( $card['features'] ) ? (string) $card['features'] : '';
				$price    = isset( $card['price'] ) ? (string) $card['price'] : '';
				$popular  = ! empty( $card['popular'] );
				$lines    = preg_split( '/\r\n|\r|\n/', $features );
				?>
				<li class="hat-pricing__card">
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
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
