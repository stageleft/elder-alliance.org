<?php
if(!class_exists('SSPins')) :
class SSPins {

	function SSPins() {
//		$this->_init();
	}

	// To make extra table for your pin,
	// set 'extra_table' to 1 on pin_actions()
	// define your table name and structure on 
	// var $extra_table = array('your_table_name' =>'table_structure');
	/* e.g.
	var $extra_table = array(
		'geo' => "`ip` INT(10) unsigned NOT NULL default '0',
			`country_abrv` CHAR(3) NOT NULL default '',
			`city` VARCHAR(40) NOT NULL default '',
			`latitude` FLOAT NOT NULL default '0',
			`longitude` FLOAT NOT NULL default '0',
			UNIQUE KEY `ip` (`ip`)"
	);
	*/
	function pin_actions() {
		return array( 'options' => 0, 'extra_table' => 0 );
	}

	// copy, paste and un-comment lines.
	function &getPinID() {
//		$name = get_class($this);
//		$id =& $this->_getPinID($name);
//		return $id;
	}

	// copy, paste and un-comment lines.
	function &getMoID($num) {
//		$pinid =& $this->getPinID();
//		$id = ($pinid *100) + 1 + $num;
//		return $id;
	}

	// pin options :: use echo or print... not return.
	// set 'options' to 1 on pin_actions()
	function pin_options() {
?>
<div class="updated"><p><?php _e('There is no available option for this Pin', 'wp-slimstat-ex'); ?></p></div>
<?php
	}
	
	// set 'options' to 1 on pin_actions()
	function pin_update_options() {
	}

	// when pin is activated.... what to do? 
	function activate_action() {
	}

	// when pin is deactivated.... what to do?
	function deactivate_action() {
	}

	// All pins has it's own pin_compatible() function, 
	// If your pin is compatible on any version of slimstat,
	// Just return array('compatible'=>true);
	function pin_compatible() {
		global $SlimCfg;
		return array(
			'compatible'=>false, 
			// no html, just text message only.
			'message'=>sprintf(__('This pin does not work with WP-SlimStat-Ex v<em>%s</em>', 'wp-slimstat-ex'), $SlimCfg->version)
		);
	}

	// when 'extra_table' is 1 on pin_actions() and $PIN_NAME->extra_table is set.
	function maybe_create_extra_table($extra_table, $check_only=false) {
		global $SlimCfg;
		if(!is_array($extra_table) || empty($extra_table))
			return;
		require_once(SLIMSTATPATH . 'lib/setup.php');
		foreach($extra_table as $tname=>$structure) {
			$table_name = $SlimCfg->tbPrefix.$tname;
			$query = "CREATE TABLE {$table_name} ( {$structure} )";
			$create_table = SSSetup::maybe_create_table($table_name, $query, $check_only);
			if(!$create_table)
				return false;
		}
		return true;
	}

	function update_pin_options($pins) {
		if(!isset($_POST['slimstat_pin_options_submit']))
			return;
		if(!get_option('wp_slimstat_ex_pin_options')) {
			update_option('wp_slimstat_ex_pin_options', array());
		}
		foreach($pins as $pin) {
			$pin[0]->pin_update_options();
		}
		echo '<div class="updated fade"><p>'.__('Options saved.').'</p></div>';
	}

	function pin_option_menu_bar($pins) {
		if(!isset($pins[0][1]->id))
			return;
//		return;// to do...
?>
<script type="text/javascript">//<![CDATA[
var current_pin_option;
function toggle_pin_otions(el, tid) {
	var panel = document.getElementById(tid);
	if(!current_pin_option)
		current_pin_option = document.getElementById('pin_option_<?php echo $pins[0][1]->id; ?>');
	if(!panel || current_pin_option == panel) 
		return;
	current_pin_option.style.display = 'none';
	panel.style.display = '';
	current_pin_option = panel;
}
//]]></script>
	<div id="pin_option_menu_bar" style="font-size:120%; padding: 8px 10px;"> |
<?php
		foreach($pins as $pin) {
?>
	<span style="padding: 2px;"><a href="javascript:void(0)" onclick="toggle_pin_otions(this, 'pin_option_<?php echo $pin[1]->id; ?>'); return false;"><?php echo $pin[1]->title; ?></a></span> |
<?php
		}
?>
	</div>
<?php
	}

