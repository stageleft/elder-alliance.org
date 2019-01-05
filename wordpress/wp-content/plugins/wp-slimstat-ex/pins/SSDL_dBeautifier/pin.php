<?php
/*
Module Name : Download Beautifier
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
*/

/* Please fill in $Pinfo values */

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSDL_dBeautifier extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'dBeautifier Downloads',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Show download stats for Download Beautifier(http://binslashbash.org/)',
		'version' => '0.5',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_moduleTop', 'title' => 'Top downloads' ),
		1 => array( 'name' => '_moduleRecent', 'title' => 'Recent downloads' ),
	);

	var $check_table;
	var $table_error = '<br /><div class="updated fade"><p style="text-align:center;font-size:1.2em;padding:4px 2px;">Cannot find &quot;dBClicks&quot; table</p></div>';

	function SSDL_dBeautifier() {
		$this->_isDBDetected();
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
			return array	('compatible' => false, 'message' => 'dBeautifier Downloads is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}
/* DO NOT CHANGE END */

	function _replaceFilterClause($filter) {
		$replaced = str_replace('%%COLUMN%%', 'ts.dBFile', $filter);
		return $replaced;
	}

	function _isDBDetected() {
		global $wpdb, $table_prefix;
		
		$_table_exists = false;
		$_table = $table_prefix."dBClicks";
		foreach ( $wpdb->get_col("SHOW TABLES", 0) as $table ) {
			if ( $table == $_table ) {
				$_table_exists = true;
				break;
			}
		}
		$this->check_table = $_table_exists;
	}

	function _moduleTop($filter_clause) {
		global $wp_version, $table_prefix, $SlimCfg;
		if(!$this->check_table) 
			return $this->table_error;
		$filter_clause = $this->_replaceFilterClause($filter_clause);
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('File name', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('last', 'wp-slimstat-ex');
		$_query[0][0] = "SELECT REPLACE(ts.dBFile, 'http://', '') AS file_name, COUNT(*) AS countall, 
									MAX(ts.dBTime) AS dt
								FROM ".$table_prefix."dBClicks ts
								WHERE $filter_clause 
								GROUP BY ts.dBFile
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=1'.$SlimCfg->get['fd_encode'];
		$_desc[0]['file_name'] = '%%basename%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$_value[0] = array('integer','date');
		$moid =& $this->getMoID(0);

		return SSFunction::getModule( $moid, $_titles,  $_query,  $_desc, $_value, $filter_clause, false, false );
	}

	function _moduleRecent($filter_clause) {
		global $table_prefix, $SlimCfg;
		if(!$this->check_table) 
			return $this->table_error;
		$filter_clause = $this->_replaceFilterClause($filter_clause);
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('File name', 'wp-slimstat-ex');
		$_titles[1] = __('When', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT REPLACE(ts.dBFile, 'http://', '') AS file_name, 
									ts.dBIP AS remote_addr, ts.dBTime AS dt  
								FROM ".$table_prefix."dBClicks ts
								WHERE $filter_clause 
								ORDER BY ts.dBTime DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=1'.$SlimCfg->get['fd_encode'];
		$_desc[0]['file_name'] = '%%basename%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$_desc[0]['remote_addr'] = '&nbsp;&nbsp;<span> &#64;&nbsp; %%flag%% ';
		$filter = '&amp;fi=%%encode%%&amp;ff=3&amp;ft=0';
		$_desc[0]['remote_addr'] .= '%%remote_ip%% '.SSFunction::filterBtn($filter, 3).' </span> ';
		$_value[0] = array('date');
		$moid =& $this->getMoID(1);
		
		return SSFunction::getModule( $moid, $_titles,  $_query,  $_desc, $_value, $filter_clause, true, false );
	}

	function _displayPanel() {
		if(!$this->check_table) {
			echo $this->table_error;
		} else {
			// If a filter by keyword was set, add it to the SQL WHERE clause
			$_filter = SSFunction::filter_switch();
			$_filter = $this->_replaceFilterClause($_filter);
			$_html = $this->_filterIntervalLink($_filter);
			$_html .= $this->_moduleRecent( $_filter );
			$_html .= $this->_moduleTop( $_filter );
			echo $_html;
		}
	}

}//end of class
?>