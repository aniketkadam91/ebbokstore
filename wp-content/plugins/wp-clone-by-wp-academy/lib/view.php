<?php

// Exit on direct access
if (!defined('ABSPATH')) {
  exit;
}

?>

<script type="text/javascript">
    jQuery ( function($) {
        var clipboard = new ClipboardJS('.copy-button');
        clipboard.on('success', function(e) {
            if(e.text.length > 0){
                alert("URL has been copied successfully!");
            }
        });
    });

</script>

<?php
if (wpa_wpfs_init()) return;

if( false === get_option( 'wpclone_backups' ) ) wpa_wpc_import_db();
$backups = get_option( 'wpclone_backups' );
?>
<div id="search-n-replace">
    <a href="#" id="close-thickbox" class="button">X</a>
    <form name="searchnreplace" action="#" method="post">
        <table class="searchnreplace">
            <tr><th><label for="searchfor">Search for</label></th><td colspan="5"><input type="text" name="searchfor" /></td></tr>
            <tr><th><label for="replacewith">Replace with</label></th><td colspan="5"><input type="text" name="replacewith" /></td></tr>
            <tr><th><label for="ignoreprefix">Ignore table prefix</label></th><td colspan="2"><input type="checkbox" name="ignoreprefix" value="true" /></td></tr>
        </table>
        <input type="submit" class="button" name="search-n-replace-submit" value="Run">
    </form>
    <div id="search-n-replace-info"></div>
</div>

<div class="wpclonse_pre_backup_modal" style="display: none;" data-url="<?php echo admin_url(); ?>">
<?php
  include_once WPCLONE_ROOT_DIR_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'backupModal' . DIRECTORY_SEPARATOR . 'modal.php';
?>
</div>

<div id="wrapper">
    <!--<div class="plugin-large-notice">
        <div class="banner-1-collapsed" style="display:none; background-image: url('<?php /*echo plugins_url( 'lib/img/banner_bg_fold.jpg', __FILE__ )*/?>')">
            <p class="left-text"><strong>BIG NEWS:</strong> We want WP Clone to arise from the dead. <a href="#">Read more</a></p>
            <p class="remove-for-good">Remove for good (please first read it!)</p>
        </div>
        <div class="banner-1" style="background-image: url('<?php /*echo plugins_url( 'lib/img/banner_bg.jpg', __FILE__ )*/?>')">
            <div class="close-icon"><img src='<?php /*echo plugins_url( 'lib/img/banner_close_icon.png', __FILE__ )*/?>'> </div>
            <div class="heading">BIG NEWS: <strong>We want WP Clone to arise from the dead.</strong> Please help us!</div>
            <div style="margin-top: 27px; font-size: 20px; color: #3a3a3a">The key points in a nutshell:</div>
            <div class="nutshell-list">
                <ul>
                    <li>1.	New contributors have been added to the plugin, and with it comes new motivation to make it a kick-ass product!</li>
                    <li>2. 	Some fixes have been applied, the plugin now works in 90% of cases (and a further 9% if you follow the process as
                        outlined on the plugin page)</li>
                    <li>
                        3.	We want to revive the plugin, make it work in 100% of cases, and add many more features. As we’re short on cash,
                        we’re crowdfunding it, and need your help:
                        <ul style="margin-left: 30px;margin-top: 15px;">
                            <li>
                                a.	<span style="text-decoration: underline;">Contribution of 5 or 10 USD:</span> You get the warm fuzzy feeling from giving a sincere “Thank you” for a plugin which <br>
                                probably saved your butt a few times in the past, and helping to further develop it!
                            </li>
                            <li>
                                b.	<span style="text-decoration: underline;">Contributions of 15 USD+:</span> As in a), plus you will be rewarded with a <strong>free plugin license</strong> <br>
                                (for the premium product which we will create)
                            </li>
                            <li>
                                c.	<span style="text-decoration: underline;">Contributions of 50 USD+:</span>  As in a), plus an <strong>unlimited websites premium license.</strong> <br>
                                This a fantastic, one-time deal. The plugin will provide many more features <br>
                                - such as backup scheduling, backup to external servers etc.<br>
                                while still being super-easy to use!
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="banner-footer">
                The crowdfunding target is USD 3,000. If we don’t reach it you’ll be refunded*. <br>
                Thank you for your support - we really depend on it!
            </div>
            <div style="margin-top: 33px;">
                <a href="#" class="button1">Contribute</a>
                <a href="#" class="button1"> Contribute & ger free license(s)</a>
            </div>
            <p style="margin-top: 33px;">
                Also check out the updated plugin description.
            </p>
            <p style="margin-top: 33px; color: #0f9087">
                *With the exception of the 5 or 10 USD amounts. We want you to have that warm fuzzy feeling forever ;)
            </p>
        </div>
    </div>-->
