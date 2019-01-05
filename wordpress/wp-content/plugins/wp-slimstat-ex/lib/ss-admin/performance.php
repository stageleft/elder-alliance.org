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
$for_localize = __('add', 'slimstat-admin').__('remove', 'slimstat-admin');
header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php _e('Wp-SlimStat-Ex &rsaquo; Add or Remove Index Keys', 'slimstat-admin'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
</head>
<body>
<h1 id="logo"><img src="slimstat-logo.png" alt="slimstat" /></h1>
<?php
// Let's check to make sure ShortStat table exists.
switch($step) {
/*_________________________________CASE 0 */
case 0:
?>
	<h1><?php _e('Welcome to SlimStat Performance tool.', 'slimstat-admin'); ?></h1>
	<h3><?php _e('Before we start', 'slimstat-admin'); ?></h3>
	<ul>
	<li><?php _e('You better understand what you\'re going to do.', 'slimstat-admin'); ?></li>
	<li><?php _e('Adding index keys can increase performance of SlimStat, but you will need <span class="bold">more storage</span> for stats mysql table', 'slimstat-admin'); ?></li>
	<li><?php _e('Removing index keys can reduce your mysql db size, but SlimStat will <span class="bold">work slowly</span>.', 'slimstat-admin'); ?></li>
	</ul>
	<h2 class="step"><a href="performance.php?step=1"><?php _e('Common Stats', 'slimstat-admin'); ?> &raquo;</a></h2>
	<h2 class="step"><a href="performance.php?step=2"><?php _e('Feed Stats', 'slimstat-admin'); ?> &raquo;</a></h2>

<?php
break;
/*_________________________________CASE 1 */
case 1:
?>
	<h1><?php _e('Common Stats', 'slimstat-admin'); ?></h1>
	<h2><?php _e('Add or remove common-stats', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_stats; ?>) <?php _e('index keys', 'slimstat-admin'); ?></h2>
	<?php 
	if(isset($_GET['job']) && isset($_GET['key'])) {
		$ssAdmin->_do_indexing($_GET['job'], $_GET['key'], 'common');
	}
?>
	<p class="message green"><?php _e('Current DB size:', 'slimstat-admin'); ?> <?php echo SSFunction::_getTableSize('common'); ?></p>
	<?php echo $ssAdmin->_indexTable('common'); ?>
	<p><?php _e('Click <a href="performance.php?step=2">HERE</a> to to add or remove index keys for feed table', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_feed; ?>)</p>
	<p>Or...</p>
	<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
<?php
break;
/*_________________________________CASE 2 */
case 2:
?>
	<h1><?php _e('Feed Stats', 'slimstat-admin'); ?></h1>
	<h2><?php _e('Add or remove feed-stats', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_feed; ?>) <?php _e('index keys', 'slimstat-admin'); ?></h2>
	<?php 
	if(isset($_GET['job']) && isset($_GET['key'])) {
		$ssAdmin->_do_indexing($_GET['job'], $_GET['key'], 'feed');
	}
?>
	<p class="message green"><?php _e('Current DB size:', 'slimstat-admin'); ?> <?php echo SSFunction::_getTableSize('feed'); ?></p>
	<?php echo $ssAdmin->_indexTable('feed'); ?>
	<p><?php _e('Click <a href="performance.php?step=1">HERE</a> to to add or remove index keys for common table', 'slimstat-admin'); ?>(<?php echo $ssAdmin->table_stats; ?>)</p>
	<p>Or...</p>
	<h2 class="step"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/<?php echo $SlimCfg->option_page; ?>"><?php _e('Back to SlimStat Options', 'slimstat-admin'); ?> &raquo;</a></h2>
	<?php
	break;
}
?>
<p id="footer"><a href="http://082net.com/tag/wp-slimstat-ex/">Wp-SlimStat-Ex</a>, Track your blog stats.</p>
</body>
</html>