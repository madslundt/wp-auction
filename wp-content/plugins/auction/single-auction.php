<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
<?php while ( have_posts() ) : the_post(); ?>
<?php
    the_title();
    the_content();
?>
<?php
	// If comments are open or we have at least one comment, load up the comment template
	if ( comments_open() || get_comments_number() ) :
		comments_template();
	endif;
?>
<?php endwhile; ?>
<?php endif; ?>
<?php get_footer(); ?>