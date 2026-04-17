<?php
/**
 * Template Name: Home
 * Description: Home page layout with hero, pricing, portfolio, experience, FAQ and idea sections.
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
