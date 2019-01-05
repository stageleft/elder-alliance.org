<?php

/******************************************************************************
 Geo Mint Pepper
 
 Developer		: Christoph Lupprich
 Plug-in Name	: Geo Mint
 Version        : 0.53
 http://www.stopbeingcarbon.com/geomint/
 http://www.stopbeingcarbon.com
 ---
 Credits go to Geoffrey Hughes who implemented his own features into GeoMint,
 which did it at least partially into this release.
 -----
 
 ******************************************************************************/

/* Marker icons from : http://jg.org/mapping/icons.html */

/******************************************************************************
*	Ported to SlimStat-Ex by 082net(http://082net.com/)
*******************************************************************************/

class GeoSlimStat extends SSPins {

	// About this Pin
	var $Pinfo = array(
		'title' => 'Geo SlimStat',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'This pin powered by <a href="http://code.google.com/p/geomint/">Geo Mint</a> by <a href="http://www.stopbeingcarbon.com">Christoph Lupprich</a>. Collects the locations of your visitors and draws it on a Google Maps.',
		'version' => '0.3',
		'type' => 2,
	);

	// About displayable modules of this Pin
	var $Moinfo = array(// function name, module title
		0 => array( 'name' => 'plotMap', 'title' => 'Visitors on Google Maps' ),
	);

	var $prefs = array(
		'googleAPI' => '',
		'plotNum' => 50,
		'plotWidth' => "100%",
		'plotHeight' => "350px",
		'plotLatitude' => 37,
		'plotLongitude' => 126,
		'plotZoom' => 2,
		'dataOption' => 1,
		'daysDisplay' => 0,
		'mapCenter' => 0,
		'wheelZoom' => 0,
		'purge_interval' => 120, // about 4 month
		'pre_connection' => 50 // limit per connection (curl_mulit)
		);

	var $extra_table = array(
		'geo' => "`ip` INT(10) unsigned NOT NULL default '0',
			`country_abrv` CHAR(3) NOT NULL default '',
			`city` VARCHAR(40) NOT NULL default '',
			`latitude` FLOAT NOT NULL default '0',
			`longitude` FLOAT NOT NULL default '0',
			UNIQUE KEY `ip` (`ip`)"
	);

