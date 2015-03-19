<?php get_header(); ?>
<h1>Search results</h1>
<?php Auction::create_search_form(); ?>
<?php
$my_query = Auction::get_search_results();
if( $my_query->have_posts() ):
    while ($my_query->have_posts()) : $my_query->the_post(); 
?>

<div class="media">
    <div class="media-left">
        <a href="<?php the_permalink(); ?>">
            <?php
                Auction::printThumbnail(get_the_ID());
            ?>
        </a>
    </div>
    <div class="media-body">
        <h4 class="media-heading"><?php the_title(); ?></h4>
        <?php
            if (get_the_excerpt()) {
                the_excerpt();
            } else {
                the_content();
            }
        ?>
    </div>
</div>

<?php
    endwhile;
endif;
wp_reset_query();  // Restore global post data stomped by the_post().
?>
<?php get_footer(); ?>