<?php
/**
 * Search form.
 *
 * @package Kitab
 */
$kitab_id = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $kitab_id ); ?>"><?php esc_html_e( 'Search for:', 'kitab' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $kitab_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'kitab' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
	<button type="submit" class="search-submit"><?php echo kitab_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="screen-reader-text"><?php esc_html_e( 'Search', 'kitab' ); ?></span></button>
</form>
