<?php
/**
 * Front page: experience / conditions block
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="hat-experience hat-section" id="experience" aria-labelledby="hat-experience-title">
	<div class="hat-container">
		<div class="hat-experience__grid">
			<p class="hat-experience__eyebrow">
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">(</span>
				<span class="hat-experience__eyebrow-word"><?php esc_html_e( 'CONDITIONS', 'hughalroztatoo' ); ?></span>
				<span class="hat-experience__eyebrow-bracket" aria-hidden="true">)</span>
			</p>

			<div class="hat-experience__heading">
				<h2 class="hat-experience__title" id="hat-experience-title">
					<span class="hat-experience__title-main hat-experience__title-main--first-row">
						<span class="hat-experience__title-main-part hat-experience__title-main-part--with-start-blur">
							<span class="hat-experience__title-blur hat-experience__title-blur--start" aria-hidden="true"></span>
							<?php esc_html_e( 'L', 'hughalroztatoo' ); ?>
						</span><?php esc_html_e( "'EXPÉRIENCE", 'hughalroztatoo' ); ?>
						<span class="hat-experience__title-tag"><?php esc_html_e( '// P /', 'hughalroztatoo' ); ?></span>
					</span>
					<span class="hat-experience__title-main hat-experience__title-main--with-wire">
						<span class="hat-experience__title-gradient"><?php esc_html_e( 'AVANT', 'hughalroztatoo' ); ?></span>
						<img class="hat-experience__wire" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/winamp-icon.png' ); ?>" alt="" aria-hidden="true" width="35" height="114">
						<span class="hat-experience__title-main-part hat-experience__title-gradient--mirror"><?php esc_html_e( 'TOU', 'hughalroztatoo' ); ?></span>
						<span class="hat-experience__title-main-part hat-experience__title-main-part--with-end-blur hat-experience__title-main-part--white">
							<?php esc_html_e( 'T', 'hughalroztatoo' ); ?>
							<span class="hat-experience__title-blur" aria-hidden="true"></span>
						</span>
					</span>
				</h2>
				<p class="hat-experience__subtitle"><?php esc_html_e( 'Un cadre.  Une intention.', 'hughalroztatoo' ); ?></p>
			</div>

			<?php
			$conditions = array(
				array( 'title' => 'ACOMPTE 30%',           'lead' => 'Reservation confirmee',    'text' => 'Un acompte de 30% est requis pour valider votre rendez-vous. Le solde est regle le jour de la session.' ),
				array( 'title' => 'ANNULATION & REPORT',   'lead' => 'Preavis minimum',          'text' => "Tout report ou annulation doit etre communique au moins 48h a l'avance pour conserver votre acompte." ),
				array( 'title' => 'NO-SHOW',               'lead' => 'Acompte conserve',         'text' => "Toute absence sans annulation prealable entraine la perte de l'acompte. Le creneau est libere automatiquement." ),
				array( 'title' => 'PHOTOS REQUISES',       'lead' => 'Preparation obligatoire',  'text' => 'Des photos claires de la zone a tatouer sont necessaires avant la consultation afin de preparer le design.' ),
				array( 'title' => 'DURÉE & DÉPASSEMENT',  'lead' => 'Temps estime',             'text' => 'Les durees sont estimees. Si la session depasse le temps prevu, un ajustement est applique selon le tarif horaire.' ),
				array( 'title' => 'ÂGE & CONDITIONS',     'lead' => 'Verification necessaire',  'text' => "Une piece d'identite valide est obligatoire. Certaines conditions medicales peuvent necessiter un avis prealable." ),
			);

			foreach ( $conditions as $i => $cond ) :
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
						<span class="hat-experience__item-title"><?php echo esc_html( $cond['title'] ); ?></span>
						<img class="hat-experience__item-arrow" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/arrow-up-outline.svg' ); ?>" alt="" aria-hidden="true" width="35" height="35">
					</button>
					<div class="hat-experience__panel" id="hat-experience-panel-<?php echo $num; ?>">
						<div class="hat-experience__panel-inner">
							<p class="hat-experience__panel-lead"><?php echo esc_html( $cond['lead'] ); ?></p>
							<p class="hat-experience__panel-text"><?php echo esc_html( $cond['text'] ); ?></p>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
