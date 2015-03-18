<?php
function filter_where( $where = '' ) {
    $where .= " AND post_date <= '" . date('Y-m-d') . "'";
    return $where;
}
add_filter('posts_where', 'filter_where');
$args=array(
  'post_type' => Auction::CUSTOM_POST_TYPE,
  'post_status' => 'publish',
  'posts_per_page' => $max_auctions,
  'caller_get_posts'=> 1,
  'orderby' => 'date',
  'order' => 'DESC'
)
$my_query = null;
$my_query = new WP_Query($args);
?>

<?php
if( $my_query->have_posts() ):
	while ($my_query->have_posts()) : $my_query->the_post(); 
?>

<div class="media">
	<div class="media-left">
		<a href="#">
			<?php
				$attachment_ids = explode( ',', get_post_meta( get_the_ID(), '_easy_image_gallery', true ));
	            $attachment_id = $attachment_ids[0];
	            $image = wp_get_attachment_image( $attachment_id, apply_filters( 'easy_image_gallery_thumbnail_image_size', 'thumbnail' ), '', array( 'alt' => trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ) ) );
	            echo $image;
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
remove_filter( 'posts_where', 'filter_where' );
?>