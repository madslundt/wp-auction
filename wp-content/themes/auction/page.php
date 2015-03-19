<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package auction
 */

get_header(); ?>
<div class="row">
    <div class="col-xs-12 col-sm-8 pull-right">
    	<div id="primary" class="content-area">
    		<main id="main" class="site-main" role="main">

    			<?php while ( have_posts() ) : the_post(); ?>

    				<?php get_template_part( 'content', 'page' ); ?>

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
    <div class="pull-left col-xs-12 col-sm-4">
        <?php get_sidebar(); ?>
    </div>
</div>
<?php get_footer(); ?>
