<?php
/**
 * Fallback template: blog index, archives and search.
 *
 * @package Kitab
 */

get_header();
?>
<main id="main" class="site-main container">
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<header class="page-header"><h1 class="page-title"><?php single_post_title(); ?></h1></header>
	<?php elseif ( is_search() ) : ?>
		<header class="page-header">
			<?php /* translators: %s: search query */ ?>
			<h1 class="page-title"><?php printf( esc_html__( 'Search results for “%s”', 'kitab' ), esc_html( get_search_query() ) ); ?></h1>
		</header>
	<?php elseif ( is_archive() ) : ?>
		<header class="page-header"><?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?></header>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="post-card__image"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<div class="post-card__body">
						<?php the_title( '<h2 class="post-card__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
						<?php kitab_posted_on(); ?>
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found. Try a different search.', 'kitab' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</main>
<?php
get_footer();
