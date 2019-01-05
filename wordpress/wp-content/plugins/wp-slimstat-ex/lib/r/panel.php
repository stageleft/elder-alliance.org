<?php // powered by K2 theme's js.php file header<http://getk2.com/>
$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
require_once($wp_config);
if(!defined('SLIMSTATPATH')) {die("Please activate Wp-SlimStat-Ex");}

$my_host = parse_url(get_option('home'));
if(strpos($_SERVER["HTTP_REFERER"], $my_host['host']) === false) {
	header("HTTP/1.1 403 Forbidden");
	die("Sorry, we do not allow direct or external access.");
}

require(SLIMSTATPATH . 'lib/display.php');
$cachelimit = (int)$SlimCfg->option['cachelimit'];
if($cachelimit > 0) {
	// check to see if the user has enabled gzip compression in the WordPress admin panel
	if ( extension_loaded('zlib') and !ini_get('zlib.output_compression') and ini_get('output_handler') != 'ob_gzhandler' and ((version_compare(phpversion(), '5.0', '>=') and ob_get_length() == false) or ob_get_length() === false) ) {
		ob_start('ob_gzhandler');
	}

	header("Cache-Control: public");
	header("Pragma: cache");
	$offset = 60*$cachelimit;
	$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s",time() + $offset)." GMT";
	$LmStr = "Last-Modified: ".gmdate("D, d M Y H:i:s",filemtime(__FILE__))." GMT";
	header($ExpStr);
	header($LmStr);
}
header('Content-Type: text/html; charset: '.get_option('blog_charset').'');
SSDisplay::wp_slimstat_ajax_display(); 
exit();
?>