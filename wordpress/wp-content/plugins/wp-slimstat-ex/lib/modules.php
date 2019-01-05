<?php

class SSModule {

	function _moduleSummary($filter_clause) {
		global $SlimCfg;
		$panel = $SlimCfg->get['pn'];
		switch ($panel) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];
		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th class=\"first\">".__('When', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"third\">".__(ucfirst($visit_type), 'wp-slimstat-ex')."</th>\n";
		$output .= "\t</tr>\n";
		$dt_blog = $SlimCfg->_mktime( time() ); // get blog midnight
		$dt_svr = $SlimCfg->sstime($dt_blog, true); // back to server time
		
		// today
		$dt_end = ($dt_svr + 86399);
		$hvu = SSFunction::calc_hvu( $dt_svr, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Today', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// yesterday
		$dt_start_svr = ($dt_svr - 86400);
		$dt_end = $dt_svr-1;
		$hvu = SSFunction::calc_hvu( $dt_start_svr, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Yesterday', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// this week
		$dt_start = $dt_blog;
		$dt_end = ($dt_svr + 86399);
		while ( date( "w", $dt_start ) !=  1 ) { // move back to start of this week (1:Monday, 0:Sunday)
			$dt_start -= 86400;
		}
		$dt_start_svr = $SlimCfg->sstime($dt_start, true); // back to server time
		if ($dt_end - $dt_start_svr <= 0 ) $dt_start_svr = $dt_svr;
		$hvu = SSFunction::calc_hvu( $dt_start_svr, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('This week', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// last week
		$dt_end = $dt_start_svr - 1;
		$dt_start_svr = ($dt_start_svr - 604800);
		$hvu = SSFunction::calc_hvu( $dt_start_svr, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Last week', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// this month
		$dt_start = $dt_blog;
		$dt_end = ($dt_svr + 86399);
		while ( date( "j", $dt_start ) > 1 ) { // Move back to start of this month
			$dt_start -= 86400;
		}
		$dt_start_svr = $SlimCfg->sstime($dt_start, true); // back to server time
		$hvu = SSFunction::calc_hvu( $dt_start_svr, 0, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('This month', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// last month
		$dt_end = $dt_start_svr - 1;
		$dt_start = mktime( 0, 0, 0, date( "n", $dt_start ) - 1, 1);
		$dt_start_svr = $SlimCfg->sstime($dt_start, true);
		$hvu = SSFunction::calc_hvu( $dt_start_svr, $dt_end, $type, $filter_clause );
		if(max($hvu) > 0) {
			$filter_url = '&amp;fd='.$dt_start_svr.'|'.$dt_end.$SlimCfg->get['fi_encode'];
			$filter_btn = SSFunction::get_filterBtns($filter_url, true);
			$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
			$output .= "\t<tr class=\"".$class."\">\n";
			$output .= "\t\t<td class=\"first\">".__('Last month', 'wp-slimstat-ex')." ".$filter_btn."</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}

		// all
		$first_hit = SSFunction::get_firsthit($type);
		$first_hit_blog = $SlimCfg->sstime($first_hit);
		$hvu = SSFunction::calc_hvu( $first_hit, 0, $type, $filter_clause );
		$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
		$output .= "\t<tr class=\"".$class."\">\n";
		$output .= "\t\t<td class=\"first\">Since ";
		$output .= $SlimCfg->_date( __('j M Y, H:i', 'wp-slimstat-ex'), $first_hit_blog )."</td>";
		$output .= "<td class=\"second\">".$hvu['hits']."</td>";
		$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
		$output .= "\t</tr>\n";

		// deleted
		$real_firsthit = SSFunction::get_real_firsthit($type);
		if( $real_firsthit && ( empty($filter_clause) || $filter_clause == '(1 = 1)' ) ) {
			$hvu = SSFunction::deleted_hvu($type);
			$output .= "\t<tr class=\"accent\">\n";
			$output .= "\t\t<td class=\"first\">".$SlimCfg->_date(__('j M, Y', 'wp-slimstat-ex'), $SlimCfg->sstime($real_firsthit) ).' - ';
			$output .= $SlimCfg->_date( __('j M, Y', 'wp-slimstat-ex'), $first_hit_blog )." (deleted)</td>";
			$output .= "<td class=\"second\">".$hvu['hits']."</td>";
			$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
			$output .= "\t</tr>\n";
		}
		$output .= "\t</table>\n";
		return SSFunction::get_module_custom( 1, $output, '', true);
	}
	// end moduleSummary
	
	function _moduleRecentReferers($filter_clause, $fbtn_type=1) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Domain', 'wp-slimstat-ex');
		$_titles[1] = __('When', 'wp-slimstat-ex');
		
		// Last 30 domains
		$_query[0][0] = "SELECT ts.resource, ts.referer, ts.domain, ts.domain domainfilter, ts.dt 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('domain')."
								WHERE ts.referer <> ''
									AND ts.resource NOT IN (0,1)
									AND ts.domain <> '' 
									AND ts.domain <> '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."'
									AND $filter_clause
								ORDER BY ts.dt DESC LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=0&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['referer'] = '<a title="'.__('Visit this referer', 'wp-slimstat-ex').'"%%encode_prefix%%>';
		$_desc[0]['domain'] = '%%short%%</a> '.$filter_btn;
		$_value[0] = array('date');
		
		return SSFunction::getModule(2, $_titles, $_query, $_desc, $_value, '', false, true, true);
	}
	// end moduleRecentReferers
	
	function _moduleRecentSearchStrings($filter_clause) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Search string', 'wp-slimstat-ex');
		$_titles[1] = __('When', 'wp-slimstat-ex');
		
		// Last 30 search strings
		$_query[0][0] = "SELECT ts.resource, ts.referer, ts.searchterms, ts.dt 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('searchterms')."
								WHERE ts.searchterms <> '' 
									AND ts.resource NOT IN (0,1) 
									AND $filter_clause
								ORDER BY ts.dt DESC 
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('dt');
		$filter = '&amp;fi=%%encode%%&amp;ff=1&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['referer'] = '<a%%encode_prefix%% ';
		$_desc[0]['searchterms'] = 'title="%%long%%">%%short%%</a> '.$filter_btn;
		$_value[0] = array('date');
		
		return SSFunction::getModule(3, $_titles, $_query, $_desc, $_value, '', false, true, true);
	}
	// end moduleRecentSearchStrings
	
	function _moduleNewDomains($filter_clause) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Domain', 'wp-slimstat-ex');
		$_titles[1] = __('When', 'wp-slimstat-ex');
		
		// Last 30 search strings
		$_query[0][0] = "SELECT ts.referer, ts.resource, ts.domain, MIN(ts.dt) mindt
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('domain')."
								WHERE ts.domain <> '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."' 
									AND ts.domain <> ''
									AND ts.referer <> ''
									AND ts.resource NOT IN (0,1)
									AND $filter_clause
								GROUP BY ts.domain
								ORDER BY mindt DESC 
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('mindt');
		$_desc[0]['referer'] = '<a title="'.__('Visit this referer', 'wp-slimstat-ex').'"%%encode_prefix%%>';
		$_desc[0]['domain'] = '%%short%%</a>';
		$_value[0] = array('date');
		
		return SSFunction::getModule(4, $_titles, $_query, $_desc, $_value, '', false, true, $links);
	}
	// end moduleNewDomains

	function _moduleRecentResources($filter_clause) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Resource', 'wp-slimstat-ex');
		$_titles[1] = __('When', 'wp-slimstat-ex');
		
		// Last 30 resources
		$_query[0][0] = "SELECT ts.resource, ts.dt 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('dt')."
								WHERE ts.resource NOT IN (0,1)
								AND $filter_clause
								ORDER BY ts.dt DESC 
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('dt');
		$filter = "&amp;fi=%%encode%%&amp;ff=2&amp;ft=0".$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['resource'] = '%%resource2title%%  '.$filter_btn;
		
		$_value[0] = array('date');
		
		return SSFunction::getModule(5, $_titles, $_query, $_desc, $_value, '', true, true, true);
	}
	// end moduleRecentResources
	
	function _moduleLast24Hours($filter_clause) {
		global $SlimCfg;
		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];
		if ( !isset($SlimCfg->get['fd']) || (isset($SlimCfg->get['fd']) && ($SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0]) <= 86400) ) {
			if( isset($SlimCfg->get['fd']) ) {
				$dt_end = strtotime( date( "Y-m-d H:00:00", $SlimCfg->get['fd'][0] ) );
				$dt_this_hour = strtotime( date( "Y-m-d H:59:59", $SlimCfg->get['fd'][1] ) );
			} else {
				$dt_this_hour = strtotime( date( "Y-m-d H:59:59" ) );
				$dt_end = $SlimCfg->_mktime( time(), true );
			}
			$output = "\n";
			$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
			$output .= "\t<tr>\n";
			$output .= "\t\t<th class=\"first\">".__('Hour', 'wp-slimstat-ex')."</th>";
			$output .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
			$output .= "<th class=\"third\">".__(ucfirst($visit_type), 'wp-slimstat-ex')."</th>\n";
			$output .= "\t</tr>\n";
				
			$i = 0;
			for ($dt_start = $dt_this_hour; $dt_start > $dt_end; $dt_start -= 3600) {
				$hvu = SSFunction::calc_hvu( ( $dt_start - 3599 ), $dt_start, $type, $filter_clause );
				if(max($hvu) > 0) {
					$filter_url = '&amp;fd='.( $dt_start - 3599 ).'|'.$dt_start.$SlimCfg->get['fi_encode'];
					$filter_btn = SSFunction::get_filterBtns($filter_url, true);
					$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
					$output .= "\t<tr class=\"".$class."\">\n";
					$output .= "\t\t<td class=\"first\">".$SlimCfg->_date( __("H:00 - H:59", 'wp-slimstat-ex'), $SlimCfg->sstime($dt_start) )." ".$filter_btn."</td>";
					$output .= "<td class=\"second\">".$hvu['hits']."</td>";
					$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
					$output .= "\t</tr>\n";
				}
				$i++;
			}
			$output .= "\t</table>\n";

			return SSFunction::get_module_custom( 6, $output, '', true);
		}
		return '';
	}
	// end moduleLast24Hours
	
	function _moduleDailyHits($filter_clause) {
		global $SlimCfg;
		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];
		$int_array = $SlimCfg->get['fd'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Day', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"third\">".__(ucfirst($visit_type), 'wp-slimstat-ex')."</th>\n";
		$output .= "\t</tr>\n";
		
		// Today, yesterday, etc...
		$dt_start = $SlimCfg->_mktime(time(), true);		
		$myFilterRange = 604801;
		if ( isset($SlimCfg->get['fd']) ) {
			$myFilterStart = $SlimCfg->get['fd'][0];
			$myFilterEnd = min( time(), $SlimCfg->get['fd'][1] );
			$dt_start = strtotime( date( "Y-m-d 00:00:00", $myFilterEnd ) );
			$myFilterRange = $myFilterEnd - $myFilterStart;
		}
		$dt_limit = $dt_start - min( $myFilterRange, 604800 );
		
		$i = 0;
		for ($dt_midnight = $dt_start; $dt_midnight > $dt_limit; $dt_midnight -= 86400) {
			$hvu = SSFunction::calc_hvu( $dt_midnight, ($dt_midnight + 86399), $type, $filter_clause );
			if(max($hvu) > 0) {
				$filter_url = '&amp;fd='.$dt_midnight.'|'.($dt_midnight + 86399).$SlimCfg->get['fi_encode'];
				$filter_btn = SSFunction::get_filterBtns($filter_url, true);
				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";
				$output .= "\t\t<td class=\"first\">".$SlimCfg->my_esc( $SlimCfg->_date(__('j F, Y', 'wp-slimstat-ex'), $SlimCfg->sstime($dt_midnight) ) )." ".$filter_btn."</td>";
				
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
				$output .= "\t</tr>\n";
			}
			$i++;
		}

		return SSFunction::get_module_custom( 7, $output, '', true);
	}
	// end moduleDailyHits
	
	function _moduleWeeklyHits($filter_clause) {
		global $SlimCfg;
		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Week', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"third\">".__(ucfirst($visit_type), 'wp-slimstat-ex')."</th>\n";
		$output .= "\t</tr>\n";
		
		$dt = $SlimCfg->sstime( time() ); // blog now time
		$dt_start = mktime( 0, 0, 0, date("m", $dt), (date("d", $dt) - date("w", $dt) + 1), date("Y", $dt) );
		$dt_start = $SlimCfg->sstime($dt_start, true); // back to server time
		
		$myFilterRange = 4838401;
		if ( isset($SlimCfg->get['fd']) ) {
			$myFilterStart = $SlimCfg->get['fd'][0];
			$myFilterEnd = $SlimCfg->get['fd'][1];
			$myFilterEnd = $SlimCfg->sstime($myFilterEnd);
			$dt_start = mktime( 0, 0, 0, date("m", $myFilterEnd), (date("d", $myFilterEnd) - date("w", $myFilterEnd) + 1), date("Y", $myFilterEnd) );
			$dt_start = $SlimCfg->sstime($dt_start, true);
			$myFilterRange = $myFilterEnd - $myFilterStart;
		}
		$dt_limit = $dt_start - min( $myFilterRange, 4838400 );
		
		$i = 0;
		for ($dt_monday = $dt_start; $dt_monday > $dt_limit; $dt_monday -= 604800) {
			$week = $SlimCfg->_date(__('j M ,Y', 'wp-slimstat-ex'), $SlimCfg->sstime($dt_monday)) . ' - ';
			$week .= $SlimCfg->_date(__('j M ,Y', 'wp-slimstat-ex'), $SlimCfg->sstime(($dt_monday + 604799)));
			$hvu = SSFunction::calc_hvu( $dt_monday, ($dt_monday + 604799), $type, $filter_clause );
			if(max($hvu) > 0) {
				$filter_url = '&amp;fd='.$dt_monday.'|'.($dt_monday + 604799).$SlimCfg->get['fi_encode'];
				$filter_btn = SSFunction::get_filterBtns($filter_url, true);
				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";
				$output .= "\t\t<td class=\"first\">".$week." ".$filter_btn."</td>";
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
				$output .= "\t</tr>\n";
			}
			$i++;
		}
		return SSFunction::get_module_custom( 8, $output, '', true);
	}
	// end moduleWeeklyHits
	
	function _moduleMonthlyHits($filter_clause) {
		global $SlimCfg;
		switch ($SlimCfg->get['pn']) {
			case 3: $type = 'common'; break;
			case 2: $type = 'feed'; break;
			case 1: default: $type = 'all'; break;
		}
		$visit_type = $SlimCfg->option['visit_type'];

		$output = "\n";
		$output .= "\t<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		$output .= "\t<tr>\n";
		$output .= "\t\t<th class=\"first\">".__('Month', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"second\">".__('Hits', 'wp-slimstat-ex')."</th>";
		$output .= "<th class=\"third\">".__(ucfirst($visit_type), 'wp-slimstat-ex')."</th>\n";
		$output .= "\t</tr>\n";
		
		$dt = $SlimCfg->sstime( time() ); // blog now time
		$dt_start = mktime( 0, 0, 0, date("m", $dt), 1, date("Y", $dt) ); // start of this month
		$dt_end = mktime( 0, 0, 0, date( "n", $dt ), date( "d", $dt ) + 1 ); // end of today
		$dt_start = $SlimCfg->sstime($dt_start, true);
		$dt_end = $SlimCfg->sstime($dt_end, true);
		
		for ($i = 0; $i < 13; $i++) {
			$hvu = SSFunction::calc_hvu( $dt_start, $dt_end, $type, $filter_clause );
			if(max($hvu) > 0) {
				$filter_url = '&amp;fd='.$dt_start.'|'.$dt_end.$SlimCfg->get['fi_encode'];
				$filter_btn = SSFunction::get_filterBtns($filter_url, true);
				$class = ($class == 'tbrow') ? 'tbrow-alt' : 'tbrow';
				$output .= "\t<tr class=\"".$class."\">\n";
				$output .= "\t\t<td class=\"first\">".$SlimCfg->_date( __("F Y", 'wp-slimstat-ex'), $SlimCfg->sstime($dt_start) )." ".$filter_btn."</td>";
				$output .= "<td class=\"second\">".$hvu['hits']."</td>";
				$output .= "<td class=\"third\">".$hvu[$visit_type]."</td>\n";
				$output .= "\t</tr>\n";
			}
			$dt_end = $dt_start - 1;
			$dt_start = mktime( 0, 0, 0, (date("m", $dt) - $i - 1), 1, date("Y", $dt) );
			$dt_start = $SlimCfg->sstime($dt_start, true);
		}

		return SSFunction::get_module_custom( 9, $output, '', true);
	}
	// end moduleMonthlyHits
	
	function _moduleTopResources( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Resource', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Last', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.resource, MAX(ts.dt) maxdt, COUNT(*) countall 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('resource')."
								WHERE ts.resource NOT IN (0,1) 
								AND $filter_clause
								GROUP BY ts.resource 
								ORDER BY countall DESC 
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'maxdt');
		$filter = "&amp;fi=%%encode%%&amp;ff=2&amp;ft=0".$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['resource'] = '%%resource2title%% '.$filter_btn;

		$_value[0] = array('integer', 'date');

		return SSFunction::getModule(10, $_titles, $_query, $_desc, $_value, '', true, true, true);
	}
	// end moduleTopResources
	
	function _moduleTopSearchStrings( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Search string', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Visits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.searchterms, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('searchterms')."
								WHERE ts.searchterms <> ''
									AND $filter_clause 
								GROUP BY ts.searchterms 
								ORDER BY countall DESC 
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'countdist');
		$filter = '&amp;fi=%%encode%%&amp;ff=1&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['searchterms'] = '%%medium%% '.$filter_btn;
		$_value[0] = array('integer', 'integer');
		
		return SSFunction::getModule(11, $_titles, $_query, $_desc, $_value, '', true, false, true);
	}
	// end moduleTopSearchStrings

	// Function: moduleTopLanguages
	// Description: module containing main languages
	// Input: filter clause, how many languages to show
	// Output: html code to display
	// Details: three columns, id = 12
	// Side effect: none
	function _moduleTopLanguages( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Language', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Visits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT CONCAT('l-', ts.language) language, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('language')."
								WHERE ts.language <> '' 
									AND ts.language <> 'xx'
									AND $filter_clause 
								GROUP BY ts.language 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'countdist');
		$filter = '&amp;fi=%%long%%&amp;ff=7&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['language'] = '%%long_locale%% '.$filter_btn;
		$_value[0] = array('integer', 'integer');
		
		return SSFunction::getModule(12, $_titles, $_query, $_desc, $_value, '', false, false, true);
	}
	// end moduleTopLanguages

