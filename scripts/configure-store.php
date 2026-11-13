<?php
/**
 * Configures WooCommerce for a Pakistani bookstore: PKR, Pakistan-only selling,
 * Cash on Delivery + bank/mobile-wallet transfer, nationwide shipping,
 * content pages, menus and shop widgets.
 *
 * Usage: wp eval-file scripts/configure-store.php
 * Safe to run more than once.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WooCommerce' ) ) {
	fwrite( STDERR, "Run with WP-CLI on a site where WooCommerce is active.\n" );
	exit( 1 );
}

$free_shipping_min = getenv( 'FREE_SHIPPING_MIN' ) ?: '3000';
$flat_rate         = getenv( 'FLAT_RATE' ) ?: '250';

/* -------------------------------------------------------------------------
 * General store settings.
 * ---------------------------------------------------------------------- */
$options = array(
	'timezone_string'                         => 'Asia/Karachi',
	'date_format'                             => 'j F Y',
	'time_format'                             => 'g:i a',
	'start_of_week'                           => '1',
	'woocommerce_store_address'               => 'Main Boulevard',
	'woocommerce_store_city'                  => 'Lahore',
	'woocommerce_default_country'             => 'PK:PB',
	'woocommerce_store_postcode'              => '54000',
	'woocommerce_currency'                    => 'PKR',
	'woocommerce_currency_pos'                => 'left_space',
	'woocommerce_price_thousand_sep'          => ',',
	'woocommerce_price_decimal_sep'           => '.',
	'woocommerce_price_num_decimals'          => '0',
	'woocommerce_allowed_countries'           => 'specific',
	'woocommerce_specific_allowed_countries'  => array( 'PK' ),
	'woocommerce_ship_to_countries'           => 'specific',
	'woocommerce_specific_ship_to_countries'  => array( 'PK' ),
	'woocommerce_default_customer_address'    => 'base',
	'woocommerce_calc_taxes'                  => 'no',
	'woocommerce_weight_unit'                 => 'kg',
	'woocommerce_dimension_unit'              => 'cm',
	'woocommerce_enable_guest_checkout'       => 'yes',
	'woocommerce_enable_checkout_login_reminder' => 'yes',
	'woocommerce_enable_signup_and_login_from_checkout' => 'yes',
	'woocommerce_enable_myaccount_registration' => 'yes',
	'woocommerce_checkout_phone_field'        => 'required',
	'woocommerce_checkout_company_field'      => 'hidden',
	'woocommerce_checkout_address_2_field'    => 'optional',
	'woocommerce_enable_reviews'              => 'yes',
	'woocommerce_review_rating_verification_label' => 'yes',
	'woocommerce_manage_stock'                => 'yes',
	'woocommerce_notify_low_stock_amount'     => '3',
	'woocommerce_hide_out_of_stock_items'     => 'no',
	'woocommerce_shop_page_display'           => '',
	'woocommerce_default_catalog_orderby'     => 'popularity',
	'woocommerce_coming_soon'                 => 'no',
	'woocommerce_store_pages_only'            => 'no',
	'woocommerce_task_list_hidden'            => 'yes',
	'woocommerce_onboarding_profile'          => array( 'skipped' => true ),
	'woocommerce_catalog_columns'             => 4,
	'woocommerce_catalog_rows'                => 3,
);
foreach ( $options as $key => $value ) {
	update_option( $key, $value );
}
WP_CLI::log( 'Store settings: PKR, Pakistan only, Asia/Karachi.' );

/* -------------------------------------------------------------------------
 * Payment methods.
 * ---------------------------------------------------------------------- */
update_option(
	'woocommerce_cod_settings',
	array(
		'enabled'            => 'yes',
		'title'              => 'Cash on Delivery',
		'description'        => 'Pay in cash when your books are delivered to your doorstep.',
		'instructions'       => 'Please keep the exact amount ready. Our rider will call you before delivery.',
		'enable_for_methods' => array(),
		'enable_for_virtual' => 'yes',
	)
);
update_option(
	'woocommerce_bacs_settings',
	array(
		'enabled'      => 'yes',
		'title'        => 'Bank Transfer / JazzCash / Easypaisa',
		'description'  => 'Transfer the order total to one of the accounts shown after checkout, then WhatsApp us the screenshot with your order number.',
		'instructions' => 'Send the payment screenshot with your order number on WhatsApp. Your order ships once payment is confirmed.',
	)
);
if ( ! get_option( 'woocommerce_bacs_accounts' ) ) {
	update_option(
		'woocommerce_bacs_accounts',
		array(
			array(
				'account_name'   => 'YOUR NAME HERE',
				'account_number' => 'Bank account number',
				'bank_name'      => 'Your bank (e.g. Meezan / HBL / UBL)',
				'sort_code'      => '',
				'iban'           => 'PK00XXXX0000000000000000',
				'bic'            => '',
			),
			array(
				'account_name'   => 'YOUR NAME HERE',
				'account_number' => '03XX XXXXXXX',
				'bank_name'      => 'JazzCash / Easypaisa',
				'sort_code'      => '',
				'iban'           => '',
				'bic'            => '',
			),
		)
	);
}
update_option( 'woocommerce_cheque_settings', array( 'enabled' => 'no' ) );
update_option( 'woocommerce_gateway_order', array( 'cod' => 0, 'bacs' => 1, 'cheque' => 2 ) );
WP_CLI::log( 'Payments: Cash on Delivery + Bank/JazzCash/Easypaisa transfer.' );