	function get_option($name) {
		global $wp_sspin_options;
		if(!is_array($wp_sspin_options))
			$wp_sspin_options = array();

		if(isset($wp_sspin_options[$name]))
			return $wp_sspin_options[$name];
		$options = get_option('wp_slimstat_ex_pin_options');
		if(!$options) {
			$options = array();
			update_option('wp_slimstat_ex_pin_options', $options);
		}
		$wp_sspin_options = $options;		
		if(isset($options[$name]))
			return $options[$name];
		return false;
	}

	function update_option($name, $newval) {
		global $wp_sspin_options;
		if(!is_array($wp_sspin_options))
			$wp_sspin_options = array();

		$options = get_option('wp_slimstat_ex_pin_options');
		$oldval = $options[$name];
		if($newval == $oldval)
			return;
		$options[$name] = $newval;
		$wp_sspin_options[$name] = $newval;
		update_option('wp_slimstat_ex_pin_options', $options);
	}

	function delete_option($name) {
		global $wp_sspin_options;
		$options = SSPins::get_option($name);
		if(isset($options[$name])) {
			unset($options[$name]);
			$wp_sspin_options = $options;
		}
		update_option('wp_slimstat_ex_pin_options', $options);
	}

	function getPin($id, $active=0) {
		global $wpdb, $SlimCfg;
		$and_active = $active ? " AND `active` = 1 " : "";
		$query = "SELECT * FROM {$SlimCfg->table_pins} WHERE `id` = {$id} {$and_active} LIMIT 1";
		return $wpdb->get_row($query);
	}

	function _getPinID($name) {
		global $wpdb, $SlimCfg;
		$query = "SELECT `id` FROM `".$SlimCfg->table_pins."` WHERE `name` = '".$name."' LIMIT 1 ";
		if($row = $wpdb->get_row($query))
			return $row->id + 100;
		else return false;
	}
	
	function _getMoID($name, $num) {
		global $wpdb, $SlimCfg;
		$query = "SELECT `id` FROM `".$SlimCfg->table_pins."` WHERE `name` = '".$name."' LIMIT 1 ";
		if($row = $wpdb->get_row($query)) $pinid = $row->id; else return false;
		$id = ($pinid * 100) + 1 + $num;
		return $id;
	}

	function _getPins($active=0, $type=0, $force=false) {
		global $wpdb, $SlimCfg, $wp_sspins;
		if(!isset($wp_sspins) || !is_array($wp_sspins))
			$wp_sspins = array();

		$active = (int)$active; // for older versions.
		if(!$force && isset($wp_sspins[$active][$type])) 
			return $wp_sspins[$active][$type];

		$is_active = ($active)?"`active` = 1":"(1 = 1)";
		// type:: 0 = panel, 1 = function, 2 = both, 3 = 0+2, 4 = 1+2, 5 = all pins
		switch($type) {
			case 0:case 1:case 2:
				$pin_type = " AND `type` = $type";
			break;
			case 3:
				$pin_type = " AND (`type` = 0 OR `type` = 2)";
			break;
			case 4:
				$pin_type = " AND (`type` = 1 OR `type` = 2)";
			break;
			case 5:
				$pin_type = "";
			break;
		}
		$q = "SELECT * FROM `".$SlimCfg->table_pins."` WHERE ".$is_active.$pin_type." ORDER BY title";
		if($pins = $wpdb->get_results($q)) {
			$wp_sspins[$active][$type] = $pins;
		} else
			$wp_sspins[$active][$type] = array();
		return $wp_sspins[$active][$type];
	}

