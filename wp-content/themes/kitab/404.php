<?php
/**
 * Not found.
 *
 * @package Kitab
 */

get_header();
?>
<main id="main" class="site-main container container--narrow error-404">
	<h1 class="page-title"><?php esc_html_e( 'This page is missing from our shelves', 'kitab' ); ?></h1>
	<p><?php esc_html_e( 'Try searching for a book, author or ISBN instead.', 'kitab' ); ?></p>
	<?php function_exists( 'get_product_search_form' ) ? get_product_search_form() : get_search_form(); ?>
	<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
		<p><a class="button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse all books', 'kitab' ); ?></a></p>
	<?php endif; ?>
</main>
<?php
get_footer();
