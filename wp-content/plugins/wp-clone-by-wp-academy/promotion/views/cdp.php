<?php

  // Namespace
  namespace Inisev\Subs;

  // Disallow direct access
  if (!defined('ABSPATH')) exit;

?>

<section class="ib-banners-wrapper">
  <span id="ib-insPP-aurl"><?php echo admin_url(); ?></span>
  <div class="ib-banner ib-banner-3">
    <div class="ib-banner-content ib-d-flex">
      <div class="ib-popup-info ib-d-flex">

        <div class="ib-col ib-logo-container-2 ib-d-flex">
          <img class="ib-logo3" src="<?php $this->_asset('images/CDP-logo.gif'); ?>" alt="logo">
          <img class="ib-rating" src="<?php $this->_asset('images/rating.svg'); ?>" alt="rating">
        </div>
        <?php if (defined('insPP_cdp_alternative')) { ?>
        <div class="ib-col ib-col-2 ib-d-flex" style="flex-basis: 70%;">
          <p class="ib-heading">Save a lot of time: <span style="font-weight: normal;">Use a plugin to quickly copy posts, so that you don’t have to manually carry over content from old to new posts!</span></p>
        </div>
        <?php } else { ?>
        <div class="ib-col ib-col-2 ib-d-flex">
          <p class="ib-heading">Try out a better plugin to duplicate posts & pages</p>
          <div class="ib-features ib-d-flex">
            <div class="ib-check ib-d-flex">
              <div class="ib-check-icon"></div><span>Easier to use</span>
            </div>
            <div class="ib-check ib-d-flex">
              <div class="ib-check-icon"></div><span>Faster copying</span>
            </div>
            <div class="ib-check ib-d-flex">
              <div class="ib-check-icon"></div><span style="margin-right: 0;">Many more features</span>
            </div>
          </div>
        </div>
        <?php } ?>

      </div>
      <div class="ib-actions ib-d-flex">
        <div class="ib-col ib-d-flex">
          <div class="ib-btn ib-btn-success ib-d-flex ib-align-items-center">
            <span class="ib-position-relative ib-text-center">Install now (free!)</span>
            <span class="ib-arrow-icon"></span>
          </div>
          <div class="ib-link">
            <p>(Source: <a href="https://bit.ly/39t5Q4M" target="_blank">WP directory</a>)</p>
          </div>
        </div>
        <div class="ib-col ib-col-3 ib-d-flex"><button class="ib-remind">Remind me later</button></div>
      </div>
      <div class="ib-close-btn">
        <div class="ib-close-icon"></div>
      </div>
    </div>
    <div class="ib-author">
      <span>Made with <span class="ib-heart">❤️</span> by <a href="<?php echo admin_url('admin.php?page=') . $this->menu; ?>"><strong><?php echo $this->byName; ?></strong></a></span>
    </div>
  </div>
</section>
