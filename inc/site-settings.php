<?php
/**
 * Site settings powered by Advanced Custom Fields.
 *
 * @package Hugh_Alroz_Tattoo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the top-level Site settings page in ACF.
 */
function hughalroztatoo_register_site_settings_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Site settings', 'hughalroztatoo' ),
			'menu_title' => __( 'Site settings', 'hughalroztatoo' ),
			'menu_slug'  => 'site-settings',
			'capability' => 'manage_options',
			'redirect'   => false,
			'position'   => 59,
			'icon_url'   => 'dashicons-admin-generic',
		)
	);
}
add_action( 'acf/init', 'hughalroztatoo_register_site_settings_page' );

/**
 * Register fields for the Site settings page.
 */
function hughalroztatoo_register_site_settings_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_hat_site_settings',
			'title'                 => __( 'Site settings', 'hughalroztatoo' ),
			'fields'                => array(
				array(
					'key'       => 'field_hat_footer_tab',
					'label'     => __( 'Footer', 'hughalroztatoo' ),
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
					'endpoint'  => 0,
				),
				array(
					'key'           => 'field_hat_footer_contact_title',
					'label'         => __( 'Contact title', 'hughalroztatoo' ),
					'name'          => 'footer_contact_title',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_phone',
					'label'         => __( 'Phone', 'hughalroztatoo' ),
					'name'          => 'footer_phone',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_email',
					'label'         => __( 'Email', 'hughalroztatoo' ),
					'name'          => 'footer_email',
					'type'          => 'email',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_social_title',
					'label'         => __( 'Social title', 'hughalroztatoo' ),
					'name'          => 'footer_social_title',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_instagram_label',
					'label'         => __( 'Instagram label', 'hughalroztatoo' ),
					'name'          => 'footer_instagram_label',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_instagram_url',
					'label'         => __( 'Instagram URL', 'hughalroztatoo' ),
					'name'          => 'footer_instagram_url',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_facebook_label',
					'label'         => __( 'Facebook label', 'hughalroztatoo' ),
					'name'          => 'footer_facebook_label',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_facebook_url',
					'label'         => __( 'Facebook URL', 'hughalroztatoo' ),
					'name'          => 'footer_facebook_url',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_to_top_label',
					'label'         => __( 'Back to top label', 'hughalroztatoo' ),
					'name'          => 'footer_to_top_label',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_copyright_name',
					'label'         => __( 'Copyright name', 'hughalroztatoo' ),
					'name'          => 'footer_copyright_name',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'           => 'field_hat_footer_copyright_text',
					'label'         => __( 'Copyright text', 'hughalroztatoo' ),
					'name'          => 'footer_copyright_text',
					'type'          => 'text',
					'default_value' => '',
					'wrapper'       => array(
						'width' => '50',
					),
				),
				array(
					'key'          => 'field_hat_footer_legal_links',
					'label'        => __( 'Legal links', 'hughalroztatoo' ),
					'name'         => 'footer_legal_links',
					'type'         => 'repeater',
					'instructions' => __( 'Used only when no menu is assigned to “Footer Menu” in Appearance → Menus.', 'hughalroztatoo' ),
					'layout'       => 'table',
					'button_label' => __( 'Add link', 'hughalroztatoo' ),
					'sub_fields'   => array(
						array(
							'key'     => 'field_hat_footer_legal_link_label',
							'label'   => __( 'Label', 'hughalroztatoo' ),
							'name'    => 'label',
							'type'    => 'text',
							'wrapper' => array(
								'width' => '50',
							),
						),
						array(
							'key'     => 'field_hat_footer_legal_link_url',
							'label'   => __( 'URL', 'hughalroztatoo' ),
							'name'    => 'url',
							'type'    => 'text',
							'wrapper' => array(
								'width' => '50',
							),
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'site-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'hughalroztatoo_register_site_settings_fields' );

/**
 * Read an ACF option field with a default fallback.
 *
 * @param string $field_name Field name.
 * @param mixed  $default    Default value.
 * @return mixed
 */
function hughalroztatoo_get_site_option( $field_name, $default = '' ) {
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $field_name, 'option' );

		if ( null !== $value && false !== $value && '' !== $value ) {
			return $value;
		}
	}

	return $default;
}
