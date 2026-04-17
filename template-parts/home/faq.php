<?php
/**
 * Front page: FAQ block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();

$faq_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'home_faq_eyebrow', $home_post_id ) : '';
$faq_title_1 = function_exists( 'get_field' ) ? (string) get_field( 'home_faq_title_line_1', $home_post_id ) : '';
$faq_title_2 = function_exists( 'get_field' ) ? (string) get_field( 'home_faq_title_line_2', $home_post_id ) : '';
$faq_items   = function_exists( 'get_field' ) ? get_field( 'home_faq_items', $home_post_id ) : array();

$faq_eyebrow = '' !== $faq_eyebrow ? $faq_eyebrow : 'FAQ';
$faq_title_1 = '' !== $faq_title_1 ? $faq_title_1 : 'QUESTIONS';
$faq_title_2 = '' !== $faq_title_2 ? $faq_title_2 : 'FREQUENTES';

if ( ! is_array( $faq_items ) || empty( $faq_items ) ) {
	$faq_items = array(
		array(
			'question' => 'COMMENT BIEN ME PREPARER AVANT LA SEANCE ?',
			'answer'   => 'Douche obligatoire. Peau propre, sans creme. Bien dormir, bien manger, bien s hydrater.',
		),
		array(
			'question' => 'COMBIEN COUTE UN TATOUAGE ?',
			'answer'   => 'Le prix depend de la taille, du style et de la complexite. Choisissez un format adapte a votre projet.',
		),
		array(
			'question' => 'COMBIEN DE TEMPS DURE UNE SEANCE ?',
			'answer'   => 'La duree depend du format reserve: short block, half day ou full day.',
		),
		array(
			'question' => 'QUELS SONT LES SOINS APRES LE TATOUAGE ?',
			'answer'   => 'Suivez le protocole de cicatrisation recommande pour garantir un resultat propre et durable.',
		),
		array(
			'question' => 'PEUT-ON MODIFIER UN TATOUAGE EXISTANT ?',
			'answer'   => 'Oui, selon le projet. Une consultation permet de definir la meilleure approche.',
		),
	);
}
?>

<section class="hat-faq" id="faq" aria-labelledby="hat-faq-title">
	<div class="hat-container">
		<div class="hat-faq__header">
			<div class="hat-faq__header-left">
				<p class="hat-faq__eyebrow">
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">(</span>
					<span class="hat-faq__eyebrow-word"><?php echo esc_html( $faq_eyebrow ); ?></span>
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">)</span>
				</p>

				<h2 class="hat-faq__title" id="hat-faq-title">
					<span class="hat-faq__title-line"><?php echo esc_html( $faq_title_1 ); ?></span>
					<span class="hat-faq__title-line hat-faq__title-line--indent"><?php echo esc_html( $faq_title_2 ); ?></span>
				</h2>
			</div>

			<div class="hat-faq__header-right">
				<span class="hat-faq__ampersand" aria-hidden="true">&amp;&amp;</span>
				<div class="hat-faq__nav" aria-label="<?php esc_attr_e( 'FAQ navigation', 'hughalroztatoo' ); ?>">
					<button class="hat-faq__nav-btn hat-faq__nav-btn--prev" type="button" aria-label="<?php esc_attr_e( 'Previous question', 'hughalroztatoo' ); ?>">
						<svg width="18" height="14" viewBox="0 0 18 14" fill="none" aria-hidden="true">
							<path d="M7 1L1 7M1 7L7 13M1 7H17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					<button class="hat-faq__nav-btn hat-faq__nav-btn--next" type="button" aria-label="<?php esc_attr_e( 'Next question', 'hughalroztatoo' ); ?>">
						<svg width="18" height="14" viewBox="0 0 18 14" fill="none" aria-hidden="true">
							<path d="M11 1L17 7M17 7L11 13M17 7H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="hat-faq__slider" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Questions fréquentes', 'hughalroztatoo' ); ?>">
		<div class="hat-faq__track">
			<?php foreach ( $faq_items as $i => $item ) : ?>
			<div
				class="hat-faq__card<?php echo 0 === $i ? ' is-active' : ''; ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr( ( $i + 1 ) . ' / ' . count( $faq_items ) ); ?>"
			>
				<h3 class="hat-faq__card-question"><?php echo esc_html( $item['question'] ); ?></h3>
				<span class="hat-faq__card-icon" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/shapie.svg' ); ?>" alt="" loading="lazy" />
				</span>
				<div class="hat-faq__card-answer-wrap">
					<p class="hat-faq__card-answer"><?php echo esc_html( $item['answer'] ); ?></p>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
