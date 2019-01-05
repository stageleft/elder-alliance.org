<?php // Powered by wordpress install tool.
$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
if (!file_exists($wp_config)) 
    die("There doesn't seem to be a <code>wp-config.php</code> file.");
if (!file_exists('_functions.php')) 
    die("There doesn't seem to be a <code>_functions.php</code> file.");

require_once($wp_config);
$SlimCfg->check_user();

require_once('_functions.php');
if(!isset($ssAdmin))
	$ssAdmin =& SSAdmin::get_instance();

if (isset($_GET['step']))
	$step = $_GET['step'];
else
	$step = 0;
$_go = $ssAdmin->_go();

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Wp-SlimStat-Ex &rsaquo; Admin', 'slimstat-admin'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />

</head>
<body>
<h1 id="logo"><img src="slimstat-logo.png" alt="slimstat" /></h1>
<?php 
switch($step) {
/*_________________________________CASE 0 */
	case 0:
		?>
<h1><?php _e('Wp-SlimStat ip-to-country update', 'slimstat-admin'); ?></h1>
<p><?php _e('This tool helps update ip-to-country database', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_countries; ?>)</p>
<?php 
	require_once(SLIMSTATPATH . '/lib/setup.php');
	$table_exists = SSSetup::_createSlimTable('country', true);
	if( !$table_exists ) {
?>
<p><?php _e('You don\'t have ip-to-country table', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_countries; ?>)<p>
<?php 
	} else {
?>
<p><?php _e('If you want to update country table, upload "ip-to-country.csv" file to <br />"wp-content/plugins/wp-slimstat-ex/lib/ss-admin" folder', 'slimstat-admin'); ?></p>
<p><?php _e('You also can use GeoIPCountryWhois.csv file from <a href="http://www.maxmind.com">MaxMind</a> site', 'slimstat-admin'); ?></p>
<p><?php _e('You can get latest ip-to-country file <a href="http://ip-to-country.webhosting.info/node/view/6">HERE</a>', 'slimstat-admin'); ?></p>
<p><?php _e('You can get latest GeoIPCountryWhois.csv file <a href="http://www.maxmind.com/app/geoip_country">HERE</a>', 'slimstat-admin'); ?></p>
<p><?php _e('When you ready to update, press "Next Step"', 'slimstat-admin'); ?></p>
<h2 class="step"><a href="iptc.php?step=1"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php 
	}
	break;
/*_________________________________CASE 1 */
	case 1:
?>
<h1>First Step</h1>
<?php 
	$isCountryfile = $ssAdmin->check_country_file('ip-to-country.csv');
	if(!$isCountryfile)
		$isCountryfile2 = $ssAdmin->check_country_file('GeoIPCountryWhois.csv');
	if(!$isCountryfile && !$isCountryfile2) {
?>
<p><?php _e('"GeoIPCountryWhois.csv" file does not exists.', 'slimstat-admin'); ?></p>
<p><?php _e('"ip-to-country.csv" file does not exists.', 'slimstat-admin'); ?></p>
<?php 
	} else {
?>
<?php 
		if( !$_go ) {
?>
<p><?php _e('Now, we will update your', 'slimstat-admin'); ?> "<?php echo $ssAdmin->table_countries; ?>" <?php _e('table', 'slimstat-admin'); ?></p>
<p><?php printf(__('We will use "%s" file to update database. If you want to use another file(%s), delete one.', 'slimstat-admin'), 
	($isCountryfile ? 'ip-to-country.csv' : 'GeoIPCountryWhois.csv'), ($isCountryfile ? 'GeoIPCountryWhois.csv' : 'ip-to-country.csv') ); ?></p>
<form action="iptc.php?step=1" method="post">
<input type="hidden" name="db_file" value="<?php echo (int)$isCountryfile; ?>" />
<input type="hidden" name="sstep" value="go" />
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Start This Step', 'slimstat-admin'); ?> &raquo;" /></p>
</form>
<?php
		} else {
?>
<p style="color:red;font-size: 16px;"><?php _e('Please do not close browser until "Ok, done" message appear', 'slimstat-admin'); ?></p>
<?php 
			$country_file = ($_POST['db_file'] == '1') ? 'ip-to-country.csv' : 'GeoIPCountryWhois.csv';
			$import_country = $ssAdmin->_importCountriesDataN($country_file);
			if(!$import_country) echo '<p>'.__('Failed to import country data', 'slimstat-admin').'</p>';
			else {
?>
			<p>Ok, done.</p>
			<p><?php _e('We successfully update your ip-to-country database', 'slimstat-admin'); ?></p>
			<p><?php _e('Now, we will optimize your updated table', 'slimstat-admin'); ?></p>
			<h2 class="step"><a href="iptc.php?step=2"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php 
			}
		}
	}
	break;
/*_________________________________CASE 2 */
	case 2:
		$query = "OPTIMIZE TABLE ".$ssAdmin->table_countries." ";
		if($wpdb->query($query) === false) {
			echo '<p>Failed to optimize table</p>';
		}
?>
			<p>Ok, done.</p>
			<p>"<?php echo $ssAdmin->table_countries; ?>" <?php _e('successfully optimized', 'slimstat-admin'); ?></p>
			<p><?php _e('Now, we will update your country data', 'slimstat-admin'); ?></p>
			<p><?php _e('You can skip next step and go back to SlimStat option page if you want', 'slimstat-admin'); ?></p>
			<h2 class="step"><a href="iptc.php?step=3"><?php _e('Next Step', 'slimstat-admin'); ?> &raquo;</a></h2>
			<h2 class="step"><?php _e('or', 'slimstat-admin'); ?>...</h2>
			<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
	break;
/*_________________________________CASE 3 */
	case 3:
		$update_stats_country = $ssAdmin->update_country_data('common');
		$update_stats_feed = $ssAdmin->update_country_data('feed');
		if(!$update_stats_country || !$update_stats_feed) {
?>
			<h1><?php _e('Failed to update table', 'slimstat-admin'); ?></h1>
			<p><?php _e('Please re-update your table', 'slimstat-admin'); ?></p>
			<h2 class="step"><a href="iptc.php?step=3"><?php _e('Do Over Again', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php } else { ?>
			<h1><?php _e('All Steps Done', 'slimstat-admin'); ?></h1>
			<p><?php _e('Now, go back to option page and "<strong>enable</strong>" tracking', 'slimstat-admin'); ?></p>
			<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php }
	break;
}
?>
<p id="footer"><a href="http://082net.com/tag/wp-slimstat-ex/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
</body>
</html>