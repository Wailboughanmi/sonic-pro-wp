<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sonicpro' );

/** Database username */
define( 'DB_USER', 'sonicpro' );

/** Database password */
define( 'DB_PASSWORD', 'sonicpro_secret' );

/** Database hostname */
define( 'DB_HOST', 'db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          'l(+/>&RpKzG0#9h>R+j*OaMzw hR)+vE#4mGM:lL/UIjd]O(D61ny&^%Q5K^*.Jv' );
define( 'SECURE_AUTH_KEY',   '~CuE{U1rm8KJ/xy+[k^(Y7O9%)X]S($WQilN,ZeW/)kQ}_(V,Pa-Q*a.lGm^vL8=' );
define( 'LOGGED_IN_KEY',     'X-/5GVW>O<c5dsOlQ;Gp~v.DG7&?+CFTt?I h1-L/be)*#7A{(&:,1*K`Et.G !9' );
define( 'NONCE_KEY',         'm?/DB8J*s6x4jCR26@X]1XveZ$4&7%zZ6-40b0nhR/{$O~hif*)1T.gTKtZ+|ww=' );
define( 'AUTH_SALT',         'nbOYH](z6(^rGI#jUaTc3&o/~`9l{<P+r97A0kQ_-k:PYy4}! F6Px:6ZWnovV_;' );
define( 'SECURE_AUTH_SALT',  'X+NYPn)NM`c4-}}o K$&kUI=r$|TPUa)z{.)$@JxwZh/`KKpeMYx!#0[X_a*S^6m' );
define( 'LOGGED_IN_SALT',    'x+f+aY&`7)2A.Ee/JhqJgV+R;uvvnCR~hwset1H[TLAz*a5>(XU^%^WgtdJWBfNK' );
define( 'NONCE_SALT',        'xI<52YZd<Y9uT6Q(fP{*K12SVulSp1t6T@f1N?7}XE~%BVdMZA1d EycUjsM;.6B' );
define( 'WP_CACHE_KEY_SALT', 'n!%UBjFQ=D/d L%g$T>Mw_2{ih;p>)[r#UP1#Ei 8W<kt>aF9K7E64#.boN-IsW(' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '2fc91e020ab06487a1ef6f07650845b3' );
define( 'WP_AUTO_UPDATE_CORE', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';