	function delete_pin($where_clause) {
		global $wpdb, $SlimCfg, $wp_sspins;
		if(!$wpdb->get_row("SELECT * FROM {$SlimCfg->table_pins} WHERE {$where_clause} LIMIT 1"))
			return;// not exists.
		$delete_pin = $wpdb->query("DELETE FROM {$SlimCfg->table_pins} WHERE {$where_clause} LIMIT 1");
		if(false === $delete_pin)// if failed to delete row, just deactivate pin.
			$wpdb->query("UPDATE {$SlimCfg->table_pins} SET `active` = 0 WHERE {$where_clause} LIMIT 1");
		$wp_sspins = array();
	}

	function findPins() {
		global $wpdb, $SlimCfg;
		$sspins_root = SLIMSTATPATH.'pins';
		if ( !is_dir( $sspins_root ) )
			return;

		$pins_dh = opendir( $sspins_root );
		$myPins = SSPins::_getPins(0,5);
		foreach ($myPins as $current) {
			if( !is_dir($sspins_root.'/'.$current->name ) || 
					!file_exists($sspins_root.'/'.$current->name.'/pin.php') ) {
				SSPins::delete_pin("`name` = '{$current->name}' ");
			}
		}
		$myPins = SSPins::_getPins(0,5);

		while ( ( $pin_dir = readdir( $pins_dh ) ) !== false ) {
			if( $pin_dir{0} == '.' || !is_dir( $sspins_root.'/'.$pin_dir ) || !file_exists( $sspins_root.'/'.$pin_dir.'/pin.php' ) )
				continue;

			$Pinfo = array('title'=>'', 'author'=>'', 'url'=>'', 'text'=>'', 'version'=>'', 'type'=>0);
			$Moinfo = array();
			$q = '';
			ob_start();
			@include_once($sspins_root.'/'.$pin_dir.'/pin.php');
			if(!class_exists($pin_dir)) {
				$get_pin = $wpdb->get_row("SELECT * FROM $SlimCfg->table_pins WHERE name = '{$pin_dir}' LIMIT 1");
				if($get_pin && $get_pin->active == 1)
					$deactivate = $wpdb->query("UPDATE $SlimCfg->table_pins SET active = 0 WHERE name = '{$pin_dir}' LIMIT 1");
				ob_end_clean();
				continue;
			}
			eval('$temp_pin = new $pin_dir();');
			$Pinfo['name'] = $pin_dir;
			$Pinfo = array_merge($Pinfo, (array)$temp_pin->Pinfo);
			$Moinfo = array_merge($Moinfo, (array)$temp_pin->Moinfo);

			$Pinfo['title'] = (empty($Pinfo['title']))?$Pinfo['name']:$Pinfo['title'];
			foreach($Moinfo as $num=>$_info) {
				if(!isset($_info['name']) || !method_exists($temp_pin, $_info['name']))
					unset($Moinfo[$num]);
			}
			if(empty($Moinfo))// it's functionable Pin
				$Pinfo['type'] = 1;
			$Moinfo = $wpdb->escape(serialize($Moinfo));

			foreach($myPins as $myPin) {
				if ($myPin->name != $Pinfo['name'])
					continue;
				if($myPin->version != trim($Pinfo['version'])) {
					$q = "UPDATE `".$SlimCfg->table_pins."` SET `author` = '".trim($Pinfo['author'])."',
						`title` = '".trim($Pinfo['title'])."',
						`url` = '".trim($Pinfo['url'])."',
						`text` = '".trim($Pinfo['text'])."',
						`modules` = '".$Moinfo."',
						`version` = '".trim($Pinfo['version'])."',
						`type` = ".(int)$Pinfo['type']."
						WHERE `name` = '".trim($Pinfo['name'])."' ";
				} else $q = 'none';
			}
			if ($q == '') {
				$q = "INSERT INTO `".$SlimCfg->table_pins."` (`name`, `title`, `author`, `url`, `text`, `modules`, `version`, `active`, `type`) 
					VALUES ('".trim($Pinfo['name'])."', '".trim($Pinfo['title'])."', '".trim($Pinfo['author'])."', '".trim($Pinfo['url'])."', 
						'".trim($Pinfo['text'])."', '".$Moinfo."', '".trim($Pinfo['version'])."', '0', '".(int)$Pinfo['type']."')";
			}
			if($q != 'none')
				$update_or_insert = $wpdb->query($q);
			ob_end_clean();
		}// end while
	}

