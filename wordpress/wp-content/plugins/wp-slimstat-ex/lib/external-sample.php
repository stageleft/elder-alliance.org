<?php
/* External Track Config
---------------------------------------------------------------*/
// ** MySQL settings ** //
$slimtrack_ext = array();
$slimtrack_ext['DB_NAME'] = 'databasename';    // The name of the database
$slimtrack_ext['DB_USER'] = 'username';     // Your MySQL username
$slimtrack_ext['DB_PASSWORD'] = 'password'; // ...and password
$slimtrack_ext['DB_HOST'] = 'localhost';    // 99% chance you won't need to change this value
if(!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
if(!defined('DB_COLLATE')) define('DB_COLLATE', '');

// Change SECRET_KEY to a unique phrase.  You won't have to remember it later,
// so make it long and complicated.  You can visit http://api.wordpress.org/secret-key/1.0/
// to get a secret key generated for you, or just make something up.
if(!defined('SECRET_KEY')) define('SECRET_KEY', 'put your unique phrase here'); // Change this to a unique phrase.

// You can have multiple installations in one database if you give each a unique prefix
$slimtrack_ext['table_prefix']  = 'wp_';   // Only numbers, letters, and underscores please!

/* That's all, stop editing! */

define('SLIMSTATPATH',  dirname( dirname( __FILE__ ) ) . '/');
define('SLIMSTAT_EXTRACK', true);
require_once(SLIMSTATPATH.'lib/external-inc.php');
?>