<?php

class SlimCfg {
	var $version = '2.000'; // Current SlimStat-Ex version
	var $external_iptc = 'internal'; // set this 'external' to use external ip-to-country database
	var $uaOption = false; // Personal use... please forget about this ;)
	var $last_db_update_version = '2.000';
	var $tbPrefix, $table_stats, $table_countries, $table_feed, $table_resource, $table_dt, $table_pins, $table_ua, $current_table;
	var $option, $exclude, $caps, $option_page, $pluginURL, $ajaxReq, $get, $basedir, $web_path, $dt, $indexkey;
	var $geo, $geoip, $geo_pecl;
	var $is_wpmu, $wp_version;
	var $version_check_url = 'http://082net.com/update-check.php?check_plugin=wp-slimstat-ex';
	var $plugin_home = 'http://082net.com/tag/wp-slimstat-ex/?orderby=modified';
	var $package_url = 'http://082net.com/?dl=wp-slimstat-ex-plugin.zip';
	var $bot_array = array();

	function SlimCfg() {
		$this->_init();
	}

	function _init() {
		$this->is_wpmu = $this->is_wpmu();
		$this->wp_version = $this->wp_version();
		if (defined('SLIMSTAT_EXTRACK')) { // if external tracking
			global $slimtrack_ext;
			$table_prefix = $slimtrack_ext['table_prefix'];
		} else 
			global $table_prefix;
		// We use a bunch of tables to store data
		global $wpmuBaseTablePrefix;
		// Table names
		if($this->is_wpmu && isset($wpmuBaseTablePrefix))
			$this->table_countries = $wpmuBaseTablePrefix . "slim_countries";
		else 
			$this->table_countries = $table_prefix . "slim_countries";	// ip-to-country
		$this->tbPrefix = $table_prefix . "slimex_";
		$this->table_stats = $table_prefix . "slimex_stats";	// common stats
		$this->table_feed = $table_prefix . "slimex_feed";	 // feed stats
		$this->table_dt = $table_prefix . "slimex_dt";	 // compressed hit, vists, uniques
		$this->table_pins = $table_prefix . "slimex_pins";	// Pin information
		$this->table_resource = $table_prefix . "slimex_resource"; // resource table
		$this->table_ua = $table_prefix . "slimex_ua"; // user agent table for dev.

		$this->basedir = dirname($this->_basename(__FILE__));	 // plugin folder name
		$this->pluginURL =  get_option('siteurl')."/wp-content/plugins/".$this->basedir;	 // Plugin URL(path)
		$this->ajaxReq = $this->pluginURL."/lib/r";	// Ajax request URL
		$this->web_path = $this->_getWebPath();	// wordpress intall folder related to site root
		$this->get = $this->parse_GET(); // panel, filters, encoded filters
		$this->current_table = $this->select_table(); // Current table to select
		$this->dt = array($this->_mktime(time()), $this->_mktime(time(), true)); // Blog midnight and server midnight
		$this->option = $this->get_options();	// SlimStat options
		$this->exclude = $this->get_exclusions();	// Exclusion options
		$this->caps = $this->get_caps(); // Capabilities
		$this->check_caps();
		$this->geoip = $this->geoip();
		$this->geo_pecl = 	function_exists('geoip_database_info');
//		if( $this->exclude['ignore_bots'] ) {
			$this->bot_array['bots'] = array(12,43,45,46,47,48,49,50,51,52,53,107,108,109,110,111,121,135,136,159,174,175,176,177,180,182);
			$this->bot_array['feeds'] = array(13,14,15,20,44,54,55,56,57,61,91,95,96,97,98,99,100,130,131,132,133,134,137,138,139,140,141,142,143,144,146,147,148,149,150,151,152,154,156,157,162,164,165,166,167,168,169,170,171,172,173,178,179,183,184,1998);
			$this->bot_array['validators'] = array(16,17,18,93,123,124,125,126,127,128,129,1999);
			$this->bot_array['tools'] = array(19,22,40,41,72,73,77,80,85,58,59,60,145,155);
//		}
	}

	function plugins_loaded() {
		if (!$this->option_page)
			$this->option_page = ($this->has_cap('manage_options') ? 'options-general.php' : 'admin.php') . '?page=wp-slimstat-ex-options';
		if (!$this->indexkey)
			$this->indexkey = array('common'=>$this->_getIndexKeys('common', true), 'feed'=>$this->_getIndexKeys('feed', true));
	}

	function wp() {
	}

