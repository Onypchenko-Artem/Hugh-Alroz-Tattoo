<?php
/**
 * Front page: experience / conditions block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_post_id = get_queried_object_id();

$experience_eyebrow = function_exists( 'get_field' ) ? (string) get_field( 'home_experience_eyebrow', $home_post_id ) : '';
$experience_title_1 = function_exists( 'get_field' ) ? (string) get_field( 'home_experience_title_line_1', $home_post_id ) : '';
$experience_title_2 = function_exists( 'get_field' ) ? (string) get_field( 'home_experience_title_line_2', $home_post_id ) : '';
$experience_tag     = function_exists( 'get_field' ) ? (string) get_field( 'home_experience_title_tag', $home_post_id ) : '';
$experience_sub     = function_exists( 'get_field' ) ? (string) get_field( 'home_experience_subtitle', $home_post_id ) : '';
$conditions         = function_exists( 'get_field' ) ? get_field( 'home_experience_conditions', $home_post_id ) : array();

$title_one_first    = substr( $experience_title_1, 0, 1 );
$title_one_rest     = substr( $experience_title_1, 1 );
$conditions         = is_array( $conditions ) ? array_values(
	array_filter(
		$conditions,
		static function ( $condition ) {
			return is_array( $condition ) && ( ! empty( $condition['title'] ) || ! empty( $condition['lead'] ) || ! empty( $condition['text'] ) );
		}
	)
) : array();

$experience_has_heading = '' !== $experience_eyebrow || '' !== $experience_title_1 || '' !== $experience_title_2 || '' !== $experience_tag || '' !== $experience_sub;

if ( ! $experience_has_heading && empty( $conditions ) ) {
	return;
}
?>

<section class="hat-experience hat-section" id="experience"<?php echo ( '' !== $experience_title_1 || '' !== $experience_title_2 ) ? ' aria-labelledby="hat-experience-title"' : ''; ?>>
	<div class="hat-container">
		<div class="hat-experience__grid">
			<?php if ( '' !== $experience_eyebrow ) : ?>
			<p class="hat-experience__eyebrow">
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-experience__eyebrow-word"><?php echo esc_html( $experience_eyebrow ); ?></span>
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">)</span>
			</p>
			<?php endif; ?>

			<?php if ( '' !== $experience_title_1 || '' !== $experience_title_2 || '' !== $experience_sub ) : ?>
			<div class="hat-experience__heading">
				<?php if ( '' !== $experience_title_1 || '' !== $experience_title_2 ) : ?>
				<h2 class="hat-experience__title" id="hat-experience-title">
					<?php if ( '' !== $experience_title_1 ) : ?>
					<span class="hat-experience__title-main hat-experience__title-main--first-row">
						<span class="hat-experience__title-first-line-word">
							<span class="hat-experience__title-main-part hat-experience__title-main-part--with-start-blur">
								<span class="hat-experience__title-blur hat-experience__title-blur--start" aria-hidden="true"></span>
								<?php echo esc_html( $title_one_first ); ?>
							</span><?php echo esc_html( $title_one_rest ); ?>
						</span>
						<?php if ( '' !== $experience_tag ) : ?>
							<span class="hat-experience__title-tag"><?php echo esc_html( $experience_tag ); ?></span>
						<?php endif; ?>
					</span>
					<?php endif; ?>
					<?php if ( '' !== $experience_title_2 ) : ?>
					<span class="hat-experience__title-main hat-experience__title-main--with-wire">
						<?php
						$line_two_parts = explode( ' ', $experience_title_2, 2 );
						$line_two_first = $line_two_parts[0];
						$line_two_rest  = isset( $line_two_parts[1] ) ? $line_two_parts[1] : '';
						$line_two_tail  = '' !== $line_two_rest ? substr( $line_two_rest, -1 ) : '';
						$line_two_body  = '' !== $line_two_rest ? substr( $line_two_rest, 0, -1 ) : '';
						?>
						<span class="hat-experience__title-gradient"><?php echo esc_html( $line_two_first ); ?></span>
						<img class="hat-experience__wire" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/winamp-icon.png' ); ?>" alt="" aria-hidden="true" width="35" height="114">
						<span class="hat-experience__title-main-part hat-experience__title-gradient--mirror"><?php echo esc_html( $line_two_body ); ?></span>
						<span class="hat-experience__title-main-part hat-experience__title-main-part--with-end-blur hat-experience__title-main-part--white">
							<?php echo esc_html( $line_two_tail ); ?>
							<span class="hat-experience__title-blur" aria-hidden="true"></span>
						</span>
					</span>
					<?php endif; ?>
				</h2>
				<?php endif; ?>
				<?php if ( '' !== $experience_sub ) : ?>
					<p class="hat-experience__subtitle"><?php echo esc_html( $experience_sub ); ?></p>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php foreach ( $conditions as $i => $cond ) :
				$num = $i + 1;
				$condition_title = isset( $cond['title'] ) ? (string) $cond['title'] : '';
				$condition_lead  = isset( $cond['lead'] ) ? (string) $cond['lead'] : '';
				$condition_text  = isset( $cond['text'] ) ? (string) $cond['text'] : '';
				$condition_has_panel = '' !== $condition_lead || '' !== $condition_text;
			?>
			<span class="hat-experience__index" aria-hidden="true">x<?php echo $num; ?></span>
			<div class="hat-experience__row">
				<div class="hat-experience__accordion">
					<button
						class="hat-experience__summary"
						type="button"
						aria-expanded="false"
						<?php echo $condition_has_panel ? 'aria-controls="hat-experience-panel-' . esc_attr( (string) $num ) . '"' : ''; ?>
					>
						<?php if ( '' !== $condition_title ) : ?>
							<span class="hat-experience__item-title"><?php echo esc_html( $condition_title ); ?></span>
						<?php endif; ?>
						<img class="hat-experience__item-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" aria-hidden="true" width="35" height="35">
					</button>
					<?php if ( $condition_has_panel ) : ?>
					<div class="hat-experience__panel" id="hat-experience-panel-<?php echo $num; ?>">
						<div class="hat-experience__panel-inner">
							<?php if ( '' !== $condition_lead ) : ?>
								<p class="hat-experience__panel-lead"><?php echo esc_html( $condition_lead ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $condition_text ) : ?>
								<p class="hat-experience__panel-text"><?php echo esc_html( $condition_text ); ?></p>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
