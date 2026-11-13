<?php
/**
 * WooCommerce integration, tuned for a Pakistani audience.
 *
 * @package Kitab
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Currency: show "Rs" instead of the ₨ glyph, which many fonts render poorly.
 * ---------------------------------------------------------------------- */
add_filter(
	'woocommerce_currency_symbol',
	static function ( $symbol, $currency ) {
		return 'PKR' === $currency ? 'Rs' : $symbol;
	},
	10,
	2
);

/**
 * Order total above which delivery is free. Keep in sync with the
 * "Free shipping" method in WooCommerce → Settings → Shipping.
 */
function kitab_free_shipping_threshold() {
	return (float) apply_filters( 'kitab_free_shipping_threshold', 3000 );
}

/* -------------------------------------------------------------------------
 * Header cart count stays live via AJAX fragments.
 * (WooCommerce 7.8+ no longer loads the fragments script everywhere.)
 * ---------------------------------------------------------------------- */
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
);

add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( $fragments ) {
		ob_start();
		kitab_cart_link();
		$fragments['a.header-cart'] = ob_get_clean();
		return $fragments;
	}
);

/* -------------------------------------------------------------------------
 * Catalogue layout.
 * ---------------------------------------------------------------------- */
add_filter(
	'loop_shop_per_page',
	static function () {
		return 12;
	}
);

