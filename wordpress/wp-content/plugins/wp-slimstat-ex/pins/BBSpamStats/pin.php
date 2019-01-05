<?php
/*
Module Name : Spam Stats
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
Powered by SlimStat(http://wettone.com/code/slimstat)'s PathStats plugin
*/

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class BBSpamStats extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'Bad Behavior',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Show Bad Behavior Stats(http://www.homelandstupidity.us/software/bad-behavior/).',
		'version' => '0.7',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_moduleBBSpamStats', 'title' => 'Recent Visitors Bad Behaviour' ),
	);

	var $rows = 50;//limit rows.
	var $show_unblocked = false; //show unblocked behavior(key:00000000) or not? show = true, hide = false
	var $check_table;
	var $table_error = '<br /><div class="updated fade"><p style="text-align:center;font-size:1.2em;padding:4px 2px;">Cannot find &quot;Bad Behavior&quot; table</p></div>';
	var $plugin_error = '<br /><div class="updated fade"><p style="text-align:center;font-size:1.2em;padding:4px 2px;">Make sure you activated &quot;Bad Behavior&quot; plugin</p></div>';

	function BBSpamStats() {
		$this->_isBBDetected();
	}

	function &getPinID() {
		$name = get_class($this);
		$id =& $this->_getPinID($name);
		return $id;
	}

	function &getMoID($num) {
		$pinid =& $this->getPinID();
		$id = ($pinid *100) + 1 + $num;
		return $id;
	}

	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '1.6') {
			return array	('compatible' => false, 'message' => 'BBSpamStats is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _replaceFilterClause($filter) {
		$replaced = str_replace('%%COLUMN%%', 'ts.ip', $filter);
		return $replaced;
	}

	function _moduleBBSpamStats($filter_clause) {
		global $wpdb, $table_prefix, $SlimCfg;
		$use_ajax = $SlimCfg->option['use_ajax'];
		if(!defined('BB2_CORE'))
			return $this->plugin_error;
		if(!$this->check_table)
			return $this->table_error;
		$filter_clause = $this->_replaceFilterClause($filter_clause);
		$offset = $_GET['offset'];
		$offset = (isset($offset) && !empty($offset))?$offset:0;
		$offset = ($offset * $this->rows);

		$str = "";
		// get requests
		$query = "SELECT ts.id, ts.ip, UNIX_TIMESTAMP(ts.date) AS dt, ts.request_uri AS uri, ";
		$query .= " ts.server_protocol AS ptc, ts.request_method AS method, ts.user_agent AS ua, ts.key ";
		$query .= " FROM ".$table_prefix."bad_behavior ts";
		$query .= " WHERE ".$filter_clause." ";
		$query .= ($this->show_unblocked)?"":" AND ts.key <> '00000000' ";
		$query .= " ORDER BY dt DESC, ts.ip";
		$query .= " LIMIT ".$offset.",".$this->rows."";

		if ( $result = mysql_query( $query ) ) {
			$prev_visit = 0;
			$visits = array();
			$visit = array();
			$pages = array();
			while ( $assoc = mysql_fetch_assoc( $result ) ) {
				if ( $assoc["ip"] != $prev_visit && !empty( $visit ) ) {
					$visits[] = $visit;
					$visit = array();
				}
				$visit[] = $assoc;
				$prev_visit = $assoc['ip'];
			}
			if ( !empty( $visit ) ) {
				$visits[] = $visit;
			}
			$pinid =& $this->getPinID();
			$moid =& $this->getMoID(0);
			$str .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<tr><th class=\"first\">".__('Visitor', 'wp-slimstat-ex').($use_ajax?" &mdash; <a href=\"#\" onclick=\"SlimStat.toggleAllSubs($moid, this, ".count($visits).");return false;\">expand</a> (all)":"")."</th>";
			$str .= "<th class=\"second\">".__('When', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Method (protocol)', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Support Key', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Country', 'wp-slimstat-ex')."</th></tr>\n";
			
			$usr_today = time();
			$svr_today = mktime( 0, 0, 0, date( "n", $usr_today ), date( "d", $usr_today ), date( "Y", $usr_today ) );
			$i = 0;
			foreach ( $visits as $visit ) {
				$subcontgl_class = ($i==0)?'':' collapsed';
				$is_today = ( $visit[0]["dt"] >= $svr_today );
				$mindt = $this->time_label( $visit[0]["dt"] );
				$maxdt = $this->time_label( $visit[ sizeof( $visit )-1 ]["dt"] );
				$filter1 = "&amp;fi=". $visit[0]["ip"]."&amp;ff=3&amp;ft=0".$SlimCfg->get['fd_encode'];
				$filter2 = "&amp;fi=". $visit[0]["ip"]."&amp;ff=99&amp;ft=0".$SlimCfg->get['fd_encode'];

				$str .= "<tr><td class=\"accent first\">".SSFunction::_whoisLink($visit[0]["ip"])." (".count($visit).") ".SSFunction::filterBtn($filter1, 3).SSFunction::filterBtn($filter2, $pinid)." ".($use_ajax?"<a href=\"#\" onclick=\"SlimStat.toggleSub($i, this, $moid);return false;\" class=\"subcontgl$subcontgl_class\" id=\"subcontgl_".$i."\"><img src=\"".$SlimCfg->pluginURL."/css/blank.gif\" class=\"icons\" alt=\"toggle\"></a>":"")."</td>";
				$str .= "<td class=\"accent second\">";
				if ( $is_today ) {
					$str .= ( ( $mindt == $maxdt ) ? $mindt : $mindt."-".$maxdt );
				} else {
					$str .= $this->time_label( $visit[0]["dt"], time() );
				}
				$str .= "</td>";
				$str .= "<td class=\"accent third\">".substr($visit[0]["method"],-4).(($visit[0]["ptc"] != '')?' ('.$visit[0]["ptc"].')':'')."</td>";
				$str .= "<td class=\"accent third\">".$this->print_support_key($visit[0]["ip"], $visit[0]["key"])."</td>";
				$country = "c-".strtolower(SSTrack::_determineCountry($visit[0]["ip"]));
				$str .= "<td class=\"accent third\">".SSFunction::get_flag( $country )." ".__($country, 'wp-slimstat-ex')."</td></tr>\n";
				
				$prev_dt = "";
				$prev_ua = "";
				foreach ( $visit as $hit ) {
					$subcon_class = ($i==0)?' subcons':' collapsed-subcons';
					if(!$use_ajax) $subcon_class = '';
					$resource2title = SSFunction::_guessPostTitle($hit["uri"]);
					$svr_todayhit = mktime( 0, 0, 0, date( "n", $visit[0]["dt"] ), date( "d", $visit[0]["dt"] ), date( "Y", $visit[0]["dt"] ) );
					$is_todayhit = ( $hit["dt"] >= $svr_todayhit );
					$str .= "<tr class=\"subcon_".$i."$subcon_class\"><td class=\"subcon first\">";
					$str .= "<a href=\"".$hit["uri"]."\" class=\"external\"";
					$str .= " title=\"Resource: ".strip_tags($resource2title)."\">";
					$str .= "<img src=\"".$SlimCfg->pluginURL."/css/external.gif\" width=\"9\" height=\"9\" alt=\"go\" /></a>&nbsp;&nbsp;";
					$filter = "&amp;fi=".urlencode($hit["uri"])."&amp;ff=2&amp;ft=0";
					$str .= $resource2title;
					$str .= "</td>";
					$dt_label = (($is_todayhit)?'':$SlimCfg->_date("j M, ", $hit["dt"])).$this->time_label( $hit["dt"] );
					if ( ( !$is_today && $prev_dt == "" ) || ( $mindt != $maxdt && $dt_label != $prev_dt ) ) {
						$str .= "<td class=\"second\">".$dt_label."</td>";
					} else {
						$str .= "<td class=\"second\">&nbsp;</td>";
					}
					$str .= "<td colspan=\"3\" class=\"last third\" style=\"text-align:center;\">";
					$str .= (( $hit["ua"] != $prev_ua )?$SlimCfg->truncate($hit["ua"], 64):"&nbsp;")."</td>";
					$prev_dt = $dt_label;
					$prev_ua = $hit["ua"];
					$str .= "</tr>\n";					
				}
				$i++;
			}
			$str .= "</table>\n";
			return SSFunction::get_module_custom($moid, $str, 'full');
		}
	}

	function get_log($key="") {
		require_once(BB2_CORE . '/responses.inc.php');
		$key = trim($key);
		$response = bb2_get_response($key);
		if($response[0] == '00000000') return " title=\"unkwon behavior\""; 
		$output = " title=\"";
		$output .= $response['response']. ' :: '.((!empty($response['log']))?$response['log']:'No informations'). '"';
		return $output;
	}

	function print_support_key($ip, $key) {
		$ip = explode(".", $ip);
		$ip_hex = "";
		foreach ($ip as $octet) {
			$ip_hex .= str_pad(dechex($octet), 2, 0, STR_PAD_LEFT);
		}
		$support_key = implode("-", str_split($ip_hex.$key, 4));
		$link = "<a href=\"http://www.ioerror.us/bb2-support-key?key=".$support_key."\" onclick=\"window.open(this.href, 'SupportKey', 'width=800,height=500'); return false;\"".$this->get_log($key).">".$support_key."</a>";
		
		return $link;
	}

	function _isBBDetected() {
		global $wpdb, $table_prefix;
		
		$_table_exists = false;
		$_table = $table_prefix."bad_behavior";
		foreach ( $wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ( $table == $_table ) {
				$_table_exists = true;
				break;
			}
		}
		$this->check_table = $_table_exists;
	}

	function time_label( $_dt, $_compared_to_dt=0 ) {
		global $SlimCfg;
		$usr_dt = $_dt;
		if ( $_compared_to_dt == 0 ) {
			if ( $SlimCfg->_date( "a", $usr_dt ) == "" ) {
				return $SlimCfg->_date( "H:i", $usr_dt );
			} else {
				return strtolower( $SlimCfg->_date( "g:i a", $usr_dt ) );
			}
			//return strftime( "%r", $usr_dt );
		} elseif ( $_dt >=  strtotime( date( "j M Y 00:00:00", $_compared_to_dt ) ) ) {
			return $this->time_label( $_dt );
		} else {
			return $SlimCfg->_date( "j M", $usr_dt );
		}
	}

	function _displayPanel() {
		if(!defined('BB2_CORE')) {
			echo $this->plugin_error;
		} elseif ($this->check_table) { // if bad behavior table exists
			global $table_prefix, $SlimCfg;
			// If a filter by keyword was set, add it to the SQL WHERE clause
			$_filter = SSFunction::filter_switch();
			$filter_new = $this->_replaceFilterClause($_filter);

			$query = "SELECT COUNT(*) AS counts FROM ".$table_prefix."bad_behavior ts WHERE $filter_new";
			$query .= ($this->show_unblocked)?"":" AND `key` <> '00000000' ";
			$_html .= SSFunction::print_pages($query, $this->rows, $this->getPinID());

			$pinid =& $this->getPinID();
			$_html .= $this->current_filters($pinid);

			$_html .= $this->_moduleBBSpamStats( $_filter );
			
			echo $_html;
		} else {
			echo $this->table_error;
		}
	}

}//end of class

?>