<div id="MainView">

    <h2>Welcome to WP Clone, by <a href="http://wpacademy.com">WP Academy</a></h2>

    <p>You can use this tool to create a backup of this site and (optionally) restore it to another server, or another WordPress installation on the same server.</p>

    <p><strong>Here is how it works:</strong> the "Backup" function will give you a URL that you can then copy and paste
        into the "Restore" dialog of a new WordPress site, which will clone the original site to the new site. You must
        install the plugin on the new site and then run the WP Clone > Restore function.</p>
    <p><b>Attention:</b> The restore process will fail on approximately 10% of installations. PLEASE read the <a href="https://wordpress.org/plugins/wp-clone-by-wp-academy/" target="_blank">plugin page</a> for more information. Only restore on a clean slate site.</p>

    <p><strong>Choose your selection below:</strong> either create a backup of this site, or choose which backup you
        would like to restore.</p>

    <p>&nbsp;</p>

    <form id="backupForm" name="backupForm" action="#" method="post">
<?php
    if ( isset($_GET['mode']) && 'advanced' == $_GET['mode'] ) { ?>
        <div class="info width-60">
            <table>
                <tr align="left"><th colspan=""><label for="zipmode">Alternate zip method</label></th><td colspan="2"><input type="checkbox" name="zipmode" value="alt" /></td></tr>
                <tr align="left"><th><label for="use_wpdb">Use wpdb to backup the database</label></th><td colspan="2"><input type="checkbox" name="use_wpdb" value="true" /></td></tr>
                <tr align="left"><th><label for="ignore_prefix">Ignore table prefix</label></th><td colspan="2"><input type="checkbox" name="ignore_prefix" value="true" /></td></tr>
                <tr>
                    <td colspan="4">
                        <p>If enabled during a backup, all the tables in the database will be included in the backup.</br>
                        If enabled during a restore, search and replace will alter all the tables in the database.</br>
                        By default, only the tables that share the wordpress table prefix are included/altered during a backup/restore.</p>
                    </td>
                </tr>
                <tr align="left"><th><label for="mysql_check">Refresh MySQL connection during Restore</label></th><td colspan="2"><input type="checkbox" name="mysql_check" value="true" /></td></tr>
                <tr>
                    <td colspan="4">
                        <p>This will check the MySQL connection inside the main loop before each database query during restore. Enable this if the restored site is incomplete.</p>
                    </td>
                </tr>
                <tr><td colspan="4"><h3>Overriding the Maximum memory and Execution time</h3></td></tr>
                <tr><td colspan="4"><p>You can use these two fields to override the maximum memory and execution time on most hosts.</br>
                            For example, if you want to increase the RAM to 2GB, enter <code>2048</code> into the Maximum memory limit field.</br>
                            And if you want to increase the execution time to 15 minutes, enter <code>900</code> into the Script execution time field.</br>
                            Default values will be used if you leave them blank. The default value for RAM is 1024MB and the default value for execution time is 600 seconds (ten minutes).</p></td></tr>
                <tr align="left"><th><label for="maxmem">Maximum memory limit</label></th><td colspan="2"><input type="text" name="maxmem" /></td></tr>
                <tr align="left"><th><label for="maxexec">Script execution time</label></th><td><input type="text" name="maxexec" /></td></tr>
                <tr><td colspan="4"><h3>Exclude directories from backup, and backup database only</h3></td></tr>
                <tr><td colspan="4"><p>Depending on your web host, WP Clone may  not work for large sites.
                            You may, however, exclude all of your 'wp-content' directory from the backup (use "Backup database only" option below), or exclude specific directories.
                            You would then copy these files over to the new site via FTP before restoring the backup with WP Clone.</p>
                        <p>You could also skip files that are larger than the value entered into the below field. For example, enter <code>100</code> if you want to skip files larger than 100MB.
                            The default value of 25MB will be used If you leave it blank. Enter <code>0</code> if you want to disable it.</p></td></tr>
                <tr align="left"><th><label for="dbonly">Backup database only</label></th><td colspan="2"><input type="checkbox" name="dbonly" value="true" /></td></tr>
                <tr align="left"><th><label for="skipfiles">Skip files larger than</label></th><td><input type="text" name="skipfiles" />&nbsp;<strong>MB</strong></td></tr>
                <tr align="left"><th><label for="exclude">Excluded directories</label></th><td><textarea cols="70" rows="5" name="exclude" ></textarea></td></tr>
                <tr><th></th><td colspan="5"><p>Enter one per line, i.e.  <code>uploads/backups</code>,use the forward slash <code>/</code> as the directory separator. Directories start at 'wp-content' level.</br>
                </br>For example, BackWPup saves its backups into <code>/wp-content/uploads/backwpup-abc123-backups/</code> (the middle part, 'abc123' in this case, is random characters).
                If you wanted to exclude that directory, you have to enter <code>uploads/backwpup-abc123-backups</code> into the above field.</p></td></tr>
            </table>
        </div>
<?php
}
?>
        <strong>Create Backup</strong>
        <input id="createBackup" name="createBackup" type="radio" value="fullBackup" checked="checked"/><br/><br/>

        <?php if( false !== $backups && ! empty( $backups ) ) : ?>

        <div class="try">

            <table class="restore-backup-options">

            <?php

                foreach ($backups AS $key => $backup) :

                $filename = convertPathIntoUrl(WPCLONE_DIR_BACKUP . $backup['name']);
                $url = wp_nonce_url( get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wp-clone&del=' . $key, 'wpclone-submit');

            ?>
                <tr>
                    <th>Restore backup</th>

                    <td><input class="restoreBackup" name="restoreBackup" type="radio" value="<?php echo $filename ?>" /></td>

                    <td>
                        <a href="<?php echo $filename ?>" class="zclip"> (<?php echo bytesToSize($backup['size']);?>)&nbsp;&nbsp;<?php echo $backup['name'] ?></a>
                        <input type="hidden" name="backup_name" value="<?php echo $filename ?>" />
                    </td>
                    <?php
                        if( isset( $backup['log'] ) ){
                            printf( '<td><a href="%s">log</a></td>', convertPathIntoUrl(WPCLONE_DIR_BACKUP . $backup['log'] ) );
                        } else {
                            echo '<td>&mdash;</td>';
                        }
                    ?>
                    <td><a class="copy-button" href="#" data-clipboard-text="<?php echo $filename ?>" >Copy URL</a></td>
                    <td><a href="<?php echo $url; ?>" class="delete" data-fileid="<?php echo $key; ?>">Delete</a></td>

                </tr>

                <?php endforeach ?>

            </table>
        </div>

        <?php endif ?>

        <strong>Restore from URL:</strong><input id="backupUrl" name="backupUrl" type="radio" value="backupUrl"/>

        <input type="text" name="restore_from_url" class="Url" value="" size="80px"/><br/><br/>

        <div class="RestoreOptions" id="RestoreOptions">

            <input type="checkbox" name="approve" id="approve" /> I AGREE (Required for "Restore" function):<br/>

            1. You have nothing of value in your current site <strong>[<?php echo site_url() ?>]</strong><br/>

            2. Your current site at <strong>[<?php echo site_url() ?>]</strong> may become unusable in case of failure,
            and you will need to re-install WordPress<br/>

            3. Your WordPress database <strong>[<?php echo DB_NAME; ?>]</strong> will be overwritten from the database in the backup file. <br/>

        </div>

        <input id="submit" name="submit" class="btn-primary btn wpclone_pre_backup_made" type="submit" value="Create Backup"/>


    <?php wp_nonce_field('wpclone-submit')?>
    </form>
    <?php
        if(!isset($_GET['mode'])){
            $link = admin_url( 'admin.php?page=wp-clone&mode=advanced' );
            echo "<p style='padding:5px;'><a href='{$link}' style='margin-top:10px'>Advanced Settings</a></p>";
        }


        echo "<p><a href='#' id='dirscan' class='button' style='margin-top:10px'>Scan and repopulate the backup list</a>"
        . "</br>Use the above button to refresh your backup list. It will list <em>all</em> the zip files found in the backup directory, it will also remove references to files that no longer exist.</p>";

        wpa_wpc_sysinfo();

        echo "<p><a href='#' id='uninstall' class='button' style='margin-top:10px'>Delete backups and remove database entries</a>"
        . "</br>WP Clone does not remove backups when you deactivate the plugin. Use the above button if you want to remove all the backups.</p>";

        $tifm_disabled = 'false';
        if (get_option('_tifm_feature_enabled') === 'disabled') {
          $tifm_disabled = 'true';
        }

        $tifm_scrollTo = false;
        if (isset($_GET['scrollToSection']) && $_GET['scrollToSection'] === 'testPlugins') {
          $tifm_scrollTo = true;
        }

        ?>

        <div id="tryOutPluginsBeforeInstall" style="margin-bottom: 20px;">
          <h2 style="margin-bottom: 2px;">
            <b><?php _e('Test new plugins before installing:', 'wp-clone-by-wp-academy'); ?></b>
          </h2>
          <div style="margin-left: 10px;">
            <div style="margin-bottom: 5px;">
              <?php _e('If this feature is activated, you’ll see “Try it out”-buttons on the screen where you can', 'wp-clone-by-wp-academy'); ?> <a href="<?php echo admin_url('plugin-install.php') ?>"><?php _e('add new plugins', 'wp-clone-by-wp-academy'); ?></a>.<br />
              <?php
              $tifm_translated = __('Clicking on it will spin up a new WordPress instance with the respective plugin installed. Powered by %s.', 'wp-clone-by-wp-academy');
              echo str_replace('%s', '<a href="https://tastewp.com" target="_blank">TasteWP</a>', $tifm_translated);
              ?>
            </div>
            <label for="allow_to_show_yes" style="line-height: 30px;">
              <input id="allow_to_show_yes" type="radio" name="allow_to_show" value="true"<?php echo ($tifm_disabled === 'false') ? 'checked' : '' ?>><?php _e('Yes', 'wp-clone-by-wp-academy'); ?>
            </label>
            <div style="display: inline-block; width: 30px;"></div>
            <label for="allow_to_show_no" style="line-height: 30px;">
              <input id="allow_to_show_no" type="radio" name="allow_to_show" value="false"<?php echo ($tifm_disabled === 'true') ? 'checked' : '' ?>><?php _e('No', 'wp-clone-by-wp-academy'); ?>
            </label>
            <div style="display: inline-block; width: 30px;"></div>
            <button id="save_tifm_decision" type="button" class="button button-primary" name="button"><?php _e('Save', 'wp-clone-by-wp-academy'); ?></button>

            <div id="tifm_success_save" style="display: none;color: green;">
              <?php _e('Settings saved.', 'wp-clone-by-wp-academy'); ?>
            </div>
            <div id="tifm_error_save" style="display: none;color: red;">
              <?php _e('Error during settings save.', 'wp-clone-by-wp-academy'); ?>
            </div>
          </div>
        </div>

        <script type="text/javascript">
          jQuery('#save_tifm_decision').on('click', () => {
            let nonce = "<?php echo wp_create_nonce('tifm_notice_nonce') ?>";
            jQuery.post(ajaxurl, { action: 'tifm_save_decision', nonce: nonce, decision: jQuery('input[name="allow_to_show"]:checked').val() }).done(() => {
              jQuery('#tifm_success_save').show(300);
              setTimeout(function () {
                jQuery('#tifm_success_save').hide(300);
              }, 1500);
            }).fail(() => {
              jQuery('#tifm_error_save').show(300);
              setTimeout(function () {
                jQuery('#tifm_error_save').hide(300);
              }, 1500);
            });
          });
          <?php if ($tifm_scrollTo === true) { ?>
            setTimeout(function () {
              document.querySelector('#tryOutPluginsBeforeInstall').scrollIntoView({behavior: "smooth", block: "center", inline: "nearest"});
              setTimeout(function () {
                jQuery('#tryOutPluginsBeforeInstall h2 b').animate({'font-size': '28px'}, 300, function () {
                  jQuery('#tryOutPluginsBeforeInstall h2 b').animate({'font-size': '16.89px'}, 300);
                });
              }, 100);
            }, 300);
          <?php } ?>
        </script>

        <hr>

        <?php

        echo '<p><a href="#TB_inline?height=200&width=600&inlineId=search-n-replace&modal=true" class="thickbox">Search and Replace</a></p>';



    ?>
</div>
<div id="sidebar">

		<ul>
			<h2>Use WP Academy’s Transfer Service</h2>
      <p>Save time and avoid headaches with WP Academy’s <a target="_blank" href="https://sellcodes.com/TGTMMjQC">Premium Transfer Service.</a></p>
		</ul>

		<ul>
			<h2>Help & Support</h2>
            <p>If you face any issues, we’re very happy to answer your questions in the <a href="http://wordpress.org/support/plugin/wp-clone-by-wp-academy" target="_blank" title="Support Forum">Support Forum</a>. <br><br>
                We still have to catch up on the old support threads, however we’ll treat new questions with a high priority! :)</p>
		</ul>

	</div>
