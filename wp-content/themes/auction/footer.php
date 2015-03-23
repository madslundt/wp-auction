<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package auction
 */
?>

	</div><!-- .container -->
	
	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="site-info container">
			<div class="col-xs-12 col-sm-4">
				<?php
					if ( is_active_sidebar( 'footer-1' ) ) {
						dynamic_sidebar( 'footer-1' );
					}
				?>
			</div>
			<div class="col-xs-12 col-sm-4">
				<?php
					if ( is_active_sidebar( 'footer-2' ) ) {
						dynamic_sidebar( 'footer-2' );
					}
				?>
			</div>
			<div class="col-xs-12 col-sm-4">
				<?php
					if ( is_active_sidebar( 'footer-3' ) ) {
						dynamic_sidebar( 'footer-3' );
					}
				?>
			</div>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
