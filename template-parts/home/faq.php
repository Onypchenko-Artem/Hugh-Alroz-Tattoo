<?php
/**
 * Front page: FAQ block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faq_items = array(
	array(
		'question' => 'COMMENT BIEN ME PRÉPARER AVANT LA SÉANCE ?',
		'answer'   => 'Douche obligatoire. Peau propre, sans crème. Bien dormir, bien manger, bien s’hydrater.',
	),
	array(
		'question' => 'COMBIEN COÛTE UN TATOUAGE ?',
		'answer'   => 'Douche obligatoire. Peau propre, sans crème. Bien dormir, bien manger, bien s’hydrater.',
	),
	array(
		'question' => 'COMBIEN DE TEMPS DURE UNE SÉANCE ?',
		'answer'   => 'Douche obligatoire. Peau propre, sans crème. Bien dormir, bien manger, bien s’hydrater.',
	),
	array(
		'question' => 'QUELS SONT LES SOINS APRÈS LE TATOUAGE ?',
		'answer'   => 'Douche obligatoire. Peau propre, sans crème. Bien dormir, bien manger, bien s’hydrater.',
	),
	array(
		'question' => 'PEUT-ON MODIFIER UN TATOUAGE EXISTANT ?',
		'answer'   => 'Douche obligatoire. Peau propre, sans crème. Bien dormir, bien manger, bien s’hydrater.',
	),
);
?>

<section class="hat-faq" id="faq" aria-labelledby="hat-faq-title">
	<div class="hat-container">
		<div class="hat-faq__header">
			<div class="hat-faq__header-left">
				<p class="hat-faq__eyebrow">
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">(</span>
					<span class="hat-faq__eyebrow-word"><?php esc_html_e( 'FAQ', 'hughalroztatoo' ); ?></span>
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">)</span>
				</p>

				<h2 class="hat-faq__title" id="hat-faq-title">
					<span class="hat-faq__title-line"><?php esc_html_e( 'QUESTIONS', 'hughalroztatoo' ); ?></span>
					<span class="hat-faq__title-line hat-faq__title-line--indent"><?php esc_html_e( 'FRÉQUENTES', 'hughalroztatoo' ); ?></span>
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
