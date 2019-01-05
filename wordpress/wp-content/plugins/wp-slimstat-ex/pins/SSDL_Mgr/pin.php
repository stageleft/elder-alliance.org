<?php
/*
Module Name : Download Manager
Module URI : http://082net.com/tag/wp-slimstat/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
This is example of Wp-SlimStat-Ex Pin
*/
/*
$Pinfo : Informations of this pin which will saved in database(usually 'wp_slim_pins')
	name => Pin's name. This must exactly same as Pin's class name (class SSDL_Mgr extends wpSSPins { .... )
	title => This will be showed at Wp-SlimStat's menu link ( Summary | Feeds | Details | .... | Downloads | .... )
	author => Author's name(can be your name)
	url => Author's or downloadable Pin's URL
	text => Pin's description
	version => current Pin's version
	type => how Pin works. display panel only(0), included function only(1) or both(2).

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
					$myFilters = $this->filter_encode(); // Retrieve filters from url
					$myContent .= $this->_getFilterForm();// insert filter form. 
						( You must wrap your contnets with <div id="filterd_result"> your contents </div> )
					$myFilterClause = $this->filter_switch();// If a filter by keyword was set, add it to the SQL WHERE clause

You can call SlimStat's options and config by global $SlimCfg;  $SlimCfg->table_stats; $SlimCfg->option['visit_type'] ....etc.
You surely can use any functions in 'wp-slimstat-ex/lib/functions.php' file( SSFunction class ) by SSFunction::_function_name()
It would be more better if you check modules.php.
See below example.
*/

/* Please fill in $Pinfo values */

if (!defined('SLIMSTATPATH')) { header('Location:/'); };

class SSDL_Mgr extends SSPins {
	// About this Pin
	var $Pinfo = array(
		'title' => 'Downloads(slimstat)',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'SlimStat table version. Show download stats for x-download-manager(http://082net.com/tag/x-download-manager)',
		'version' => '0.5',
		'type' => 0,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => '_moduleTop', 'title' => 'Top downloads' ),
		1 => array( 'name' => '_moduleRecent', 'title' => 'Recent downloads' ),
	);

	function SSDL_Mgr() {
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
			return array	('compatible' => false, 'message' => 'Download Manager(slimstat) is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function _replaceFilterClause($filter) {
		global $SlimCfg;
		$replaced = str_replace('%%COLUMN%%', 'tr.rs_title', $filter);
		return $replaced;
	}

	function _moduleTop($filter_clause) {
		global $wp_version, $SlimCfg;
		$myQueryArray = array();
		$myTableTitles = array();
		$myDescrLinkFormat = array();
		$myValueFormat = array();

		$filter_clause = $this->_replaceFilterClause($filter_clause);
		
		// Table header: three columns
		$myTableTitles[0] = __('File name', 'wp-slimstat-ex');
		$myTableTitles[1] = __('Hits', 'wp-slimstat-ex');
		$myTableTitles[2] = __('last', 'wp-slimstat-ex');
		$myLimitRows = $SlimCfg->option['limitrows'];
		$myQueryArray[0][0] = "SELECT COUNT(ts.resource) AS countall, MAX(ts.dt) AS dt, ts.resource, tr.rs_title AS filename
								FROM $SlimCfg->table_stats ts, $SlimCfg->table_resource tr
								WHERE ts.resource = tr.id
								AND tr.rs_condition = '[download]'
								AND $filter_clause
								GROUP BY ts.resource
								ORDER BY countall DESC, ts.dt
								LIMIT $myLimitRows";
		$myQueryArray[0][1] = array('countall', 'dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$myDescrLinkFormat[0]['filename'] = '%%short%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$myValueFormat[0] = array('integer','date');
		// define other ajax links
		$links = '';
		$moid =& $this->getMoID(0);

		return SSFunction::getModule( $moid, 
			$myTableTitles, 
			$myQueryArray, 
			$myDescrLinkFormat,
			$myValueFormat,
			$filter_clause,
			false,
			true,
			$links
			 );
	}

	function _moduleRecent($filter_clause) {
		global $SlimCfg, $wpdb;
		$myQueryArray = array();
		$myTableTitles = array();
		$myDescrLinkFormat = array();
		$myValueFormat = array();
		
		$filter_clause = $this->_replaceFilterClause($filter_clause);

		// Table header: three columns
		$myTableTitles[0] = __('File name', 'wp-slimstat-ex');
		$myTableTitles[1] = __('When', 'wp-slimstat-ex');
		
		$myLimitRows = $SlimCfg->option['limitrows'];
		$myQueryArray[0][0] = "SELECT INET_NTOA(ts.remote_ip) AS remote_ip_a, 
								CONCAT('c-', LOWER(ts.country)) AS country, ts.dt, tr.rs_title AS filename
								FROM $SlimCfg->table_stats ts, $SlimCfg->table_resource tr
								WHERE ts.resource = tr.id
								AND tr.rs_condition = '[download]'
								AND $filter_clause
								ORDER BY ts.dt DESC
								LIMIT $myLimitRows";
		$myQueryArray[0][1] = array('dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=99&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$myDescrLinkFormat[0]['referer'] ='<span class="external"><a%%encode_prefix%% title="'.__('Downloaded from', 'wp-slimstat-ex').': %%short%%"><img src="'.$SlimCfg->pluginURL.'/css/external.gif" alt="" /></a></span>&nbsp;&nbsp;';
		$myDescrLinkFormat[0]['filename'] = '%%medium%% '.SSFunction::filterBtn($filter, $this->getPinID()).'';
		$myDescrLinkFormat[0]['country'] = '&nbsp;&nbsp;<span> &#64;&nbsp; %%flag%% ';
		$filter = '&amp;fi=%%encode%%&amp;ff=3&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$myDescrLinkFormat[0]['remote_ip_a'] = '%%remote_ip%% '.SSFunction::filterBtn($filter, 3).' </span> ';
		$myValueFormat[0] = array('date');
		// define other ajax links
		$links = '';
		$moid =& $this->getMoID(1);
		
		return SSFunction::getModule( $moid, 
			$myTableTitles, 
			$myQueryArray, 
			$myDescrLinkFormat,
			$myValueFormat,
			$filter_clause,
			true,
			false,
			$links
			 );
	}

	function _displaymodule($filter) {// to do (or.. remove)
		return $this->_moduleTop($filter);
	}

	function _displayPanel() {
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();

		$myContent = $this->_filterIntervalLink($myFilterClause);
		$myContent .= $this->_moduleRecent( $myFilterClause );
		$myContent .= $this->_moduleTop( $myFilterClause );
		
		echo $myContent;
	}

}//end of class
?>