	function default_options() {
		return array('tracking'=>1, 'usepins'=>1, 'cachelimit'=>0, 'guesstitle'=>1, 'dbmaxage'=>0, 'limitrows'=>20, 'iptohost'=>0, 'whois'=>1, 'whois_db'=>'dnsstuff', 'meta'=>0, 'visit_type'=>'uniques', 'count_type'=>'hits', 'stats_type'=>'all', 'time_offset'=>0, 'use_ajax'=>1, 'nice_titles'=>1, 'ignore_bots'=>0, 'track_mode'=>'full');
	}

	function default_exclusions() {
		return array('ignore_bots'=>0, 'ig_bots'=>0, 'ig_feeds'=>0, 'ig_validators'=>0, 'ig_tools'=>0, 'black_ua'=>'', 'white_ua'=>'', 'ignore_ip'=>'');
	}

	function default_caps() {
		return array( 
			'administrator'=>array('ignore_slimstat_track', 'view_slimstat_stats', 'manage_slimstat_options'),
			'editor'=>array('ignore_slimstat_track', 'view_slimstat_stats'),
			'author'=>array('ignore_slimstat_track', 'view_slimstat_stats'),
			'contributor'=>array('ignore_slimstat_track'),
			'subscriber'=>array('ignore_slimstat_track')
		);
	}

	function get_options() {
		$op = get_option('wp_slimstat_ex');
		if (is_array($op))
			return array_merge($this->default_options(), $op);
		return $this->default_options();
	}

	function get_exclusions() {
		$exclude = get_option('wp_slimstat_ex_exclude');
		if (is_array($exclude))
			return array_merge($this->default_exclusions(), $exclude);
		return $this->default_exclusions();
	}

	function get_caps() {
		$cap = get_option('wp_slimstat_ex_caps');
		if (is_array($cap) && !empty($cap))
			return array_merge($this->default_caps(), $cap);
		return $this->default_caps();
	}

	function has_cap($cap='') {
		if (empty($cap))
			return false;
		return current_user_can($cap);
	}

	function check_caps($force = false) {
		if (defined('SLIMSTAT_EXTRACK'))
			return;
		$checked = get_option('wp_slimstat_ex_cap_checked');
		if (!$force && $checked)
			return;
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();

		$default_caps = array_values($this->caps['administrator']);
		foreach ($wp_roles->role_names as $rolekey => $role_name) {
			if (!isset($this->caps[$rolekey])) continue;
			foreach ($default_caps as $cap) {
				if ( in_array($cap, $this->caps[$rolekey]) && !$wp_roles->role_objects[$rolekey]->has_cap($cap) )
					$wp_roles->role_objects[$rolekey]->add_cap($cap);
				elseif ( !in_array($cap, $this->caps[$rolekey]) && $wp_roles->role_objects[$rolekey]->has_cap($cap) )
					$wp_roles->role_objects[$rolekey]->remove_cap($cap);
			}
		}
		update_option('wp_slimstat_ex_cap_checked', 1);
	}

	function _getIndexKeys($table = 'common', $deep = false) {
		global $wpdb;
		$_table = $this->string2table($table);
		$current_keys = array();
		$pre_len = array();
		$cur_len = array();
		$key_array = array('dt_total', 'resource_total', 'resource', 'searchterms', 'domain', 'referer', 'platform', 'browser', 'language', 'visit', 'country', 'remote_ip', 'dt', 'ip_to', 'ip_from_to_idx', 'ip_from_idx', 'rs_string', 'rs_md5');
		if($myIndexStructure = $wpdb->get_results("SHOW INDEX FROM $_table", ARRAY_A)){
			foreach ( $myIndexStructure as $index_details ) {
				$key = $index_details['Key_name'];
				$col = $index_details['Column_name'];
				$len = $index_details['Cardinality'];
				$len = isset($pre_len[$key]) ? max($pre_len[$key], $len) : $len;
				$pre_len[$key] = $len;
				if(in_array($key, $key_array)) {
					if($deep) {
						$current_keys[$key]['column'][] = $col;
						$current_keys[$key]['length'] = $len;
					} else
						$current_keys[] = $key;
				}
			}
			return $current_keys;
		}
		return array();
	}

