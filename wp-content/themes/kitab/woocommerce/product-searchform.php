<?php
/**
 * Product search form (overrides WooCommerce template).
 *
 * @package Kitab
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
$kitab_id = wp_unique_id( 'product-search-' );
?>
<form role="search" method="get" class="search-form woocommerce-product-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $kitab_id ); ?>"><?php esc_html_e( 'Search books:', 'kitab' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $kitab_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search by title, author or ISBN…', 'kitab' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button type="submit" class="search-submit"><?php echo kitab_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php esc_html_e( 'Search', 'kitab' ); ?></span></button>
	<input type="hidden" name="post_type" value="product">
</form>
