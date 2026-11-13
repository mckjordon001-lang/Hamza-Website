<?php
/**
 * Wrapper for all WooCommerce shop and product pages.
 *
 * @package Kitab
 */

get_header();

$kitab_sidebar = ! is_product() && is_active_sidebar( 'sidebar-shop' );
?>
<main id="main" class="site-main container shop-main">
	<?php woocommerce_breadcrumb(); ?>
	<div class="shop-layout<?php echo $kitab_sidebar ? ' shop-layout--sidebar' : ''; ?>">
		<?php if ( $kitab_sidebar ) : ?>
			<aside class="shop-sidebar" aria-label="<?php esc_attr_e( 'Filter books', 'kitab' ); ?>">
				<?php dynamic_sidebar( 'sidebar-shop' ); ?>
			</aside>
		<?php endif; ?>
		<div class="shop-content">
			<?php woocommerce_content(); ?>
		</div>
	</div>
</main>
<?php
get_footer();
