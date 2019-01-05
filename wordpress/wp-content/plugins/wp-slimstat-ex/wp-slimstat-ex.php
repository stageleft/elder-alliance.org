<?php
/*
Plugin Name: WP-SlimStat-Ex
Plugin URI: http://082net.com/tag/wp-slimstat-ex/
Description: Track your blog stats. Based on <a href="http://www.duechiacchiere.it/">Mr. Coolmann</a>'s <a href="http://www.duechiacchiere.it/wp-slimstat/">Wp-SlimStat</a>. 
Version: 2.000
Author: Cheon, Young-Min
Author URI: http://082net.com/
*/

/*
ABOUT MODIFICATION ::
	Almost php and sql codes has written by Mr. Coolmann(http://www.duechiacchiere.it)
	What I've done is intergrating Ajax, constructing Pins(plugable panel) condition and some little patches.
	Thanks, Coolmann.

License ::
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

http://www.gnu.org/licenses/gpl.txt

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
______________________________________________________________*/

/*
Powered by
	jQuery :: http://jquery.com
	icons :: Mark James(http://www.famfamfam.com/)
	Ajax.History :: Siegfried Puchbauer <rails-spinoffs@lists.rubyonrails.org>
	SweetTitles :: Dustin Diaz (http://www.dustindiaz.com)
	IP-Lookup :: http://ip-lookup.net/ and http://dnsstuff.com
	GeoIp :: http://maxmind.com
*/

// Protect the script from direct access
if ( !defined('ABSPATH') ) {
	header("Location: /");
	exit();
}

define('SLIMSTATPATH',  realpath( dirname( __FILE__ ) ) . '/');

// include SlimStat config file
include(SLIMSTATPATH . 'wp-slimstat-ex-config.php');

// localize plugin
load_plugin_textdomain('wp-slimstat-ex', 'wp-content/plugins/'.$SlimCfg->basedir.'/lang');

class wp_slimstat_ex {