	var $country2geocode = array( 'o1'=>array('0.0000','0.0000'), 'ap'=>array('35.0000','105.0000'), 'eu'=>array('47.0000','8.0000'), 'ad'=>array('42.5000','1.5000'), 'ae'=>array('24.0000','54.0000'), 'af'=>array('33.0000','65.0000'), 'ag'=>array('17.0500','-61.8000'), 'ai'=>array('18.2500','-63.1667'), 'al'=>array('41.0000','20.0000'), 'am'=>array('40.0000','45.0000'), 'an'=>array('12.2500','-68.7500'), 'ao'=>array('-12.5000','18.5000'), 'aq'=>array('-90.0000','0.0000'), 'ar'=>array('-34.0000','-64.0000'), 'as'=>array('-14.3333','-170.0000'), 'at'=>array('47.3333','13.3333'), 'au'=>array('-27.0000','133.0000'), 'aw'=>array('12.5000','-69.9667'), 'ax'=>array('42.720427','1.839759'), 'az'=>array('40.5000','47.5000'), 'ba'=>array('44.0000','18.0000'), 'bb'=>array('13.1667','-59.5333'), 'bd'=>array('24.0000','90.0000'), 'be'=>array('50.8333','4.0000'), 'bf'=>array('13.0000','-2.0000'), 'bg'=>array('43.0000','25.0000'), 'bh'=>array('26.0000','50.5500'), 'bi'=>array('-3.5000','30.0000'), 'bj'=>array('9.5000','2.2500'), 'bm'=>array('32.3333','-64.7500'), 'bn'=>array('4.5000','114.6667'), 'bo'=>array('-17.0000','-65.0000'), 'br'=>array('-10.0000','-55.0000'), 'bs'=>array('24.2500','-76.0000'), 'bt'=>array('27.5000','90.5000'), 'bv'=>array('-54.4333','3.4000'), 'bw'=>array('-22.0000','24.0000'), 'by'=>array('53.0000','28.0000'), 'bz'=>array('17.2500','-88.7500'), 'ca'=>array('60.0000','-95.0000'), 'cc'=>array('-12.5000','96.8333'), 'cd'=>array('0.0000','25.0000'), 'cf'=>array('7.0000','21.0000'), 'cg'=>array('-1.0000','15.0000'), 'ch'=>array('47.0000','8.0000'), 'ci'=>array('8.0000','-5.0000'), 'ck'=>array('-21.2333','-159.7667'), 'cl'=>array('-30.0000','-71.0000'), 'cm'=>array('6.0000','12.0000'), 'cn'=>array('35.0000','105.0000'), 'co'=>array('4.0000','-72.0000'), 'cr'=>array('10.0000','-84.0000'), 'cs'=>array('44.024774','20.721869'), 'cu'=>array('21.5000','-80.0000'), 'cv'=>array('16.0000','-24.0000'), 'cx'=>array('-10.5000','105.6667'), 'cy'=>array('35.0000','33.0000'), 'cz'=>array('49.7500','15.5000'), 'de'=>array('51.0000','9.0000'), 'dj'=>array('11.5000','43.0000'), 'dk'=>array('56.0000','10.0000'), 'dm'=>array('15.4167','-61.3333'), 'do'=>array('19.0000','-70.6667'), 'dz'=>array('28.0000','3.0000'), 'ec'=>array('-2.0000','-77.5000'), 'ee'=>array('59.0000','26.0000'), 'eg'=>array('27.0000','30.0000'), 'eh'=>array('24.5000','-13.0000'), 'er'=>array('15.0000','39.0000'), 'es'=>array('40.0000','-4.0000'), 'et'=>array('8.0000','38.0000'), 'fi'=>array('64.0000','26.0000'), 'fj'=>array('-18.0000','175.0000'), 'fk'=>array('-51.7500','-59.0000'), 'fm'=>array('6.9167','158.2500'), 'fo'=>array('62.0000','-7.0000'), 'fr'=>array('46.0000','2.0000'), 'ga'=>array('-1.0000','11.7500'), 'gb'=>array('54.0000','-2.0000'), 'gd'=>array('12.1167','-61.6667'), 'ge'=>array('42.0000','43.5000'), 'gf'=>array('4.0000','-53.0000'), 'gh'=>array('8.0000','-2.0000'), 'gi'=>array('36.1833','-5.3667'), 'gl'=>array('72.0000','-40.0000'), 'gm'=>array('13.4667','-16.5667'), 'gn'=>array('11.0000','-10.0000'), 'gp'=>array('16.2500','-61.5833'), 'gq'=>array('2.0000','10.0000'), 'gr'=>array('39.0000','22.0000'), 'gs'=>array('-54.5000','-37.0000'), 'gt'=>array('15.5000','-90.2500'), 'gu'=>array('13.4667','144.7833'), 'gw'=>array('12.0000','-15.0000'), 'gy'=>array('5.0000','-59.0000'), 'hk'=>array('22.2500','114.1667'), 'hm'=>array('-53.1000','72.5167'), 'hn'=>array('15.0000','-86.5000'), 'hr'=>array('45.1667','15.5000'), 'ht'=>array('19.0000','-72.4167'), 'hu'=>array('47.0000','20.0000'), 'id'=>array('-5.0000','120.0000'), 'ie'=>array('53.0000','-8.0000'), 'il'=>array('31.5000','34.7500'), 'in'=>array('20.0000','77.0000'), 'io'=>array('-6.0000','71.5000'), 'iq'=>array('33.0000','44.0000'), 'ir'=>array('32.0000','53.0000'), 'is'=>array('65.0000','-18.0000'), 'it'=>array('42.8333','12.8333'), 'jm'=>array('18.2500','-77.5000'), 'jo'=>array('31.0000','36.0000'), 'jp'=>array('36.0000','138.0000'), 'ke'=>array('1.0000','38.0000'), 'kg'=>array('41.0000','75.0000'), 'kh'=>array('13.0000','105.0000'), 'ki'=>array('1.4167','173.0000'), 'km'=>array('-12.1667','44.2500'), 'kn'=>array('17.3333','-62.7500'), 'kp'=>array('40.0000','127.0000'), 'kr'=>array('37.0000','127.5000'), 'kw'=>array('29.5000','45.7500'), 'ky'=>array('19.5000','-80.5000'), 'kz'=>array('48.0000','68.0000'), 'la'=>array('18.0000','105.0000'), 'lb'=>array('33.8333','35.8333'), 'lc'=>array('13.8833','-61.1333'), 'li'=>array('47.1667','9.5333'), 'lk'=>array('7.0000','81.0000'), 'lr'=>array('6.5000','-9.5000'), 'ls'=>array('-29.5000','28.5000'), 'lt'=>array('56.0000','24.0000'), 'lu'=>array('49.7500','6.1667'), 'lv'=>array('57.0000','25.0000'), 'ly'=>array('25.0000','17.0000'), 'ma'=>array('32.0000','-5.0000'), 'mc'=>array('43.7333','7.4000'), 'md'=>array('47.0000','29.0000'), 'mg'=>array('-20.0000','47.0000'), 'mh'=>array('9.0000','168.0000'), 'mk'=>array('41.8333','22.0000'), 'ml'=>array('17.0000','-4.0000'), 'mm'=>array('22.0000','98.0000'), 'mn'=>array('46.0000','105.0000'), 'mo'=>array('22.1667','113.5500'), 'mp'=>array('15.2000','145.7500'), 'mq'=>array('14.6667','-61.0000'), 'mr'=>array('20.0000','-12.0000'), 'ms'=>array('16.7500','-62.2000'), 'mt'=>array('35.8333','14.5833'), 'mu'=>array('-20.2833','57.5500'), 'mv'=>array('3.2500','73.0000'), 'mw'=>array('-13.5000','34.0000'), 'mx'=>array('23.0000','-102.0000'), 'my'=>array('2.5000','112.5000'), 'mz'=>array('-18.2500','35.0000'), 'na'=>array('-22.0000','17.0000'), 'nc'=>array('-21.5000','165.5000'), 'ne'=>array('16.0000','8.0000'), 'nf'=>array('-29.0333','167.9500'), 'ng'=>array('10.0000','8.0000'), 'ni'=>array('13.0000','-85.0000'), 'nl'=>array('52.5000','5.7500'), 'no'=>array('62.0000','10.0000'), 'np'=>array('28.0000','84.0000'), 'nr'=>array('-0.5333','166.9167'), 'nu'=>array('-19.0333','-169.8667'), 'nz'=>array('-41.0000','174.0000'), 'om'=>array('21.0000','57.0000'), 'pa'=>array('9.0000','-80.0000'), 'pe'=>array('-10.0000','-76.0000'), 'pf'=>array('-15.0000','-140.0000'), 'pg'=>array('-6.0000','147.0000'), 'ph'=>array('13.0000','122.0000'), 'pk'=>array('30.0000','70.0000'), 'pl'=>array('52.0000','20.0000'), 'pm'=>array('46.8333','-56.3333'), 'pr'=>array('18.2500','-66.5000'), 'ps'=>array('32.0000','35.2500'), 'pt'=>array('39.5000','-8.0000'), 'pw'=>array('7.5000','134.5000'), 'py'=>array('-23.0000','-58.0000'), 'qa'=>array('25.5000','51.2500'), 're'=>array('-21.1000','55.6000'), 'ro'=>array('46.0000','25.0000'), 'ru'=>array('60.0000','100.0000'), 'rw'=>array('-2.0000','30.0000'), 'sa'=>array('25.0000','45.0000'), 'sb'=>array('-8.0000','159.0000'), 'sc'=>array('-4.5833','55.6667'), 'sd'=>array('15.0000','30.0000'), 'se'=>array('62.0000','15.0000'), 'sg'=>array('1.3667','103.8000'), 'sh'=>array('-15.9333','-5.7000'), 'si'=>array('46.0000','15.0000'), 'sj'=>array('78.0000','20.0000'), 'sk'=>array('48.6667','19.5000'), 'sl'=>array('8.5000','-11.5000'), 'sm'=>array('43.7667','12.4167'), 'sn'=>array('14.0000','-14.0000'), 'so'=>array('10.0000','49.0000'), 'sr'=>array('4.0000','-56.0000'), 'st'=>array('1.0000','7.0000'), 'sv'=>array('13.8333','-88.9167'), 'sy'=>array('35.0000','38.0000'), 'sz'=>array('-26.5000','31.5000'), 'tc'=>array('21.7500','-71.5833'), 'td'=>array('15.0000','19.0000'), 'tf'=>array('-43.0000','67.0000'), 'tg'=>array('8.0000','1.1667'), 'th'=>array('15.0000','100.0000'), 'tj'=>array('39.0000','71.0000'), 'tk'=>array('-9.0000','-172.0000'), 'tm'=>array('40.0000','60.0000'), 'tn'=>array('34.0000','9.0000'), 'to'=>array('-20.0000','-175.0000'), 'tr'=>array('39.0000','35.0000'), 'tt'=>array('11.0000','-61.0000'), 'tv'=>array('-8.0000','178.0000'), 'tw'=>array('23.5000','121.0000'), 'tz'=>array('-6.0000','35.0000'), 'ua'=>array('49.0000','32.0000'), 'ug'=>array('1.0000','32.0000'), 'um'=>array('19.2833','166.6000'), 'us'=>array('38.0000','-97.0000'), 'uy'=>array('-33.0000','-56.0000'), 'uz'=>array('41.0000','64.0000'), 'va'=>array('41.9000','12.4500'), 'vc'=>array('13.2500','-61.2000'), 've'=>array('8.0000','-66.0000'), 'vg'=>array('18.5000','-64.5000'), 'vi'=>array('18.3333','-64.8333'), 'vn'=>array('16.0000','106.0000'), 'vu'=>array('-16.0000','167.0000'), 'wf'=>array('-13.3000','-176.2000'), 'ws'=>array('-13.5833','-172.3333'), 'ye'=>array('15.0000','48.0000'), 'yt'=>array('-12.8333','45.1667'), 'rs'=>array('44.0000','21.0000'), 'za'=>array('-29.0000','24.0000'), 'zm'=>array('-15.0000','30.0000'), 'me'=>array('42.0000','19.0000'), 'zw'=>array('-20.0000','30.0000'), 'a1'=>array('0.0000','0.0000'), 'a2'=>array('0.0000','0.0000') );

