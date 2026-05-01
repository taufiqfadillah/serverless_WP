<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'shopify' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

if ( !defined('WP_CLI') ) {
    define( 'WP_SITEURL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
    define( 'WP_HOME',    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] );
}



/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'AMC5nYLT5W6hxwwW9lBLeoh6hVvXnPOOhJPVErLjNTclb1xTHLfX8dx4qYaH3IC5' );
define( 'SECURE_AUTH_KEY',  'PMJzdjAjix7Ix54bYgiG1lKDOmT8zcUI3gkA3gOC88wsrfP5hqO6dauSylOnIjMT' );
define( 'LOGGED_IN_KEY',    'ywnVFjUlEEQCc7QrAF9GrkNvFBN2wkk1b3z163NZpEvr2g6kRvYCIjr3LNwpolLE' );
define( 'NONCE_KEY',        'eAkaj2yYTrOjjIarjfRWzzFEbA9Lz3qQuPpnlWn8HrrhMKBmySRcoJ1vtn1RHVYa' );
define( 'AUTH_SALT',        '2YEFyjzrmRkpwcrWQtNGiiTen7APqzbAsz4PcBJJZGtociCQ1FkGICknaHYtLpJb' );
define( 'SECURE_AUTH_SALT', 'auJXcIRdqub4c6gjkaPuDEBdp8DggkhjYEWWoBcFjQeOdfhFMMVo4td9zMKMnPJ8' );
define( 'LOGGED_IN_SALT',   'ZIpxQEITWz7JbNLriNkBvfpELMRUz5HMmSNUCcKpCrLv4yaiap4liIenvwlb83SL' );
define( 'NONCE_SALT',       'Xfrp6bXpFgANenc4533bYlAuNYeRHCN0o3sWry5CVsotyVzojrLcbpZ1q1oKB9yV' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
