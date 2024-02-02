<?php

// Exit on direct access
if (!defined('ABSPATH')) {
  exit;
}

?>
<div class="wpclone_modal">
  <div class="wpclone_modal__body">

    <h2 class="modal__title-text">
      Use a better way to create backups & migrate
    </h2>

    <a href="#" class="wpclone_close_modal_a modal__btn-close" aria-label="close modal">
      <svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5869 2.47658C10.8467 2.22546 11.0372 1.9397 11.0805 1.56735C11.0805 1.55869 11.0891 1.55003 11.0978 1.54137V1.24695C11.0632 1.12572 11.0372 0.995829 10.9939 0.883257C10.7861 0.398332 10.4224 0.112572 9.90281 0.0259782C9.88549 0.0259782 9.85952 0.00865939 9.8422 0.00865939H9.53912C9.49582 0.0173188 9.45253 0.0346375 9.40923 0.0432969C9.11481 0.103913 8.86369 0.259781 8.6472 0.467607C7.65137 1.45478 6.65554 2.45061 5.65106 3.43778C5.62508 3.45509 5.5991 3.49839 5.57312 3.53303C5.5558 3.54169 5.53848 3.54169 5.51251 3.55035C5.49519 3.50705 5.47787 3.46375 5.44323 3.42912C4.45606 2.45061 3.46889 1.4721 2.48172 0.484925C2.2306 0.233803 1.94484 0.0519563 1.58114 0.00865939C1.58114 0.0173188 1.57249 0.00865938 1.56383 0H1.26075C1.11354 0.0432969 0.966328 0.0692751 0.827778 0.129891C-0.0381601 0.519563 -0.280623 1.64528 0.368831 2.32937C0.992307 2.97883 1.64176 3.6023 2.2739 4.23444C2.68089 4.63277 3.07922 5.02244 3.47755 5.42077C3.50353 5.44675 3.52951 5.47273 3.5728 5.52469C3.52951 5.55066 3.48621 5.56798 3.46023 5.60262C2.45574 6.58979 1.45991 7.58562 0.455425 8.57279C-0.00352255 9.03174 -0.124754 9.64655 0.135028 10.2008C0.533359 11.0407 1.65908 11.2659 2.34317 10.6337C2.58563 10.4086 2.81944 10.1748 3.05324 9.94097C3.8499 9.15297 4.64657 8.36496 5.44323 7.5683C5.46921 7.54232 5.49519 7.49903 5.51251 7.46439C5.52982 7.46439 5.54714 7.46439 5.56446 7.46439C5.5991 7.50769 5.62508 7.55964 5.66837 7.60294C6.6642 8.59011 7.65137 9.57728 8.6472 10.5558C9.11481 11.0147 9.72963 11.1273 10.2925 10.8502C10.7255 10.6424 10.9766 10.2873 11.0632 9.81974C11.0718 9.79376 11.0805 9.77644 11.0891 9.75047V9.44739C11.0805 9.40409 11.0632 9.36079 11.0545 9.3175C10.9939 9.02308 10.838 8.78061 10.6215 8.56413C9.62571 7.58562 8.63854 6.59845 7.64271 5.61128C7.61674 5.5853 7.57344 5.55066 7.53014 5.51603C7.57344 5.47273 7.59942 5.43809 7.63405 5.41211C8.62122 4.4336 9.59973 3.45509 10.5869 2.47658Z" fill="white"></path>
      </svg>
    </a>

    <div class="modal__bmplug modal__bmplug bmplug">
      <svg class="bmplug__logo-img" width="70" height="70" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="35" cy="35" r="35" fill="white"></circle>
        <path d="M35 0C30.2928 0 25.8097 0.928637 21.7429 2.59378V37.3696C22.1272 44.8628 28.3394 50.8188 35.9286 50.8188C40.5078 50.8188 44.6066 48.6414 47.2004 45.247C46.5599 48.8335 44.8307 52.2598 42.0769 55.0457C38.1061 59.0165 32.4062 61.226 26.8024 60.0412C26.0659 59.8811 25.3614 59.6889 24.6889 59.4328C20.4941 57.9277 16.9076 54.7896 14.4419 51.1711C11.5279 46.8801 9.95883 41.7887 9.95883 36.6011C9.95883 36.2489 9.95883 35.9286 9.99085 35.5764V10.5352C3.81061 16.8435 0 25.4575 0 35C0 54.3413 15.6587 70 35 70C54.3413 70 70 54.3413 70 35C70 15.6587 54.3092 0 35 0Z" fill="#0F9990"></path>
        <path d="M38.394 62.4749C40.7636 61.3862 42.9731 59.8811 44.9265 57.9278C50.1781 52.6762 52.3556 44.4145 51.2028 37.1455C50.1461 30.581 44.7664 22.3194 37.2732 22.4795C37.2732 23.6963 37.2732 24.9452 37.2732 26.162C37.2732 26.6744 36.921 29.3002 37.2732 29.6524C37.0811 29.4282 24.9768 17.3239 24.9768 17.3239L37.2732 5.02752V10.6954C50.9786 11.3999 61.8981 22.7677 61.8981 36.6332C61.8981 50.1144 51.555 61.2261 38.394 62.4749Z" fill="#55BDBD"></path>
      </svg>
      <div class="bmplug__content">
        <p class="bmplug__title">
          We recently released the
          <span class="text-primary">Backup & Migration plugin</span>
          which is simply better:
        </p>
        <ul class="bmplug__features-list">
          <li class="bmplug__features-item">Super-easy</li>
          <li class="bmplug__features-item">More reliable</li>
          <li class="bmplug__features-item">Faster</li>
          <li class="bmplug__features-item">More options</li>
          <li class="bmplug__features-item">Support for <b>any</b> issue</li>
        </ul>
        <p class="bmplug__text-free">
          …and <span class="text-primary">still 100% free!*</span>
        </p>

        <div class="bmplug__btn-wrap">
          <?php if (is_dir(trailingslashit(WP_PLUGIN_DIR) . 'backup-backup')) { ?>
          <a href="#" class="bmplug__install-link">
            Try it out
          </a>
          <?php } else { ?>
          <a href="#" class="bmplug__install-link">
            Install it now
          </a>
          <?php } ?>
          <p class="bmplug__btn-wrap-text">
            (from <a href="https://wordpress.org/plugins/backup-backup/" target="_blank" class="text-primary text-primary--l">WP directory</a>)
          </p>
        </div>

      </div>
    </div>

    <a href="#" class="modal__rejection-link">
      No, let me do it with WP Clone
    </a>
    <p class="modal__hint-text">
      *For backups up to 2 GB (which covers approx. 99% of sites)
    </p>

  </div>
</div>