	var $powered_by = '<p style="height:18px; text-align:right;">Powered by <a href="http://code.google.com/p/geomint/">GeoMint</a>, ';

	var $api_error = '<br /><div class="updated fade"><p style="text-align:center;font-size:1.2em;padding:4px 2px;">Please enter your Google Map API key.<br />
				If you don\'t have a Google Map API key yet, You can get one at <a href="http://www.google.com/apis/maps/" title="Sign up for a Google Maps API key">HERE</a>.</p></div>';

	var $tbGeo, $tbStats, $_tb, $purge_interval, $last_purge;
	var $api_defined = false;
	var $serverUnavailable = false;
	var $multi_c;


	function GeoSlimStat() {
		global $SlimCfg;
		
		if ($SlimCfg->geoip != 'mysql')
			$this->powered_by .= '<a href="http://maxmind.com">MaxMind</a>';
		else {
			$this->powered_by .= '<a href="http://www.seomoz.org/">SEOMOZ.org</a>';
			if (function_exists('curl_multi_init'))
				$this->powered_by .= ' and <a href="http://www.melissadata.com/">MelissaDATA</a>';
			else 
				$this->powered_by .= ' and <a href="http://hostip.info">HostIP Info</a>';
		}
//		$SlimCfg->get['slim_table'] = isset($SlimCfg->get['slim_table']) ? $SlimCfg->get['slim_table'] : 'common';
		$this->tbGeo = $SlimCfg->tbPrefix."geo";
		$this->_tb =	$SlimCfg->get['slim_table'];
		switch($this->_tb) {
			case 'common': default:
				$this->tbStats = $SlimCfg->table_stats;
			break;
			case 'feed':
				$this->tbStats = $SlimCfg->table_feed;
			break;
		}
		$cur_opt = $this->get_option('geo_slimstat');
		if($cur_opt) {
			$this->prefs = array_merge($this->prefs, $cur_opt);
		}
		if(!empty($this->prefs['googleAPI'])) {
			$this->api_defined = true;
		} 
		$this->last_purge = get_option('geo_slimstat_lastpurge');
		if(!$this->last_purge) {
			$this->last_purge = time();
			update_option('geo_slimstat_lastpurge', $this->last_purge);
		}
		$this->purge_interval = (int)$this->prefs['purge_interval'];
	}

	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '1.61') {
			return array	('compatible' => false, 'message' => 'GeoSlimStat 0.3 is only compatible with SlimStat-Ex 1.61 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function pin_actions() {
		return array('options'=>1, 'extra_table'=>1);
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

	function pin_update_options() {
		if(!isset($_POST['geo_slimstat']))
			return;
		$int = array('plotNum', 'plotZoom', 'daysDisplay', 'mapCenter',  'purge_interval');
		$ops = $_POST['geo_slimstat'];
		foreach($ops as $k=>$v) {
			if(in_array($k, $int))
				$ops[$k] = (int)$v;
			else
				$ops[$k] = stripslashes(trim($v));
		}
		$this->prefs = array_merge($this->prefs, $ops);
		$this->update_option('geo_slimstat', $this->prefs);
	}

	function pin_options() {
		$op = $this->get_option('geo_slimstat');
		if(!$op)
			$op = $this->prefs;
?>
<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Google Map API Key:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Enter your Google Maps API Key.', 'wp-slimstat-ex'); ?>
	<?php _e('You can get one at <a href="http://www.google.com/apis/maps/signup.html" title="Sign up for a Google Maps API key">HERE</a>', 'wp-slimstat-ex'); ?><br />
	<input type="text" name="geo_slimstat[googleAPI]" value="<?php echo wp_specialchars($op['googleAPI'], true); ?>" size="64" /></td> 
</tr>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Limit Quries:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('The number of queries (try 30 first, then 50 or more).', 'wp-slimstat-ex'); ?><br />
	<input type="text" name="geo_slimstat[plotNum]" value="<?php echo $op['plotNum']; ?>" size="3" /> Queries</td>
</tr>
<?php /*
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Limit Days:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('The number of days to display in the map (0 means no limit).', 'wp-slimstat-ex'); ?><br />
	<input type="text" name="geo_slimstat[daysDisplay]" value="<?php echo $op['daysDisplay']; ?>" size="3" /> Days</td>
</tr>
*/ ?>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Wheel Zoom:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Use mouse wheel to zoom map. You can use double click zoom without wheel zoom.', 'wp-slimstat-ex'); ?><br />
		<select name="geo_slimstat[wheelZoom]">
			<option value="0"<?php if(!$op['wheelZoom']) { ?> selected="selected"<?php } ?>>NO</option>
			<option value="1"<?php if($op['wheelZoom']) { ?> selected="selected"<?php } ?>>YES</option>
		</select></td>
</tr>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Center Type:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Average point of visitors or custom point you set below (Latitude and Longitude).', 'wp-slimstat-ex'); ?><br />
		<select name="geo_slimstat[mapCenter]">
			<option value="0"<?php if(!$op['mapCenter']) { ?> selected="selected"<?php } ?>><?php _e('Average point of visitors', 'wp-slimstat-ex') ?></option>
			<option value="1"<?php if($op['mapCenter']) { ?> selected="selected"<?php } ?>><?php _e('Point I set below', 'wp-slimstat-ex') ?></option>
		</select> &mdash;
	<?php _e('Zoom:', 'wp-slimstat-ex') ?> <input type="text" name="geo_slimstat[plotZoom]" value="<?php echo $op['plotZoom']; ?>" size="2" /></td>
</tr>
<tr valign="top">
	<th width="20%" scope="row"><?php _e('Starting Center:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Select a type for define starting center.', 'wp-slimstat-ex'); ?><br />
	<?php _e('Latitude:', 'wp-slimstat-ex') ?> <input type="text" name="geo_slimstat[plotLatitude]" value="<?php echo $op['plotLatitude']; ?>" size="6" /> &mdash;
	<?php _e('Longitude:', 'wp-slimstat-ex') ?> <input type="text" name="geo_slimstat[plotLongitude]" value="<?php echo $op['plotLongitude']; ?>" size="6" />
	<p>Get your Latitude, Longitude (replace Seoul,Korea and __APIKEY__ with yours).<br />
	http://maps.google.com/maps/geo?q=Seoul,Korea&amp;output=csv&amp;key=__APIKEY__</p>
	</td>
</tr>
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Purge Negatives:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('When ip-to-geocode result from Hostip.info was empty, Geo SlimStat will append the IP to negative list.', 'wp-slimstat-ex'); ?><br />
	<?php _e('You can purge the negative list every xxx days (0 means no purge)', 'wp-slimstat-ex') ?><br /> 
	<input type="text" name="geo_slimstat[purge_interval]" value="<?php echo $op['purge_interval']; ?>" size="3" /> Days</td>
</tr>
</table>
<?php
	}

	function switchTable() {
		global $SlimCfg;
		$filter_img = "<img src=\"".$SlimCfg->pluginURL."/css/filter-self.gif\" alt=\"Filter\" style=\"vertical-align:bottom;\" />";
		$pinid =& $this->getPinID();
		$use_ajax = $SlimCfg->option['use_ajax'];
		$href = ($use_ajax)?"#":"?page=".$SlimCfg->base."&amp;panel=".$pinid;
		$output .= "<br />\n";
		$output .= "\t<div class=\"interval-filter\">&nbsp;&nbsp;<span>".__('Select Table', 'wp-slimstat-ex')." : \n";
		// Common
		$filter_url = '&amp;slim_table=common';
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View map for &#039;common stats&#039;', 'wp-slimstat-ex')."\" ";
		$output .= "onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\">";
		$output .= __('Common', 'wp-slimstat-ex').$filter_img."</a> | ";
		// Feed
		$filter_url = '&amp;slim_table=feed';
		$output .= "<a href=\"".$href.(($use_ajax)?'':$filter_url)."\" title=\"".__('View map for &#039;feed stats&#039;', 'wp-slimstat-ex')."\" ";
		$output .= "onclick=\"SlimStat.panel('".$pinid."', '".$filter_url."'); return false;\">";
		$output .= __('Feed', 'wp-slimstat-ex').$filter_img."</a>";
		$output .= " <span class=\"filter_string\"> Visitors on ".($this->_tb == 'feed' ? 'feed':'common')." table </span>";
		$output .= "</div>\n";
		return $output;
	}

	function _displayPanel() {
		global $SlimCfg;
		// trick filter encode query
		$myFilterClause = SSFunction::filter_switch();
		$html = '';
		$html .= SSFunction::_getFilterForm();
		$html .= $this->switchTable();

		$html .= $this->plotMap($myFilterClause);// drawing the map
		$html .= SSModule::_moduleSummary($myFilterClause);
//		$html .= SSModule::_moduleRecentReferers($myFilterClause);
		$html .= SSModule::_moduleTopCountries($myFilterClause);

		echo $html;
	}

	function ip_lookup_extra($ip) {
		global $SlimCfg;
		$url = 'http://www.seomoz.org/ip2location/look.php?ip='.$ip;
		$result =  $SlimCfg->remote_fopen($url);
		$result = trim(strip_tags($result));
		$city = $country = $lat = $long = null;
		$result = explode("if (GBrowserIsCompatible()) {", $result);
		if(!$result[1])
			return array();

		$location = $result[0];
		$geo = $result[1];

		if(!empty($geo) && preg_match('#map\.setCenter\(new\s+GLatLng\(([^\)]*)\)#i', $geo, $m)) {
			$geocode = explode(',', $m[1]);
			$lat = trim($geocode[0]);
			$long = trim($geocode[1]);
			if(!empty($location)) {
				$location = explode("\n", trim($location));
				if(count($location) <= 2) {
					$location[0] = trim($location[0]);
					if($location[0] != '-' && !preg_match('#^-,#', $location[0])) {
						$_city = explode(',', $location[0]);
						$_city[0] = trim($_city[0]); $_city[1] = trim($_city[1]);
						if(!empty($_city[1]) && $_city[0] == $_city[1]) {
							$location[0] = $_city[0];
						}
						$city = ucwords(strtolower(str_replace(',', ', ', $location[0])));
					}
				}
			}
			return array('lat'=>$lat, 'long'=>$long, 'city'=>$city, 'country'=>$country);
		}
		return array();
	}

	function ip_lookup_multi($ips) {
		global $SlimCfg;
		$info = array();
		$uris = array();
		$count_ips = count($ips);
		for ($i = 0; $i < $count_ips; $i++) {
			$uris[$i] = 'http://www.seomoz.org/ip2location/look.php?ip='.long2ip($ips[$i]);
//			$uris[$i] = 'http://www.webconfs.com/ip-to-city.php?submit=submit&ipaddress='.long2ip($ips[$i]);
		}
		$data = $SlimCfg->multi_remote_fopen($uris, false);
		if (empty($data) || !is_array($data))
			return $info;

		for ($i = 0; $i < $count_ips; $i++) {
			$result = trim(strip_tags($data[$i]));
			$city = $country = $lat = $long = null;
			$result = explode("if (GBrowserIsCompatible()) {", $result);
			if(!$result[1]) {
				continue;
			}

			$location = $result[0];
			$geo = $result[1];

			if(!empty($geo) && preg_match('#map\.setCenter\(new\s+GLatLng\(([^,]*),([^\)]*)\)#isU', $geo, $m)) {
				$lat = trim($m[1]);
				$long = trim($m[2]);
				if(!empty($location)) {
					$location = explode("\n", trim($location));
					if(count($location) <= 2) {
						$location[0] = trim($location[0]);
						if($location[0] != '-' && !preg_match('#^-,#', $location[0])) {
							$_city = explode(',', $location[0]);
							$_city[0] = trim($_city[0]); $_city[1] = trim($_city[1]);
							if(!empty($_city[1]) && $_city[0] == $_city[1]) {
								$location[0] = $_city[0];
							}
							$city = ucwords(strtolower(str_replace(',', ', ', $location[0])));
						}
					}
				}
				$info[$ips[$i]] = array('lat'=>$lat, 'long'=>$long, 'city'=>$city, 'country'=>$country);
			}
		}
		return $info;
	}

	function ip_lookup_multi_sub($ips) {// just for city information
		global $SlimCfg;
		$count_ips = count($ips);
		$uris = array();
		$info = array();
		for ($i = 0; $i < $count_ips; $i++) {
			$uris[$i] = 'http://www.melissadata.com/lookups/iplocation.asp?ipaddress='.long2ip($ips[$i]);
		}
		$data = $SlimCfg->multi_remote_fopen($uris, false);
		if (empty($data) || !is_array($data))
			return $info;

		for ($i = 0; $i < $count_ips; $i++) {
			$result = trim(strip_tags($data[$i], '<b>'));
			if (preg_match('#City\s*<b>(.*?)</b>\s*State or Region\s*<b>(.*?)</b>#is', $result, $loc)) {
				$city = trim($loc[1]) == '-' ? '' : trim($loc[1]);
				$city .= trim($loc[2]) == '-' ? '' : ', '.trim($loc[2]);
				$info[$ips[$i]] = ucwords(strtolower($city));
			}
		}
		return $info;
	}

	function insert_geo_data($info) {
		global $wpdb, $SlimCfg;
//		$info = apply_filters('geoslimstat_geo_data', $info);
		$ip = $info['ip'];
		$lat = $info['lat'];
		$long = $info['long'];
		$city = $info['city'];
		$country_abrv = strtolower($info['country_abrv']);
		if (!seems_utf8($city)) {
			$city = $SlimCfg->convert_encoding($city);
		}
		$city = $wpdb->escape($city);
		$country_abrv = $wpdb->escape($country_abrv);
		$query = "INSERT INTO {$this->tbGeo} (ip, country_abrv, city, latitude, longitude)
									VALUES ($ip, '$country_abrv', '$city', '$lat', '$long')";
		$result = $wpdb->query($query);
	}

	function queryHostIP_bin($ip) {
		global $SlimCfg;
		$now = time();
		if($now > $this->last_purge + (60*60*24*$this->purge_interval)) {// Purge neg ip list every "purge_interval" days
			update_option('geo_slimstat_negip', array());
			update_option('geo_slimstat_lastpurge', time());
		}
		$_neg = (array)get_option('geo_slimstat_negip');
		if(in_array($ip, $_neg))
			return;
		$_ip = long2ip($ip);
		$loc = $SlimCfg->geoip_location($_ip);

		if (!$loc || !$loc->latitude && !$loc->longitude) {
			$_neg[] = $ip;
			update_option('geo_slimstat_negip', $_neg);
			return;
		}

		$lat = round($loc->latitude, 4);
		$long = round($loc->longitude, 4);
		$city = $loc->city ? $loc->city : '';
		$city .= $loc->region_full ? ', '.$loc->region_full : '';
		$country_abrv = strtolower($loc->country_code);

		$this->insert_geo_data( array( 'ip'=>$ip, 'city'=>$city, 'lat'=>$lat, 'long'=>$long, 'country_abrv'=>$country_abrv ) );
	}

	function multi_queryHostIP($iparrs) {
		global $wpdb;
		$now = time();
		if($now > $this->last_purge + (60*60*24*$this->purge_interval)) {// Purge neg ip list every "purge_interval" days
			update_option('geo_slimstat_negip', array());
			update_option('geo_slimstat_lastpurge', time());
		}
		$ip_info_arrs = array();
		$count = count($iparrs);
	
		for ($i=0; $i < $count; $i++) {
			$count_ips = count($iparrs[$i]);
			$_neg = (array)get_option('geo_slimstat_negip');

			for ($j=0; $j<$count_ips; $j++) {
				if (in_array($iparrs[$i][$j], $_neg))
					array_splice($iparrs[$i], $j, 1);
			}

			$count_ips = count($iparrs[$i]);
			$infos = $this->ip_lookup_multi($iparrs[$i]);
			$info_sub = $ips_sub = array();
			for ($j=0; $j<$count_ips; $j++) {
				$ip = $iparrs[$i][$j];
				$info = $infos[$ip];
				$lat = round(trim($info['lat']), 4);
				$long = round(trim($info['long']), 4);
				if(!$lat && !$long) {
					$_neg[] = $ip;
					update_option('geo_slimstat_negip', $_neg);
					continue;
				}
				if (is_null($lat)) $lat = 0;
				if (is_null($long)) $long = 0;
				$country_abrv = SSTrack::_determineCountry($ip);
				$city = $info['city'] ? trim($info['city']) : '';
				if ($city == '') {
					$ips_sub[] = $ip;
					$info_sub[$ip]['lat'] = $lat;
					$info_sub[$ip]['long'] = $long;
					$info_sub[$ip]['country_abrv'] = $country_abrv;
				} else {
					$this->insert_geo_data( array( 'ip'=>$ip, 'city'=>$city, 'lat'=>$lat, 'long'=>$long, 'country_abrv'=>$country_abrv ) );
				}
			}

			if (!empty($ips_sub)) {
				$cities = $this->ip_lookup_multi_sub($ips_sub);
				foreach ($ips_sub as $ip) {
					$city = isset($cities[$ip]) ? $cities[$ip] : '';
					$this->insert_geo_data( array( 'ip'=>$ip, 'city'=>$city, 'lat'=>$info_sub[$ip]['lat'], 'long'=>$info_sub[$ip]['long'], 'country_abrv'=>$info_sub[$ip]['country_abrv'] ) );
				}
			}
		}
	}

	/**************************************************************************
	queryHostIP($ip_adr)
		This is the function which queries HostIP.info and stores the data in our
		local db
	**************************************************************************/
	function queryHostIP($ip_adr) {
		global $wpdb, $SlimCfg;
		
		$now = time();
		if($now > $this->last_purge + (60*60*24*$this->purge_interval)) {// Purge neg ip list every "purge_interval" days
			update_option('geo_slimstat_negip', array());
			update_option('geo_slimstat_lastpurge', time());
		}
		$_neg = get_option('geo_slimstat_negip');
		if(!is_array($_neg))
			$_neg = array();
		if(in_array($ip_adr, $_neg))
			return;

		$ip_adr_ip = long2ip($ip_adr);
		$ip_info = $this->ip_lookup_extra($ip_adr_ip);
		if(!empty($ip_info) && !is_null($ip_info['lat']) && !is_null($ip_info['long'])) {
			$lat = round(trim($ip_info['lat']), 4);
			$long = round(trim($ip_info['long']), 4);
			$city = $ip_info['city'] ? trim($ip_info['city']) : '';
			$country_abrv = SSTrack::_determineCountry($ip_adr);
		} else {

			$ip_lookup_uri = "http://api.hostip.info/get_html.php?ip={$ip_adr_ip}&position=true";
			$data = $SlimCfg->remote_fopen($ip_lookup_uri);
			if(!$data || '' == $data)
				return;

			$pos = strpos($data, "City:");
			$tmp_string = substr($data, 0, $pos);
			$tmp_string = trim($tmp_string);
			$tmp_string = substr($tmp_string, 9, strlen($tmp_string));
					
			$country_abrv = substr($tmp_string, strpos($tmp_string, "("), strlen($tmp_string));
			$country_abrv = trim($country_abrv, "()");
			if ($country_abrv == '')
				$country_abrv = 'Unk';
					
			$tmp_string = substr($data, $pos, strpos($data, "Latitude:") - $pos);
			$city = substr($tmp_string, 5, strlen($tmp_string));
			$city = trim($city);
			if (preg_match("/unknown city/i", $city))
				$city = '';

			$tmp_string = substr($data, strpos($data, "Latitude:"), strlen($data));
			$lat = substr($tmp_string, 9, strpos($tmp_string, "Longitude:") - 9);
			$lat = trim($lat);
					
			$tmp_string = substr($tmp_string, strpos($tmp_string, "Longitude:"), strlen($tmp_string));
			$long = substr($tmp_string, 10, strlen($tmp_string));
			$long = trim($long);
		}

		if(!$lat && !$long) {
			$_neg[] = $ip_adr;
			update_option('geo_slimstat_negip', $_neg);
			return;
		}
		if (is_null($lat)) $lat = 0;
		if (is_null($long)) $long = 0;
		$this->insert_geo_data( array( 'ip'=>$ip_adr, 'city'=>$city, 'lat'=>$lat, 'long'=>$long, 'country_abrv'=>$country_abrv ) );
	}

	function get_MapCenter($positions) {
		global $SlimCfg;
		$zoom = $this->prefs['plotZoom'];
		// if country filter is set and user defined zoom level is smaller than 5
		if($zoom < 5 && isset($SlimCfg->get['ff']) && $SlimCfg->get['ff'] == 6)
			$zoom = 5;
		if(empty($positions) || $this->prefs['mapCenter'] == 1)
			return array($this->prefs['plotLatitude'], $this->prefs['plotLongitude'], $zoom);
		$lat = $positions['lat'];
		$long = $positions['long'];
		$avr_lat = round((array_sum($lat)/count($lat)), 4);
		$avr_long = round((array_sum($long)/count($long)), 4);
		return array($avr_lat, $avr_long, $zoom);
	}

	/**************************************************************************
	plotMap()
	**************************************************************************/
	function plotMap($filter_clause) {
		global $SlimCfg, $wpdb;
		if(!$this->api_defined) {
			return $this->api_error;
		}
	
		$html = '';
		$cnt_querieslocal = 0;				// some ugly vars
		$cnt_querieshostip = 0;
		$n_host_msg = '';
		$cnt_points = 0;
		$positions = array();
	
		$prefs = $this->prefs;
		// The most important part, the sql-query. Gets the latest, DISTINCT IPs by date dt. Main Mint db
		$query = "SELECT ts.remote_ip, ts.dt
							FROM {$this->tbStats} ts
							WHERE $filter_clause
							GROUP BY ts.remote_ip
							ORDER BY ts.dt DESC
							LIMIT 0, {$prefs['plotNum']}";

		if($result = $wpdb->get_results($query, ARRAY_A)) {
			if ($this->serverUnavailable == false) {
				$ips = array();
				$i = 0;
				foreach($result as $r) {
				$_neg = get_option('geo_slimstat_negip');
				if(!is_array($_neg))
					$_neg = array();
				if(in_array($r['remote_ip'], $_neg))
					continue;
					$query = "SELECT * FROM {$this->tbGeo} tg
										WHERE tg.ip = '{$r['remote_ip']}'
										LIMIT 1";
					if(!$wpdb->get_row($query)) {
//						timer_start();
						if ( $SlimCfg->geoip == 'city' ) {
							$this->queryHostIP_bin($r['remote_ip']);
						} else if ( function_exists('curl_multi_init') ) {
							$count = count($ips[$i]);
							if ( $count !=0 && ($count % 50) == 0 ) $i++;
							$ips[$i][] = $r['remote_ip'];
						} else {
							$this->queryHostIP($r['remote_ip']);// Not in our local db? Query HostIP
						}
//						echo "hostip query time : "; timer_stop(1); echo "<br />\n";
						$cnt_querieshostip++;
					}
				}
				if (!empty($ips)) {
//						timer_start();
					$this->multi_queryHostIP($ips);
//						echo "hostip query time : "; timer_stop(1); echo "<br />\n";
				}
			} else {
				$n_host_msg = 'HostIP.info unavailable.';
			}
		}

		$html_js = '';

		if ($prefs['daysDisplay']) {
			$dt_limit = time() - 86400 * $prefs['daysDisplay'];
			$daysDisplay_query = "HAVING dt_max > {$dt_limit}";
		}
		$query = "SELECT tg.ip, tg.country_abrv, tg.city, tg.latitude, tg.longitude, ts.country,
							MAX(ts.dt) AS dt_max, COUNT(*) AS hits
							FROM {$this->tbStats} ts, {$this->tbGeo} tg
							WHERE ts.remote_ip = tg.ip 
								AND (ts.country <> '' OR tg.country_abrv <> 'Unk')
								AND $filter_clause
							GROUP BY ts.remote_ip
							{$daysDisplay_query}
							ORDER BY dt_max DESC
							LIMIT 0 , {$prefs['plotNum']}";

		$local_q_ip = array();
		if($result_geo = $wpdb->get_results($query, ARRAY_A)) {
			foreach($result_geo as $row) {
				$_city = ('' == $row['city']) ? __('Unknown City', 'wp-slimstat-ex') : str_replace("'", "\\'", $row['city']);
				$_country = ($row['country_abrv'] && 'Unk' == $row['country_abrv']) ? strtolower($row['country']) : strtolower($row['country_abrv']);
				$_ip = long2ip($row['ip']);
				$_ip = "<a href=\'http://private.dnsstuff.com/tools/ipall.ch?ip={$_ip}#map\' title=\'Who is?\' target=\'_blank\'>{$_ip}</a>";
				$_lat = $row['latitude'];
				$_long = $row['longitude'];
				$_dtmax = $row['dt_max'];
				if($_lat == 0 || $_long == 0) {
					if(!isset($this->country2geocode[$_country]))
						continue;
					$c2geo = $this->country2geocode[$_country];
					$_lat = $c2geo[0];
					$_long = $c2geo[1];
				}
				$local_q_ip[] = $row['ip'];
				$positions['lat'][] = $_lat;
				$positions['long'][] = $_long;
				$cnt_querieslocal++;

				$tmp_date = $SlimCfg->_date(__("F j, Y g:ia", 'wp-slimstat-ex'), $_dtmax);
				$tmp = "<div style=\'line-height:20px;\'><div style=\'white-space:nowrap;\'>{$_ip} ({$row['hits']} hits)</div>";
				$tmp .= "<div style=\'white-space:nowrap;\'>{$_city}, ".__('c-'.$_country, 'wp-slimstat-ex')."</div><div style=\'white-space:nowrap;\'>{$tmp_date}</div></div>";

$html_js .= <<<JAVASCRIPT

	GeoPoint = new GLatLng({$_lat}, {$_long});
	GeoMarker[{$cnt_points}] = createMarker(GeoPoint, {$cnt_points}, '');
	map.addOverlay(GeoMarker[{$cnt_points}]);
	SlimVisitor[{$cnt_points}] = new Array();
	SlimVisitor[{$cnt_points}][0] = '{$tmp}';
JAVASCRIPT;
				$cnt_points++;
			}
		}

		$local_query_limit = $prefs['plotNum'] - count($local_q_ip);
		if ($local_query_limit > 0 ) {
			$qs_remove_local_q = empty($local_q_ip) ? "" : " AND ts.remote_ip NOT IN (".implode(', ', $local_q_ip).") ";
			$query = "SELECT ts.remote_ip, ts.country, MAX(ts.dt) as dt_max, COUNT(*) AS hits
					FROM {$this->tbStats} ts
					USE INDEX (remote_ip)
					WHERE ts.country <> ''
						AND ts.remote_ip <> 0
						{$qs_remove_local_q}
						AND $filter_clause
					GROUP BY ts.remote_ip
					ORDER BY dt_max DESC
					LIMIT 0, {$local_query_limit}";
			$lat_long = array();
//			timer_start();
			if($result_stats = $wpdb->get_results($query, ARRAY_A)) {
				foreach($result_stats as $row) {
					if(in_array($row['remote_ip'], $local_q_ip))
						continue;
					$_country = strtolower($row['country']);
					$_ip = long2ip($row['remote_ip']);
					$_ip = "<a href=\'http://private.dnsstuff.com/tools/ipall.ch?ip={$_ip}#map\' title=\'Who is?\' target=\'_blank\'>{$_ip}</a>";
					$_dtmax = $row['dt_max'];
					if(!isset($this->country2geocode[$_country]))
						continue;
					$c2geo = $this->country2geocode[$_country];
					$_lat = $c2geo[0];
					$_long = $c2geo[1];
					$positions['lat'][] = $_lat;
					$positions['long'][] = $_long;
					$_lat_long = ''.$_lat.','.$_long.'';
					$lat_long[$_lat_long][] = array('ip'=>''.$_ip.'', 'hits'=>$row['hits'], 'dtmax'=>$_dtmax, 'country'=>''.$_country.'');
				}
			}
//			echo "Stats DB time : "; timer_stop(1); echo "<br />\n";
			foreach($lat_long as $lkey=>$val) {
				$lkey_str = str_replace("'", '', $lkey);
				$count = count($val);
				if ($count >99)
					$count = 'blank';
				elseif ($count == 1)
					$count = '';
$html_js .=<<<JAVASCRIPT

	GeoPoint = new GLatLng({$lkey_str});
JAVASCRIPT;

$html_js .=<<<JAVASCRIPT

	GeoMarker[{$cnt_points}] = createMarker(GeoPoint, {$cnt_points}, '{$count}');
	map.addOverlay(GeoMarker[{$cnt_points}]);
	SlimVisitor[{$cnt_points}] = new Array();
JAVASCRIPT;
				$j = 0;
				foreach($val as $v) {
					$tmp_date = $SlimCfg->_date(__("F j, Y g:ia", 'wp-slimstat-ex'), $v['dtmax']);
					$tmp = "<div style=\'line-height:20px;\'><div style=\'white-space:nowrap;\'>{$v['ip']} ({$v['hits']} hits)</div>";
					$tmp .= "<div style=\'white-space:nowrap;\'>Unkown City, ".__('c-'.$v['country'], 'wp-slimstat-ex')."</div><div style=\'white-space:nowrap;\'>{$tmp_date}</div></div>";

$html_js .= <<<JAVASCRIPT
	SlimVisitor[{$cnt_points}][{$j}] = '{$tmp}';
JAVASCRIPT;
					$j++;
				}
				$cnt_points++;
				$cnt_querieslocal++;
			}
		}
		$map_center = $this->get_MapCenter($positions);

		$html_js_header = <<<JAVASCRIPT

// Disable sweetTitles events.
if(typeof(sweetTitles) != 'undefined') {
	sweetTitles.tipOut = function() {};
	sweetTitles.tipOver = function() {};
}

function do_load_map() {
	if (GBrowserIsCompatible()) {
		var mapDOM = document.getElementById('geo_map');
		mapDOM.setAttribute('style', 'width: {$prefs['plotWidth']}; height: {$prefs['plotHeight']};');
		
		map = new GMap2(mapDOM);
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		var overviewmap = new GOverviewMapControl(new GSize(160,120));
		map.addControl(overviewmap);
		overviewmap.hide(true);
// Mouse wheel zoom - Attach event handlers -----
		map.enableDoubleClickZoom(); 
		map.enableContinuousZoom();
JAVASCRIPT;

	if($prefs['wheelZoom']) {
		$html_js_header .= <<<JAVASCRIPT

		GEvent.addDomListener(mapDOM, 'DOMMouseScroll', wheelZoom);
		GEvent.addDomListener(mapDOM, 'mousewheel', wheelZoom);
JAVASCRIPT;
	}
		$html_js_header .= <<<JAVASCRIPT

		map.setCenter(new GLatLng({$map_center[0]}, {$map_center[1]}), {$map_center[2]});

JAVASCRIPT;

		$html_js_footer = <<<JAVASCRIPT
	}
}
JAVASCRIPT;

$html_js = $html_js_header.$html_js.$html_js_footer;
$html_geo = $this->geo_script;
$html_geo .= <<<HTML
<!--[if IE]>
<style type="text/css">
#geo_map{ width: {$prefs['plotWidth']}; height: {$prefs['plotHeight']}
</style>
<![endif]-->
<div id="geo_map" style="width: {$prefs['plotWidth']}; height: {$prefs['plotHeight']};"></div>
HTML;

$html_geo .= <<<HTML
<img alt="blank_image" src="{$SlimCfg->pluginURL}/css/blank.gif" style="position:absolute;width:0px;height:0px" onload="(function(){
	{$html_js}
	var _interval = setInterval(function(){
		if(document.getElementById('geo_map')){
			clearInterval(_interval);
			do_load_map();
		}
	}, 10);

})()" onunload="GUnload()" />

HTML;
//		$html_geo .= "<div style='background-color:#EDF7DF;height:70px;'>";
//		$html_geo .= "&nbsp;&mdash;&nbsp;Working with $cnt_points points $n_host_msg";
//		$html_geo .= ", $cnt_querieshostip queries from Hostip.info";
//		$html_geo .= ", $cnt_querieslocal local queries";
		$html_geo .= $this->powered_by;
//		$html_geo .= "</div>";

		$moid =& $this->getMoID(0);
		return SSFunction::get_module_custom($moid, $html_geo, 'wide', '', array('height'=>'404px', 'width'=>'98%'));
	}
}