	function use_indexkey($mokey, $table='') {
		$table = ($table == '') ? $this->current_table : $table;
		$keys = ($table == $this->table_stats) ? $this->indexkey['common'] : $this->indexkey['feed'];
		$pkeys = array();
		if(isset($keys['remote_ip'])) $pkeys[] = 'remote_ip';
		if(isset($keys['resource'])) $pkeys[] = 'resource';
		if(isset($keys['referer'])) $pkeys[] = 'referer';
		if(isset($keys['domain'])) $pkeys[] = 'domain';
		if(isset($this->get['fd']) && isset($keys['dt'])) {// dt index will automatically applied
			if($mokey != 'dt' && in_array($mokey, $pkeys))
				return " USE INDEX (dt,{$mokey}) ";
			return " USE INDEX (dt)";
		}
		if(!isset($this->get['ff']))// use default 
			return "";
		$use_key = "";
		if(!isset($keys[$mokey]))
			$mokey = "";
		switch($this->get['ff']) {
			case 0:
				if(isset($keys['domain']))// varchar(255) index keys...
					return " USE INDEX (domain) ";
			break;
			case 1:
				if(isset($keys['searchterms']))// varchar(255) index keys...
					return " USE INDEX (searchterms) ";
			break;
			case 2:
				if(isset($keys['resource']))// This is primary key
					return " USE INDEX (resource) ";
			break;
			case 3:
				if(isset($keys['remote_ip']))// This is primary key
					return " USE INDEX (remote_ip) ";
				$use_key = 'remote_ip';
			break;
			case 4:
				$use_key = 'browser';
			break;
			case 5:
				$use_key = 'platform';
			break;
			case 6:
				$use_key = 'country';
			break;
			case 6:
				$use_key = 'language';
			break;
			default:
			break;
		}
		if(!isset($keys[$use_key]))
			$use_key = $mokey;
		if('' == $use_key)
			return "";
		return " USE INDEX (".(($keys[$mokey]['length'] > $keys[$use_key]['length']) ? $mokey : $use_key).") ";
	}

	// key length desc
	function indexkey_sort($a, $b) {
		if ($a['length'] == $b['length'])
			return 0;
		return ($a['length'] > $b['length']) ? -1 : 1;
	}

	function _basename($file) {
		return plugin_basename($file);
	}

	function _getWebPath($url="home") {
		$home_path = @parse_url(get_option($url));
		if ( isset($home_path['path']) )
			$home_path = $home_path['path'];
		else
			$home_path = '';
		$home_path = trim($home_path, '/');
		return $home_path;
	}

	function my_esc( $str = '' ) {
		return addslashes( $str );// Disable rest for now, causing problems
/*		if( version_compare( phpversion(), '4.3.0' ) == '-1' )
			return mysql_escape_string( $str );
		else
			return mysql_real_escape_string( $str );*/
	}

	function select_table() {
		$neededTable = ( $this->get['pn'] == 2 ) ? $this->table_feed : $this->table_stats;
		return $neededTable;
	}

	function string2table($table) {
		switch($table) {
			case 'feed':
				$_table = $this->table_feed;
			break;
			case 'country':
				$_table = $this->table_countries;
			break;
			case 'dt':
				$_table = $this->table_dt;
			break;
			case 'pins':
				$_table = $this->table_pins;
			break;
			case 'resource':
				$_table = $this->table_resource;
			break;
			case 'common':
				$_table = $this->table_stats;
			break;
			default:
				$_table = $table;
			break;
		}
		return $_table;
	}

	function sstime($time, $back_to_server_time = false) {
		$offset = (int)$this->option['time_offset'] * 60 * 60;
		if ($offset != 0) {
			$time = $back_to_server_time ? $time - $offset : $time + $offset;
		}
		return $time;
	}

	function _mktime($time, $back_to_server_time = false) {
		$dt = $this->sstime($time);
		$new_dt = mktime( 0, 0, 0, date( "n", $dt ), date( "d", $dt ), date( "Y", $dt ) );
		if($back_to_server_time)
			$new_dt = $this->sstime( $new_dt, true );
		return $new_dt;
	}

	function _date($date_format, $time) {
		return mysql2date($date_format, date('Y-m-d H:i:s', $time));
	}

