<?php // Powered by wordpress install tool.
$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
if (!file_exists($wp_config)) 
    die("There doesn't seem to be a <code>wp-config.php</code> file.");

require_once($wp_config);
$SlimCfg->check_user();
load_plugin_textdomain('slimstat-admin', 'wp-content/plugins/wp-slimstat-ex/lang');	

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
<h1><?php _e('Wp-SlimStat available modules', 'slimstat-admin'); ?></h1>
	<table width="100%" cellpadding="3" cellspacing="3">
	<tr class="alternate">
		<th>ID</th>
		<th>Name</th>
		<th>Description</th>
	</tr>
<?php 
$defaults = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,91,92);
$i = 0;
foreach($defaults as $dmo) {
?>
	<tr<?php if ($i % 2 != 0) echo ' class="alternate"'; ?>>
		<td><?php echo $dmo; ?></td>
		<td><?php echo SSFunction::id2module($dmo); ?></td>
		<td><?php echo SSFunction::get_title($dmo); ?></td>
	</tr>
<?php
	$i++;
}
if($SlimCfg->option['usepins']) {
	$pins = $wpdb->get_results("SELECT id FROM $SlimCfg->table_pins WHERE active = 1");
	if($pins) {
		foreach($pins as $pin) {
			$mo_info = SSFunction::pin_mod_info($pin->id);
			$mo_info = $mo_info['modules'];
			foreach($mo_info as $n=>$info) {
				$moid = (($pin->id + 100)*100)+1+$n;
?>
		<tr<?php if ($i % 2 != 0) echo ' class="alternate"'; ?>>
			<td><?php echo $moid; ?></td>
			<td><?php echo $info['name']; ?></td>
			<td><?php echo $info['title']; ?></td>
		</tr>
<?php
				$i++;
			}
		}
	}
}
?>
	</table>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat option page', 'slimstat-admin'); ?></a></h2>

<p id="footer"><a href="http://082net.com/tag/wp-slimstat-ex/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
</body>
</html>