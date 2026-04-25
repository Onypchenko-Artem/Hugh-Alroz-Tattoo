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

$faq_items = is_array( $faq_items ) ? array_values(
	array_filter(
		$faq_items,
		static function ( $item ) {
			return is_array( $item ) && ( ! empty( $item['question'] ) || ! empty( $item['answer'] ) );
		}
	)
) : array();

$faq_has_heading = '' !== $faq_eyebrow || '' !== $faq_title_1 || '' !== $faq_title_2;

if ( ! $faq_has_heading && empty( $faq_items ) ) {
	return;
}
?>

<section class="hat-faq" id="faq"<?php echo ( '' !== $faq_title_1 || '' !== $faq_title_2 ) ? ' aria-labelledby="hat-faq-title"' : ''; ?>>
	<?php if ( $faq_has_heading ) : ?>
	<div class="hat-container">
		<div class="hat-faq__header">
			<div class="hat-faq__header-left">
				<?php if ( '' !== $faq_eyebrow ) : ?>
				<p class="hat-faq__eyebrow">
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">(</span>
					<span class="hat-faq__eyebrow-word"><?php echo esc_html( $faq_eyebrow ); ?></span>
					<span class="hat-faq__eyebrow-bracket" aria-hidden="true">)</span>
				</p>
				<?php endif; ?>

				<?php if ( '' !== $faq_title_1 || '' !== $faq_title_2 ) : ?>
				<h2 class="hat-faq__title" id="hat-faq-title">
					<?php if ( '' !== $faq_title_1 ) : ?>
						<span class="hat-faq__title-line"><?php echo esc_html( $faq_title_1 ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $faq_title_2 ) : ?>
						<span class="hat-faq__title-line hat-faq__title-line--indent"><?php echo esc_html( $faq_title_2 ); ?></span>
					<?php endif; ?>
				</h2>
				<?php endif; ?>
			</div>

			<?php if ( count( $faq_items ) > 1 ) : ?>
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
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $faq_items ) ) : ?>
	<div class="hat-faq__slider" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Questions fréquentes', 'hughalroztatoo' ); ?>">
		<div class="hat-faq__track">
			<?php foreach ( $faq_items as $i => $item ) : ?>
			<?php
			$question = isset( $item['question'] ) ? (string) $item['question'] : '';
			$answer   = isset( $item['answer'] ) ? (string) $item['answer'] : '';
			?>
			<div
				class="hat-faq__card<?php echo 0 === $i ? ' is-active' : ''; ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr( ( $i + 1 ) . ' / ' . count( $faq_items ) ); ?>"
			>
				<?php if ( '' !== $question ) : ?>
					<h3 class="hat-faq__card-question"><?php echo esc_html( $question ); ?></h3>
				<?php endif; ?>
				<span class="hat-faq__card-icon" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/shapie.svg' ); ?>" alt="" loading="lazy" />
				</span>
				<?php if ( '' !== $answer ) : ?>
				<div class="hat-faq__card-answer-wrap">
					<p class="hat-faq__card-answer"><?php echo esc_html( $answer ); ?></p>
				</div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>
</section>
