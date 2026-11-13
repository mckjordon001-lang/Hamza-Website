<?php
/**
 * Single blog post.
 *
 * @package Kitab
 */

get_header();
?>
<main id="main" class="site-main container container--narrow">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="page-header">
				<?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
				<?php kitab_posted_on(); ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry-thumbnail"><?php the_post_thumbnail( 'large' ); ?></figure>
			<?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
		<?php
		the_post_navigation();
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
