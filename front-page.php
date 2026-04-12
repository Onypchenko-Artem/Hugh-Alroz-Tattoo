<?php
/**
 * Front page template
 *
 * @package Hugh_Alroz_Tattoo
 */

get_header();

get_template_part( 'template-parts/home/hero-banner' );
get_template_part( 'template-parts/home/pricing' );
get_template_part( 'template-parts/home/portfolio' );
get_template_part( 'template-parts/home/experience' );
get_template_part( 'template-parts/home/faq' );
get_template_part( 'template-parts/home/idea' );

get_footer();
