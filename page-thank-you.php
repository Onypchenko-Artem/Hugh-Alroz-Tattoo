<?php
/**
 * Template Name: Thank You
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$done_title   = hughalroztatoo_thank_you_field( 'booking_step8_title', hughalroztatoo_booking_t( 'thankYouTitle' ) );
$done_message = hughalroztatoo_thank_you_field( 'booking_step8_message', hughalroztatoo_booking_t( 'thankYouMessage' ) );
$done_close   = hughalroztatoo_booking_t( 'doneClose' );

$format_value = isset( $_GET['hat_format'] ) ? sanitize_text_field( wp_unslash( $_GET['hat_format'] ) ) : '—';
$date_value   = isset( $_GET['hat_date'] ) ? sanitize_text_field( wp_unslash( $_GET['hat_date'] ) ) : '—';
$slot_value   = isset( $_GET['hat_slot'] ) ? sanitize_text_field( wp_unslash( $_GET['hat_slot'] ) ) : '—';
$paid_value   = isset( $_GET['hat_paid'] ) ? sanitize_text_field( wp_unslash( $_GET['hat_paid'] ) ) : '—';

if ( '' === $done_close ) {
	$done_close = __( 'Fermer', 'hughalroztatoo' );
}

$close_url = home_url( '/' );
if ( function_exists( 'pll_home_url' ) && function_exists( 'pll_current_language' ) ) {
	$lang = pll_current_language( 'slug' );
	if ( is_string( $lang ) && '' !== $lang ) {
		$close_url = pll_home_url( $lang );
	}
}

get_header();
?>

<div class="hugh-ms-thank-you-page hat-container hat-section">
	<div class="hugh-ms-booking hugh-ms-booking--thank-you-static" role="region" aria-live="polite">
		<div class="hugh-ms__inner hugh-ms__inner--step-9">
			<div class="hugh-ms__panel hugh-ms__panel--done">
				<div class="hugh-ms__done-wrap">
					<div class="hugh-ms__done-icon" aria-hidden="true"></div>
					<h2 class="hugh-ms__title"><?php echo esc_html( $done_title ); ?></h2>
					<div class="hugh-ms__done-sheet">
						<div class="hugh-ms__done-row">
							<span class="hugh-ms__done-k"><?php echo esc_html( hughalroztatoo_booking_t( 'doneRowFormat' ) ); ?></span>
							<span class="hugh-ms__done-v"><?php echo esc_html( $format_value ); ?></span>
						</div>
						<div class="hugh-ms__done-row">
							<span class="hugh-ms__done-k"><?php echo esc_html( hughalroztatoo_booking_t( 'doneRowDate' ) ); ?></span>
							<span class="hugh-ms__done-v"><?php echo esc_html( $date_value ); ?></span>
						</div>
						<div class="hugh-ms__done-row">
							<span class="hugh-ms__done-k"><?php echo esc_html( hughalroztatoo_booking_t( 'doneRowSlot' ) ); ?></span>
							<span class="hugh-ms__done-v"><?php echo esc_html( $slot_value ); ?></span>
						</div>
						<div class="hugh-ms__done-row is-strong">
							<span class="hugh-ms__done-k"><?php echo esc_html( hughalroztatoo_booking_t( 'doneRowPaid' ) ); ?></span>
							<span class="hugh-ms__done-v"><?php echo esc_html( $paid_value ); ?></span>
						</div>
					</div>
					<p class="hugh-ms__done-note"><?php echo esc_html( $done_message ); ?></p>
					<a class="hugh-ms__done-close" href="<?php echo esc_url( $close_url ); ?>">
						<?php echo esc_html( $done_close ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
// Invisible wizard: Stripe success_url lands here; script finalizes payment then reloads with hat_* only.
?>
<div class="hugh-ms-stripe-bridge" aria-hidden="true"><?php echo do_shortcode( '[hugh_amelia_booking]' ); ?></div>

<?php
get_footer();
