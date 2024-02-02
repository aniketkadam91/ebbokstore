<?php

  /**
   * Promotion of plugins that may be very useful for individual user
   *
   * @category Child Plugin
   * @version v0.1.0
   * @since v0.1.0
   * @author iClyde <kontakt@iclyde.pl>
   */

  // Namespace
  namespace Inisev\Subs;


  // Disallow direct access
  if (defined('ABSPATH')) {

    /**
     * Main class for handling the Carousel
     */
    if (!class_exists('Inisev\Subs\InisevPlugPromo')) {

      /**
       * Class which handles everything about Plug Promo
       */
      class InisevPlugPromo {

        // Private variables
        public $dir;
        public $url;
        private $file;
        private $slug;
        private $byName;
        private $menu;
        private $allSlugs = false;
        private $canRenderCDP = false;
        private $canRenderMPU = false;
        private $canRenderBMI = false;

        // Time limitations
        private $installationDelay = '+8 days'; // Time which before any banner will show
        private $bannersDelay = '+14 days'; // Time before any actions
        private $remindDelay = '+7 days'; // Time before try to remind again
        private $dismissDelay = '+180 days'; // Time after dissmissing
        private $successDelay = '+14 days'; // Time after successfull installation (for next banner check)
        private $timeAfterBodySuccess = '+10 days'; // Time after successfull body check (for alternative MPU rules)
        private $timeAfterBodyFail = '+4 days'; // Time after failed body check (for alternative MPU rules)

        // PLugin slugs
        private $bmi_slug = 'backup-backup/backup-backup.php';
        private $cdp_slug = 'copy-delete-posts/copy-delete-posts.php';
        private $mpu_slug = 'pop-up-pop-up/pop-up-pop-up.php';

        // We need basic information about the plugin
        function __construct($file, $slug, $byName, $menu) {

          if (!is_admin() || !current_user_can('install_plugins')) return;

          // Plugin __DIR__
          $this->dir = trailingslashit(dirname($file));

          // Plugin __FILE__
          $this->file = trailingslashit(dirname($file));

          // Current plugin slug
          $this->slug = $slug;

          // Plugin's name displayed under banner
          $this->byName = $byName;

          // Plugin's settings
          $this->menu = $menu;

          // Plugin assets
          $this->url = trailingslashit(plugins_url('', $file)) . 'promotion/assets/';

          // Show something?
          if ($this->checkIfCanShow()) {

            // Render the banner
            add_action('admin_notices', [$this, 'renderBanner']);

            // Add Scrips & Styles if Anything rendered
            add_action('admin_enqueue_scripts', [$this, 'addScriptsAndStyles']);

            // Ajax Handler
            add_action('wp_ajax_insPP_ajax', [$this, 'ajaxHandler']);

          }

        }

        // Check if page won't be broken
        public function checkSite() {

          global $pagenow;
          if ((isset($_GET['action']) && $_GET['action'] == 'edit') || in_array($pagenow, ['post-new.php'])) {
            return false;
          } else {
            return true;
          }

        }

        // Helper for assets
        public function _asset($file) {

          // Root URL for assets
          echo $this->url . $file;

        }

        // Helper function remove _ -/ characters and make lowercase
        private function makelower($str) {

          $str = str_replace('_', '', $str);
          $str = str_replace('-', '', $str);
          $str = str_replace('/', '', $str);
          $str = str_replace('\/', '', $str);
          $str = str_replace(' ', '', $str);
          $str = strtolower($str);

          return $str;

        }

        // Scan all slugs to calculate the
        private function getAllSlugs($_return = false) {

          if ($this->allSlugs == false) {

            $scannedFiles = scandir(WP_PLUGIN_DIR);
            $files = [];

            foreach($scannedFiles as $file) {
              if (in_array($file, ['.', '..'])) continue;
              if (is_dir(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $file)) $files[] = $this->makelower($file);
            }

            $this->allSlugs = $files;

          }

          if ($_return) {
            return $this->allSlugs;
          }

        }

        // Check if plugin is installed
        private function checkIfPluginsInstalled($slugs) {

          if ($this->allSlugs === false) $this->getAllSlugs();

          for ($i = 0; $i < sizeof($slugs); ++$i) {

            $slug = $this->makelower($slugs[$i]);
            if (in_array($slug, $this->allSlugs)) {
              return true;
            }

          }

          return false;

        }

        // It will check if CDP is right plugin for current user
        private function checkIfCDP() {

          // If CDP is installed ignore
          if ($this->checkIfPluginsInstalled(['copy-delete-posts'])) {
            return false;
          }

          // Check if CDP was already displayed and dismissed/reminded
          $delayed = get_option('insPP_delay_cdp', false);
          if ($delayed && intval($delayed) > time()) {
            return false;
          }

          // Check List
          $plugins = ['duplicate-post',
                      'duplicate-page',
                      'duplicate-pp',
                      'post-duplicator',
                      'duplicate-wp-page-post',
                      'delete-duplicate-posts',
                      'duplicate-post-page-menu-custom-post-type',
                      'duplicate-page-or-post',
                      'rduplicator',
                      'wp-post-page-clone'];

          // Check if required plugin is installed, otherwise user may not need CDP
          if ($this->checkIfPluginsInstalled($plugins)) {

            if (!defined('insPP_renderScripts')) {
              define('insPP_renderScripts', true);
            }

            $this->canRenderCDP = true;
            return true;

          }

          return false;

        }

        // It will check if BMI is right plugin for current user
        private function checkIfBMI() {

          // If BMI is installed ignore
          if ($this->checkIfPluginsInstalled(['backup-migration', 'backup-backup'])) {
            return false;
          }

          // Check if BMI was already displayed and dismissed/reminded
          $delayed = get_option('insPP_delay_bmi', false);
          if ($delayed && intval($delayed) > time()) {
            return false;
          }

          // Check List
          $plugins = ['updraftplus',
                      'backwpup',
                      'backup',
                      'wpvivid-backuprestore',
                      'xcloner-backup-and-restore',
                      'all-in-one-wp-migration',
                      'duplicator',
                      'jetpack',
                      'boldgrid-backup',
                      'wp-database-backup',
                      'blogvault-real-time-backup',
                      'wp-backitup',
                      'wp-staging',
                      'wpvivid-backup-mainwp',
                      'wp-all-backup',
                      'keep-backup-daily',
                      'wp-rollback',
                      'wp-backup-bank',
                      'wp-dbmanager',
                      'backup-wd',
                      'advanced-database-cleaner',
                      'vaultpress',
                      'backupwordpress',
                      'wp-migrate-db',
                      'wp-db-backup',
                      'backup-database',
                      'migrate-guru',
                      'wp-site-migrate',
                      'wp-migration-duplicator',
                      'wpsynchro'];

          // Check if required plugin is installed, otherwise user may not need CDP
          if ($this->checkIfPluginsInstalled($plugins)) {

            if (!defined('insPP_renderScripts')) {
              define('insPP_renderScripts', true);
            }

            $this->canRenderBMI = true;
            return true;

          }

        }

        // It will check if MPU is right plugin for current user
        private function checkIfMPU($alternative = false) {

          // If MPU is installed ignore
          if ($this->checkIfPluginsInstalled(['pop-up-pop-up', 'wp-mypopups'])) {
            return false;
          }

          // Check if MPU was already displayed and dismissed/reminded
          $delayed = get_option('insPP_delay_mpu', false);
          if ($delayed && intval($delayed) > time()) {
            return false;
          }

          // Check List
          $plugins = ['optinmonster',
                      'justuno',
                      'wisepops-popups',
                      'popup-maker-wp',
                      'getsitecontrol',
                      'sumome',
                      'poptin',
                      'convertkit',
                      'cm-pop-up-banners',
                      'wordpress-popu',
                      'ninja-forms',
                      'popup-builder',
                      'popup-maker',
                      'pop-up',
                      'popup-by-supsystic',
                      'ari-fancy-lightbox',
                      'exit-popup',
                      'icegram',
                      'alligator-popup',
                      'video-popup',
                      'easy-fancybox',
                      'itro-popup',
                      'hellobar',
                      'popups',
                      'popupally',
                      'yith-woocommerce-popup',
                      'themify-popup',
                      'jazz-popups',
                      'provesource',
                      'smart-popup-blaster',
                      'popliup',
                      'cool-fade-popup',
                      'exit-intent-popups-by-optimonk',
                      'yeloni-free-exit-popup',
                      'wp-optin-wheel',
                      'mailoptin',
                      'omnisend-connect'];

          // Check if required plugin is installed, otherwise user may not need CDP
          $alternative_check = false;
          if ($alternative) {
            $alternative_check = $this->checkIfMPUAlt();
          }

          if ($this->checkIfPluginsInstalled($plugins) || $alternative_check) {

            if (!defined('insPP_renderScripts')) {
              define('insPP_renderScripts', true);
            }

            $this->canRenderMPU = true;
            return true;

          }

        }

        // It will check if CDP is right plugin for current user alternative
        private function checkIfCDPAlt() {

          // If CDP is installed ignore
          if ($this->checkIfPluginsInstalled(['copy-delete-posts'])) {
            return false;
          }

          // Check if CDP was already displayed and dismissed/reminded
          $delayed = get_option('insPP_delay_cdp', false);
          if ($delayed && intval($delayed) > time()) {
            return false;
          }

          // Make sure previous is false
          if ($this->checkIfCDP() !== false) {
            return false;
          }

          // Check post/page count
          $posts = wp_count_posts('post');
          $pages = wp_count_posts('page');
          $total = intval($posts->publish) + intval($posts->draft);
          $total += intval($pages->publish) + intval($pages->draft);

          // Check the minimum requirement
          if ($total < 5) {
            return false;
          }

          // Otherwise return true
          if (!defined('insPP_renderScripts')) {
            define('insPP_renderScripts', true);
          }

          // Alternative text
          if (!defined('insPP_cdp_alternative')) {
            define('insPP_cdp_alternative', true);
          }

          $this->canRenderCDP = true;
          return true;

        }

        // It will check if MPU is right plugin for current user alternative
        private function checkIfMPUAlt() {

          // Check if MPU was already displayed and dismissed/reminded
          $delayed = get_option('insPP_delay_mpu', false);
          if ($delayed && intval($delayed) > time()) {
            return false;
          }

          $keys = ['optinmonster',
                   'justuno',
                   'holdonstranger',
                   'wisepops',
                   'sleeknote',
                   'popupmaker',
                   'getsitecontrol',
                   'sethspopupcreator',
                   'wppopupmanager',
                   'pippity',
                   'Sumome sumo',
                   'convertflow',
                   'privy',
                   'poptin',
                   'convertkit',
                   'cm-pop-up-banners',
                   'wordpress-popup',
                   'ninja-popups',
                   'popup-builder',
                   'plugins/popup-maker/',
                   'CcPopUp',
                   'popup-by-supsystic',
                   'responsive-lightbox-popup',
                   'ari-fancy-lightbox',
                   'exit-popup',
                   'icegram',
                   'alligator-popup',
                   'video-popup',
                   'easy-fancybox',
                   'eu-cookie-law',
                   'itro-popup',
                   'cookie-law-info',
                   'omniconvert',
                   'blockadblock',
                   'exitmist',
                   'appocalypsis',
                   'exitmonitor',
                   'picreel',
                   'hellobar',
                   'brightinfo',
                   'gdpr-cookie-compliance',
                   'popups',
                   'popupally',
                   'yith-woocommerce-popup',
                   'themify-popup',
                   'jazz-popups',
                   'provesource',
                   'smart-popup-blaster',
                   'popliup',
                   'cool-fade-popup',
                   'popup-seo-optimized',
                   'exit-intent-popups-by-optimonk',
                   'Elementor',
                   'yeloni-free-exit-popup',
                   'optin-spin',
                   'exit-pop',
                   'wp-exit-page-redirect',
                   'popupdomination',
                   'inkexit',
                   'thriveleads',
                   'wp-optin-wheel',
                   'mailoptin',
                   'omnisend',
                   'magnificPopup',
                   'mailchimp',
                   'popup-lifterapps',
                   'popupsmart',
                   'constantcontact',
                   'mailmunch',
                   'bloom',
                   'layeredpopups',
                   'convertplus'];

          if (get_option('insPP_body_alt_timeout', false) != false) {
            if (intval(get_option('insPP_body_alt_timeout')) > intval(time())) {
              return get_option('insPP_body_alt_timeout_res', false);
            }
          }

          $body = wp_remote_get(site_url(), [ 'httpversion' => '1.1', 'sslverify' => false, 'timeout' => 3 ]);
          if ($body && is_array($body) && isset($body['body'])) {

            $found = false;
            $res = str_replace("\n", "", $body['body']);
            $res = strtolower($res);

            for ($i = 0; $i < sizeof($keys); ++$i) {
              $key = strtolower($keys[$i]);
              if (strpos($res, $key) !== false) {
                $found = true;
                break;
              }
            }

            update_option('insPP_body_alt_timeout', strtotime($this->timeAfterBodySuccess));
            update_option('insPP_body_alt_timeout_res', $found);
            return $found;

          } else {

            update_option('insPP_body_alt_timeout', strtotime($this->timeAfterBodyFail));
            update_option('insPP_body_alt_timeout_res', false);
            return false;

          }

        }

        // Render BMI Banner
        private function renderBMI() {

          if (defined('insPP_bmiDisplayed')) return;
          else define('insPP_bmiDisplayed', true);

          require_once trailingslashit(__DIR__) . 'views/bmi.php';

        }

        // Render CDP Banner
        private function renderCDP() {

          if (defined('insPP_cdpDisplayed')) return;
          else define('insPP_cdpDisplayed', true);

          require_once trailingslashit(__DIR__) . 'views/cdp.php';

        }

        // Render MPU Banner
        private function renderMPU() {

          if (defined('insPP_mpuDisplayed')) return;
          else define('insPP_mpuDisplayed', true);

          require_once trailingslashit(__DIR__) . 'views/mpu.php';

        }

        // Debug function
        private function resetAllDB() {

          // delete_option('insPP_timeout');
          // delete_option('insPP_body_alt_timeout');
          // delete_option('insPP_body_alt_timeout_res');
          // delete_option('insPP_delay_cdp');
          // delete_option('insPP_delay_bmi');
          // delete_option('insPP_delay_mpu');

        }

        // It will make sure it won't show multiple times
        private function checkIfCanShow() {

          // Don't show it immidietaly
          if (!get_option('insPP_timeout', false)) update_option('insPP_timeout', strtotime($this->installationDelay));
          else if (intval(get_option('insPP_timeout')) > intval(time())) return false;

          // Don't even try to show it multiple times
          if (defined('insPP_initialized')) return false;
          else define('insPP_initialized', true);

          // Renders won't allow to show it multiple times via different plugins.
          if ($this->checkIfBMI()) return true;
          else if ($this->checkIfCDP()) return true;
          else if ($this->checkIfMPU()) return true;
          else if ($this->checkIfCDPAlt()) return true;
          else if ($this->checkIfMPU(true)) return true;
          else return false;

        }

        // Handler for JSON response (error)
        private function send_json_error($data = [], $durringInstall = false) {

          wp_send_json_error($data);

        }

        // Handler for JSON response (success)
        private function send_json_success($data = []) {

          update_option('insPP_timeout', strtotime($this->successDelay));
          wp_send_json_success($data);

        }

        // Install plugin (worker)
        private function finalInstallPlugin($plugin_zip = false, $upgrade = false) {

          // Include upgrader
          include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
          wp_cache_flush();

          // Initialize WP upgrader & install the plugin
          $upgrader = new \Plugin_Upgrader();
          if ($upgrade) $installed = $upgrader->upgrade($plugin_zip);
          else $installed = $upgrader->install($plugin_zip);

          // Return status or WP Error
          return $installed;

        }

        // Install plugin (handler)
        private function _install($directory_slug, $slug) {

          // Prepare the URLs and full slug
          $plugin_slug = $slug;
          $plugin_zip = 'https://downloads.wordpress.org/plugin/' . $directory_slug . '.latest-stable.zip';

          // Make sure the plugin is not installed
          if ($this->checkIfPluginsInstalled([$directory_slug])) {

            // Upgrade the plugin if it's installed somehow
            $this->finalInstallPlugin($plugin_slug, true);
            $installed = true;

            // Install instead
          } else $installed = $this->finalInstallPlugin($plugin_zip);

          // Check if there was any error
          if (!is_wp_error($installed) && $installed) {
            $activate = activate_plugin($plugin_slug);

            if (is_null($activate)) {

              $url = admin_url('', 'admin');

              // CDP has special alert when installed with quick-install module
              if ($directory_slug === 'copy-delete-posts') {
                update_option('_cdp_cool_installation', true);
                update_option('_cdp_redirect', true);
                update_option('_cdp_banner_installation', true);
                $url = admin_url() . 'admin.php?page=copy-delete-posts';
              }

              // Redirection for MPU
              if ($directory_slug === 'pop-up-pop-up') {
                update_option('wp_mypopups_do_activation_redirect', true);
                update_option('wp_mypopups_banner_installation', true);
                $url = admin_url() . 'admin.php?page=wp-mypopups';
              }

              // Redirection for BMI
              if ($directory_slug === 'backup-backup') {
                update_option('_bmi_redirect', true);
                update_option('_bmi_banner_installation', true);
                $url = admin_url() . 'admin.php?page=backup-migration';
              }

              // Send success
              $this->send_json_success([ 'url' => $url ]);

              // I don't know what happened here and if it's even possible
            } else $this->send_json_error([], true);

            // Send fail
          } else $this->send_json_error([], true);

        }

        // Dismiss plugin
        private function _dismiss($slug = false) {

          if ($slug) {

            $newDisplayCheck = strtotime($this->dismissDelay);
            $option = 'insPP_delay_' . $slug;
            update_option($option, $newDisplayCheck);
            update_option('insPP_timeout', strtotime($this->bannersDelay));

          }

        }

        // Remind plugin
        private function _remind($slug = false) {

          if ($slug) {

            $newDisplayCheck = strtotime($this->remindDelay);
            $option = 'insPP_delay_' . $slug;
            update_option($option, $newDisplayCheck);
            update_option('insPP_timeout', $newDisplayCheck);

          }

        }

        // Handle Ajax Actions
        public function ajaxHandler() {

          if (check_ajax_referer('invr_recommendation', 'nonce', false) === false) {
            return wp_send_json_error();
          }

          if (!current_user_can('install_plugins')) {
            return wp_send_json_error();
          }

          @error_reporting(0);

          $method = sanitize_text_field($_POST['method']);
          $classes = sanitize_text_field($_POST['classes']);

          if (strpos($classes, '-1') !== false) {

            if ($method == 'install') $this->_install('pop-up-pop-up', $this->mpu_slug);
            else if ($method == 'dismiss') $this->_dismiss('mpu');
            else if ($method == 'remind') $this->_remind('mpu');

          } else if (strpos($classes, '-2') !== false) {

            if ($method == 'install') $this->_install('backup-backup', $this->bmi_slug);
            else if ($method == 'dismiss') $this->_dismiss('bmi');
            else if ($method == 'remind') $this->_remind('bmi');

          } else if (strpos($classes, '-3') !== false) {

            if ($method == 'install') $this->_install('copy-delete-posts', $this->cdp_slug);
            else if ($method == 'dismiss') $this->_dismiss('cdp');
            else if ($method == 'remind') $this->_remind('cdp');

          } else $this->send_json_error();

          // Kill WordPress
          wp_die();

        }

        // Include scripts which handles the banner
        public function addScriptsAndStyles() {

          // Make sure something is rendered
          if (defined('insPP_renderScripts')) {

            // Make sure it won't run twice
            if (defined('insPP_scriptsLoaded')) return;
            else define('insPP_scriptsLoaded', true);

            // Load styles & script
            wp_enqueue_script('inisev-promotion-script', ($this->url . 'script.min.js'), [], '0.1.1');
            wp_enqueue_style('inisev-promotion-style', ($this->url . 'style.min.css'), [], '0.1.1');

            // Pass nonce to JS
            wp_localize_script('inisev-promotion-script', 'invr_recommendation', [
              'nonce' => wp_create_nonce('invr_recommendation'),
            ], true);

          }

        }

        // It will make sure it won't show multiple times
        public function renderBanner() {

          // Don't show it immidietaly
          if (!$this->checkSite()) return false;
          if (!get_option('insPP_timeout', false)) update_option('insPP_timeout', strtotime($this->installationDelay));
          else if (intval(get_option('insPP_timeout')) > intval(time())) return false;

          // Renders won't allow to show it multiple times via different plugins.
          if ($this->canRenderBMI) $this->renderBMI();
          else if ($this->canRenderCDP) $this->renderCDP();
          else if ($this->canRenderMPU) $this->renderMPU();

        }

      }

    }

  }