	// Function: moduleTopDomains
	// Description: module containing main referring domains
	// Input: filter clause, how many domains to show
	// Output: html code to display
	// Details: three columns, id = 13
	// Side effect: none
	function _moduleTopDomains( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Domain', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Visits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.domain, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('domain')."
								WHERE ts.domain <> '' 
									AND ts.domain <> '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."'
									AND $filter_clause 
								GROUP BY ts.domain 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'countdist');
		$_desc[0]['domain'] = '<a title="'.__('Visit this domain', 'wp-slimstat-ex').'" href="http://%%long%%">%%short%%</a>';
		$_value[0] = array('integer', 'integer');
		
		return SSFunction::getModule(13, $_titles, $_query, $_desc, $_value, '', false, false, true);
	}
	// end moduleTopDomains

	// Function: moduleInternallyReferred
	// Description: module containing visits coming from internal pages
	// Input: filter clause, how many rows to show
	// Output: html code to display
	// Details: two columns, id = 14
	// Side effect: none
	function _moduleInternallyReferred( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Resource', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.resource, ts.referer, COUNT(*) countall
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('domain')."
								WHERE ts.resource NOT IN (0,1)
									AND ts.domain = '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."'
									AND $filter_clause
								GROUP BY ts.resource
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall');
		$_desc[0]['referer'] = '<a title="'.__('Referred by', 'wp-slimstat-ex').' %%long%%"%%encode_prefix%%>';
		$_desc[0]['resource'] = '%%resource2title%%</a>';
		$_value[0] = array('integer');

		return SSFunction::getModule(14, $_titles, $_query, $_desc, $_value, '', true, true, true);
	}
	// end moduleInternallyReferred
	
	// Function: moduleTopInternalSearchStrings
	// Description: module containing search strings sent via local search form
	// Input: filter clause, how many rows to show
	// Output: html code to display
	// Details: two columns, id = 15
	// Side effect: none
	function _moduleTopInternalSearchStrings( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Search string', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.searchterms, ts.referer, COUNT(*) countall
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('resource')."
								WHERE ts.resource = 1
									AND ts.searchterms <> ''
									AND $filter_clause
								GROUP BY ts.searchterms
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall');
		$_desc[0]['referer'] = '<a title="'.__('Searched in', 'wp-slimstat-ex').' %%long%%"%%encode_prefix%%>';
		$_desc[0]['searchterms'] = '%%medium%%</a>';
		$_value[0] = array('integer');

		return SSFunction::getModule(15, $_titles, $_query, $_desc, $_value, '', false, false, true);
	}
	// end moduleInternallyReferred
	
	// Function: moduleTopRemoteAddresses
	// Description: module containing top remote addresses
	// Input: filter clause, how many addresses to show
	// Output: html code to display
	// Details: three columns, id = 16
	// Side effect: none
	function _moduleTopRemoteAddresses( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Remote address', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('%', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT INET_NTOA(ts.remote_ip) remote_ip_a, COUNT(*) countall, CONCAT( 'c-', LOWER(ts.country)) country 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('remote_ip')."
								WHERE ts.remote_ip <> 0
									AND $filter_clause 
								GROUP BY ts.remote_ip 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'countall');
		$filter = '&amp;fi=%%encode%%&amp;ff=3&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['country'] = '%%flag%% ';
		$_desc[0]['remote_ip_a'] = '%%remote_ip%% '.$filter_btn;
		$_value[0] = array('integer','percentage');

		return SSFunction::getModule(16, $_titles, $_query, $_desc, $_value, $filter_clause, false, false, true);
	}
	// end moduleTopRemoteAddresses

	function _moduleTopBrowsers( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Browser', 'wp-slimstat-ex');
		$_titles[1] = __('%', 'wp-slimstat-ex');

		$_query[0][0] = "SELECT ts.browser, ts.version, COUNT(*) countall 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('browser')."
								WHERE ts.browser <> -1
									AND ts.version <> '' 
									AND $filter_clause 
								GROUP BY ts.browser, ts.version 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall');
		$_desc[0]['browser'] = '%%b_id2string%% ';
		$_desc[0]['version'] = '%%medium%%';
		$_value[0] = array('percentage');

		return SSFunction::getModule(17, $_titles, $_query, $_desc, $_value, $filter_clause, false, false, true);
	}
	// end moduleTopBrowsers

	// Function: moduleTopPlatforms
	// Description: module containing top platforms (operating systems)
	// Input: filter clause, how many platforms to show
	// Output: html code to display
	// Details: three columns, id = 18
	// Side effect: none
	function _moduleTopPlatforms( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Platform', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Visits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.platform, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('platform')."
								WHERE ts.platform <> -1 
									AND $filter_clause 
								GROUP BY ts.platform 
								ORDER BY countall DESC 
								LIMIT 0,".$SlimCfg->option['limitrows']."";
		$_query[0][1] = array('countall', 'countdist');
		$filter = '&amp;fi=%%long%%&amp;ff=5&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['platform'] = '%%p_id2string%% '.$filter_btn;
		$_value[0] = array('integer', 'integer');

		return SSFunction::getModule(18, $_titles, $_query, $_desc, $_value, '', false, false, true);
	}
	// end moduleTopPlatforms
		
	// Function: moduleTopCountries
	// Description: module containing top user's countries
	// Input: filter clause, how many countries to show
	// Output: html code to display
	// Details: two columns, id = 19
	// Side effect: none
	function _moduleTopCountries( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Country', 'wp-slimstat-ex');
		$_titles[1] = __('%', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT LOWER(CONCAT( 'c-', ts.country)) country, COUNT(*) countall
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('country')."
								WHERE ts.country <> ''
									AND $filter_clause 
								GROUP BY ts.country 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall');
		$filter = '&amp;fi=%%long%%&amp;ff=6&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['country'] = '%%flag%% - %%long_locale%% '.$filter_btn;
		$_value[0] = array('percentage');

		return SSFunction::getModule(19, $_titles, $_query, $_desc, $_value, $filter_clause, false, false, true);
	}
	// end moduleTopCountries

	// Function: moduleTopReferers
	// Description: module containing top referring pages
	// Input: filter clause, how many referers to show
	// Output: html code to display
	// Details: three columns, wide, id = 20
	// Side effect: none
	function _moduleTopReferers( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: three columns
		$_titles[0] = __('Referer', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Visits', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.referer, ts.resource, COUNT(*) countall, COUNT( DISTINCT ts.remote_ip ) countdist
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('referer')."
								WHERE ts.referer <> '' 
									AND ts.resource NOT IN (0,1)
									AND ts.domain <> '".$SlimCfg->my_esc($_SERVER['HTTP_HOST'])."' 
									AND $filter_clause 
								GROUP BY ts.referer 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall', 'countdist');
		$_desc[0]['referer'] = '<a title="'.__('Visit this referer', 'wp-slimstat-ex').'"%%encode_prefix%%>%%medium%%</a>';
		$_value[0] = array('integer', 'integer');

		return SSFunction::getModule(20, $_titles, $_query, $_desc, $_value, '', true, true, true);
	}
	// end moduleTopReferers

	function _moduleTopBrowsersOnly( $filter_clause ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();
		
		// Table header: two columns
		$_titles[0] = __('Browser', 'wp-slimstat-ex');
		$_titles[1] = __('%', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT ts.browser, COUNT(*) countall 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('browser')."
								WHERE ts.browser <> -1 
									AND $filter_clause 
								GROUP BY ts.browser 
								ORDER BY countall DESC
								LIMIT ".$SlimCfg->option['limitrows'];
		$_query[0][1] = array('countall');
		$filter = '&amp;fi=%%long%%&amp;ff=4&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['browser'] = '%%b_id2string%% '.$filter_btn;
		
		$_value[0] = array('percentage');

		return SSFunction::getModule(91, $_titles, $_query, $_desc, $_value, $filter_clause, false, false, true);
	}
	// end moduleTopBrowsersOnly

	// Function: moduleRecentRemoteip
	// Description: module containing recent remote IP within last 7 days
	// Input: filter clause, how many referers to show
	// Output: html code to display
	// Details: three columns, wide, id = 92
	// Side effect: none
	function _moduleRecentRemoteip( $filter_clause='' ) {
		global $SlimCfg;
		$_query = array();
		$_titles = array();
		$_desc = array();
		$_value = array();

//		$dt_this_hour = strtotime( date( "Y-m-d H:59:59" ) );
		
		// Table header: three columns
		$_titles[0] = __('Remote IP', 'wp-slimstat-ex');
		$_titles[1] = __('Hits', 'wp-slimstat-ex');
		$_titles[2] = __('Last', 'wp-slimstat-ex');
		
		$_query[0][0] = "SELECT INET_NTOA(ts.remote_ip) remote_ip_a, 
								CONCAT('c-', LOWER(ts.country)) country, MAX(ts.dt) AS dt, COUNT(*) countall 
								FROM $SlimCfg->current_table ts
								".$SlimCfg->use_indexkey('remote_ip')."
								WHERE ts.remote_ip <> 0
									AND $filter_clause
								GROUP BY ts.remote_ip 
								ORDER BY dt DESC 
								LIMIT ".$SlimCfg->option['limitrows'];

		$_query[0][1] = array('countall', 'dt');
		$filter = '&amp;fi=%%long%%&amp;ff=3&amp;ft=0'.$SlimCfg->get['fd_encode'];
		$filter_btn = SSFunction::get_filterBtns($filter);
		$_desc[0]['country'] = '%%flag%% ';
		$_desc[0]['remote_ip_a'] = '%%remote_ip%% '.$filter_btn;	
		$_value[0] = array('integer','date');

		return SSFunction::getModule(92, $_titles, $_query, $_desc, $_value, '', false, false, true);
	}

}//end of class

?>