	function admin_head() {
		global $SlimCfg;
		if (wp_slimstat_ex::is_slimstat_option_page()) {
			echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/options.css?ver='.$SlimCfg->version.'" />';
		} else if (wp_slimstat_ex::is_slimstat_page()) {
			$load_css = '<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/slimstat.css?ver='.$SlimCfg->version.'" />';
			$load_css .= ($SlimCfg->option['nice_titles'])?"\n".'<link rel="stylesheet" type="text/css" media="screen" href="'.$SlimCfg->pluginURL.'/css/sweetTitles.css?ver='.$SlimCfg->version.'" />':'';
			echo "\n".'<!-- Added by Wp-SlimStat-Ex '.$SlimCfg->version.' -->'."\n".$load_css."\n";

		if ($SlimCfg->base_jslib == 'prototype') { //remain this for compatibility ?>
<script type="text/javascript">//<![CDATA[
Event.observe(window, 'load', function() {
	var current_hash_string = location.hash.replace(/^#/, '');
	// use Ajax.History for ajax history
	var historyHandler = Ajax.History.initialize({
					callback: function(hash){SlimStat.ajax(hash);},
					iframeSrc: "<?php echo $SlimCfg->pluginURL; ?>/js/_blank.html"
				});
	if(current_hash_string != '')
		var SlimStatLoader = SlimStat.ajax(current_hash_string);
	else 
		var SlimStatLoader = SlimStat.panel('1');
}, false);
//]]></script>
<?php } else if ($SlimCfg->base_jslib == 'jquery') { ?>
<script type="text/javascript">//<![CDATA[
jQuery(document).ready(function() {
	var historyHandler = jQuery.historyInit(SlimLoading.start);
	var current_hash_string = jQuery.historyCurrentHash.replace(/^#/, '');
	if (current_hash_string != '')
		var SlimStatLoader = '';//SlimStat.ajax(current_hash_string);
	else 
		var SlimStatLoader = SlimStat.panel('1');
});
//]]></script>
<?php }
		}
	}

	function enqueue_script_admin() {
		global $SlimCfg;
		$src_path = $SlimCfg->pluginURL.'/js/';
		$SlimCfg->base_jslib = 'jquery';//$SlimCfg->wp_version < '2.5' ? 'prototype' : 'jquery';
		switch($SlimCfg->base_jslib) {
			case 'jquery':
			wp_register_script('jquery.history', $src_path.'jquery.history.js', array('jquery'), $SlimCfg->version);
			wp_enqueue_script('ajax-slimstat-jquery', $src_path.'ajax-slimstat-jquery.js', array('jquery-form', 'jquery.history'), $SlimCfg->version);
//			if($SlimCfg->option['nice_titles']) {
//				wp_enqueue_script('jquery.tooltip', $src_path.'jquery-tooltip/jquery.tooltip.js', array('dimensions'), '1.2');
//			}
			break;
			case 'prototype':
			wp_register_script('moo.fx.base', $src_path.'moo.fx.base.js', array('prototype'), '2.0');
			wp_register_script('ajax.history', $src_path.'ajax.history.js', array('prototype'), $SlimCfg->version);
			wp_enqueue_script('ajax-slimstat-proto', $src_path.'ajax-slimstat-proto.js', array('prototype', 'moo.fx.base','ajax.history'), $SlimCfg->version);
			break;
		}
		if($SlimCfg->option['nice_titles']) {
			wp_register_script('addEvent', $src_path.'addEvent.js', false, $SlimCfg->version);
			wp_enqueue_script('sweetTitles', $src_path.'sweetTitles.js', array('addEvent'), $SlimCfg->version);
		}
	}

	function _upgrade_notice() {
		global $SlimCfg;
		$slimstat_admin_url = get_option('siteurl') . '/wp-content/plugins/' . $SlimCfg->basedir . '/lib/ss-admin/upgrade.php';
		echo "<div id='wpssex_upgrade_notice' class='error fade'><p>".sprintf(__('WP-SlimStat-Ex needs <a href=\'%s\'>upgrade</a>', 'wp-slimstat-ex'), $slimstat_admin_url)."</p></div>";
	}

	function _not_compatible() {
		echo "<div id='wpssex_not_compatible' class='error fade'><p>".__('WP-SlimStat-Ex is NOT active. WP-SlimStat-Ex requires WP 2.5 or greater.', 'wp-slimstat-ex')."</p></div>";
	}

	function option_page() {
		global $SlimCfg, $user_level;
		if (function_exists('add_options_page')) {
			require_once(SLIMSTATPATH . 'wp-slimstat-ex-options.php');
			$wp_slimstat_ex_options =& wp_slimstat_ex_options::get_instance();
			add_options_page("SlimStat", "SlimStat", 'manage_slimstat_options', 'wp-slimstat-ex-options', array(&$wp_slimstat_ex_options, 'options_page'));
		}
	}

	function is_slimstat_page () {
		return ( is_admin() && 'wp-slimstat-ex' == trim($_GET['page']) );
	}

	function is_slimstat_option_page () {
		return ( is_admin() && 'wp-slimstat-ex-options' == trim($_GET['page']) );
	}

	function set_options($force=false) {// just for reset options
		global $SlimCfg;
		// set options if not exists
		if($force || !get_option('wp_slimstat_ex')) {
			$SlimCfg->option = $SlimCfg->default_options();
			update_option('wp_slimstat_ex', $SlimCfg->option);
		}
		// set exclude options
		if($force || !get_option('wp_slimstat_ex_exclude')) {
			$SlimCfg->exclude = $SlimCfg->default_exclusions();
			update_option('wp_slimstat_ex_exclude', $SlimCfg->exclude);
		}
		// set capabilities options
		if($force || !get_option('wp_slimstat_ex_caps')) {
			$SlimCfg->caps = $SlimCfg->default_caps();
			$SlimCfg->check_caps(true);
			update_option('wp_slimstat_ex_caps', $SlimCfg->caps);
		}
	}

	function setup() {
		global $SlimCfg;
		if ($SlimCfg->wp_version < '2.5')
			return;
		require(SLIMSTATPATH . 'lib/setup.php');
		SSSetup::do_setup();
	}

	function check_current_version($install=false) {
		global $SlimCfg;

		$current = get_option('wp_slimstat_ex_version');
		if (!$install) {// we cannot check tables, there's no slimstat tables yet.
			if (!$current || $current < $SlimCfg->last_db_update_version)
				return false;
			update_option('wp_slimstat_ex_version', $SlimCfg->version);
			return true;
		}

		if(!$current || $current == '0.1') {
			require_once(SLIMSTATPATH . 'lib/ss-admin/_functions.php');
			if(!isset($ssAdmin))
				$ssAdmin =& SSAdmin::get_instance();
			// check if Pins table has 'type' column
			$is_15 = $ssAdmin->maybe_add_column($SlimCfg->table_pins, 'type', '', true);
			if(!$is_15) {
				update_option('wp_slimstat_ex_version', '0.1');
				return false;
			}
			if (empty($SlimCfg->indexkey))
				$SlimCfg->indexkey = 	array('common'=>$SlimCfg->_getIndexKeys('common', true), 'feed'=>$SlimCfg->_getIndexKeys('feed', true));
			$is_16 = isset($SlimCfg->indexkey['common']['dt']) && isset($SlimCfg->indexkey['common']['remote_ip']);
			if(!$is_16) {
				update_option('wp_slimstat_ex_version', '1.5');
				return false;
			}
			$is_20 = $ssAdmin->maybe_add_column($SlimCfg->table_resource, 'rs_md5', '', true);
			if(!$is_20) {
				update_option('wp_slimstat_ex_version', '1.6');
				return false;
			}
			update_option('wp_slimstat_ex_version', '2.0');
		}
		$current = get_option('wp_slimstat_ex_version');
		if($current < $SlimCfg->last_db_update_version)
			return false;
		update_option('wp_slimstat_ex_version', $SlimCfg->version);
		return true;
	}

	function call_queried_object() {
		global $SlimCfg;
		global $wp_query, $wp_the_query;
		if( !is_single() && !is_page() && !is_attachment() )
			return;
		// Fix queried object bug, if there's no wp_title on header.php of current theme.
		if (is_null($wp_the_query->queried_object_id))
			$wp_the_query->get_queried_object();
		return;
	}

	function plugin_update_row($file) {
		global $SlimCfg;
		if($file != $SlimCfg->_basename(__FILE__))
			return;
		$r = get_option('wp_slimstat_ex_latest');
		if (!$r) {
			$r->last_checked = time() - 43200;
			$r->response->slug = 'wp-slimstat-ex';
			$r->response->new_version = $SlimCfg->version;
			update_option('wp_slimstat_ex_latest', $r);
		}
		$time_not_changed = isset( $r->last_checked ) && 43200 > ( time() - $r->last_checked );
		if ($time_not_changed) return;
		if ($r->new_version <= $SlimCfg->version) {
			$r->new_version = $SlimCfg->version;
			if ($update = $SlimCfg->version_check()) {
				$r->new_version = $update;
				$r->last_checked = time();
			}
		}
		update_option('wp_slimstat_ex_latest', $r);
		if ($r->new_version > $SlimCfg->version) {
			$current = get_option( 'update_plugins' );
			if (!isset($current->response[$file])) {
				$current->response[$file]->id = '999999999999';
				$current->response[$file]->slug = $r->slug;
				$current->response[$file]->new_version = $r->new_version;
				$current->response[$file]->url = $SlimCfg->plugin_home;
				$current->response[$file]->package = $SlimCfg->package_url;
				update_option('update_plugins', $current);
			}
		}
/*		if($update) {
		echo "<tr><td colspan='5' class='plugin-update'>";
		printf( __('There is a new version of %s available. <a href="%s">Download version %s here</a>.'), 'WP-SlimStat-Ex', $SlimCfg->plugin_home, $update );
		echo "</td></tr>";
		}*/
	}

}
// end of class wp_slimstat_ex

/* Setup, Option Page, Load CSS & Javascripts
-------------------------------------------*/
add_action('plugins_loaded', array(&$SlimCfg, 'plugins_loaded'));

// setup WP-SlimStat-Ex
add_action('activate_'.$SlimCfg->_basename(__FILE__), array('wp_slimstat_ex', 'setup'));

/* WP version check
-------------------------------------------*/
if ($SlimCfg->wp_version < '2.5') {
	add_action('admin_notices', array('wp_slimstat_ex', '_not_compatible'));
	return;// do not load wp-slimstat-ex any more
}

// load css and js on admin panel
add_action('admin_head', array('wp_slimstat_ex', 'admin_head'), 20);
if ($SlimCfg->option['use_ajax'] && wp_slimstat_ex::is_slimstat_page()) {
	add_action('wp_print_scripts', array('wp_slimstat_ex', 'enqueue_script_admin'));
}

// option page
add_action('admin_menu', array('wp_slimstat_ex', 'option_page'));

/* Upgrade from previous version */
if(!wp_slimstat_ex::check_current_version()) {
	$SlimCfg->option['tracking'] = 0;
	add_action('admin_notices', array('wp_slimstat_ex', '_upgrade_notice'));
	return;
}

/* Includes
-------------------------------------------*/
if ( $SlimCfg->geoip != 'mysql' ) {
	if ( !function_exists('geoip_country_code_by_name') )
		require_once(SLIMSTATPATH . 'lib/geoip/geoipcity.inc');
	require_once(SLIMSTATPATH . 'lib/geoip/geoipregionvars.php');

	add_action( 'shutdown', array( &$SlimCfg, 'geoip_close' ), 25 );
}

require_once(SLIMSTATPATH . 'lib/functions.php');

// include Pin functions
if ($SlimCfg->option['usepins']) {
	require(SLIMSTATPATH . 'lib/pins.php');
	$current_pins = SSPins::_getPins(1, 4);
	if(!empty($current_pins)) {
		foreach($current_pins as $current_pin) {
			$current_pin_file = SLIMSTATPATH . 'pins/'. $current_pin->name . '/pin.php';
			if('' != $current_pin->name && file_exists($current_pin_file))
				include_once($current_pin_file);
		}
	}
}

/* Stats Display
-------------------------------------------*/
if( is_admin() ) {
	require_once(SLIMSTATPATH . 'lib/display.php');
	add_action('admin_menu', array('SSDisplay','add_tab'));
}

/* Call queried object (Fix for http://trac.wordpress.org/ticket/5121)
-------------------------------------------*/
add_action('wp', array('wp_slimstat_ex', 'call_queried_object'), 0);

/* Plugin update check
-------------------------------------------*/
add_action( 'after_plugin_row', array('wp_slimstat_ex', 'plugin_update_row' ), 0 );
//add_action ( 'admin_init', array('wp_slimstat_ex', 'admin_init') );


/* Track visitors
-------------------------------------------*/
require_once(SLIMSTATPATH . 'lib/track.php');
if(!$SlimCfg->option['track_mode'] || $SlimCfg->option['track_mode'] == 'full') {
	// Disable track when redirecting
	add_filter('wp_redirect', array(&$SSTrack, 'remove_shutdown_hooks'));
	// Track all pages
	add_action( 'shutdown', array( &$SSTrack, 'slimtrack' ) );
} else {
	add_action( 'wp_footer', array( &$SSTrack, 'slimtrack' ) );
	if ( $SlimCfg->option['track_mode'] == 'footer_feed' ) {
		add_action( 'template_redirect', array( &$SSTrack, 'feed_track_footer' ) );
	}
}

?>