<?php
/**
 * The template for displaying all single posts.
 *
 * @package auction
 */

get_header(); ?>
<div class="row">
	<div class="col-xs-12">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php if (get_current_user_id() == get_the_author_meta( 'ID' )): ?>
					<div class="alert alert-success" role="alert"><?php _e('Since you are the owner of this post you have the option to edit this product', 'auction'); ?></div>
				<?php elseif (current_user_can('edit_users')): ?>
					<div class="alert alert-info" role="alert"><?php _e('Since you are administrator you have the option to edit this product', 'auction'); ?></div>
				<?php endif; ?>

				<?php get_template_part( 'content', 'single' ); ?>

				<?php the_post_navigation(); ?>

				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
				?>

			<?php endwhile; // end of the loop. ?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div>
</div>

<?php get_footer(); ?>
