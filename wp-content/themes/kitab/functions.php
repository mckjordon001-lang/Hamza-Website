<?php
/**
 * Kitab theme functions.
 *
 * @package Kitab
 */

defined( 'ABSPATH' ) || exit;

define( 'KITAB_VERSION', '1.0.0' );

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';

if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/**
 * Theme setup.
 */
function kitab_setup() {
	load_theme_textdomain( 'kitab', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 400,
			'single_image_width'    => 600,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'kitab' ),
			'footer'  => __( 'Footer menu', 'kitab' ),
		)
	);
}
add_action( 'after_setup_theme', 'kitab_setup' );

/**
 * Widget areas.
 */
function kitab_widgets_init() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$common,
			array(
				'name'        => __( 'Shop sidebar', 'kitab' ),
				'id'          => 'sidebar-shop',
				'description' => __( 'Filters shown next to the book listings.', 'kitab' ),
			)
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array_merge(
				$common,
				array(
					/* translators: %d: footer column number */
					'name' => sprintf( __( 'Footer column %d', 'kitab' ), $i ),
					'id'   => 'footer-' . $i,
				)
			)
		);
	}
}
add_action( 'widgets_init', 'kitab_widgets_init' );

/**
 * Styles and scripts.
 */
function kitab_scripts() {
	wp_enqueue_style(
		'kitab-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=Noto+Nastaliq+Urdu:wght@400;600&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	);
	wp_enqueue_style( 'kitab-style', get_stylesheet_uri(), array( 'kitab-fonts' ), KITAB_VERSION );
	wp_enqueue_script( 'kitab-main', get_template_directory_uri() . '/assets/js/main.js', array(), KITAB_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'kitab_scripts' );

/**
 * Preconnect to Google Fonts.
 */
function kitab_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'kitab_resource_hints', 10, 2 );

/**
 * Body classes.
 */
function kitab_body_classes( $classes ) {
	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product_taxonomy() ) && is_active_sidebar( 'sidebar-shop' ) ) {
		$classes[] = 'has-shop-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'kitab_body_classes' );