/* -------------------------------------------------------------------------
 * Shipping: one nationwide zone.
 * ---------------------------------------------------------------------- */
$zone = null;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Pakistan' === $z['zone_name'] ) {
		$zone = new WC_Shipping_Zone( $z['id'] );
		break;
	}
}
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Pakistan' );
	$zone->set_zone_order( 0 );
	$zone->add_location( 'PK', 'country' );
	$zone->save();

	$flat_id = $zone->add_shipping_method( 'flat_rate' );
	update_option(
		'woocommerce_flat_rate_' . $flat_id . '_settings',
		array(
			'title'      => 'Standard delivery (2–5 working days)',
			'tax_status' => 'none',
			'cost'       => $flat_rate,
		)
	);

	$free_id = $zone->add_shipping_method( 'free_shipping' );
	update_option(
		'woocommerce_free_shipping_' . $free_id . '_settings',
		array(
			'title'                => 'Free delivery',
			'requires'             => 'min_amount',
			'min_amount'           => $free_shipping_min,
			'ignore_discounts'     => 'no',
		)
	);
	WP_CLI::log( "Shipping: Pakistan zone — Rs $flat_rate flat, free over Rs $free_shipping_min." );
}
// Hide paid rates when free shipping is available.
update_option( 'woocommerce_shipping_hide_rates_when_free', 'yes' );

/* -------------------------------------------------------------------------
 * Pages.
 * ---------------------------------------------------------------------- */
WC_Install::create_pages();

// Classic cart & checkout: best compatibility with Pakistani payment/courier plugins.
foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]' ) as $page => $shortcode ) {
	$page_id = wc_get_page_id( $page );
	if ( $page_id > 0 ) {
		wp_update_post( array( 'ID' => $page_id, 'post_content' => '<!-- wp:shortcode -->' . $shortcode . '<!-- /wp:shortcode -->' ) );
	}
}

/**
 * Create a page if one with the slug does not exist. Returns the ID.
 */
function kitab_setup_page( $slug, $title, $content = '' ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	return wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

$p = function ( $text ) {
	return "<!-- wp:paragraph -->\n<p>$text</p>\n<!-- /wp:paragraph -->\n";
};
$h = function ( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">$text</h2>\n<!-- /wp:heading -->\n";
};

$home_id  = kitab_setup_page( 'home', 'Home' );
$blog_id  = kitab_setup_page( 'blog', 'Blog' );
$about_id = kitab_setup_page(
	'about',
	'About us',
	$p( 'We are a small team of book lovers on a mission to make great books easy to find and affordable for readers all over Pakistan.' ) .
	$p( 'From timeless Urdu classics and poetry to Pakistani writers in English, Islamic books, self-help and children’s stories — every book is checked before it is packed and shipped to your door.' ) .
	$p( 'Questions or special requests? Message us on WhatsApp — if a book is not on the website, we will try our best to find it for you.' )
);
$contact_id = kitab_setup_page(
	'contact',
	'Contact us',
	$p( '<strong>WhatsApp / Phone:</strong> 0300-0000000' ) .
	$p( '<strong>Email:</strong> orders@example.com' ) .
	$p( '<strong>Address:</strong> Lahore, Punjab, Pakistan' ) .
	$p( '<strong>Hours:</strong> Monday to Saturday, 10am – 7pm' )
);
$shipping_id = kitab_setup_page(
	'shipping-delivery',
	'Shipping & delivery',
	$h( 'Where do you deliver?' ) .
	$p( 'We deliver to every city and town in Pakistan through trusted courier partners.' ) .
	$h( 'How much does delivery cost?' ) .
	$p( "Standard delivery is Rs $flat_rate per order. Delivery is <strong>FREE</strong> on orders of Rs " . number_format( (float) $free_shipping_min ) . ' or more.' ) .
	$h( 'How long does it take?' ) .
	$p( 'Major cities (Lahore, Karachi, Islamabad, Rawalpindi, Faisalabad, Multan, Peshawar): 2–3 working days. Other areas: 3–5 working days.' ) .
	$h( 'Can I track my order?' ) .
	$p( 'Yes — we send your tracking number by SMS/WhatsApp as soon as your parcel is booked.' )
);
$returns_id = kitab_setup_page(
	'returns-refunds',
	'Returns & refunds',
	$p( 'If you receive a damaged, defective or wrong book, let us know on WhatsApp within <strong>7 days</strong> of delivery with a photo of the book and the parcel.' ) .
	$p( 'We will send a replacement free of charge or issue a refund to your bank account, JazzCash or Easypaisa within 5 working days of receiving the returned item.' ) .
	$p( 'Books must be returned in the condition they were received. Change-of-mind returns are accepted for unopened books only; the customer pays return delivery in that case.' )
);
$faq_id = kitab_setup_page(
	'faq',
	'FAQ',
	$h( 'Do you offer Cash on Delivery?' ) .
	$p( 'Yes, Cash on Delivery is available all over Pakistan.' ) .
	$h( 'Are your books original?' ) .
	$p( 'We source our books from publishers and authorised distributors.' ) .
	$h( 'Can I order on WhatsApp?' ) .
	$p( 'Yes! Tap “Order on WhatsApp” on any book page and send us the message.' ) .
	$h( 'Can I cancel my order?' ) .
	$p( 'You can cancel any time before the order is shipped — just message us with your order number.' )
);
$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home_id );
update_option( 'page_for_posts', $blog_id );
if ( $returns_id ) {
	update_option( 'woocommerce_terms_page_id', $returns_id );
}
WP_CLI::log( 'Pages: Home, About, Contact, Shipping, Returns, FAQ, Blog.' );

