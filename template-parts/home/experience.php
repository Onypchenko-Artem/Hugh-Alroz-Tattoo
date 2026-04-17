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

$experience_eyebrow = '' !== $experience_eyebrow ? $experience_eyebrow : 'CONDITIONS';
$experience_title_1 = '' !== $experience_title_1 ? $experience_title_1 : "L'EXPERIENCE";
$experience_title_2 = '' !== $experience_title_2 ? $experience_title_2 : 'AVANT TOUT';
$experience_tag     = '' !== $experience_tag ? $experience_tag : '// P /';
$experience_sub     = '' !== $experience_sub ? $experience_sub : 'Un cadre. Une intention.';
$title_one_first    = substr( $experience_title_1, 0, 1 );
$title_one_rest     = substr( $experience_title_1, 1 );

if ( ! is_array( $conditions ) || empty( $conditions ) ) {
	$conditions = array(
		array( 'title' => 'ACOMPTE 30%',         'lead' => 'Reservation confirmee',   'text' => 'Un acompte de 30% est requis pour valider votre rendez-vous. Le solde est regle le jour de la session.' ),
		array( 'title' => 'ANNULATION & REPORT', 'lead' => 'Preavis minimum',         'text' => "Tout report ou annulation doit etre communique au moins 48h a l'avance pour conserver votre acompte." ),
		array( 'title' => 'NO-SHOW',             'lead' => 'Acompte conserve',        'text' => "Toute absence sans annulation prealable entraine la perte de l'acompte. Le creneau est libere automatiquement." ),
		array( 'title' => 'PHOTOS REQUISES',     'lead' => 'Preparation obligatoire', 'text' => 'Des photos claires de la zone a tatouer sont necessaires avant la consultation afin de preparer le design.' ),
		array( 'title' => 'DUREE & DEPASSEMENT', 'lead' => 'Temps estime',            'text' => 'Les durees sont estimees. Si la session depasse le temps prevu, un ajustement est applique selon le tarif horaire.' ),
		array( 'title' => 'AGE & CONDITIONS',    'lead' => 'Verification necessaire', 'text' => "Une piece d'identite valide est obligatoire. Certaines conditions medicales peuvent necessiter un avis prealable." ),
	);
}
?>

<section class="hat-experience hat-section" id="experience" aria-labelledby="hat-experience-title">
	<div class="hat-container">
		<div class="hat-experience__grid">
			<p class="hat-experience__eyebrow">
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-experience__eyebrow-word"><?php echo esc_html( $experience_eyebrow ); ?></span>
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">)</span>
			</p>

			<div class="hat-experience__heading">
				<h2 class="hat-experience__title" id="hat-experience-title">
					<span class="hat-experience__title-main hat-experience__title-main--first-row">
						<span class="hat-experience__title-main-part hat-experience__title-main-part--with-start-blur">
							<span class="hat-experience__title-blur hat-experience__title-blur--start" aria-hidden="true"></span>
							<?php echo esc_html( $title_one_first ); ?>
						</span><?php echo esc_html( $title_one_rest ); ?>
						<span class="hat-experience__title-tag"><?php echo esc_html( $experience_tag ); ?></span>
					</span>
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
				</h2>
				<p class="hat-experience__subtitle"><?php echo esc_html( $experience_sub ); ?></p>
			</div>

			<?php foreach ( $conditions as $i => $cond ) :
				$num = $i + 1;
			?>
			<span class="hat-experience__index" aria-hidden="true">x<?php echo $num; ?></span>
			<div class="hat-experience__row">
				<div class="hat-experience__accordion">
					<button
						class="hat-experience__summary"
						type="button"
						aria-expanded="false"
						aria-controls="hat-experience-panel-<?php echo $num; ?>"
					>
						<span class="hat-experience__item-title"><?php echo esc_html( isset( $cond['title'] ) ? (string) $cond['title'] : '' ); ?></span>
						<img class="hat-experience__item-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" aria-hidden="true" width="35" height="35">
					</button>
					<div class="hat-experience__panel" id="hat-experience-panel-<?php echo $num; ?>">
						<div class="hat-experience__panel-inner">
							<p class="hat-experience__panel-lead"><?php echo esc_html( isset( $cond['lead'] ) ? (string) $cond['lead'] : '' ); ?></p>
							<p class="hat-experience__panel-text"><?php echo esc_html( isset( $cond['text'] ) ? (string) $cond['text'] : '' ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
