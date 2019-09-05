<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

require_once(__DIR__ . '/vendor/autoload.php');
(new \Dotenv\Dotenv(__DIR__))->load();

define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASSWORD', getenv('DB_PASSWORD'));
define('DB_HOST', getenv('DB_HOST'));

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE',false); // true // Changed by WP-Staging //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/home/bigbio/big-bio.org/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '_|jI/NUda2X/Qt@8O7W:vmZTKA+8G`3CN?g(cnw20)Hl:#gViE;A_4SjYHRmbCu"');
define('SECURE_AUTH_KEY',  'Q;X)M_2O"00QhdD6?A`fuu1|LcFHU#3;tnU?kmqSR9?gvi@cQSkXFU^vMky0ej;x');
define('LOGGED_IN_KEY',    'r(K^(Ax&OKttQr(jD`S431#0L+1OkJV;qYn4^OuHY)#Si~:)RR*cg)7c;ExIYAc^');
define('NONCE_KEY',        '4/R"iyJLe*VIP|eid^BvZXjj#KwxT:E%T+e8tQf$:dG?9In""DF0``3Y~Kp1gr%0');
define('AUTH_SALT',        'T6L$:I2;qCV$40e9~lkmfnuWXdTtD+8t|m|A&A?ec2Y^$Jm*1K33B:db+W_&6m|d');
define('SECURE_AUTH_SALT', 'XT":(|LrTrf;B"QH&sKcMyHAr;gn|PGLEf*/wIkZZ5/4(|:wIh(sHNxdHF~(pv%M');
define('LOGGED_IN_SALT',   '$^H*hf!mY&R3Xa)?L$A+r/GoVHLr*7dTmZV:L7hR9|S`8bQcr/KvVQ`dZ*@?m$if');
define('NONCE_SALT',       'qG#0Y(B:JcoLAyi74;;_2AOm!NviRYQ`Cg+4?7Bm#q7HPR4rVz6gv*&nXy&%oCW9');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpstg1_';//  = 'wp_6tj6vd_';

/**
 * Limits total Post Revisions saved per Post/Page.
 * Change or comment this line out if you would like to increase or remove the limit.
 */
define('WP_POST_REVISIONS',  10);

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