function GeoSlimStat_admin_head() {
	global $SlimCfg;
	$op = SSPins::get_option('geo_slimstat');
	if(!$op || empty($op['googleAPI']))
		return;
	if(wp_slimstat_ex::is_slimstat_page()) {
		$icon_url = $SlimCfg->pluginURL.'/pins/GeoSlimStat/markers/marker';
?>
<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $op['googleAPI']; ?>" charset="utf-8"></script>
<script type='text/javascript' charset='utf-8'>//<![CDATA[
//---------------------------------
// Global variables
var map;
var GeoNav = 0;
var GeoMarkerMsgLoaded = false;
var GeoPoint;
var wheelZooming = false;
var mouseLatLng;
var GeoMarker = new Array();
var SlimVisitor = new Array();

//---------------------------------

var baseIcon = new GIcon();
baseIcon.shadow = 'http://www.google.com/mapfiles/shadow50.png';
baseIcon.image = 'http://www.google.com/mapfiles/marker.png';
baseIcon.iconSize = new GSize(20, 34);
baseIcon.shadowSize = new GSize(37, 34);
baseIcon.iconAnchor = new GPoint(9, 34);
baseIcon.infoWindowAnchor = new GPoint(9, 2);
baseIcon.infoShadowAnchor = new GPoint(18, 25);

function createMarker(point, num, str) {
	var GeoIcon = new GIcon(baseIcon);
	GeoIcon.image = '<?php echo $icon_url; ?>'+str+'.png';
	var marker = new GMarker(point, GeoIcon);
	GEvent.addListener(marker, 'click', function() {
		GeoMarkerMsgLoaded = true;
		marker.openInfoWindowHtml(GeoMarkerMsgLoad(num));
//		map.panTo(point, map.getZoom());
	});
	return marker;
}

function _createMarker(point,num,str) {
	var GeoIcon = new GIcon(baseIcon);
	GeoIcon.image = '<?php echo $icon_url; ?>'+str+'.png';
	var marker = new GMarker(point, GeoIcon);

	GEvent.addListener(marker, "click", function() {
		var tab1 = new GInfoWindowTab("Info", GeoMarkerMsgLoad(num));
		var tab2 = new GInfoWindowTab("Location", '<div id="detailmap"><'+'/div>');
		var infoTabs = [tab1,tab2];
		marker.openInfoWindowTabsHtml(infoTabs);

		var dMapDiv = document.getElementById("detailmap");
		var detailmap = new GMap2(dMapDiv);
		dMapDiv.setAttribute('style', 'width:100%;height:120px;');
		var detailzoom = map.getZoom() + 5;
		if(detailzoom > 10)
			detailzoom = 10;			
		detailmap.setCenter(point , detailzoom);
		detailmap.addOverlay(new GMarker(point, GeoIcon));
		var CopyrightDiv = dMapDiv.firstChild.nextSibling;
		var CopyrightImg = dMapDiv.firstChild.nextSibling.nextSibling;
		CopyrightDiv.style.display = "none"; 
		CopyrightImg.style.display = "none"; 
	});
	return marker;
}

function PrevVisitor(num) {
	GeoNav--;
	if(GeoNav<0)
		GeoNav = SlimVisitor[num].length - 1;
	GeoMarker[num].openInfoWindowHtml(GeoMarkerMsgLoad(num));
}
function NextVisitor(num) {
	GeoNav++;
	if(GeoNav>=SlimVisitor[num].length)
		GeoNav = 0;
	GeoMarker[num].openInfoWindowHtml(GeoMarkerMsgLoad(num));
}
function GeoMarkerMsgLoad(num) {
	var MarkerMsg = '';
	var total = SlimVisitor[num].length;

	MarkerMsg += SlimVisitor[num][GeoNav];

	if(GeoMarkerMsgLoaded) {
		GeoMarkerMsgLoaded = false;
		GeoNav = 0;
	}
	if(total==1) {
		GeoNav = 0;
	} else {
		var now = GeoNav + 1;
		if(now<=1)
			MarkerMsg += ' '+now+' / '+total+' <a href=\'javascript:NextVisitor('+num+')\'><strong>&raquo;<'+'/strong><'+'/a>';
		else if(now>=total)
			MarkerMsg += '<a href=\'javascript:PrevVisitor('+num+')\'><strong>&laquo;<'+'/strong><'+'/a> '+now+' / '+total;
		else
			MarkerMsg += '<a href=\'javascript:PrevVisitor('+num+')\'><strong>&laquo;<'+'/strong><'+'/a> '+now+' / '+total+' <a href=\'javascript:NextVisitor('+num+')\'><strong>&raquo;<'+'/strong><'+'/a>';
	}
	return MarkerMsg;
}

// Code from http://maps.forum.nu/
// Mouse wheel zoom - Event handler -----
function wheelZoom(event) {
	if (wheelZooming)
		return;
	wheelZooming = true;

	if (event.cancelable) {
		event.preventDefault();
	}
	map.closeInfoWindow(); 
	if((event.detail || -event.wheelDelta) < 0) {
		window.setTimeout(function(){
			map.zoomIn(mouseLatLng,true,true);
			wheelZooming = false;
		},350);
	} 
	else {
		window.setTimeout(function(){
			map.zoomOut(mouseLatLng,true);
			wheelZooming = false;
		},350);
	}
	return false; 
}
// End event handler -----
//]]></script>
<?php
	}
}

function GeoSlimStat_fellow_links($links, $id) {
	global $SlimCfg, $GeoSlimStat;
	if ( !is_object($GeoSlimStat) )
		$GeoSlimStat = new GeoSlimStat;
	
	$panel = $GeoSlimStat->getPinID();
	$map = $GeoSlimStat->getMoID(0);
	if ($SlimCfg->get['pn'] != $panel)
		return $links;
	switch($id) {
		case '1': $links = array(6,7,8,9); break;
		case '19': $links = array(2,3); break;
		default: break;
	}
	return $links;
}

add_action('admin_head', 'GeoSlimStat_admin_head', 50);
add_filter('slimstat_fellow_links', 'GeoSlimStat_fellow_links', 10, 2);
//if (file_exists(dirname(__FILE__) . '/custom_geo_query.php')) {
//	require_once(dirname(__FILE__) . '/custom_geo_query.php');
//}
?>