<?php
/**
 * Customizer settings: Appearance → Customize → Bookstore settings.
 *
 * @package Kitab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for theme mods.
 */
function kitab_defaults() {
	return array(
		'announcement'   => __( 'Free delivery all over Pakistan on orders above Rs 3,000 · Cash on Delivery available', 'kitab' ),
		'hero_eyebrow'   => __( 'Pakistan’s friendly online bookshop', 'kitab' ),
		'hero_title'     => __( 'Find your next favourite book', 'kitab' ),
		'hero_urdu'      => 'کتاب بہترین دوست ہے',
		'hero_text'      => __( 'Urdu classics, Pakistani writers, Islamic books and international bestsellers — delivered to your door from Karachi to Khyber.', 'kitab' ),
		'hero_button'    => __( 'Browse all books', 'kitab' ),
		'phone'          => '0300-0000000',
		'whatsapp'       => '923000000000',
		'email'          => 'orders@example.com',
		'address'        => __( 'Lahore, Punjab, Pakistan', 'kitab' ),
		'free_shipping'  => __( 'Free delivery over Rs 3,000', 'kitab' ),
		'delivery_time'  => __( '2–5 working days nationwide', 'kitab' ),
	);
}

/**
 * Get a theme mod with a sensible default.
 */
function kitab_mod( $key ) {
	$defaults = kitab_defaults();
	return get_theme_mod( 'kitab_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function kitab_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'kitab_store',
		array(
			'title'    => __( 'Bookstore settings', 'kitab' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'announcement'  => array( __( 'Announcement bar text', 'kitab' ), 'text' ),
		'hero_eyebrow'  => array( __( 'Homepage: small heading', 'kitab' ), 'text' ),
		'hero_title'    => array( __( 'Homepage: main heading', 'kitab' ), 'text' ),
		'hero_urdu'     => array( __( 'Homepage: Urdu tagline', 'kitab' ), 'text' ),
		'hero_text'     => array( __( 'Homepage: intro text', 'kitab' ), 'textarea' ),
		'hero_button'   => array( __( 'Homepage: button label', 'kitab' ), 'text' ),
		'free_shipping' => array( __( 'Free delivery message', 'kitab' ), 'text' ),
		'delivery_time' => array( __( 'Delivery time message', 'kitab' ), 'text' ),
		'phone'         => array( __( 'Phone number', 'kitab' ), 'text' ),
		'whatsapp'      => array( __( 'WhatsApp number (international format, digits only, e.g. 923001234567)', 'kitab' ), 'text' ),
		'email'         => array( __( 'Contact email', 'kitab' ), 'email' ),
		'address'       => array( __( 'Address', 'kitab' ), 'text' ),
	);

	$defaults = kitab_defaults();
	foreach ( $fields as $key => $field ) {
		$wp_customize->add_setting(
			'kitab_' . $key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $field[1] ? 'sanitize_textarea_field' : ( 'email' === $field[1] ? 'sanitize_email' : 'sanitize_text_field' ),
			)
		);
		$wp_customize->add_control(
			'kitab_' . $key,
			array(
				'label'   => $field[0],
				'section' => 'kitab_store',
				'type'    => $field[1],
			)
		);
	}
}
add_action( 'customize_register', 'kitab_customize_register' );