</div> <!--wrapper-->
<p style="clear: both;" ></p>
<?php
    do_action('wp_clone_accessor_print');
    function wpa_wpc_sysinfo(){
        global $wpdb;
        echo '<div class="info width-60">';
        echo '<h3>System Info:</h3><p>';
        echo 'Memory limit: ' . ini_get('memory_limit');
        if( false === ini_set( 'memory_limit', '257M' ) ) {
            echo '&nbsp;<span style="color:#660000">memory limit cannot be increased</span></br>';
        } else {
            echo '</br>';
        }
        echo 'Maximum execution time: ' . ini_get('max_execution_time') . ' seconds</br>';
        echo 'PHP version : ' . phpversion() . '</br>';
        echo 'MySQL version : ' . $wpdb->db_version() . '</br>';
        if (ini_get('safe_mode')) { echo '<span style="color:#f11">PHP is running in safemode!</span></br>'; }
        printf( 'Root directory : <code>%s</code></br>', WPCLONE_ROOT );
        if ( ! file_exists( WPCLONE_DIR_BACKUP ) ) {
            echo 'Backup path :<span style="color:#660000">Backup directory not found. '
            . 'Unless there is a permissions or ownership issue, refreshing the backup list should create the directory.</span></br>';
        } else {
            echo 'Backup directory : <code>' . WPCLONE_DIR_BACKUP . '</code></br>';
        }
        echo 'Files : <span id="filesize"><img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '"></span></br>';
        if ( file_exists( WPCLONE_DIR_BACKUP ) && !is_writable(WPCLONE_DIR_BACKUP)) { echo '<span style="color:#f11">Backup directory is not writable, please change its permissions.</span></br>'; }
        if (!is_writable(WPCLONE_WP_CONTENT)) { echo '<span style="color:#f11">wp-content is not writable, please change its permissions before you perform a restore.</span></br>'; }
        if (!is_writable(wpa_wpconfig_path())) { echo '<span style="color:#f11">wp-config.php is not writable, please change its permissions before you perform a restore.</span></br>'; }
        echo '</p></div>';
    }

/** it all ends here folks. */

echo "<br /><br /><br />";
do_action('ins_global_print_carrousel');
