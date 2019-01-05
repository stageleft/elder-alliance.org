<?php // powered by K2 theme's js.php file header<http://getk2.com/>
$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
require_once($wp_config);
if(!defined('SLIMSTATPATH')) {die("Please activate Wp-SlimStat-Ex");}

$my_host = parse_url(get_option('home'));
if(strpos($_SERVER["HTTP_REFERER"], $my_host['host']) === false) {
	header("HTTP/1.1 403 Forbidden");
	die("Sorry, we do not allow direct or external access.");
}

require(SLIMSTATPATH . 'lib/modules.php');
$cachelimit = (int)$SlimCfg->option['cachelimit'];
if( $cachelimit > 0 ) {
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
$id = (int)$_GET["moid"]; 
$myFilterClause = SSFunction::filter_switch(); 
$myFilterClauseNoInt = SSFunction::filter_switch(false);

if($id < 100) {
	switch($id) {
		case 1:
			echo SSModule::_moduleSummary($myFilterClauseNoInt);
		break;
		case 2: 
			echo SSModule::_moduleRecentReferers($myFilterClause);
		break;
		case 3: 
			echo SSModule::_moduleRecentSearchStrings($myFilterClause);
		break;
		case 4: 
			echo SSModule::_moduleNewDomains($myFilterClause);
		break;
		case 5: 
			echo SSModule::_moduleRecentResources($myFilterClause);
		break;
		case 6: 
			echo SSModule::_moduleLast24Hours($myFilterClauseNoInt);
		break;
		case 7: 
			echo SSModule::_moduleDailyHits($myFilterClauseNoInt);
		break;
		case 8: 
			echo SSModule::_moduleWeeklyHits($myFilterClauseNoInt);
		break;
		case 9: 
			echo SSModule::_moduleMonthlyHits($myFilterClauseNoInt);
		break;
		case 10: 
			echo SSModule::_moduleTopResources($myFilterClause);
		break;
		case 11: 
			echo SSModule::_moduleTopSearchStrings($myFilterClause);
		break;
		case 12: 
			echo SSModule::_moduleTopLanguages($myFilterClause);
		break;
		case 13: 
			echo SSModule::_moduleTopDomains($myFilterClause);
		break;
		case 14: 
			echo SSModule::_moduleInternallyReferred($myFilterClause);
		break;
		case 15: 
			echo SSModule::_moduleTopInternalSearchStrings($myFilterClause);
		break;
		case 16: 
			echo SSModule::_moduleTopRemoteAddresses($myFilterClause);
		break;
		case 17: 
			echo SSModule::_moduleTopBrowsers($myFilterClause);
		break;
		case 18: 
			echo SSModule::_moduleTopPlatforms($myFilterClause);
		break;
		case 19: 
			echo SSModule::_moduleTopCountries($myFilterClause);
		break;
		case 20: 
			echo SSModule::_moduleTopReferers($myFilterClause);
		break;
		case 91: 
			echo SSModule::_moduleTopBrowsersOnly($myFilterClause);
		break;
		case 92: 
			echo SSModule::_moduleRecentRemoteip($myFilterClause);
		break;
		default:
			echo '<p>There is no such module('.$id.')</p>';
		break;
	}
} else {
	$pinid = floor($id/100) - 100;
	$mo = $id - (($pinid+100)*100) - 1;
	$mos = SSFunction::pin_mod_info($pinid);
	$pinName = $mos['name'];
	$file = SLIMSTATPATH . 'pins/'. $mos['name'] . '/pin.php';
	require_once(SLIMSTATPATH . 'lib/pins.php');
	require_once($file);
	eval('$'.$mos['name'].' =& new $pinName();'."\n");
	eval('echo $'.$mos['name'].'->'.$mos['modules'][$mo]["name"].'($myFilterClause);');
}
exit();
?>