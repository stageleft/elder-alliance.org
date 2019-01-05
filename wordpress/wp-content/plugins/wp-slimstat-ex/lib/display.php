<?php
require_once(SLIMSTATPATH . 'lib/modules.php');
/*
if ($SlimCfg->option['usepins']) {
	require_once(SLIMSTATPATH . 'lib/pins.php');
}
*/
if(!class_exists('SSDisplay')) :
class SSDisplay {

	function SSDisplay() {
	}
	
	function _displaySummary() {
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();
		$myFilterClauseNoInt = SSFunction::filter_switch(false);
		// Initialize content for this panel
		$myContent = "";
		$myContent .= SSFunction::get_nav();
		// Module ID 1: All summary include resetted hits, visits, uniques
		$myContent .= SSModule::_moduleSummary($myFilterClauseNoInt);
		// Module ID 2: recent referers
		$myContent .= SSModule::_moduleRecentReferers($myFilterClause);
		// Module ID 3: recent search strings
		$myContent .= SSModule::_moduleRecentSearchStrings($myFilterClause);
		// Module ID 4: new domains
		$myContent .= SSModule::_moduleRecentRemoteip($myFilterClause);
		// Module ID 5: recent resources
		$myContent .= SSModule::_moduleRecentResources($myFilterClause);
		// And now, let's see what we have got
		echo $myContent;
	}
	// end displaySummary

