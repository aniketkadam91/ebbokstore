=== Clone ===
Contributors: migrate, nick843
Tags: migrate, clone, backup, migration, backups, copy, restore, recover, restoration, duplicate
Author URI: https://backupbliss.com
Requires PHP: 5.5
Requires at least: 3.3
Tested up to: 6.4.2
Stable tag: 2.4.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

100% FREE clone and migration

== Description ==

**Try it out on your free dummy site: Click here => [https://tastewp.com/plugins/wp-clone-by-wp-academy](https://tastewp.com/plugins/wp-clone-by-wp-academy).**
(this trick works for all plugins in the WP repo - just replace "wordpress" with "tastewp" in the URL)

WP Clone is a great way to backup, migrate or clone a WordPress site to another domain or hosting server.

You can also use it to backup, migrate or clone your site to/from local server hosting, to create backup of your site for development or testing purposes, and to install pre-configured backups of WordPress.

WP Clone is a superior to other backup & migrate plugins for the following reasons:

* It does not require FTP access to backup files you migrate or clone, neither the source or destination site; just install a new WordPress on the destination site, install our backup plugin, and follow the prompts to migrate or clone your site.
* It does not backup or restore the WordPress system files (it just creates user content and database backups); reducing upload time for migration and improving security of your site
* It fetches the site backup via your host's direct http connection, which saves you from having to upload large backup files, making it easier to migrate.

= What are today's limitations? =

Today:

- 90% of cases: Backups & migrations work flawlessly (we fixed some key bugs in the most recent version)
- 9% of cases: Backups or migrations fail due to your hoster's configurations (most likely limits in up- and downloads) which is typically the case when you backup or migrate very large sites. However, there's a workaround: simply do a “Database Only” backup (use “Advanced Settings”), transfer the wp-content directory over with FTP, and then restore new site. Then backup and migration also works.
- 1% of cases: Your site/hosting is abnormal (pardon our French) and backup or migration doesn't work. However: that's what we'll now be working on, so that eventually backups and migrations will work in all cases.

The 1% case means:

- Basic rule: DO NOT use it as your only backup solution! Only use it for migrations (so that if something fails, you still have the files on your old site as backup).
- If you want to use it as backup, test it by restoring the backup file on a new site. If that works fine you should be safe.
- In any case, we cannot take any responsibility if backup or migration fails.

Note:
* There is never an issue in damaging the source installation (i.e. on the site where you create the backup). So backup sites at your pleasure. If your backup succeeds then chances are good that the migration (i.e. restore on another site) will also succeed. But don't take any chances.
* If backup or migration (restore) fails, just try it again. Often it works on second attempt.

= Other tips how to backup and migrate =

* NEVER overwrite an installation for which you do not have an alternate backup source (e.g. a cPanel backup). Normally you would restore the backup onto a fresh WP installation on another host or on a subdomain. If the restore fails your destination site might become unusable, so be prepared to enter cPanel and then destroy / recreate the new installation if necessary.
* DO NOT use our backup plugin on WP Engine or any hosting system with proprietary operating system. Instead, use their built-in backup tools.
* Large sites (>2GB) might take as long as an hour to backup and migrate. Sites of 250 MB or less should take no more than a minute or two to backup, depending on your server.
* We recommend you deactivate and delete page caching, security and maybe redirection plugins before you migrate, and re-install them on the new site, if necessary. In general, delete all unnecessary plugins and data from your site before you backup. You can also use the "Exclude directories" option if you have large media files, which you can then copy back to the new site with FTP.
* How to copy from local server to your hosted website: Create a backup of the local site in the usual way, then save the backup file (right-click > Save) to your local disk. Upload this file to the root directory of your destination website and then use this url in the “Restore” dialog of the new site: http://yourdomain.com/<name of the backup file.zip>.

= Help Video =

[youtube http://www.youtube.com/watch?v=xN5Ffhyn4Ao]

= Credits =
WP Clone uses functions from the "Safe Search and Replace on Database with Serialized Data" script first written by David Coveney of Interconnect IT Ltd (UK) http://www.davidcoveney.com or http://www.interconnectit.com and released under the WTFPL http://sam.zoy.org/wtfpl/. Partial script with full changelog is placed inside 'lib/files' directory.

This plugin is part of the Inisev product family - [check out our other products](https://inisev.com).


== Frequently Asked Questions ==

= How to create a website backup first? =

Once you have the Clone plugin installed on your site, navigate to the Clone plugin menu within the admin dashboard, then click on the “Create Backup” button, and select the option with the Clone plugin, if you don’t want to proceed with a modernized [Backup Migration](https://wordpress.org/plugins/backup-backup/) plugin.

Clone plugin will by default create a backup that contains everything from your site, except the Clone plugin’s own backups.

You can download backup or migrate your backup (to clone the site) immediately after the backup has been created.

= How do I restore a backup? =

- If your backup file is already **located on your site**: Go to the Clone plugin menu screen, tick the backup file that you want to restore, tick the checkbox “I agree”, and finally on the button “Restore Backup”.

- If your backup file is **located on another site**: Go to the Clone plugin menu screen on the source site, and click on the “Copy URL” button next to the backup file name. After that, go to the Clone plugin screen on the destination site, tick the “Restore from URL” checkbox, paste the copied link, tick the “I agree” checkbox, and hit the “Restore from URL” button. This process will first import the backup and then restore it, i.e. Clone plugin also serves as a backup importer.

- If your backup file is *located on another device*: Go to the Clone plugin screen on the source site where you have created a backup, and click on the backup name to download the backup file. Upload the backup file to the destination site, the default folder location is  wp-content/uploads/wp-clone/. After that, go to the Clone plugin screen and click on the “Scan and repopulate the backup list” button. In a moment, your uploaded backup file should appear in the list within this menu screen. Tick the boxes and start the restoration by clicking on the “Restore Backup” button.

= How do I migrate or clone my site? =

Migrate (or clone) a WordPress site by creating a full backup on the site that you want to migrate (clone) - site #1.

- To transfer website **directly from site #1 to site #2**: Go to the Clone plugin screen on site #1, where you have the backups listed, and click on the Copy URL button. Go to the Clone plugin screen on site #2, click on “Restore from URL”, paste the copied link, and hit the “Restore from URL” button. Make sure that the backup file on site #1 is accessible (the site is online, etc.)

- To migrate the website **indirectly**: Go to the Clone plugin screen, and upload the backup file to /wp-content/uploads/wp-clone/. After the upload, head back to Clone plugin menu, rescan the backup list, and continue with the restore of the specific backup file.

= Where can I find my backups? =

Clone plugin allows you to download backups, migrate backups, or delete backups directly from the plugin screen Manage & Restore Backup(s). By default, the migrator plugin will store a backup to **/wp-content/uploads/wp-clone/** and you should upload Clone backups to that folder too.

= How to set up automatic backups? =

Enabling automatic backups is not available in the Clone plugin, for that, we recommend the new [Backup Migration](https://wordpress.org/plugins/backup-backup/) plugin. There, auto backup can run on a monthly, weekly, or daily basis. You can set the exact time (and day) and how many automatic backups would should be kept.

= How big are backup files? =

Backup file size depends on what is included in the backup file. For example, if it is only a database backup, the backup file will usually be just as few megabytes. Usually, WordPress’ Uploads folder is the heaviest, while Databases are the lightest. If you are looking to save up space, you might want to exclude some folders from the backup. You can do that in the Advanced Settings within the Clune plugin menu screen.

= Is the backup creation and site migration free? =

Yes. You can create full site backups and clone your site (duplicate site) free of charge. [Backup Migration Pro](https://sellcodes.com/oZxnXtc2) provides more sophisticated filters and selections of files that will be included/excluded from backups (affecting backup size), faster backup creation times, number of external backup storage locations, backup encryption, backup file compression methods, advanced backup triggers, additional backup notifications by email, priority support, and more.

= How to create staging sites? =

To avoid manual work, such as setting up a fresh WordPress instance or a subdomain yourself, we recommend the new [Backup Migration](https://wordpress.org/plugins/backup-backup/) plugin. You can easily set up a staging environment for your website with the BackupBliss plugin. You can choose to create a staging site either on your server/machine or on [TasteWP](https://tastewp.com/). Both options are free and super-easy to use!

= Is cloud backup available? =

Clone plugin has no features to sync to the cloud. For that, we recommend [BackupBliss - Backup Migration Staging Pro](https://sellcodes.com/oZxnXtc2).

= How to upload my backup file? =

Uploading a backup can be simply done via FTP or a simple plugin such as WP File Manager. Just navigate to the folder on your site /wp-content/uploads/wp-clone/ and drag-and-drop the backup file there. Remember to re-scan the folder from the Clone plugin menu, and the backup should appear on the list.

= How to backup database only? =

You can back up the website database only, if you navigate to the Advanced Settings section of the Clone plugin menu, and tick the box “Backup database only”. WordPress database is usually small so DB backup is done quickly.


== Installation ==

1. Navigate to Plugins > Add New
2. Search for "WP Clone by WP Academy"
3. Install and activate the backup and migration plugin
4. Follow remaining instructions in the help video

== Frequently Asked Questions ==
Backup and migration FAQ are under construction

== Changelog ==

= 2.4.4 =
* [NOTE] Upgraded "TryItOut" module to latest version
* [NOTE] Upgraded analyst module to latest version

= 2.4.3 =
* Improved plugin security 
* Tested with WordPress 6.4.2
* Tested with PHP 8.3

= 2.4.2 =
* Fixed issue with rendering of external module

= 2.4.1 =
* Tested with WordPress 6.4-beta2 and PHP 8.2
* Updated all modules to support latest PHP versions
* Decreased amount of unwanted warnings and notices in PHP 8.2
* Updated readme details and added more FAQ answers

= 2.4.0 =
* Forced "Try it out" module to be disabled by default, user can still enable it manually.

= 2.3.9 =
* Tested with WP 6.3 RC
* Updated all shared modules to their latest versions

= 2.3.8 =
* Fixed fatal error while site was unable to send external requests
* Adjusted old version databases compatibility
* Added nonce verification for particular option
* Removed unused modules
* Updated carrousel module
* Tested with WP 6.2

= 2.3.7 =
* Adjusted PHP compatibility

= 2.3.6 =
* Added black-friday theme (only for that period)
* Tested up to WordPress 6.1.1

= 2.3.5 =
* Fully tested with WordPress 6.1-RC5
* Added support for PHP 8.1
* Removed deprecated translation method (domain path)

= 2.3.4 =
* Fully tested with WordPress 6.0.2
* NEW: Added feature that allows to try plugins before installation

= 2.3.3 =
* Added support for WordPress 5.8.1
* Added new modals
* Removed notices from error logging

= 2.3.2 =
* Added support for WordPress 5.8

= 2.3.1 =
* Updated information banner

= 2.3.0 =
* Added support for PHP 8.0
* Added support for WordPress 5.6
* Removed outdated banners
* Fixed deprecated functions
* Fixed some security issues

= 2.2.10 =
* Updated links

= 2.2.9 =
* Updated feedback version
* Updated migration texts

= 2.2.8 =
* Updated migration texts

= 2.2.7 =
* Updated backup plugin pic
* Updated backup texts

= 2.2.6 =
* Integrated feedback system

= 2.2.5 =
* Major bug that % got hashed fixed
* Other basic bug fixes (e.g. backups and migrations didn’t work on new domains)
* Texts in plugin updated, broken links removed

= 2.2.4 =
* Updated: `Tested up to` tag for backup and migration

= 2.2.3 =
* Added: PHP7 support of backup and migration
* Added: a multisite check during restore (not required at time of backup)
* Fixed: failed backups due to unreadable files

= 2.2.1 =
* Fixed: Backup names will use the time zone selected in general settings
* Added: basic backup and migration logs
* Added: An option to exclude files in backup and migration based on size (files larger than 25MB will be excluded by default for backups and migration)
* Added: An option to ignore the wordpress table prefix during backup and migration
* Added: An option to check the mysql connection during migration (restore)
* Changed: Files are no longer copied to a temporary location during backup
* Changed: siteurl option is updated during the database import

= 2.2 =
* Fixed: Missing backups that some users encountered after upgrading to 2.1.9
* Added: An option to refresh the backup list
* Added: An option to remove the database entry and delete all the backup files
* Added: A section that shows the uncompressed database size and the uncompressed size and the number of files that will be archived during a full backup
* Added: Notes in the advanced settings section regarding the Maximum memory limit and the Script backup execution time fields.
* Added: The report returned from the search and replace process into the restore successful page
* Changed: Moved the backup list location from the custom table to the wp_options table. (previous backups will be imported and the custom table will be removed on existing installations)
* Changed: Only the tables with the wordpress table prefix will be altered during a restore
* Changed: Only the tables with the wordpress table prefix will be saved during a backup
* Changed: Backup deletion is now handled using AJAX

= 2.1.6 =
* Added: An option to exclude specific directories during backup
* Added: An option to only backup the database
* Changed: File operations during backup are now handled directly instead of using the WP filesystem abstraction class

= 2.1.4 =
* Fixed: When javascript is disabled,submit button shows "Create Backup" but the plugin attempts to do a restore
* Changed: The temporary directory location during the backup restore process from '/wp-content/' to '/wp-content/wpclone-temp/'

= 2.1.3 =
* Added: An option to backup the database using WordPress' WPDB class
* Removed: The need to keep the original backup names intact
* Changed: The backup name structure
* Changed: Backup file downloads are now handled using WP core functions

= 2.0.2 =
* Initial release

== Screenshots ==
1. Configuration Page

== Upgrade Notice ==
= 2.4.4 =
* [NOTE] Upgraded "TryItOut" module to latest version
* [NOTE] Upgraded analyst module to latest version