<?php
/**
 * Front page: idea / CTA block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();
$idea_cta_url_raw = function_exists( 'get_field' ) ? get_field( 'home_idea_cta_url', $home_post_id ) : '';

$idea_eyebrow   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_eyebrow', $home_post_id ) : '';
$idea_title_1   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_title_line_1', $home_post_id ) : '';
$idea_title_2   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_title_line_2', $home_post_id ) : '';
$idea_text      = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_text', $home_post_id ) : '';
$idea_cta_label = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_cta_label', $home_post_id ) : '';
$idea_cta_url   = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['url'] ?? '' ) : (string) $idea_cta_url_raw;
$idea_cta_target = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['target'] ?? '' ) : '';
$idea_cta_title = is_array( $idea_cta_url_raw ) ? (string) ( $idea_cta_url_raw['title'] ?? '' ) : '';
$idea_strip_1   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_strip_1', $home_post_id ) : '';
$idea_strip_2   = function_exists( 'get_field' ) ? (string) get_field( 'home_idea_strip_2', $home_post_id ) : '';

$idea_eyebrow   = '' !== $idea_eyebrow ? $idea_eyebrow : 'RESERVATION';
$idea_title_1   = '' !== $idea_title_1 ? $idea_title_1 : "L'IDEE. L'ENCRE.";
$idea_title_2   = '' !== $idea_title_2 ? $idea_title_2 : 'VOUS.';
$idea_text      = '' !== $idea_text ? $idea_text : "Sur rendez-vous uniquement afin d'offrir une experience entierement personnalisee et dediee a chaque projet. Reservation en ligne disponible 24/7 pour organiser votre seance en toute simplicite, a l'heure qui vous convient.";
$idea_cta_label = '' !== $idea_cta_label ? $idea_cta_label : ( '' !== $idea_cta_title ? $idea_cta_title : 'RESERVER UNE SEANCE' );
$idea_cta_url   = '' !== $idea_cta_url ? $idea_cta_url : '#contact';
$idea_cta_target = '' !== $idea_cta_target ? $idea_cta_target : '_self';
$idea_strip_1   = '' !== $idea_strip_1 ? $idea_strip_1 : 'RDV//';
$idea_strip_2   = '' !== $idea_strip_2 ? $idea_strip_2 : '/ 20.2026 /';
?>

<section class="hat-idea hat-section" id="idea" aria-labelledby="hat-idea-title">
	<div class="hat-container hat-idea__inner">
		<p class="hat-idea__eyebrow">
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">(</span>
			<span class="hat-idea__eyebrow-word"><?php echo esc_html( $idea_eyebrow ); ?></span>
			<span class="hat-idea__eyebrow-bracket" aria-hidden="true">)</span>
		</p>

		<div class="hat-idea__title-row">
			<h2 class="hat-idea__title" id="hat-idea-title">
				<span class="hat-idea__title-line"><?php echo esc_html( $idea_title_1 ); ?></span>
				<span class="hat-idea__title-line"><?php echo esc_html( $idea_title_2 ); ?></span>
			</h2>

			<span class="hat-idea__ornament" aria-hidden="true">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/idea-element.svg' ); ?>" alt="" width="98" height="55">
			</span>
		</div>

		<div class="hat-idea__action-group">
			<p class="hat-idea__text">
				<?php echo esc_html( $idea_text ); ?>
			</p>

			<a class="hat-idea__cta" href="<?php echo esc_url( $idea_cta_url ); ?>" target="<?php echo esc_attr( $idea_cta_target ); ?>">
				<span class="hat-idea__cta-label"><?php echo esc_html( $idea_cta_label ); ?></span>
				<span class="hat-idea__cta-arrow" aria-hidden="true">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" width="35" height="35">
				</span>
			</a>
		</div>

		<div class="hat-idea__strip" aria-hidden="true">
			<span class="hat-idea__strip-item"><?php echo esc_html( $idea_strip_1 ); ?></span>
			<span class="hat-idea__strip-item"><?php echo esc_html( $idea_strip_2 ); ?></span>
		</div>
	</div>
</section>
