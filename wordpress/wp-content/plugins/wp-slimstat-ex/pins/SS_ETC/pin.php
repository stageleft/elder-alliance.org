<?php
/*
Module Name : Miscellaneous Stats
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
This is example of Wp-SlimStat-Ex Pin
*/

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SS_ETC extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'Miscellaneous',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Contain top spam ip, top spam trackback ip, most commented post, popular post.... etc.',
		'version' => '0.3',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_moduleTopCommented', 'title' => 'Top Commented' ),
		1 => array( 'name' => '_modulePopularArticle', 'title' => 'Popular Articles' ),
		2 => array( 'name' => '_moduleTopSpamIP', 'title' => 'Top Spam Comment IP' ),
		3 => array( 'name' => '_moduleTopSpingIP', 'title' => 'Top Spam Trackback IP' ),
	);

	function SS_ETC() {
		//nothing
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
			return array	('compatible' => false, 'message' => 'Miscellaneous Stats is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _replaceFilterClause($filter, $column) {
		$replaced = preg_replace('#\s+ts\.dt\s+#', " UNIX_TIMESTAMP({$column}) ", $filter);
		return $replaced;
	}

	function _moduleTopCommented($filter_clause) {
		global $wpdb, $SlimCfg;
		$filter_clause = $this->_replaceFilterClause($filter_clause, 'ts.comment_date');
		$query = "SELECT * , COUNT(*) AS counts, MAX(ts.comment_date) AS dt FROM $wpdb->comments ts
					WHERE ts.comment_approved = '1' AND ".$filter_clause."
					GROUP BY ts.comment_post_ID
					ORDER BY counts DESC
					LIMIT ".$SlimCfg->option['limitrows'];
		if($results = $wpdb->get_results($query)) {
			$str = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<tr><th class=\"first\">".__('Resource', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"second\">".__('Count', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Last', 'wp-slimstat-ex')."</th></tr>\n";
			foreach($results as $r) {
				$post_title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = $r->comment_post_ID LIMIT 1");
				$post_title = apply_filters('the_title', $post_title);
				$dt = strtotime($r->dt);
				$dt = ($dt >= time())?'Today, '.$SlimCfg->_date(__("H:i", 'wp-slimstat-ex'), $dt):$SlimCfg->_date(__("d M, H:i", 'wp-slimstat-ex'), $dt);
				$str .= '<tr>';
				$str .= '<td class="first"><a href="'.wp_specialchars(get_permalink($r->comment_post_ID), true).'" title="'.__('Visit this resource', 'wp-slimstat-ex').': '.($post_title).'">'.$post_title.'</a></td>';
				$str .= '<td class="second">'.$r->counts.'</td>';
				$str .= '<td class="third">'.$dt.'</td></tr>'."\n";
			}
			$str .= "</table>\n";
		} else {
			$str .= '<div class="noresults-msg">&nbsp;&nbsp;'.__('No results found', 'wp-slimstat-ex').'</div>';
		}
		$moid =& $this->getMoID(0);
		return SSFunction::get_module_custom($moid, $str, 'wide');
	}

	function _modulePopularArticle($filter_caluse) {
		global $wpdb, $wp_rewrite, $SlimCfg;
		$query = "SELECT MAX(ts.dt) AS maxdt, COUNT(*) AS countall, tr.rs_string, tr.rs_title
				FROM $SlimCfg->table_stats ts, $SlimCfg->table_resource tr
				WHERE tr.id = ts.resource
					AND (tr.rs_condition LIKE '%[post]' OR tr.rs_condition LIKE '%[page]')
					AND $filter_caluse
				GROUP BY ts.resource
				ORDER BY countall DESC
				LIMIT ".$SlimCfg->option['limitrows'];

		if($rows = $wpdb->get_results($query)) {
			$str = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<tr><th class=\"first\">".__('Resource', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Last', 'wp-slimstat-ex')."</th></tr>\n";
			foreach($rows as $r) {
				$rowstyle = ($rowstyle == ' class="tbrow"') ? ' class="tbrow-alt"' : ' class="tbrow"';
				$str .= '<tr'.$rowstyle.'>';

				$filter = "&amp;fi=".urlencode($r->rs_string)."&amp;ff=2&amp;ft=0".$SlimCfg->get['fd_encode'];
				$dt = $SlimCfg->sstime($r->maxdt);
				$dt = ($dt >= $SlimCfg->_mktime(time()))?'Today, '.$SlimCfg->_date(__("H:i", 'wp-slimstat-ex'), $dt):$SlimCfg->_date(__("d M, H:i", 'wp-slimstat-ex'), $dt);
				$str .= '<td class="first"><a href="'.wp_specialchars($r->rs_string, true).'" title="'.__('Visit this resource', 'wp-slimstat-ex').': '.strip_tags($r->rs_title).'">'.$r->rs_title.'</a> '.SSFunction::filterBtn($filter, 3).'</td>';
				$str .= '<td class="second">'.$r->countall.'</td>';
				$str .= '<td class="third">'.$dt.'</td>'."\n";

				$str .= '</tr>';
			}
			$str .= "</table>\n";
		} else {
			$str .= '<div class="noresults-msg">&nbsp;&nbsp;'.__('No results found', 'wp-slimstat-ex').'</div>';
		}
		$moid =& $this->getMoID(1);
		return SSFunction::get_module_custom($moid, $str, 'wide');
	}

	function _getTopSpammers($filter_clause, $type = 'comment') {
		global $wpdb, $SlimCfg;
		switch($type) {
			case 'trackback':
				$where_clause = " AND tr.rs_condition LIKE '[trackback]%' ";
			break;
			case 'comment': default:
				$where_clause = " AND tr.rs_condition LIKE '[add comment]' ";
			break;
		}
		$query = "SELECT INET_NTOA(ts.remote_ip) AS remote_ip_a, CONCAT('c-', LOWER(ts.country)) AS country, 
					COUNT(*) AS countall, MAX(ts.dt) AS maxdt 
				FROM $SlimCfg->table_stats ts, $SlimCfg->table_resource tr
				WHERE tr.id = ts.resource
					$where_clause 
					AND $filter_clause 
				GROUP BY ts.remote_ip 
				ORDER BY countall DESC 
				LIMIT ".$SlimCfg->option['limitrows'];

		if($results = $wpdb->get_results($query, ARRAY_A)) {
			$str = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$str .= "<tr><th class=\"first\">".__('Remote IP', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
			$str .= "<th class=\"third\">".__('Last', 'wp-slimstat-ex')."</th></tr>\n";
			$and_clause = ($type == 'comment')?"AND comment_type = ''":"AND (comment_type = 'trackback' OR comment_type = 'pingback')";
			foreach ($results as $r) {
				$query = "SELECT comment_ID FROM $wpdb->comments WHERE comment_author_IP = '".$r['remote_ip_a']."' AND comment_approved != 'spam' $and_clause LIMIT 1";
				$good_comment = $wpdb->get_row($query);
				if(!$good_comment) {
					$dt = $SlimCfg->sstime($r['maxdt']);
					$dt = ($dt >= $SlimCfg->_mktime(time()))?'Today, '.$SlimCfg->_date(__("H:i", 'wp-slimstat-ex'), $dt):$SlimCfg->_date(__("d M, H:i", 'wp-slimstat-ex'), $dt);
					$filter = "&amp;fi=". $r['remote_ip_a']."&amp;ff=3&amp;ft=0".$SlimCfg->get['fd_encode'];
					$str .= '<tr><td class="first">'.SSFunction::get_flag($r['country']).' '.SSFunction::_whoisLink($r['remote_ip_a']).' '.SSFunction::filterBtn($filter, 3).'</td>';
					$str .= '<td class="second">'.$r['countall'].'</td>';
					$str .= '<td class="third">'.$dt.'</td></tr>'."\n";
				}
			}
			$str .= '</table>';
		} else {
			$str .= '<div class="noresults-msg">&nbsp;&nbsp;'.__('No results found', 'wp-slimstat-ex').'</div>';
		}
		return $str;
	}

	function _moduleTopSpamIP($filter_clause) {
		$moid =& $this->getMoID(2);
		$table = $this->_getTopSpammers($filter_clause, 'comment');
		return SSFunction::get_module_custom($moid, $table);
	}

	function _moduleTopSpingIP($filter_clause) {
		$moid =& $this->getMoID(3);
		$table = $this->_getTopSpammers($filter_clause, 'trackback');
		return SSFunction::get_module_custom($moid, $table);
	}

	function _displayPanel() {
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();
		// Initialize content for this panel
		$myContent .= $this->_filterIntervalLink($myFilterClause);
		//filterd result wrapper
		$myContent .= '<div id="filterd_result">';
		$myContent .= $this->_moduleTopCommented( $myFilterClause );
		$myContent .= $this->_moduleTopSpamIP( $myFilterClause );
		$myContent .= $this->_moduleTopSpingIP( $myFilterClause );
		$myContent .= $this->_modulePopularArticle( $myFilterClause );
		$myContent .= '</div>';
		echo $myContent;
	}

}//end of class
?>