	function PinMenulinks() {
		global $SlimCfg;
		$pins =& SSPins::_getPins(1, 3);
		$r = '';
		if (!empty($pins)) {
			foreach ($pins as $pin) {
				$name = $pin->name;
				$id = $pin->id + 100;
				$r .= ' | ';
				if($SlimCfg->option['use_ajax']) {
					$r .= '<a id="slm'.$id.'" class="slm" href="#" onclick="SlimStat.panel('.$id.'); return false;"> ';
				} else {
					$r .= '<a id="slm'.$id.'" class="slm'.(($SlimCfg->get['pn'] == $id)?' slm_current':'').'" href="?page=wp-slimstat-ex&amp;panel='.$id.'"> ';
				}
				$r .= ''.__($pin->title, 'wp-slimstat-ex').'</a>'."\n";
			}
		return $r;
		}
	}

	function _incPins($type = 0) {
		global $wpdb, $SlimCfg;
		$pins = SSPins::_getPins(1, $type);
		if(!is_array($pins) || empty($pins))
			return;
		foreach($pins as $pin) {
			$file = SLIMSTATPATH . 'pins/'. $pin->name . '/pin.php';
			if(!file_exists($file))
				continue;
			include_once($file);
		}
	}

	function _resetPins() {
		global $wpdb, $SlimCfg;
		$query = "TRUNCATE TABLE `".$SlimCfg->table_pins."` ";
		if($wpdb->query($query) === false)
			return false;
		return true;
	}

	function current_filters($pid=1){
		global $SlimCfg;
		$use_ajax = $SlimCfg->option['use_ajax'];
		$output = '';
		if(!empty($SlimCfg->get['fi']) && !empty($SlimCfg->get['ff'])) {
			switch($SlimCfg->get['ff']) {
				case 0:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Domain', 'wp-slimstat-ex');
				break;
				case 1:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Search string', 'wp-slimstat-ex');
				break;
				case 2:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Resource', 'wp-slimstat-ex');
				break;
				case 3:
				$fi_val = $SlimCfg->get['fi'];
				$fi_title = __('Remote IP', 'wp-slimstat-ex');
				break;
				case 4:
				$fi_val = SSFunction::_translateBrowserID($SlimCfg->get['fi']);
				$fi_title = __('Browser', 'wp-slimstat-ex');
				break;
				case 5:
				$fi_val = SSFunction::_translatePlatformID($SlimCfg->get['fi']);
				$fi_title = __('Platform', 'wp-slimstat-ex');
				break;
				case 6:
				$fi_val =  __($SlimCfg->get['fi'], 'wp-slimstat-ex');
				$fi_title = __('Country', 'wp-slimstat-ex');
				break;
				case 7:
				$fi_val =  __($SlimCfg->get['fi'], 'wp-slimstat-ex');
				$fi_title = __('Language', 'wp-slimstat-ex');
				break;
				case 99:
				$fi_val =  __($SlimCfg->get['fi'], 'wp-slimstat-ex');
				$fi_title = __('Custom', 'wp-slimstat-ex');
				default:
				break;
			}
			$output .= "\t".'<br /><br /><span class="filter_string">';
			$output .= $fi_title.' : '.$fi_val.'</span>';
			if($use_ajax) 
				$output .= ' [ <a href="#" onclick="SlimStat.panel(\''.$SlimCfg->get['pn'].'\', \''.$SlimCfg->get['fd_encode'].'\'); return false;"';
			else
				$output .= ' [ <a href="?page=wp-slimstat-ex&amp;panel='.$SlimCfg->get['pn'].$SlimCfg->get['fd_encode'].'"';
			$output .= ' id="reset-filters" title="'.__('Reset filters', 'wp-slimstat-ex').'">'.__('Reset filters', 'wp-slimstat-ex').'</a> ]'."\n";
		}
		if(!empty($SlimCfg->get['fd'])) {
			$dt_start = date( __('d/m/Y H:i', 'wp-slimstat-ex'), $SlimCfg->sstime($SlimCfg->get['fd'][0]) );
			$dt_end = date( __('d/m/Y H:i', 'wp-slimstat-ex'), $SlimCfg->sstime($SlimCfg->get['fd'][1]) );
			$output .= "\t".'<br /><br /><span class="filter_string">';
			$output .= $dt_start.' - '.$dt_end.'</span>';
			$output .= ' [ <a '.(($use_ajax)?'href="#" onclick="SlimStat.panel(\''.$pid.'\', \''.$SlimCfg->get['fi_encode'].'\'); return false;"':'href="?page=wp-slimstat-ex&amp;panel='.$pid.'"').' id="reset-interval" title="'.__('Reset interval', 'wp-slimstat-ex').'">'.__('Reset interval', 'wp-slimstat-ex').'</a> ]';
		}
		return $output;
	}

