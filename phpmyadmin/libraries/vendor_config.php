<?php
/**
 * File for vendor customization, you can change here paths or some behaviour,
 * which vendors such as Linux distributions might want to change.
 *
 * For changing this file you should know what you are doing. For this reason
 * options here are not part of normal configuration.
 */

declare(strict_types=1);

// phpcs:disable PSR1.Files.SideEffects
if (! defined('PHPMYADMIN')) {
    exit;
}
// phpcs:enable

/**
 * Path to vendor autoload file. Useful when you want to
 * have have vendor dependencies somewhere else.
 */
define('AUTOLOAD_FILE', './autoload.php');

/**
 * Directory where cache files are stored.
 */
define('TEMP_DIR', '/var/lib/phpmyadmin/tmp/');

/**
 * Path to changelog file, can be gzip compressed. Useful when you want to
 * have documentation somewhere else, eg. /usr/share/doc.
 */
define('CHANGELOG_FILE', '/usr/share/doc/phpmyadmin/changelog.gz');

/**
 * Path to license file. Useful when you want to have documentation somewhere
 * else, eg. /usr/share/doc.
 */
define('LICENSE_FILE', '/usr/share/doc/phpmyadmin/copyright');

/**
 * Directory where SQL scripts to create/upgrade configuration storage reside.
 */
define('SQL_DIR', ROOT_PATH . 'sql' . DIRECTORY_SEPARATOR);

/**
 * Directory where configuration files are stored.
 * It is not used directly in code, just a convenient
 * define used further in this file.
 */
define('CONFIG_DIR', '/etc/phpmyadmin/');

/**
 * Filename of a configuration file.
 */
define('CONFIG_FILE', CONFIG_DIR . 'config.inc.php');

/**
 * Filename of custom header file.
 */
define('CUSTOM_HEADER_FILE', CONFIG_DIR . 'config.header.inc.php');

/**
 * Filename of custom footer file.
 */
define('CUSTOM_FOOTER_FILE', CONFIG_DIR . 'config.footer.inc.php');

/**
 * Default value for check for version upgrades.
 */
define('VERSION_CHECK_DEFAULT', false);

/**
 * Path to files with compiled locales (*.mo)
 */
define('LOCALE_PATH', ROOT_PATH . 'locale' . DIRECTORY_SEPARATOR);

/**
 * Define the cache directory for routing cache an other cache files
 */
define('CACHE_DIR', ROOT_PATH . 'libraries' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

/**
 * Suffix to add to the phpMyAdmin version
 */
define('VERSION_SUFFIX', 'deb5ubuntu1');
