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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'db1');

/** MySQL database username */
define('DB_USER', 'tiptop');

/** MySQL database password */
define('DB_PASSWORD', '5642');

/** MySQL hostname */
//define('DB_HOST', 'db1.cityhost.com.ua');
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'y;|P2p42-X9|Lx,kyK*}vD= *#N,dm?}/Q^vyzqD_rO!lA@.dg}<j9;WjVHr`JQV');
define('SECURE_AUTH_KEY',  'BChnL%4:nYY;CyyRe*5T:3[t2y@*];Xb7G; 1?C3@g=P,>j`DZueTWP+ne.-7F]7');
define('LOGGED_IN_KEY',    'V)F,r*DA<:IuaO(F/!<{u>lCogt~l;X1PK-Pg|/#x_SK,)/GU6Hqn.!aFy/|,>+B');
define('NONCE_KEY',        '8JS+[($e$]bYDX.36h,:+GL~.~n{`N{3|55!G?ygk>~|TaEkcsbw4%+g!V?>gfU|');
define('AUTH_SALT',        ')I,2Em>naT1Vus$S^Y%}%+Sj2W>XA>LfV%R>GWmihO_xW-U+d(-.F!W@28`!NY:*');
define('SECURE_AUTH_SALT', '-jvk.Y7eDwsQd{)W@GzcO+vPVxU^pd3[A73!!P^v4Q`Fd#zk&BS=%o1A?FmjD:36');
define('LOGGED_IN_SALT',   'Oe<}&?O`QZBK]j6t&8J}|yVwyoc-P8PX(e!^v#q[/2QjC=i3]vL80Cg+a|sivMz`');
define('NONCE_SALT',       'vkQ|*BcufDkR%^%QHhi)>=_wUJF$x#qL|*}.c~pn%=c K<|x ZW!OeXxfvh+kHw0');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', '');

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