	function parse_GET($filter_encode = true, $interval = true, $normal = true) {
		$get = array();
		$get['pn'] = (isset($_GET['panel']))?(int)$_GET['panel']:1;
		$f_interval = isset($_GET['fd']) ? trim(urldecode($_GET['fd'])) : null;
		if( $normal && isset($_GET['fi']) && isset($_GET['ff']) && isset($_GET['ft']) ) {
			$get['ff'] = (int)$_GET['ff'];
			$get['ft'] = (int)$_GET['ft'];
			$get['fi'] = urldecode($_GET['fi']);
		}
		if ( $interval && isset( $f_interval ) && strpos( $f_interval, "|" ) ) {
			$get['fd'] = array(0, 0);
			$intervals = explode( "|", $f_interval, 2 );
			$intervals[0] = (int)$intervals[0];
			$intervals[1] = (int)$intervals[1];
			if( max( $intervals ) > 0 && ($intervals[1] > $intervals[0]) ) {
				$get['fd'] = array( $intervals[0], $intervals[1] );
			}
		}
		$get['slim_table'] = isset($_GET['slim_table']) ? trim($_GET['slim_table']) : null;
		//re-encode filter values
		if($filter_encode) {
			$get['fi_encode'] = "";
			$get['fd_encode'] = "";
			if($normal && isset($get['fi'])) 
				$get['fi_encode'] = '&amp;ff='.$get['ff'].'&amp;ft='.$get['ft'].'&amp;fi='.urlencode($get['fi']);
			if ( $interval && isset($get['fd']) ) 
				$get['fd_encode'] = '&amp;fd='.$f_interval;
			if($get['slim_table'])
				$get['fi_encode'] .= '&amp;slim_table='.$get['slim_table'];
		}
		$get['offset'] = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
		$get['offset'] = ($get['offset'] < 0) ? 0 : $get['offset'];
		return $get;
	}

	function convert_encoding($str, $charset='') {
		if (!function_exists('mb_convert_encoding'))
			return $str;
		$charset = '' == $charset ? 'ASCII, UTF-8, EUC-KR, ISO-8859-1, JIS, EUC-JP, SJIS' : $charset;
		$str = mb_convert_encoding($str, get_option('blog_charset'), $charset );
		return $str;
	}

	function truncate($text, $len = 120) {
		if (function_exists('mb_strcut')) {
			$output = (strlen($text) >$len) ? mb_strcut($text, 0, $len, get_option('blog_charset')) . '...' : $text;
		} else {
			$output = (strlen($text) >$len) ? substr($text, 0, $len) . "..." : $text;
		}
		return $output;
	}

	function trimString($r, $length = 26) {
		$r = eregi_replace( "^http://", "", $r );
		$r = eregi_replace( "^www.", "", $r );
		$r = $this->truncate($r, $length);
		return $r;
	}

	function is_wpmu() {
		if(isset($this->is_wpmu))
			return $this->is_wpmu;
		global $wp_version, $wporg_version, $wpmu_version;
		if(strpos($wp_version, 'wordpress-mu') !== false)
			return true;
		if(isset($wporg_version) || isset($wpmu_version))
			return true;
		return false;
	}
	
	function wp_version() {
		if(isset($this->wp_version))
			return $this->wp_version;
		global $wp_version, $wporg_version, $wpmu_version;
		if(!$this->is_wpmu || isset($wpmu_version))
			return $wp_version;

		if(isset($wporg_version))
			return $wporg_version;
		// wpmu - increment version by 1.0 to match wp
		// borrowed from K2 theme (http://getk2.com)
		preg_match("/\d\.\d/i", $wp_version, $match);
		$match[0] = $match[0] + 1.0;
		return $match[0];
	}

	//borrowed from Extended Live Archives(http://www.sonsofskadi.net/extended-live-archive/)
	function version_check() {
		$remote = $this->remote_fopen($this->version_check_url);
		if(!$remote || strlen($remote) > 8 || 'error' == $remote) return -1;
		if ($remote > $this->version) return $remote; else return 0;
	}

	function remote_fopen($uri, $curl_force_post = false) {
		$timeout = 4;
		$parsed_url = @parse_url($uri);

		if ( !$parsed_url || !is_array($parsed_url) )
			return false;

		if ( !isset($parsed_url['scheme']) || !in_array($parsed_url['scheme'], array('http','https')) ) {
			$parsed_url['scheme'] = 'http';
			$uri = 'http://'.$uri;
		}

		if ( function_exists('curl_init') ) {// curl
			$handle = curl_init();
			curl_setopt ($handle, CURLOPT_URL, $uri);
			curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($handle, CURLOPT_TIMEOUT, $timeout);
			if($curl_force_post && isset($parsed_url['query'])) {
				curl_setopt($handle, CURLOPT_POST, true);
				curl_setopt($handle, CURLOPT_POSTFIELDS, $parsed_url['query']);
			}
			$buffer = curl_exec($handle);
			if (curl_errno($handle))
				return false;
			curl_close($handle);
			return $buffer;
		} else if ( ini_get('allow_url_fopen') ) {// fopen
			$fp = @fopen( $uri, 'r' );
			if ( !$fp )
				return false;
			//stream_set_timeout($fp, $timeout); // Requires php 4.3
			$linea = '';
			while( $remote_read = fread($fp, 4096) )
				$linea .= $remote_read;
			fclose($fp);
			return $linea;
		} else {// snoopy
			if(!class_exists('Snoopy')) 
				require(ABSPATH . 'wp-includes/class-snoopy.php');
			$client = new Snoopy();
			$client->_fp_timeout = $timeout;
			if (@$client->fetch($uri) === false)
				return false;
			return $client->results;
		}
	}