/* -------------------------------------------------------------------------
 * Menus.
 * ---------------------------------------------------------------------- */
function kitab_setup_menu( $name, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return $menu->term_id;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $items as $item ) {
		wp_update_nav_menu_item( $menu_id, 0, array_merge( array( 'menu-item-status' => 'publish' ), $item ) );
	}
	return $menu_id;
}
$page_item = static function ( $id ) {
	return array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $id );
};
$cat_item = static function ( $slug, $label ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	return $term ? array( 'menu-item-type' => 'taxonomy', 'menu-item-object' => 'product_cat', 'menu-item-object-id' => $term->term_id, 'menu-item-title' => $label ) : null;
};

$primary = array_filter(
	array(
		array( 'menu-item-title' => 'Home', 'menu-item-url' => home_url( '/' ), 'menu-item-type' => 'custom' ),
		array_merge( $page_item( wc_get_page_id( 'shop' ) ), array( 'menu-item-title' => 'All books' ) ),
		$cat_item( 'urdu-novels', 'Urdu Novels' ),
		$cat_item( 'urdu-poetry', 'Poetry' ),
		$cat_item( 'pakistani-writers-english', 'Pakistani Writers' ),
		$cat_item( 'islamic-books', 'Islamic' ),
		$cat_item( 'self-help-business', 'Self-Help' ),
		$cat_item( 'childrens-books', 'Kids' ),
		$page_item( $contact_id ),
	)
);
$footer = array_filter(
	array(
		$page_item( $shipping_id ),
		$page_item( $returns_id ),
		$page_item( $faq_id ),
		$page_item( $about_id ),
		$page_item( $contact_id ),
		array_merge( $page_item( wc_get_page_id( 'myaccount' ) ), array( 'menu-item-title' => 'Track / my orders' ) ),
		$privacy_id ? $page_item( $privacy_id ) : null,
	)
);

$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = kitab_setup_menu( 'Primary', $primary );
$locations['footer']  = kitab_setup_menu( 'Footer', $footer );
set_theme_mod( 'nav_menu_locations', $locations );
WP_CLI::log( 'Menus: Primary and Footer.' );

/* -------------------------------------------------------------------------
 * Shop sidebar widgets (classic widgets).
 * ---------------------------------------------------------------------- */
$sidebars = get_option( 'sidebars_widgets', array() );
if ( empty( $sidebars['sidebar-shop'] ) ) {
	update_option( 'widget_woocommerce_product_categories', array( 1 => array( 'title' => 'Categories', 'orderby' => 'order', 'count' => 1, 'hierarchical' => 1, 'dropdown' => 0, 'show_children_only' => 0, 'hide_empty' => 1 ), '_multiwidget' => 1 ) );
	update_option( 'widget_woocommerce_price_filter', array( 1 => array( 'title' => 'Filter by price' ), '_multiwidget' => 1 ) );
	update_option( 'widget_woocommerce_top_rated_products', array( 1 => array( 'title' => 'Top rated', 'number' => 3 ), '_multiwidget' => 1 ) );
	$sidebars['sidebar-shop'] = array( 'woocommerce_product_categories-1', 'woocommerce_price_filter-1' );
	update_option( 'sidebars_widgets', $sidebars );
	WP_CLI::log( 'Widgets: shop sidebar categories + price filter.' );
}

WP_CLI::success( 'Store configured.' );
