<?php
/**
 * Site header.
 *
 * @package Kitab
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'kitab' ); ?></a>

<?php if ( kitab_mod( 'announcement' ) ) : ?>
	<div class="announcement-bar"><div class="container"><?php echo esc_html( kitab_mod( 'announcement' ) ); ?></div></div>
<?php endif; ?>

<header class="site-header">
	<div class="container header-main">
		<button class="nav-toggle" aria-controls="site-navigation" aria-expanded="false">
			<?php echo kitab_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'kitab' ); ?></span>
		</button>

		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php echo kitab_icon( 'book' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<div class="header-search">
			<?php
			if ( function_exists( 'get_product_search_form' ) ) {
				get_product_search_form();
			} else {
				get_search_form();
			}
			?>
		</div>

		<div class="header-actions">
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="header-account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<?php echo kitab_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'My account', 'kitab' ); ?></span>
				</a>
				<?php kitab_cart_link(); ?>
			<?php endif; ?>
		</div>
	</div>

	<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'kitab' ); ?>">
		<div class="container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 2,
					'fallback_cb'    => 'kitab_menu_fallback',
				)
			);
			?>
		</div>
	</nav>
</header>

<div id="content" class="site-content">
