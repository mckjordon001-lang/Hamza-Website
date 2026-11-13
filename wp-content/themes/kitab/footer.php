<?php
/**
 * Site footer.
 *
 * @package Kitab
 */
?>
</div><!-- #content -->

<section class="trust-bar" aria-label="<?php esc_attr_e( 'Why shop with us', 'kitab' ); ?>">
	<div class="container trust-grid">
		<div class="trust-item"><?php echo kitab_icon( 'cash' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><strong><?php esc_html_e( 'Cash on Delivery', 'kitab' ); ?></strong><span><?php esc_html_e( 'Pay when your books arrive', 'kitab' ); ?></span></div></div>
		<div class="trust-item"><?php echo kitab_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><strong><?php echo esc_html( kitab_mod( 'free_shipping' ) ); ?></strong><span><?php echo esc_html( kitab_mod( 'delivery_time' ) ); ?></span></div></div>
		<div class="trust-item"><?php echo kitab_icon( 'return' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><strong><?php esc_html_e( 'Easy returns', 'kitab' ); ?></strong><span><?php esc_html_e( '7 days on damaged or wrong items', 'kitab' ); ?></span></div></div>
		<div class="trust-item"><?php echo kitab_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><div><strong><?php esc_html_e( 'WhatsApp support', 'kitab' ); ?></strong><span><?php echo esc_html( kitab_mod( 'phone' ) ); ?></span></div></div>
	</div>
</section>

<footer class="site-footer">
	<div class="container footer-grid">
		<div class="footer-about">
			<p class="footer-title"><?php bloginfo( 'name' ); ?></p>
			<p><?php bloginfo( 'description' ); ?></p>
			<ul class="footer-contact">
				<li><?php echo kitab_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', kitab_mod( 'phone' ) ) ); ?>"><?php echo esc_html( kitab_mod( 'phone' ) ); ?></a></li>
				<li><a href="mailto:<?php echo esc_attr( kitab_mod( 'email' ) ); ?>"><?php echo esc_html( kitab_mod( 'email' ) ); ?></a></li>
				<li><?php echo esc_html( kitab_mod( 'address' ) ); ?></li>
			</ul>
		</div>
		<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
			<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
				<div class="footer-widgets"><?php dynamic_sidebar( 'footer-' . $i ); ?></div>
			<?php endif; ?>
		<?php endfor; ?>
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'kitab' ); ?>">
				<p class="footer-title"><?php esc_html_e( 'Help', 'kitab' ); ?></p>
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) ); ?>
			</nav>
		<?php endif; ?>
	</div>
	<div class="container footer-bottom">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All prices in Pakistani Rupees (PKR).', 'kitab' ); ?></p>
		<p class="payment-methods"><?php esc_html_e( 'Cash on Delivery · Bank Transfer · JazzCash · Easypaisa', 'kitab' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
