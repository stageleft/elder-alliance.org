<?php
/*
Module Name : Download Manager
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
This is example of Wp-SlimStat-Ex Pin
*/
/*
$Pinfo : Informations of this pin which will saved in database(usually 'wp_slim_pins')
	name => Pin's name. This must exactly same as Pin's class name (class SSDL_Mgr extends SSPins { .... )
	title => This will be showed at Wp-SlimStat's menu link ( Summary | Feeds | Details | .... | Downloads | .... )
	author => Author's name(can be your name)
	url => Author's or downloadable Pin's URL
	text => Pin's description
	version => current Pin's version

$Moinfo : Modules displayed on this Pin's panel. You must add your displayable modules in this array
			(module's number must start from "0" and each modules have child array values(name, title) )
		name : module's function name ( e.g. function _moduleTop() { .... } would be '_moduleTop')
		title : module's display name(title) which will be showed at module's header

Basic functions : 

	function Your_Class_Name() : Same name as class name ( e.g. class SSDL_Mgr extends.... function SSDL_Mgr() { ... } )
	function &getPinID() : get pin's ID from database. ( Pin's id(on database) + 100 )
	function &getMoID($num) : get module's ID from database. $num is array number you setted on $Moinfo ( e.g. 0=>array('name'=>........) )
	function _displayPanel() : Usually contains some base functions(below). Actually, this is the main function to be showed on Wp-SlimStat page.
				(You must "echo" your contents not "return" )
					$myFilters = $this->get['fd_encode'].$this->get['fi_encode']; // Retrieve filters from url
					$_html .= $this->_getFilterForm();// insert filter form. 
						( You must wrap your contnets with <div id="filterd_result"> your contents </div> )
					$_filter = $this->filter_switch();// If a filter by keyword was set, add it to the SQL WHERE clause

You can call SlimStat's options and config by global $SlimCfg;  $SlimCfg->table_stats; $SlimCfg->option['visit_type'] ....etc.
You surely can use any functions in 'wp-slimstat-ex/lib/functions.php' file( SSFunction class ) by SSFunction::_function_name()
It would be more better if you check modules.php.
See below example.
*/

/* Please fill in $Pinfo values */

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSDL_MgrN extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'Downloads',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Download manager database version. Show download stats for download-manager(http://guff.szub.net/plugins/)',
		'version' => '0.6',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_moduleTop', 'title' => 'Top downloads' ),
		1 => array( 'name' => '_moduleRecent', 'title' => 'Recent downloads' ),
	);

	var $check_table;
	var $table_error = '<br /><div class="updated fade"><p style="text-align:center;font-size:1.2em;padding:4px 2px;">Cannot find &quot;Downloads&quot; table</p></div>';

	function SSDL_MgrN() {
		$this->_isDMGRDetected();
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
			return array	('compatible' => false, 'message' => 'Download Manager is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _isDMGRDetected() {
		global $wpdb, $table_prefix;
		
		$_table_exists = false;
		$_table = $table_prefix."downloads";
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
		$_query[0][0] = "SELECT ts.file_name, COUNT(*) AS countall, MAX(UNIX_TIMESTAMP(ts.date)) AS dt
								FROM {$table_prefix}downloads ts
								WHERE $filter_clause 
								GROUP BY ts.file_name 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$_desc[0]['file_name'] = '%%short%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$_value[0] = array('integer','date');
		$moid =& $this->getMoID(0);

		return SSFunction::getModule( $moid, $_titles, 	$_query, 	$_desc, $_value, $filter_clause, false, false );
	}

	function _replaceFilterClause($filter) {
		global $SlimCfg;
		$replaced = str_replace('%%COLUMN%%', 'ts.file_name', $filter);
		$replaced = preg_replace('#\s+ts\.dt\s+#', ' UNIX_TIMESTAMP(ts.date) ', $replaced);
		return $replaced;
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
		
		$_query[0][0] = "SELECT ts.file_name, ts.referer, ts.remote_addr, UNIX_TIMESTAMP(ts.date) AS dt  
								FROM {$table_prefix}downloads ts
								WHERE $filter_clause 
								ORDER BY dt DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$_desc[0]['referer'] ='<span class="external"><a%%encode_prefix%% title="'.__('Downloaded from', 'wp-slimstat-ex').': %%short%%"><img src="'.$SlimCfg->pluginURL.'/css/external.gif" alt="" /></a></span>&nbsp;&nbsp;';
		$_desc[0]['file_name'] = '%%medium%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$_desc[0]['remote_addr'] = '&nbsp;&nbsp;<span> &#64;&nbsp; %%flag%% ';
		$filter = '&amp;fi=%%encode%%&amp;ff=3&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$_desc[0]['remote_addr'] .= '%%remote_ip%% '.SSFunction::filterBtn($filter, 3).' </span> ';
		$_value[0] = array('date');
		$moid =& $this->getMoID(1);
		
		return SSFunction::getModule( $moid, $_titles, 	$_query, 	$_desc, $_value, $filter_clause, true, false );
	}

	function _displayPanel() {
		if($this->check_table) {
		// Initialize content for this panel
//		$_html .= SSFunction::_getFilterForm();
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$_filter = SSFunction::filter_switch();
		$_filter = $this->_replaceFilterClause($_filter);
		$_html = $this->_filterIntervalLink($_filter);
		//filterd result wrapper
		$_html .= '<div id="filterd_result">';
		$_html .= $this->_moduleRecent( $_filter );
		$_html .= $this->_moduleTop( $_filter );
		$_html .= '</div>';
		echo $_html;
		} else {
			echo $this->table_error;
		}
	}

}//end of class
?>