	function add_tab( $s ) {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('view_slimstat_stats'))
			return;
		if(function_exists('add_submenu_page')) {
			add_submenu_page('index.php', 'SlimStat', 'SlimStat', 'publish_posts', 'wp-slimstat-ex', array( 'SSDisplay', 'displayStats' ));
		}
		return $s;
	}

	function displayStats() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('view_slimstat_stats')) {
			echo '<div id="message" class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$panel = $SlimCfg->get['pn'];
		$use_ajax = $SlimCfg->option['use_ajax'];
		$href = ($use_ajax)?"#":"?page=wp-slimstat-ex&amp;panel=";
		$class = ' slm_current';
//		echo	'<span id="debug"></span>';
		echo  "\t".'<div class="wrap" id="wrap">';
		echo  "\t".'<a id="ajax_request" href="'.$SlimCfg->ajaxReq.'"></a>'."\n";
		echo  "\t\t".'<h2>SlimStat <span style="font-size:18px;color:#535353;">:: '.get_option('blogname').'</span></h2>'."\n";
		echo  "\t\t".'<div id="wp_slimstat">'."\n";
		echo  "\t\t".'<h3 class="wp_slimstat_tabs" id="slim_menu">'."\n";
		echo  "\t\t".'<a id="slm1" class="slm'.(($panel==1)?$class:'').'" href="'.$href.'1"'.(($use_ajax)?' onclick="SlimStat.panel(\'1\', \'\'); return false;"':'').'>'.__('Summary', 'wp-slimstat-ex').'</a> | '."\n";
		echo  "\t\t".'<a id="slm2" class="slm'.(($panel==2)?$class:'').'" href="'.$href.'2"'.(($use_ajax)?' onclick="SlimStat.panel(\'2\', \'\'); return false;"':'').'>'.__('Feeds', 'wp-slimstat-ex').'</a> |	'."\n";
		echo  "\t\t".'<a id="slm3" class="slm'.(($panel==3)?$class:'').'" href="'.$href.'3"'.(($use_ajax)?' onclick="SlimStat.panel(\'3\', \'\'); return false;"':'').'>'.__('Details', 'wp-slimstat-ex').'</a> '."\n";
		echo  (($SlimCfg->option['usepins'])?SSPins::PinMenulinks():'');
		echo  '<span id="slimloading" style="display:none;"> ( Loading... ) </span>';
		echo  "\t\t".'</h3>'."\n";
		echo  "\t\t".'<div><div id="main_wraper"><div id="slim_main">'."\n";
		if(!$use_ajax) {
			SSDisplay::wp_slimstat_ajax_display($panel);
		}
		echo  "\t\t".'</div></div>'."\n";
		echo  "\t\t".'<div id="donotremove">Wp-SlimStat-Ex '.$SlimCfg->version.' (<a href="http://082net.com/tag/wp-slimstat-ex/">'.__('check for updates', 'wp-slimstat-ex').'</a>), ';
		echo  __("based on", "wp-slimstat-ex").' <a href="http://www.duechiacchiere.it/wp-slimstat/">Wp-SlimStat 0.9.2</a> and ';
		echo  '<a href="http://wettone.com/code/slimstat">SlimStat</a><br />'."\n";
		echo  "\t\t".__("Data size", "wp-slimstat-ex").' : '.SSFunction::_getTableSize().' ('.__("Feed", "wp-slimstat-ex").': '.SSFunction::_getTableSize('feed').', ';
		echo  __('iptocountry', 'wp-slimstat-ex').': '.SSFunction::_getTableSize('country').')'."\n";
		echo  "\t\t".'</div></div></div>'."\n";
		echo  "\t".'</div>'."\n";
	}

	function wp_slimstat_ajax_display($p='') {
		global $SlimCfg;
		$p = ($p)?$p:$SlimCfg->get['pn'];
		if($p < 100) {
			switch ($p) {
				case 5:
					SSDisplay::_displayConfig();
					break;
				case 3:
					SSDisplay::_displayDetails();
					break;
				case 2:
					SSDisplay::_displayFeeds();
					break;
				case 1:
				default:
					SSDisplay::_displaySummary();
					break;
			}
		} else { 
			if($SlimCfg->option['usepins']) {
				$pin_list = SSPins::_getPins(1, 3);
				if(!is_array($pin_list) || empty($pin_list))
					return;
				foreach($pin_list as $pin) {
					if ( !class_exists($pin->name) || ($pin->id + 100) != $p )
						continue;
					eval('$'.$pin->name.' =& new $pin->name();'."\n");
					eval('$'.$pin->name.'->_displayPanel();');
				}
			}
		}
	}

	function _displayFeeds() {
		$myContent = SSFunction::get_nav();
		// Initialize content for this panel
		$myContent .= SSFunction::_getFilterForm();
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();
		$myFilterClauseNoInt = SSFunction::filter_switch(false);
		//filterd result wrapper
		$myContent .= '<div id="filterd_result">';
		// Module ID 1: some general information
		$myContent .= SSModule::_moduleSummary($myFilterClauseNoInt);
		// Module ID 16: top 20 visitors
		$myContent .= SSModule::_moduleTopRemoteAddresses( $myFilterClause );
		// Module ID 91: top 20 browsers
		$myContent .= SSModule::_moduleTopBrowsersOnly( $myFilterClause );
		// Module ID 19: top 20 countries
		$myContent .= SSModule::_moduleTopCountries( $myFilterClause );
		// Module ID 20: top 20 referers
		$myContent .= SSModule::_moduleTopReferers( $myFilterClause );
		// Module ID 10: top 20 resources
		$myContent .= SSModule::_moduleTopResources( $myFilterClause );
		// Module ID 3: recent search strings
		$myContent .= SSModule::_moduleRecentSearchStrings($myFilterClause);
		$myContent .= '</div>';
		// And now, let's see what we have got
		echo $myContent;
	}

	function _displayDetails() { // show all filterd results
		$myContent = SSFunction::get_nav();
		// Initialize content for this panel
		$myContent .= SSFunction::_getFilterForm();
		// If a filter by keyword was set, add it to the SQL WHERE clause
		$myFilterClause = SSFunction::filter_switch();
		$myFilterClauseNoInt = SSFunction::filter_switch(false);
		//filterd result wrapper
		$myContent .= '<div id="filterd_result">';
		// Module ID 1: Summary
		$myContent .= SSModule::_moduleSummary($myFilterClauseNoInt);
		// Module ID 16: top 20 visitors
		$myContent .= SSModule::_moduleTopRemoteAddresses( $myFilterClause );
		// Module ID 91: top 20 browsers
		$myContent .= SSModule::_moduleTopBrowsersOnly( $myFilterClause );
		// Module ID 19: top 20 countries
		$myContent .= SSModule::_moduleTopCountries( $myFilterClause );
		// Module ID 20: top 20 referers
		$myContent .= SSModule::_moduleTopReferers( $myFilterClause );
		// Module ID 10: top 20 resources
		$myContent .= SSModule::_moduleTopResources( $myFilterClause );
		// Module ID 3: recent search strings
		$myContent .= SSModule::_moduleRecentSearchStrings( $myFilterClause );
		$myContent .= '</div>';
		// And now, let's see what we have got
		echo $myContent;
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSDisplay();
		}
		return $instance[0];
	}

}// end of class
endif;

if($SlimCfg->option['usepins']) {
	SSPins::_incPins(0);
}

?>