	function multi_remote_fopen($uris, $curl_force_post = false) {
		if (!function_exists('curl_multi_init'))
			return false;
		$chs = array();
		$info = array();
		$data = array();
		$count = count($uris);
		$mh = curl_multi_init();
		for ($i = 0; $i < $count; $i++) {
			$uri = $uris[$i];
			$parsed_url = @parse_url($uri);

			$chs[$i] = curl_init();
			curl_setopt ($chs[$i], CURLOPT_URL, $uri);
			curl_setopt ($chs[$i], CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt ($chs[$i], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($chs[$i], CURLOPT_TIMEOUT, 4);
			curl_setopt ($chs[$i], CURLOPT_HEADER, 0);
			if ( isset($parsec_url['query']) && $curl_force_post ) {
				curl_setopt ($chs[$i], CURLOPT_POST, true);
				curl_setopt ($chs[$i], CURLOPT_POSTFIELDS, $parsed_url['query']);
			}
			curl_multi_add_handle($mh, $chs[$i]);
		}

		$running=null;
		do {
			curl_multi_exec($mh, $running);
		} while ($running > 0);

		for ($i = 0; $i < $count; $i++) {
			$data[$i] = curl_multi_getcontent($chs[$i]);
			curl_multi_remove_handle($mh, $chs[$i]);
		}
		curl_multi_close($mh);
		return $data;
	}

	function check_user($redirect = true) {// for slimstat-admin only.
		auth_redirect();
		$has_cap = $this->has_cap('manage_options');
		$location = get_option('siteurl'). '/wp-admin/'.$this->option_page;
		if ( !$has_cap ) {
			if($redirect) {
				wp_redirect($location);
				exit();
			} else 
				return false;
		}
		return true;
	}

	function geoip() {
		$geoip = 'mysql';
		if ( function_exists('geoip_record_by_name') )
			return 'city';
		$geo_dir = SLIMSTATPATH . "lib/geoip/";
		$geo_country = $geo_dir . "GeoIP.dat";
		$geo_city = $geo_dir . "GeoLiteCity.dat";

		if ( is_dir($geo_dir) ) {
			if ( is_readable($geo_city) )
				return 'city';
			if ( is_readable($geo_country) )
				return 'country';
		}
		return $geoip;
	}

	function geoip_close() {
		if ($this->geo)
			geoip_close($this->geo);
	}

	function geoip_country($ip) {
		if ( !$ip || $this->geoip == 'mysql' )
			return '';
		if ( strpos($ip, '.') === false )
			$ip = long2ip($ip);
		if ($this->geo_pecl)
			$country_code = geoip_country_code_by_name($ip);
		else {
			if (!$this->geo) {
				$geo_file = SLIMSTATPATH . 'lib/geoip/'.($this->geoip == 'city' ? 'GeoLiteCity':'GeoIP').'.dat';
				$this->geo = geoip_open($geo_file, GEOIP_STANDARD);
			}
			$country_code = geoip_country_code_by_addr($this->geo, $ip);
		}
		if (!$country_code)
			return "";
		return strtolower($country_code);
	}

	function geoip_location($ip) {
		global $GEOIP_REGION_NAME;
		if ( !$ip || strpos($this->geoip, 'city') === false )
			return '';
		if ( strpos($ip, '.') === false )
			$ip = long2ip($ip);
		if ($this->geo_pecl) {
			$loc = (object) geoip_record_by_name($ip);
		} else {
			if (!$this->geo) {
				$city_file = SLIMSTATPATH . 'lib/geoip/GeoLiteCity.dat';
				$this->geo = geoip_open($city_file,GEOIP_STANDARD);
			}
			$loc = geoip_record_by_addr($this->geo, $ip);
		}
		if ($loc->country_code) {
			if ( $loc->city && !seems_utf8($loc->city) ) {
				$loc->city = $this->convert_encoding($loc->city);
			}
			if ( $loc->region )
				$loc->region_full = $GEOIP_REGION_NAME[$loc->country_code][$loc->region];
		}
		return $loc;
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SlimCfg();
		}
		return $instance[0];
	}
}

if(!isset($SlimCfg))
	$SlimCfg =& SlimCfg::get_instance();

$GLOBALS['SlimCfg'] =& $SlimCfg;
?>