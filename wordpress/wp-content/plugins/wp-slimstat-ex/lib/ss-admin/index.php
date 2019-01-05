<?php // Powered by wordpress install tool.
$wp_config = preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php';
if (!file_exists($wp_config)) 
    die("There doesn't seem to be a <code>wp-config.php</code> file.");
if (!file_exists('_functions.php')) 
    die("There doesn't seem to be a <code>_functions.php</code> file.");

require_once($wp_config);
$SlimCfg->check_user();
include_once('_functions.php');

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Wp-SlimStat-Ex &rsaquo; Admin Tool', 'slimstat-admin'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
</head>
<body>
<h1 id="logo"><img src="slimstat-logo.png" alt="slimstat" /></h1>
<h1><?php _e('Wp-SlimStat-Ex Admin Tool', 'slimstat-admin'); ?></h1>
<p><?php _e('First of all, go to "wp-admin &gt; Options &gt; Slimstat" <span style="color:red;font-weight:bold;">disable</span> tracking option', 'slimstat-admin'); ?></p>
<h3><a href="admin.php"><?php _e('Delete Old Database', 'slimstat-admin'); ?></a></h3>
<h3><a href="performance.php"><?php _e('SlimStat Performance Tool', 'slimstat-admin'); ?></a></h3>
<?php if ($SlimCfg->geoip == 'mysql') { ?>
<h3><a href="iptc.php"><?php _e('Update ip-to-country database', 'slimstat-admin'); ?></a></h3>
<?php } ?><?php /* does not supports upgrade from shortstat or slimstat anymore ?>
<h3><a href="slim2ex.php"><?php _e('Upgrade From Wp-SlimStat(0.92)', 'slimstat-admin'); ?></a></h3>
<h3><a href="short2slim.php"><?php _e('Upgrade From Wp-ShortStat', 'slimstat-admin'); ?></a></h3><?php */ ?>
<h3><a href="modulelist.php"><?php _e('Display available modules', 'slimstat-admin'); ?></a></h3>
<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat option page', 'slimstat-admin'); ?></a></h2>
<p id="footer"><a href="http://082net.com/tag/wp-slimstat-ex/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
</body>
</html>