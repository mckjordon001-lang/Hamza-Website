<?php
/**
 * Small template helpers.
 *
 * @package Kitab
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icons.
 */
function kitab_icon( $name ) {
	$icons = array(
		'cart'    => '<path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
		'user'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
		'search'  => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'menu'    => '<path d="M3 6h18M3 12h18M3 18h18"/>',
		'truck'   => '<path d="M1 4h14v12H1z"/><path d="M15 9h4l3 3v4h-7"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
		'cash'    => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/>',
		'return'  => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
		'book'    => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5z"/><path d="M4 19.5A2.5 2.5 0 0 0 6.5 22H20v-5"/>',
		'phone'   => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
		'whatsapp'=> '<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.4z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 1c-1-.5-1.5-1-2-2l1-1-1-2z"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg class="icon icon-' . esc_attr( $name ) . '" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Header cart link (refreshed via WooCommerce cart fragments).
 */
function kitab_cart_link() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$count = WC()->cart->get_cart_contents_count();
	?>
	<a class="header-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php echo kitab_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'kitab' ); ?></span>
		<span class="cart-count" data-count="<?php echo esc_attr( $count ); ?>"><?php echo esc_html( $count ); ?></span>
	</a>
	<?php
}

/**
 * Post meta line.
 */
function kitab_posted_on() {
	printf(
		'<p class="entry-meta"><time datetime="%1$s">%2$s</time> · %3$s</p>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() ),
		esc_html( get_the_author() )
	);
}

/**
 * Fallback when no menu is assigned.
 */
function kitab_menu_fallback() {
	echo '<ul class="menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'kitab' ) . '</a></li>';
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Shop', 'kitab' ) . '</a></li>';
	}
	echo '</ul>';
}