	function _filterIntervalLink($filter_clause) {
		global $SlimCfg;
		$output = "";
		$class = 'fd-link';
		$filter_img = "<img src=\"".$SlimCfg->pluginURL."/css/filter-self.gif\" alt=\"Filter\" style=\"vertical-align:bottom;\" />";
		$pinid =& $this->getPinID();
		$use_ajax = $SlimCfg->option['use_ajax'];
		$href = ($use_ajax)?"#":"?page=wp-slimstat-ex&amp;panel=".$pinid;
		$output .= "<br />\n";
		$output .= "\t<div class=\"interval-filter\">&nbsp;&nbsp;<span>".__('Time interval', 'wp-slimstat-ex')." : \n";
		// today
		$dt_end = ($SlimCfg->dt[1] + 86399);
		$filter_url = '&amp;fd='.$SlimCfg->dt[1].'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;Today&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('Today', 'wp-slimstat-ex').$filter_img."</a> | ";
		// yesterday
		$dt_start_svr = ($SlimCfg->dt[1] - 86400);
		$dt_end = $SlimCfg->dt[1]-1;
		$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;Yesterday&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('Yesterday', 'wp-slimstat-ex').$filter_img."</a> | ";
		// this week
		$dt_start = $SlimCfg->dt[0];
		$dt_end = ($SlimCfg->dt[1] + 86399);
		while ( date( "w", $dt_start ) !=  1 ) { // move back to start of this week (1:Monday, 0:Sunday)
			$dt_start -= 86400;
		}
		$dt_start_svr = $SlimCfg->sstime($dt_start, true); // back to server time
		if ($dt_end - $dt_start_svr <= 0 ) $dt_start_svr = $SlimCfg->dt[1];
		$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;This week&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('This week', 'wp-slimstat-ex').$filter_img."</a> | ";
		// last week
		$dt_end = $dt_start_svr - 1;
		$dt_start_svr = ($dt_start_svr - 604800);
		$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;Last week&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('Last week', 'wp-slimstat-ex').$filter_img."</a> | ";
		// this month
		$dt_start = $SlimCfg->dt[0];
		$dt_end = ($SlimCfg->dt[1] + 86399);
		while ( date( "j", $dt_start ) > 1 ) { // Move back to start of this month
			$dt_start -= 86400;
		}
		$dt_start_svr = $SlimCfg->sstime($dt_start, true); // back to server time
		$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;This month&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('This month', 'wp-slimstat-ex').$filter_img."</a> | ";
		// last month
		$dt_end = $dt_start_svr - 1;
		$dt_start = mktime( 0, 0, 0, date( "n", $dt_start ) - 1, 1);
		$dt_start_svr = $SlimCfg->sstime($dt_start, true);
		$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View stats for &#039;Last month&#039;', 'wp-slimstat-ex')."\"";
		$output .= ($use_ajax)?" onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\"":'';
		$output .= ">";
		$output .= __('Last month', 'wp-slimstat-ex').$filter_img."</a>";

		$output .= SSPins::current_filters($pinid);		

		$output .= "</span></div>\n";
		return $output;
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSPins();
		}
		return $instance[0];
	}

}// end of class
endif;
?>