add_filter(
	'woocommerce_output_related_products_args',
	static function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

add_filter(
	'woocommerce_breadcrumb_defaults',
	static function ( $defaults ) {
		$defaults['delimiter'] = '<span class="sep" aria-hidden="true">/</span>';
		return $defaults;
	}
);

/* -------------------------------------------------------------------------
 * Generated book covers for products that have no image yet.
 * ---------------------------------------------------------------------- */

/**
 * Build a typographic cover for a product.
 *
 * @param WC_Product $product Product.
 * @param string     $extra_class Additional class.
 */
function kitab_book_cover_html( $product, $extra_class = '' ) {
	$title = $product->get_name();
	$hue   = absint( crc32( $title ) ) % 360;
	$urdu  = $product->get_meta( '_kitab_title_urdu' );

	$author = '';
	if ( taxonomy_exists( 'book_author' ) ) {
		$terms = get_the_terms( $product->get_id(), 'book_author' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$author = implode( ', ', wp_list_pluck( $terms, 'name' ) );
		}
	}

	$html  = '<div class="book-cover ' . esc_attr( $extra_class ) . '" style="--cover-hue:' . esc_attr( $hue ) . '" role="img" aria-label="' . esc_attr( $title ) . '">';
	$html .= '<span class="book-cover__band"></span>';
	if ( $urdu ) {
		$html .= '<span class="book-cover__urdu" lang="ur" dir="rtl">' . esc_html( $urdu ) . '</span>';
	}
	$html .= '<span class="book-cover__title">' . esc_html( $title ) . '</span>';
	if ( $author ) {
		$html .= '<span class="book-cover__author">' . esc_html( $author ) . '</span>';
	}
	$html .= '</div>';
	return $html;
}

add_filter(
	'woocommerce_product_get_image',
	static function ( $image, $product, $size, $attr, $placeholder ) {
		if ( ! $placeholder || ! $product instanceof WC_Product ) {
			return $image;
		}
		$has_image = $product->get_image_id() || ( $product->get_parent_id() && get_post_thumbnail_id( $product->get_parent_id() ) );
		return $has_image ? $image : kitab_book_cover_html( $product );
	},
	10,
	5
);

/**
 * Single product: use the generated cover when there is no gallery.
 */
function kitab_single_product_images() {
	global $product;
	if ( $product && ! $product->get_image_id() ) {
		echo '<div class="woocommerce-product-gallery images kitab-gallery">' . kitab_book_cover_html( $product, 'book-cover--large' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	woocommerce_show_product_images();
}
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
add_action( 'woocommerce_before_single_product_summary', 'kitab_single_product_images', 20 );

/* -------------------------------------------------------------------------
 * Single product: trust badges and "Order on WhatsApp".
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_single_product_summary',
	static function () {
		global $product;

		$number = preg_replace( '/\D+/', '', (string) kitab_mod( 'whatsapp' ) );
		if ( $number && $product->is_purchasable() ) {
			/* translators: 1: product name, 2: product URL */
			$message = sprintf( __( 'Assalam o Alaikum! I would like to order: %1$s — %2$s', 'kitab' ), $product->get_name(), get_permalink( $product->get_id() ) );
			printf(
				'<a class="button whatsapp-order" href="%1$s" target="_blank" rel="noopener">%2$s %3$s</a>',
				esc_url( 'https://wa.me/' . $number . '?text=' . rawurlencode( $message ) ),
				kitab_icon( 'whatsapp' ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html__( 'Order on WhatsApp', 'kitab' )
			);
		}
		?>
		<ul class="product-assurances">
			<li><?php echo kitab_icon( 'cash' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Cash on Delivery available', 'kitab' ); ?></li>
			<li><?php echo kitab_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( kitab_mod( 'delivery_time' ) ); ?></li>
			<li><?php echo kitab_icon( 'return' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( '7-day easy returns on damaged or wrong items', 'kitab' ); ?></li>
		</ul>
		<?php
	},
	35
);

/* -------------------------------------------------------------------------
 * Cart: free delivery progress message.
 * ---------------------------------------------------------------------- */
function kitab_free_shipping_notice() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$threshold = kitab_free_shipping_threshold();
	$subtotal  = (float) WC()->cart->get_displayed_subtotal();
	if ( $threshold <= 0 ) {
		return;
	}
	$pct = min( 100, round( $subtotal / $threshold * 100 ) );
	echo '<div class="free-shipping-meter">';
	if ( $subtotal >= $threshold ) {
		echo '<p>' . esc_html__( 'You’ve unlocked FREE delivery!', 'kitab' ) . '</p>';
	} else {
		/* translators: %s: amount remaining */
		echo '<p>' . wp_kses_post( sprintf( __( 'Add %s more for FREE delivery.', 'kitab' ), wc_price( $threshold - $subtotal ) ) ) . '</p>';
	}
	echo '<div class="free-shipping-meter__bar"><span style="width:' . esc_attr( $pct ) . '%"></span></div></div>';
}
add_action( 'woocommerce_before_cart', 'kitab_free_shipping_notice' );
add_action( 'woocommerce_before_checkout_form', 'kitab_free_shipping_notice', 5 );

/* -------------------------------------------------------------------------
 * Checkout tuned for Pakistan.
 * ---------------------------------------------------------------------- */

// Postcode optional, "Province" label for Pakistan (applies to classic and block checkout).
add_filter(
	'woocommerce_get_country_locale',
	static function ( $locale ) {
		$locale['PK']['postcode']['required'] = false;
		$locale['PK']['state']['label']       = __( 'Province', 'kitab' );
		$locale['PK']['city']['label']        = __( 'City', 'kitab' );
		return $locale;
	}
);

add_filter(
	'woocommerce_billing_fields',
	static function ( $fields ) {
		if ( isset( $fields['billing_phone'] ) ) {
			$fields['billing_phone']['required']    = true;
			$fields['billing_phone']['label']       = __( 'Mobile number', 'kitab' );
			$fields['billing_phone']['placeholder'] = '03XX XXXXXXX';
			$fields['billing_phone']['priority']    = 25;
		}
		if ( isset( $fields['billing_company'] ) ) {
			unset( $fields['billing_company'] );
		}
		return $fields;
	}
);

add_filter(
	'woocommerce_shipping_fields',
	static function ( $fields ) {
		unset( $fields['shipping_company'] );
		return $fields;
	}
);

add_filter(
	'woocommerce_checkout_fields',
	static function ( $fields ) {
		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['placeholder'] = __( 'Nearest landmark, preferred delivery time, etc.', 'kitab' );
		}
		return $fields;
	}
);

/**
 * Validate Pakistani mobile numbers: 03XXXXXXXXX or +923XXXXXXXXX.
 */
add_action(
	'woocommerce_after_checkout_validation',
	static function ( $data, $errors ) {
		if ( empty( $data['billing_country'] ) || 'PK' !== $data['billing_country'] || empty( $data['billing_phone'] ) ) {
			return;
		}
		$digits = preg_replace( '/\D+/', '', $data['billing_phone'] );
		if ( ! preg_match( '/^(?:0|92)3\d{9}$/', $digits ) ) {
			$errors->add( 'validation', __( 'Please enter a valid Pakistani mobile number, e.g. 0300 1234567.', 'kitab' ) );
		}
	},
	10,
	2
);
