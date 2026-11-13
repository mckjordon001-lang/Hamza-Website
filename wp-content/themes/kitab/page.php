<?php
/**
 * Single page.
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
			<header class="page-header"><?php the_title( '<h1 class="page-title">', '</h1>' ); ?></header>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</main>
<?php
get_footer();
