<?php get_header(); ?>

<?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <a href="<?php the_permalink() ?>">
            <?php the_title( '<h1>', '</h1>' ); ?>
        </a>
        <?php the_content(); ?>
    <?php endwhile; ?>
<?php endif; ?>

<?php //get_sidebar(); ?>
<?php get_footer(); ?>