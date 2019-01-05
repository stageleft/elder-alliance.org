<?php
/*
Module Name : PathStats
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Stephen Wettone, Cheon, Young-Min
Author URI : http://wettone.com/, http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
Powered by SlimStat(http://wettone.com/code/slimstat)'s PathStats plugin

Originally written by Stephen Wettone(http://wettone.com/code/slimstat)
*/

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSPathStats extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'PathStats',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Show paths taken by recent visitors',
		'version' => '0.7',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_modulePathStats', 'title' => 'Paths taken by recent visitors' ),
	);

	var $since;
	var $show_crawlers = false; //show crawlers or not?
	var $rows = 50;//limit rows.

	function SSPathStats() {
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
			return array	('compatible' => false, 'message' => 'PathStat is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _modulePathStats($filter_clause) {
		global $wpdb, $SlimCfg;
		$use_ajax = $SlimCfg->option['use_ajax'];
		$offset = $_GET['offset'];
		$offset = (isset($offset) && !empty($offset))?$offset:0;
		// get max visit
		$query = "SELECT MAX(ts.visit) FROM $SlimCfg->table_stats ts";
		$max_visit = $wpdb->get_var($query);
		
		$str = "";
		
		// get requests
		$query = "SELECT * FROM $SlimCfg->table_stats ts WHERE ";
		if(!$this->show_crawlers) {
			$query .= "ts.browser NOT IN (".implode(',', SSFunction::get_bot_array(true)).") AND ";
		}
		$query .= "ts.visit >= ".( $max_visit - ($this->rows * ($offset + 1) ) );
		$query .= " AND ts.visit < ".( $max_visit - ($this->rows * $offset) );
		$query .= " AND ts.resource NOT IN (0,1) ";
		$query .= " AND ".$filter_clause." ";
		$query .= " ORDER BY ts.visit DESC, ts.dt DESC";

		if ( $result = mysql_query( $query ) ) {
			$prev_visit = 0;
			$visits = array();
			$visit = array();
			$pages = array();
			while ( $assoc = mysql_fetch_assoc( $result ) ) {
				if ( $assoc["visit"] != $prev_visit && !empty( $visit ) ) {
					$visits[] = $visit;
					$visit = array();
				}
				$visit[] = $assoc;
				$prev_visit = $assoc['visit'];
			}
			if ( !empty( $visit ) ) {
				$visits[] = $visit;
			}
			$pinid =& $this->getPinID();
			$moid =& $this->getMoID(0);
			$str .= "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<tr><th class=\"first\">".__('Visitor', 'wp-slimstat-ex').($use_ajax?" &mdash; <a href=\"#\" onclick=\"SlimStat.toggleAllSubs($moid, this, ".count($visits).");return false;\">expand</a> (all)":"")."</th>";
			$str .= "<th class=\"second\">".__('When', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Browser', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Platform', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Country', 'wp-slimstat-ex')."</th></tr>\n";
			
			$usr_today = time();
			$svr_today = mktime( 0, 0, 0, date( "n", $usr_today ), date( "d", $usr_today ), date( "Y", $usr_today ) );
			$i = 0;
			foreach ( $visits as $visit ) {
				$subcontgl_class = ($i==0)?'':' collapsed';
				$visit_ip = long2ip($visit[0]["remote_ip"]);
				$is_today = ( $visit[0]["dt"] >= $svr_today );
				$mindt = $this->time_label( $visit[0]["dt"] );
				$maxdt = $this->time_label( $visit[ sizeof( $visit )-1 ]["dt"] );
				$filter = "&amp;fi=".$visit_ip."&amp;ff=3&amp;ft=0";
				$str .= "<tr><td class=\"accent first\">".SSFunction::_whoisLink($visit_ip)." (".count($visit).") ".SSFunction::filterBtn($filter, 3).SSFunction::filterBtn($filter, $pinid).($use_ajax?" <a href=\"#\" onclick=\"SlimStat.toggleSub($i, this, $moid);return false;\" class=\"subcontgl$subcontgl_class\" id=\"subcontgl_".$i."\"><img src=\"".$SlimCfg->pluginURL."/css/blank.gif\" class=\"icons\" alt=\"toggle\"></a>":"")."</td>";
				$str .= "<td class=\"accent second\">";
				if ( $is_today ) {
					$str .= ( ( $mindt == $maxdt ) ? $mindt : $mindt."-".$maxdt );
				} else {
					$str .= $this->time_label( $visit[0]["dt"], time() );
				}
				$str .= "</td>";
				$str .= "<td class=\"accent third\">".__(SSFunction::_translateBrowserID( $visit[0]["browser"] ), 'wp-slimstat-ex')."";
				if ( $visit[0]["version"] != '' ) {
					$str .= " ".htmlentities( $visit[0]["version"] );
				}
				$str .="</td><td class=\"accent third\">".__(SSFunction::_translatePlatformID($visit[0]["platform"]), 'wp-slimstat-ex')."</td>";
				$country = 'c-'.strtolower($visit[0]["country"]);
				$str .= "<td class=\"accent third\">".SSFunction::get_flag( $country )." ".__($country, 'wp-slimstat-ex')."</td></tr>\n";
				
				$prev_dt = "";
				foreach ( $visit as $hit ) {
					$hit['resource'] = SSFunction::_id2resource($hit['resource']);
					$subcon_class = ($i==0)?' subcons':' collapsed-subcons';
					if(!$use_ajax) $subcon_class = '';
					$resource2title = SSFunction::_guessPostTitle($hit["resource"]);
					$str .= "<tr class=\"subcon_".$i."$subcon_class\"><td class=\"subcon first\">";
					$str .= "<a href=\"".wp_specialchars($hit["resource"],1)."\" class=\"external\"";
					$str .= " title=\"Resource: ".strip_tags($resource2title)."\">";
					$str .= "<img src=\"".$SlimCfg->pluginURL."/css/external.gif\" width=\"9\" height=\"9\" alt=\"go\" /></a>&nbsp;&nbsp;";
					$filter = "&amp;fi=".urlencode($hit["resource"])."&amp;ff=2&amp;ft=0";
					$str .= $resource2title;
					$str .= "</td>";
					$dt_label = $this->time_label( $hit["dt"] );
					if ( ( !$is_today && $prev_dt == "" ) || ( $mindt != $maxdt && $dt_label != $prev_dt ) ) {
						$str .= "<td class=\"second\">".$dt_label."</td>";
					} else {
						$str .= "<td class=\"second\">&nbsp;</td>";
					}
					$prev_dt = $dt_label;
					if ( $hit["referer"] != "" && $hit["domain"] != $_SERVER['HTTP_HOST'] ) {
						$str .= "<td colspan=\"3\" class=\"last third\" style=\"text-align:center;\">";
						$filter = "&amp;fi=".urlencode( $hit["domain"] )."&amp;ff=0&amp;ft=1";
						$str .= "".htmlentities( $SlimCfg->truncate( $hit["domain"], 30 ) )."&nbsp;&nbsp;";
						$str .= "<a href=\"http://".wp_specialchars($hit["referer"],1)."\" class=\"external\" rel=\"nofollow\"";
						$str .= " title=\"Visit this referer\">";
						$str .= "<img src=\"".$SlimCfg->pluginURL."/css/external.gif\" width=\"9\" height=\"9\" alt=\"\" /></a>";
						$str .= ' '.SSFunction::filterBtn($filter, 3);
					} else {
						$str .= "<td colspan=\"3\" class=\"third\">&nbsp;</td>";
					}
					$str .= "</tr>\n";					
				}
				$i++;
			}
			
			$str .= "</table>\n";
			return SSFunction::get_module_custom($moid, $str, 'full');
		}
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
		global $SlimCfg;
		// Initialize content for this panel
		$myContent = '';
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();
		$query = "SELECT COUNT(DISTINCT ts.visit) AS counts FROM $SlimCfg->table_stats ts WHERE $myFilterClause";
		if(!$this->show_crawlers) {
			$query .= " AND ts.browser NOT IN (".implode(',', SSFunction::get_bot_array(true)).") ";
		}
		$myContent .= SSFunction::print_pages($query, $this->rows, $this->getPinID());

		$pinid =& $this->getPinID();
		$myContent .= $this->current_filters($pinid);

		$myContent .= $this->_modulePathStats( $myFilterClause );
		
		echo $myContent;
	}

}//end of class
?>