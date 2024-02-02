<?php
/**
 * The template part for displaying Grid Layout
 *
 * @package VW Book Store
 * @subpackage vw-book-store
 * @since VW Book Store 1.0
 */
?>
<?php 
  $vw_book_store_archive_year  = get_the_time('Y'); 
  $vw_book_store_archive_month = get_the_time('m'); 
  $vw_book_store_archive_day   = get_the_time('d'); 
?>
<div class="col-lg-4 col-md-6">
	<article id="post-<?php the_ID(); ?>" <?php post_class('inner-service'); ?>>
	   <div class="grid-post-main-box wow zoomInUp delay-1000" data-wow-duration="2s">
	      	<div class="box-image">
	          	<?php 
		            if(has_post_thumbnail() && get_theme_mod( 'vw_book_store_featured_image_hide_show',true) == 1) { 
		              the_post_thumbnail(); 
		            }
	          	?>  
	        </div>
	        <h3 class="section-title"><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo the_title_attribute(); ?>"><?php the_title();?><span class="screen-reader-text"><?php the_title(); ?></span></a></h3> 
	       	<?php if( get_theme_mod( 'vw_book_store_grid_postdate',true) == 1 || get_theme_mod( 'vw_book_store_grid_author',true) == 1 || get_theme_mod( 'vw_book_store_grid_comments',true) == 1) { ?>
	            <div class="post-info">
	                <?php if(get_theme_mod('vw_book_store_grid_postdate',true)==1){ ?>
	                    <i class="<?php echo esc_attr(get_theme_mod('vw_book_store_grid_postdate_icon','fas fa-calendar-alt')); ?>"></i><span class="entry-date"><a href="<?php echo esc_url( get_day_link( $vw_book_store_archive_year, $vw_book_store_archive_month, $vw_book_store_archive_day)); ?>"><?php echo esc_html( get_the_date() ); ?><span class="screen-reader-text"><?php echo esc_html( get_the_date() ); ?></span></a></span><span><?php echo esc_html(get_theme_mod('vw_book_store_grid_post_meta_field_separator', '|'));?></span>
	                <?php } ?>   

	                <?php if(get_theme_mod('vw_book_store_grid_author',true)==1){ ?>
	                    <i class="<?php echo esc_attr(get_theme_mod('vw_book_store_grid_author_icon','fas fa-user')); ?>"></i><span class="entry-author"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' )) ); ?>"><?php the_author(); ?><span class="screen-reader-text"><?php the_author(); ?></span></a></span><span><?php echo esc_html(get_theme_mod('vw_book_store_grid_post_meta_field_separator', '|'));?></span>
	                <?php } ?>

	                <?php if(get_theme_mod('vw_book_store_grid_comments',true)==1){ ?>
	                    <i class="<?php echo esc_attr(get_theme_mod('vw_book_store_grid_comments_icon','fa fa-comments')); ?>" aria-hidden="true"></i><span class="entry-comments"><?php comments_number( __('0 Comment', 'vw-book-store'), __('0 Comments', 'vw-book-store'), __('% Comments', 'vw-book-store') ); ?> </span>
	                <?php } ?>
	            </div>
        	<?php } ?>     
	        <div class="new-text">
	          	<div class="entry-content">
	          		<p>
			          <?php $vw_book_store_excerpt = get_the_excerpt(); echo esc_html( vw_book_store_string_limit_words( $vw_book_store_excerpt, esc_attr(get_theme_mod('vw_book_store_grid_excerpt_number','30')))); ?> <?php echo esc_html( get_theme_mod('vw_book_store_grid_excerpt_suffix','') ); ?>
			        </p>
	          	</div>
	        </div>
	        <?php if( get_theme_mod('vw_book_store_grid_button_text','Read More') != ''){ ?>
		        <div class="content-bttn">
	              <a href="<?php echo esc_url( get_permalink() );?>" class="blogbutton-small"><?php echo esc_html(get_theme_mod('vw_book_store_grid_button_text',__('Read More','vw-book-store')));?><span class="screen-reader-text"><?php echo esc_html(get_theme_mod('vw_book_store_grid_button_text',__('Read More','vw-book-store')));?></span></a>
	            </div>
		    <?php } ?>
	    </div>
	    <div class="clearfix"></div>
  	</article>
</div>