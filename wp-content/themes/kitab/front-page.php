<?php
/**
 * Homepage.
 *
 * @package Kitab
 */

get_header();

$kitab_has_wc = class_exists( 'WooCommerce' );
$kitab_shop   = $kitab_has_wc ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

/**
 * Print a product section using a WooCommerce shortcode.
 */
$kitab_section = static function ( $title, $shortcode, $link = '', $class = '' ) {
	$html = do_shortcode( $shortcode );
	if ( false === strpos( $html, 'product' ) || false !== strpos( $html, 'woocommerce-info' ) ) {
		return;
	}
	?>
	<section class="home-section <?php echo esc_attr( $class ); ?>">
		<div class="container">
			<div class="section-heading">
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php if ( $link ) : ?>
					<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'View all', 'kitab' ); ?> &rarr;</a>
				<?php endif; ?>
			</div>
			<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
	</section>
	<?php
};
?>
<main id="main" class="site-main home">

	<section class="hero">
		<div class="container hero-inner">
			<div class="hero-copy">
				<p class="eyebrow"><?php echo esc_html( kitab_mod( 'hero_eyebrow' ) ); ?></p>
				<h1><?php echo esc_html( kitab_mod( 'hero_title' ) ); ?></h1>
				<?php if ( kitab_mod( 'hero_urdu' ) ) : ?>
					<p class="hero-urdu" lang="ur" dir="rtl"><?php echo esc_html( kitab_mod( 'hero_urdu' ) ); ?></p>
				<?php endif; ?>
				<p class="hero-text"><?php echo esc_html( kitab_mod( 'hero_text' ) ); ?></p>
				<div class="hero-search">
					<?php function_exists( 'get_product_search_form' ) ? get_product_search_form() : get_search_form(); ?>
				</div>
				<a class="button button--primary" href="<?php echo esc_url( $kitab_shop ); ?>"><?php echo esc_html( kitab_mod( 'hero_button' ) ); ?></a>
			</div>
			<div class="hero-art" aria-hidden="true">
				<span class="hero-book hero-book--1"></span>
				<span class="hero-book hero-book--2"></span>
				<span class="hero-book hero-book--3"></span>
				<span class="hero-book hero-book--4"></span>
				<span class="hero-book hero-book--5"></span>
			</div>
		</div>
	</section>

	<?php
	if ( $kitab_has_wc ) :
		$kitab_cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
				'number'     => 8,
				'orderby'    => 'menu_order',
			)
		);
		if ( $kitab_cats && ! is_wp_error( $kitab_cats ) ) :
			?>
			<section class="home-section">
				<div class="container">
					<div class="section-heading"><h2><?php esc_html_e( 'Shop by category', 'kitab' ); ?></h2></div>
					<ul class="category-grid">
						<?php foreach ( $kitab_cats as $kitab_cat ) : ?>
							<li style="--cover-hue:<?php echo esc_attr( absint( crc32( $kitab_cat->slug ) ) % 360 ); ?>">
								<a href="<?php echo esc_url( get_term_link( $kitab_cat ) ); ?>">
									<span class="category-name"><?php echo esc_html( $kitab_cat->name ); ?></span>
									<?php /* translators: %d: number of books */ ?>
									<span class="category-count"><?php echo esc_html( sprintf( _n( '%d book', '%d books', $kitab_cat->count, 'kitab' ), $kitab_cat->count ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
			<?php
		endif;

		$kitab_section( __( 'Staff picks', 'kitab' ), '[products limit="8" columns="4" visibility="featured"]', $kitab_shop );
		$kitab_section( __( 'Bestsellers', 'kitab' ), '[products limit="4" columns="4" best_selling="true"]', add_query_arg( 'orderby', 'popularity', $kitab_shop ), 'home-section--tinted' );

		$kitab_urdu = get_term_by( 'slug', 'urdu-novels', 'product_cat' );
		if ( $kitab_urdu ) {
			$kitab_section( __( 'Urdu novels', 'kitab' ), '[products limit="4" columns="4" category="urdu-novels"]', get_term_link( $kitab_urdu ) );
		}
		?>

		<section class="quote-band">
			<div class="container">
				<blockquote>
					<p lang="ur" dir="rtl">خودی کو کر بلند اتنا کہ ہر تقدیر سے پہلے<br>خدا بندے سے خود پوچھے بتا تیری رضا کیا ہے</p>
					<cite><?php esc_html_e( '— Allama Muhammad Iqbal', 'kitab' ); ?></cite>
				</blockquote>
			</div>
		</section>

		<?php
		$kitab_section( __( 'New arrivals', 'kitab' ), '[products limit="8" columns="4" orderby="date" order="DESC"]', add_query_arg( 'orderby', 'date', $kitab_shop ) );
		$kitab_section( __( 'On sale', 'kitab' ), '[products limit="4" columns="4" on_sale="true"]', '', 'home-section--tinted' );
	endif;

	while ( have_posts() ) :
		the_post();
		if ( '' !== trim( get_the_content() ) ) :
			?>
			<section class="home-section"><div class="container container--narrow entry-content"><?php the_content(); ?></div></section>
			<?php
		endif;
	endwhile;
	?>
</main>
<?php
get_footer();
