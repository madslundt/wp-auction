<?php
$args = array(
  'post_type' => Auction::CUSTOM_POST_TYPE,
  'post_status' => 'publish',
  'posts_per_page' => $max_auctions,
  'ignore_sticky_posts'=> 1,
  'orderby' => 'comment_count',
  'order' => 'DESC',
  'date_query' => array(
  	array(
  		'after' => strtotime(date('Y-m-d'))
  	)
  ),
  'meta_query' => array(
  	'key' => 'end_date',
  	'value' => date(Auction::DATE_FORMAT_PHP),
  	'compare' => '<='
  )
);

$my_query = null;
$my_query = new WP_Query($args);
?>

<?php
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
		<?php comments_number( 'no responses', 'one response', '% responses' ); ?>
	</div>
</div>

<?php
	endwhile;
endif;
wp_reset_query();  // Restore global post data stomped by the_post().
?>