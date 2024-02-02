<?php
/**
 * Template Name: Custom Home
 */

get_header(); ?>

<main id="maincontent" role="main">
  <?php do_action( 'vw_book_store_before_slider' ); ?>

  <?php if( get_theme_mod( 'vw_book_store_slider_hide_show', false) == 1 || get_theme_mod( 'vw_book_store_resp_slider_hide_show', true) == 1) { ?>
    <section id="slider">
      <?php if(get_theme_mod('vw_book_store_slider_type', 'Default slider') == 'Default slider' ){ ?>
        <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel" data-bs-interval="<?php echo esc_attr(get_theme_mod( 'vw_book_store_slider_speed',4000)) ?>">
          <?php $vw_book_store_slider_pages = array();
            for ( $count = 1; $count <= 3; $count++ ) {
              $mod = intval( get_theme_mod( 'vw_book_store_slider_page' . $count ));
              if ( 'page-none-selected' != $mod ) {
                $vw_book_store_slider_pages[] = $mod;
              }
            }
            if( !empty($vw_book_store_slider_pages) ) :
              $args = array(
                'post_type' => 'page',
                'post__in' => $vw_book_store_slider_pages,
                'orderby' => 'post__in'
              );
              $query = new WP_Query( $args );
              if ( $query->have_posts() ) :
                $i = 1;
          ?>
          <div class="carousel-inner" role="listbox">
            <?php  while ( $query->have_posts() ) : $query->the_post(); ?>
              <div <?php if($i == 1){echo 'class="carousel-item active"';} else{ echo 'class="carousel-item"';}?>>
                <?php if(has_post_thumbnail()){
                  the_post_thumbnail();
                } else{?>
                  <img src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/block-patterns/images/banner.png" alt="" />
                <?php } ?>
                <div class="carousel-caption">
                  <div class="inner_carousel">
                    <?php if( get_theme_mod('vw_book_store_slider_title_hide_show',true) == 1){ ?>
                      <h1 class="<?php if( get_theme_mod( 'vw_book_store_animation', true) == 1) { ?> wow zoomInUp delay-1000" data-wow-duration="2s"<?php } else { ?> heading <?php } ?>"><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
                    <?php }?>
                    <?php if( get_theme_mod('vw_book_store_slider_content_hide_show',true) == 1){ ?>
                      <p class="wow zoomInDown delay-1000" data-wow-duration="2s"><?php $vw_book_store_excerpt = get_the_excerpt(); echo esc_html( vw_book_store_string_limit_words( $vw_book_store_excerpt, esc_attr(get_theme_mod('vw_book_store_slider_excerpt_number','30')))); ?></p>
                    <?php } ?>
                    <?php if( get_theme_mod('vw_book_store_slider_button_text','READ MORE') != ''){ ?>
                      <div class="more-btn wow zoomInDown delay-1000" data-wow-duration="2s">
                        <a href="<?php echo esc_url(get_theme_mod('vw_book_store_top_button_url',false));?>"><?php echo esc_html(get_theme_mod('vw_book_store_slider_button_text',__('READ MORE','vw-book-store')));?><span class="screen-reader-text"><?php echo esc_html(get_theme_mod('vw_book_store_slider_button_text',__('READ MORE','vw-book-store')));?></span></a>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            <?php $i++; endwhile;
            wp_reset_postdata();?>
          </div>
          <?php else : ?>
              <div class="no-postfound"></div>
          <?php endif;
          endif;?>
          <?php if(get_theme_mod('vw_book_store_slider_arrow_hide_show', true)){?>
            <a class="carousel-control-prev" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev" role="button">
              <span class="carousel-control-prev-icon w-auto h-auto" aria-hidden="true"><i class="<?php echo esc_attr(get_theme_mod('vw_book_store_slider_prev_icon','fas fa-chevron-left')); ?>"></i></span>
              <span class="screen-reader-text"><?php esc_html_e( 'Previous','vw-book-store' );?></span>
            </a>
            <a class="carousel-control-next" data-bs-target="#carouselExampleCaptions" data-bs-slide="next" role="button">
              <span class="carousel-control-next-icon w-auto h-auto" aria-hidden="true"><i class="<?php echo esc_attr(get_theme_mod('vw_book_store_slider_next_icon','fas fa-chevron-right')); ?>"></i></span>
              <span class="screen-reader-text"><?php esc_html_e( 'Next','vw-book-store' );?></span> 
            </a>
          <?php } ?>
        </div>
        <div class="clearfix"></div>
          <?php } else if(get_theme_mod('vw_book_store_slider_type', 'Advance slider') == 'Advance slider'){?>
            <?php echo do_shortcode(get_theme_mod('vw_book_store_advance_slider_shortcode')); ?>
          <?php } ?>
    </section>
  <?php }?>

  <?php do_action( 'vw_book_store_after_slider' ); ?>

  <section id="book-store" class="wow zoomInDown delay-1000" data-wow-duration="2s">
    <div class="container">
      <?php $vw_book_store_products_pages = array();
      for ( $count = 0; $count <= 0; $count++ ) {
        $mod = absint( get_theme_mod( 'vw_book_shop_product_page' . $count ));
        if ( 'page-none-selected' != $mod ) {
          $vw_book_store_products_pages[] = $mod;
        }
      }
      if( !empty($vw_book_store_products_pages) ) :
        $args = array(
          'post_type' => 'page',
          'post__in' => $vw_book_store_products_pages,
          'orderby' => 'post__in'
        );
        $query = new WP_Query( $args );
        if ( $query->have_posts() ) :
          $count = 0;
          while ( $query->have_posts() ) : $query->the_post(); ?>
            <div class="">
              <h2><?php the_title(); ?></h2>
              <p><?php the_content(); ?></p>
              <div class="clearfix"></div>
            </div>
          <?php $count++; endwhile; ?>
        <?php else : ?>
            <div class="no-postfound"></div>
        <?php endif;
      endif;
      wp_reset_postdata()?>
        <div class="clearfix"></div>
    </div>
  </section>

  <?php do_action( 'vw_book_store_after_trending_post' ); ?>

  <div class="content-vw">
    <div class="container">
      <?php while ( have_posts() ) : the_post(); ?>
        <?php the_content(); ?>
      <?php endwhile; // end of the loop. ?>
    </div>
  </div>
</main>

<?php get_footer(); ?>
