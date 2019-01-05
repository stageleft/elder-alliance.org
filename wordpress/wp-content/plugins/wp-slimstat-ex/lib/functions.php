<?php
// stripos() needed because stripos is only present on PHP 5
// borrowed from bad behaviour plugin (http://www.bad-behavior.ioerror.us/)
if (!function_exists('stripos')) {
	function stripos($haystack,$needle,$offset = 0) {
		return(strpos(strtolower($haystack),strtolower($needle),$offset));
	}
}

// sort array by value of given key
// borrowed from comment on PHP manual (http://php.net/manual/en/function.uasort.php#52888)
if (!function_exists('__masort')):
function __masort(&$data, $sortby) {
	static $sort_funcs = array();

	if (empty($sort_funcs[$sortby])) {
		$code = "\$c=0;";
		foreach (split(',', $sortby) as $key) {
			$array = array_pop($data);
			array_push($data, $array);
			if (is_numeric($array[$key]))
				$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] < \$b['$key']) ? -1 : 1 )) ) return \$c;";
			else
				$code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return \$c;\n";
		}
		$code .= 'return $c;';
		$sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
	} else {
		$sort_func = $sort_funcs[$sortby];
	}
	$sort_func = $sort_funcs[$sortby];
	uasort($data, $sort_func);
}
endif;

class SSFunction {

	function _translateBrowserID( $browser_id = '-1' ) {

		$id2browser = array( 
			0=>__('Mozilla', 'wp-slimstat-ex'),1=>__('Netscape', 'wp-slimstat-ex'),2=>__('Safari', 'wp-slimstat-ex'),3=>__('iCab', 'wp-slimstat-ex'),4=>__('Firefox', 'wp-slimstat-ex'),5=>__('Firebird', 'wp-slimstat-ex'),6=>__('Phoenix', 'wp-slimstat-ex'),7=>__('Camino', 'wp-slimstat-ex'),8=>__('Chimera', 'wp-slimstat-ex'),9=>__('Internet Explorer', 'wp-slimstat-ex'),10=>__('MSN Explorer', 'wp-slimstat-ex'),11=>__('WordPress', 'wp-slimstat-ex'),12=>__('BlogSearch Engine', 'wp-slimstat-ex'),13=>__('AllBlog.net RssSync', 'wp-slimstat-ex'),14=>__('HanRSS', 'wp-slimstat-ex'),15=>__('Blogging Client', 'wp-slimstat-ex'),16=>__('W3C HTML Validator', 'wp-slimstat-ex'),17=>__('ATOM &amp; RSS Validator', 'wp-slimstat-ex'),18=>__('W3C CSS Validator', 'wp-slimstat-ex'),19=>__('Python-urllib', 'wp-slimstat-ex'),20=>__('NewsGator', 'wp-slimstat-ex'),21=>__('Google Desktop', 'wp-slimstat-ex'),22=>__('Java', 'wp-slimstat-ex'),23=>__('AOL', 'wp-slimstat-ex'),24=>__('AOL Browser', 'wp-slimstat-ex'),25=>__('K-Meleon', 'wp-slimstat-ex'),26=>__('Beonex', 'wp-slimstat-ex'),27=>__('Opera', 'wp-slimstat-ex'),28=>__('OmniWeb', 'wp-slimstat-ex'),29=>__('Konqueror', 'wp-slimstat-ex'),30=>__('Galeon', 'wp-slimstat-ex'),	31=>__('Epiphany', 'wp-slimstat-ex'),32=>__('Kazehakase', 'wp-slimstat-ex'),33=>__('Amaya', 'wp-slimstat-ex'),34=>__('Crawler', 'wp-slimstat-ex'),35=>__('Lynx', 'wp-slimstat-ex'),36=>__('Links', 'wp-slimstat-ex'),37=>__('ELinks', 'wp-slimstat-ex'),38=>__('Thunderbird', 'wp-slimstat-ex'),39=>__('Flock', 'wp-slimstat-ex'),40=>__('libwww Perl library', 'wp-slimstat-ex'),41=>__('Apache Bench tool (ab)', 'wp-slimstat-ex'),42=>__('SeaMonkey', 'wp-slimstat-ex'),43=>__('Google MediaPartners', 'wp-slimstat-ex'),44=>__('Google FeedFetcher', 'wp-slimstat-ex'),45=>__('GoogleBot', 'wp-slimstat-ex'),46=>__('MsnBot', 'wp-slimstat-ex'),47=>__('Yahoo Blogs', 'wp-slimstat-ex'),48=>__('GigaBot', 'wp-slimstat-ex'),49=>__('ZyBorg', 'wp-slimstat-ex'),50=>__('Nutch CVS', 'wp-slimstat-ex'),51=>__('Ichiro', 'wp-slimstat-ex'),52=>__('Technorati Bot', 'wp-slimstat-ex'),53=>__('Heritrix', 'wp-slimstat-ex'),54=>__('FeedBurner', 'wp-slimstat-ex'),55=>__('Feedpath(JP)', 'wp-slimstat-ex'),56=>__('Netvibes', 'wp-slimstat-ex'),57=>__('GreatNews', 'wp-slimstat-ex'),58=>__('MagpieRSS', 'wp-slimstat-ex'),59=>__('livedoor FeedFetcher', 'wp-slimstat-ex'),60=>__('Snoopy', 'wp-slimstat-ex'),61=>__('Eolin', 'wp-slimstat-ex'),62=>__('Mac Finder', 'wp-slimstat-ex'),63=>__('Movabletype', 'wp-slimstat-ex'),64=>__('Typepad', 'wp-slimstat-ex'),65=>__('Drupal', 'wp-slimstat-ex'),66=>__('AnonyMouse', 'wp-slimstat-ex'),67=>__('Bluefish', 'wp-slimstat-ex'),68=>__('Amiga-AWeb', 'wp-slimstat-ex'),69=>__('avant Browser', 'wp-slimstat-ex'),70=>__('AmigaVoyager', 'wp-slimstat-ex'),71=>__('Emacs-W3', 'wp-slimstat-ex'),72=>__('cURL', 'wp-slimstat-ex'),73=>__('Democracy', 'wp-slimstat-ex'),74=>__('Dillo', 'wp-slimstat-ex'),75=>__('DocZilla', 'wp-slimstat-ex'),76=>__('edbrowse', 'wp-slimstat-ex'),77=>__('libFetch', 'wp-slimstat-ex'),78=>__('IceWeasel', 'wp-slimstat-ex'),79=>__('IBrowse', 'wp-slimstat-ex'),80=>__('ICE Browser', 'wp-slimstat-ex'),81=>__('Danger Hiptop', 'wp-slimstat-ex'),82=>__('KKman', 'wp-slimstat-ex'),83=>__('Mosaic', 'wp-slimstat-ex'),84=>__('NetPositive', 'wp-slimstat-ex'),85=>__('Songbird', 'wp-slimstat-ex'),86=>__('Sylera', 'wp-slimstat-ex'),87=>__('Shiira', 'wp-slimstat-ex'),88=>__('WebTV (MS)', 'wp-slimstat-ex'),89=>__('W3M', 'wp-slimstat-ex'),90=>__('HistoryHound', 'wp-slimstat-ex'),91=>__('BlogKorea', 'wp-slimstat-ex'),92=>__('Oregano', 'wp-slimstat-ex'),93=>__('WDG_Validator', 'wp-slimstat-ex'),94=>__('Docomo', 'wp-slimstat-ex'),95=>__('NewsFire', 'wp-slimstat-ex'),96=>__('NewsAlloy', 'wp-slimstat-ex'),97=>__('Liferea', 'wp-slimstat-ex'),98=>__('Hatena RSS', 'wp-slimstat-ex'),99=>__('Feedshow', 'wp-slimstat-ex'),100=>__('NetNewsWire', 'wp-slimstat-ex'),101=>__('Acorn Browse', 'wp-slimstat-ex'),102=>__('Bonecho', 'wp-slimstat-ex'),103=>__('Lycos Crawlers', 'wp-slimstat-ex'),104=>__('Multizilla', 'wp-slimstat-ex'),105=>__('J2ME/MIDP', 'wp-slimstat-ex'),106=>__('PHP', 'wp-slimstat-ex'),107=>__('Yahoo! Slurp', 'wp-slimstat-ex'),108=>__('Google Bots', 'wp-slimstat-ex'),109=>__('Google Appliance', 'wp-slimstat-ex'),110=>__('Yahoo Crawlers', 'wp-slimstat-ex'),111=>__('Inktomi Crawlers', 'wp-slimstat-ex'),112=>__('Cheshire', 'wp-slimstat-ex'),113=>__('Crazy Browser', 'wp-slimstat-ex'),114=>__('Enigma Browser', 'wp-slimstat-ex'),115=>__('GranParadiso', 'wp-slimstat-ex'),116=>__('Iceape', 'wp-slimstat-ex'),117=>__('K-Ninja', 'wp-slimstat-ex'),118=>__('Maxthon', 'wp-slimstat-ex'),119=>__('Minefield', 'wp-slimstat-ex'),120=>__('MyIE2', 'wp-slimstat-ex'),121=>__('MSN Crawlers', 'wp-slimstat-ex'),122=>__('Wii Libnup', 'wp-slimstat-ex'),123=>__('W3C-checklink', 'wp-slimstat-ex'),124=>__('Xenu Link Sleuth', 'wp-slimstat-ex'),125=>__('CSE HTML Validator', 'wp-slimstat-ex'),126=>__('CSSCheck', 'wp-slimstat-ex'),127=>__('Cynthia', 'wp-slimstat-ex'),128=>__('HTMLParser', 'wp-slimstat-ex'),129=>__('P3P Validator', 'wp-slimstat-ex'),130=>__('Gregarius', 'wp-slimstat-ex'),131=>__('Bloglines', 'wp-slimstat-ex'),132=>__('EveryFeed-Spider', 'wp-slimstat-ex'),133=>__('!Susie', 'wp-slimstat-ex'),134=>__('Cocoal.icio.us', 'wp-slimstat-ex'),135=>__('DomainsDB.net MetaCrawler', 'wp-slimstat-ex'),136=>__('GSiteCrawler', 'wp-slimstat-ex'),137=>__('FeedDemon', 'wp-slimstat-ex'),138=>__('Zhuaxia', 'wp-slimstat-ex'),139=>__('Akregator', 'wp-slimstat-ex'),140=>__('AppleSyndication', 'wp-slimstat-ex'),141=>__('Blog Conversation Project', 'wp-slimstat-ex'),142=>__('BottomFeeder', 'wp-slimstat-ex'),143=>__('JetBrains Omea Reader', 'wp-slimstat-ex'),144=>__('ping.blo.gs', 'wp-slimstat-ex'),145=>__('Raggle', 'wp-slimstat-ex'),146=>__('RssBandit', 'wp-slimstat-ex'),147=>__('SharpReader', 'wp-slimstat-ex'),148=>__('My Yahoo!', 'wp-slimstat-ex'),149=>__('Rojo', 'wp-slimstat-ex'),150=>__('Rmail', 'wp-slimstat-ex'),151=>__('Sage (Firefox)', 'wp-slimstat-ex'),152=>__('Daum RSS', 'wp-slimstat-ex'),153=>__('Thunderbird', 'wp-slimstat-ex'),154=>__('Windows RSS Platform', 'wp-slimstat-ex'),155=>__('FeedParser', 'wp-slimstat-ex'),156=>__('LiveJournal', 'wp-slimstat-ex'),157=>__('Vienna', 'wp-slimstat-ex'),158=>__('iTunes', 'wp-slimstat-ex'),159=>__('QuickTime', 'wp-slimstat-ex'),160=>__('RealPlayer', 'wp-slimstat-ex'),161=>__('WorldLingo', 'wp-slimstat-ex'),162=>__('xMind', 'wp-slimstat-ex'),163=>__('HP Web PrintSmart', 'wp-slimstat-ex'),164=>__('Plagger', 'wp-slimstat-ex'),165=>__('Blog Bridge', 'wp-slimstat-ex'),166=>__('Fastladder', 'wp-slimstat-ex'),167=>__('NewsLife', 'wp-slimstat-ex'),168=>__('RSS Owl', 'wp-slimstat-ex'),169=>__('YeonMo', 'wp-slimstat-ex'),170=>__('YOZMN (yozmn.com)', 'wp-slimstat-ex'),171=>__('Feed On Feeds', 'wp-slimstat-ex'),172=>__('Technorati Feed Bot', 'wp-slimstat-ex'),173=>__('CazoodleBot', 'wp-slimstat-ex'),174=>__('Snapbot (snap.com)', 'wp-slimstat-ex'),175=>__('UCLA C.S.dept Robot', 'wp-slimstat-ex'),176=>__('HTTPClientBox', 'wp-slimstat-ex'),177=>__('ONNET API Bot', 'wp-slimstat-ex'),178=>__('Wing Feed Bot', 'wp-slimstat-ex'),179=>__('Openmaru Feed Aggregator', 'wp-slimstat-ex'),180=>__('WebScouter', 'wp-slimstat-ex'), 181=>__('OpenID Servers', 'wp-slimstat-ex'), 182=>__('REBI-Shoveler', 'wp-slimstat-ex'), 183=>__('Mixsh RSSSync', 'wp-slimstat-ex'), 184=>__('FeedWave', 'wp-slimstat-ex'), 

			// Mobile Browsers 
			1001=>__('Sony PSP', 'wp-slimstat-ex'),1002=>__('WebPro', 'wp-slimstat-ex'),1003=>__('NetFront', 'wp-slimstat-ex'),1004=>__('Xiino', 'wp-slimstat-ex'),1005=>__('BlackBerry Mobile', 'wp-slimstat-ex'),1006=>__('Orange SPV Mobile', 'wp-slimstat-ex'),1007=>__('LG Mobile', 'wp-slimstat-ex'),1008=>__('Motorola Mobile', 'wp-slimstat-ex'),1009=>__('Nokia Mobile', 'wp-slimstat-ex'),1010=>__('Blazer Mobile', 'wp-slimstat-ex'),1011=>__('Siemens Mobile', 'wp-slimstat-ex'),1012=>__('SamSung Mobile', 'wp-slimstat-ex'),1013=>__('Sony/Ericsson', 'wp-slimstat-ex'),1014=>__('Dopod Mobile', 'wp-slimstat-ex'),1015=>__('O2 XDA Mobile', 'wp-slimstat-ex'),1016=>__('Doris Mobile', 'wp-slimstat-ex'),1017=>__('iPhone', 'wp-slimstat-ex'),1018=>__('Jig Mobile', 'wp-slimstat-ex'),1019=>__('KDDI Mobile', 'wp-slimstat-ex'),1020=>__('OpenWave Up.Browser', 'wp-slimstat-ex'),1021=>__('Obigo Mobile', 'wp-slimstat-ex'),1022=>__('Playstation', 'wp-slimstat-ex'),1023=>__('Pocket PC', 'wp-slimstat-ex'),1024=>__('SEMC Browser', 'wp-slimstat-ex'),1025=>__('Vodafone Mobile', 'wp-slimstat-ex'),1026=>__('AIR-EDGE', 'wp-slimstat-ex'),1027=>__('EudoraWeb', 'wp-slimstat-ex'),1028=>__('Minimo Moblie', 'wp-slimstat-ex'),1029=>__('Plucker Mobile', 'wp-slimstat-ex'),1030=>__('HP iPAQ', 'wp-slimstat-ex'),1031=>__('NEC Mobile', 'wp-slimstat-ex'),1032=>__('Nintendo Wii', 'wp-slimstat-ex'),1033=>__('Opera Mini', 'wp-slimstat-ex'),1034=>__('palmOne', 'wp-slimstat-ex'),1035=>__('Psion Mobile', 'wp-slimstat-ex'),1036=>__('Sprint Mobile', 'wp-slimstat-ex'),1037=>__('ibisBrowser Mobile', 'wp-slimstat-ex'),
			
			// Miscellaneous Browsers
			1998=>__('Miscellaneous Readers', 'wp-slimstat-ex'),1999=>__('Miscellaneous Validators', 'wp-slimstat-ex'),2000=>__('Miscellaneous Downloaders', 'wp-slimstat-ex')
		);

		$myBrowserString = (isset($id2browser[$browser_id]))?$id2browser[$browser_id]:__('xx', 'wp-slimstat-ex');
		return $myBrowserString;	
	}
	// end translateBrowserID

	function _translatePlatformID( $platform_id = '-1' ) {
		$id2platform = array( 
			0=>__('Windows 2000', 'wp-slimstat-ex'),1=>__('Windows XP', 'wp-slimstat-ex'),2=>__('Windows 2003', 'wp-slimstat-ex'),3=>__('Windows Vista', 'wp-slimstat-ex'),3=>__('Windows NT', 'wp-slimstat-ex'),5=>__('Windows 98', 'wp-slimstat-ex'),6=>__('Windows 95', 'wp-slimstat-ex'),7=>__('Windows 3.1', 'wp-slimstat-ex'),8=>__('Windows generic', 'wp-slimstat-ex'),9=>__('Mac 68k', 'wp-slimstat-ex'),10=>__('Mac OS X', 'wp-slimstat-ex'),11=>__('Mac PPC', 'wp-slimstat-ex'),12=>__('Mac', 'wp-slimstat-ex'),13=>__('OS/2', 'wp-slimstat-ex'),14=>__('Sun OS', 'wp-slimstat-ex'),15=>__('Unix Irix', 'wp-slimstat-ex'),16=>__('HP Unix', 'wp-slimstat-ex'),17=>__('Aix', 'wp-slimstat-ex'),18=>__('Dec Alpha', 'wp-slimstat-ex'),19=>__('Vax', 'wp-slimstat-ex'),20=>__('Linux', 'wp-slimstat-ex'),21=>__('Free BSD', 'wp-slimstat-ex'),22=>__('Intel Mac', 'wp-slimstat-ex'),23=>__('AmigaOS', 'wp-slimstat-ex'),24=>__('C-64', 'wp-slimstat-ex'),25=>__('Windows XP 64bit', 'wp-slimstat-ex'),			26=>__('Windows ME', 'wp-slimstat-ex'),27=>__('Windows NT 4.0', 'wp-slimstat-ex'),28=>__('RISC OS', 'wp-slimstat-ex'),29=>__('Debian GNU/Linux', 'wp-slimstat-ex'),30=>__('Mandrake Linux', 'wp-slimstat-ex'),31=>__('SuSE Linux', 'wp-slimstat-ex'),32=>__('Novell Linux', 'wp-slimstat-ex'),33=>__('Ubuntu Linux', 'wp-slimstat-ex'),34=>__('RedHat Linux', 'wp-slimstat-ex'),35=>__('Gentoo Linux', 'wp-slimstat-ex'),36=>__('Fedora Linux', 'wp-slimstat-ex'),37=>__('MEPIS Linux', 'wp-slimstat-ex'),38=>__('Knoppix Linux', 'wp-slimstat-ex'),39=>__('Slackware Linux', 'wp-slimstat-ex'),40=>__('Xandros Linux', 'wp-slimstat-ex'),41=>__('Kanotix Linux', 'wp-slimstat-ex'),42=>__('NetBSD', 'wp-slimstat-ex'),43=>__('OpenBSD', 'wp-slimstat-ex'),44=>__('Generic Unix', 'wp-slimstat-ex'),45=>__('BeOS', 'wp-slimstat-ex'),

			// Mobile OS
			71=>__('Windows CE', 'wp-slimstat-ex'),72=>__('Microsoft PocketPC', 'wp-slimstat-ex'),73=>__('Microsoft Smartphone', 'wp-slimstat-ex'),74=>__('Palm OS', 'wp-slimstat-ex'),75=>__('Qtopia', 'wp-slimstat-ex'),76=>__('Linux Zaurus', 'wp-slimstat-ex'),77=>__('Symbian OS', 'wp-slimstat-ex'),78=>__('Linux WAP', 'wp-slimstat-ex'),
		);
		$myPlatformString = (isset($id2platform[$platform_id]))?$id2platform[$platform_id]:__( 'xx', 'wp-slimstat-ex' );
		return $myPlatformString;
	}
	// end translatePlatformID

	function _translateLocaleCode( $locale = '' ) {
		//check locale type
		$locale_list = split('-', $locale);
		if ($locale_list[0] == 'c') {
			return __($locale, 'wp-slimstat-ex');
		} elseif ($locale_list[0] == 'l') {
			$c = count($locale_list);
			if ($c == 2 ) 
				return __($locale, 'wp-slimstat-ex');
			elseif ($c == 3 ) 
				return __('l-'.$locale_list[1], 'wp-slimstat-ex').'/'.__('c-'.$locale_list[2], 'wp-slimstat-ex');
			elseif ($c > 3) 
				return __('l-'.$locale_list[1], 'wp-slimstat-ex');
		}
		return $locale;
	}

	function get_title($id, $small = false) {//get module title form module id
		if (!$small) {
			$title = array( 1=>__('Summary', 'wp-slimstat-ex'), 2=>__('Recent domains', 'wp-slimstat-ex'), 3=>__('Recent search', 'wp-slimstat-ex'), 4=>__('New domains', 'wp-slimstat-ex'), 5=>__('Recent resources', 'wp-slimstat-ex'), 6=>__('Hourly hits', 'wp-slimstat-ex'), 7=>__('Daily hits', 'wp-slimstat-ex'), 8=>__('Weekly hits', 'wp-slimstat-ex'), 9=>__('Monthly hits', 'wp-slimstat-ex'), 10=>__('Top resources', 'wp-slimstat-ex'), 11=>__('Top search', 'wp-slimstat-ex'), 12=>__('Top languages', 'wp-slimstat-ex'), 13=>__('Top domains', 'wp-slimstat-ex'), 14=>__('Internally referred', 'wp-slimstat-ex'), 15=>__('Internal search', 'wp-slimstat-ex'),16=>__('Top visitors', 'wp-slimstat-ex'), 17=>__('Browser versions', 'wp-slimstat-ex'), 18=>__('Platforms', 'wp-slimstat-ex'), 19=>__('Countries', 'wp-slimstat-ex'), 20=>__('Top referers', 'wp-slimstat-ex'), 91=>__('Browsers', 'wp-slimstat-ex'), 92=>__('Recent visitors', 'wp-slimstat-ex') );
		} else {
			$title = array( 1=>__('Summary', 'wp-slimstat-ex'), 2=>__('Recent domains', 'wp-slimstat-ex'), 3=>__('Recent search', 'wp-slimstat-ex'), 4=>__('New domains', 'wp-slimstat-ex'), 5=>__('Recent resources', 'wp-slimstat-ex'), 6=>__('Hourly', 'wp-slimstat-ex'), 7=>__('Daily', 'wp-slimstat-ex'), 8=>__('Weekly', 'wp-slimstat-ex'), 9=>__('Monthly', 'wp-slimstat-ex'), 10=>__('Top resources', 'wp-slimstat-ex'), 11=>__('Top search', 'wp-slimstat-ex'), 12=>__('Languages', 'wp-slimstat-ex'), 13=>__('Top domains', 'wp-slimstat-ex'), 14=>__('Internally referred', 'wp-slimstat-ex'), 15=>__('Internal search', 'wp-slimstat-ex'), 16=>__('Top visitors', 'wp-slimstat-ex'), 17=>__('Browser versions', 'wp-slimstat-ex'), 18=>__('Platforms', 'wp-slimstat-ex'), 19=>__('Countries', 'wp-slimstat-ex'), 20=>__('Top referers', 'wp-slimstat-ex'), 91=>__('Browsers', 'wp-slimstat-ex'), 92=>__('Recent visitors', 'wp-slimstat-ex') );
		}
		if ($id < 100) {
			return $title[$id];
		} else {//pin
			$pinid = floor($id/100) - 100;
			$mo = $id - (($pinid+100)*100) - 1;
			$mos = SSFunction::pin_mod_info($pinid);
			return __($mos['modules'][$mo]['title'], 'wp-slimstat-ex');
		}
	}

	function id2module($id) {
		$names = array( 1=>'_moduleSummary', 2=>'_moduleRecentReferers', 3=>'_moduleRecentSearchStrings', 4=>'_moduleNewDomains', 5=>'_moduleRecentResources', 6=>'_moduleLast24Hours', 7=>'_moduleDailyHits', 8=>'_moduleWeeklyHits', 9=>'_moduleMonthlyHits', 10=>'_moduleTopResources', 11=>'_moduleTopSearchStrings', 12=>'_moduleTopLanguages', 13=>'_moduleTopDomains', 14=>'_moduleInternallyReferred', 15=>'_moduleTopInternalSearchStrings', 16=>'_moduleTopRemoteAddresses', 17=>'_moduleTopBrowsers', 18=>'_moduleTopPlatforms', 19=>'_moduleTopCountries', 20=>'_moduleTopReferers', 91=>'_moduleTopBrowsersOnly', 92=>'_moduleRecentRemoteip' );
		return ((isset($names[$id]))?$names[$id]:false);
	}

	function fellow_links($id) {
		global $SlimCfg;
		$links = '';
		if (!$SlimCfg->option['use_ajax'])
			return $links;
		if ($SlimCfg->get['pn'] == 3 || $SlimCfg->get['pn'] == 2) {
			switch($id) {
				case '1': $links = array(6,7,8,9); break;
				case '3': $links = array(11,15); break;
				case '7': $links = array(6,8,9); break;
				case '10': $links = array(5,14); break;
				case '16': $links = array(92); break;
				case '19': $links = array(12); break;
				case '20': $links = array(2,13,4); break;
				case '91': $links = array(17,18); break;
				default: break;
			} 
		} else if ($SlimCfg->get['pn'] == 1) {
			switch($id) {
				case '1': $links = array(6,7,8,9); break;
				case '2': $links = array(4,13); break;
				case '3': $links = array(11,15); break;
				case '4': $links = array(2,13); break;
				case '5': $links = array(10,14,20); break;
				case '92': $links = array(16); break;
				default: break;
			} 
		}
		$links = apply_filters('slimstat_fellow_links', $links, $id);
		return $links;
	}

	function get_nav() {
		global $SlimCfg;
		$panel = $SlimCfg->get['pn'];
		$modules = array();
		if (empty($panel) || $panel < 100) {
			switch($SlimCfg->get['pn']) {
				case '1': $modules = array(1,2,3,92,5); break;
				case '3': $modules = array(1,16,91,19,20,3,10); break;
				case '2': $modules = array(1,16,91,19,20,3,10); break;
				default: break;
			}
		}
		if (empty($modules)) return '';
		$r = "\n\t\t".'<p class="module_nav">'.__('Modules', 'wp-slimstat-ex').' : '."\n";
		foreach($modules as $m) {
			$r .= "\t\t\t".'[ <a href="#module_'.$m.'">'.SSFunction::get_title($m).'</a> ] '."\n";
		} 
		$r .= "\t\t".'</p>'."\n";
		return $r;
	}

	function _getTableSize($table='common') {
		global $wpdb, $SlimCfg;
		switch($table) {
			case 'feed':
				$_table = $SlimCfg->table_feed;
				break;
			case 'country':
				$_table = $SlimCfg->table_countries;
				break;
			case 'dt':
				$_table = $SlimCfg->table_dt;
				break;
			case 'common': default:
				$_table = $SlimCfg->table_stats;
				break;
		}
		$query = "SHOW TABLE STATUS LIKE '{$_table}' ";
		if ( $table_details = $wpdb->get_row($query, ARRAY_A, 0) ) {
			$table_size = ( $table_details["Data_length"] / 1024 ) + ( $table_details["Index_length"] / 1024 );
			return number_format($table_size, 0, ".", ",")." Kbyte";
		}
		return 0;
	}
	// end getTableSize

	function filter_switch($interval = true) {
		global $SlimCfg;
		// Retrieve data from url
		$_filter = "(1 = 1)";
		if ( $interval && isset($SlimCfg->get['fd']) ) {
			$_filter = " ts.dt >= ".$SlimCfg->get['fd'][0]." AND ts.dt <= ".$SlimCfg->get['fd'][1]." ";
		}
		if ( isset($SlimCfg->get['fi']) ) {
			if ( $_filter == "(1 = 1)" ) $_filter = "";
			else $_filter .= " AND";
			$get_fi = $SlimCfg->my_esc($SlimCfg->get['fi']);
			$_filter_str = ( $SlimCfg->get['ft'] == 0 ) ? " = '{$get_fi}'" : " LIKE '%{$get_fi}%'";
			switch ( $SlimCfg->get['ff'] ) {
				case 0:
					$_filter .= " ts.domain $_filter_str";
					break;
				case 1:
					$_filter .= " ts.searchterms $_filter_str";
					break;
				case 2:
					$_filter_str = SSFunction::_resourcefilter2id($_filter_str, $get_fi);
					$_filter .= " ts.resource $_filter_str";
					break;
				case 3: 
					$_filter_str = SSFunction::convert_ip_filter_string($SlimCfg->get['fi'], $SlimCfg->get['ft']);
					$_filter .= " ts.remote_ip $_filter_str";
					break;
				case 4:
					$_filter .= " ts.browser $_filter_str";
					break;
				case 5:
					$_filter .= " ts.platform $_filter_str";
					break;
				case 6:
					$_filter .= " ts.country = '".preg_replace('|^c-|', '', $SlimCfg->my_esc($SlimCfg->get['fi']))."' ";
					break;
				case 7:
					$_filter .= " ts.language = '".preg_replace('|^l-|', '', $SlimCfg->my_esc($SlimCfg->get['fi']))."' ";
					break;
				case 99:// custom column for Pins
					$_filter .= " %%COLUMN%% $_filter_str";
				break;
				default:
					break;
			}
		}
		return $_filter;
	}
	// end filterSwitch

	function convert_ip_filter_string($ip, $ft=0) {
		if ($ft == 0) {
			$str = is_long($ip) ? $ip : sprintf('%u', ip2long($ip));
			return "= {$str}";
		}
		if (is_long($ip))
			$ip = long2ip($ip);
		$ip_ = trim($ip, '.');
		$_ip = $ip_;
		$ip_arr = explode('.', $ip_);
		$rest = (4 - count($ip_arr));
		for($i=0; $i<$rest;$i++) {
			$ip_ .= '.0';
			$_ip .= '.255';
		}
		$ip_ = sprintf('%u', ip2long($ip_));
		$_ip = sprintf('%u', ip2long($_ip));
		return ">= {$ip_} AND ts.remote_ip <= {$_ip}";
	}
	
	function getFormattedValue( $type = 'integer', $value = '', $filter_clause = '' ) {
		global $wpdb, $SlimCfg, $cache_countall;
		switch ( $type ) {
			case "percentage":
				if (!isset($cache_countall))
					$cache_countall = array();
				if (isset($cache_countall[$SlimCfg->current_table][$filter_caluse])) {
					$count = $cache_countall[$SlimCfg->current_table][$filter_caluse];
				} else {
					$count = $wpdb->get_var( "SELECT COUNT(*) FROM $SlimCfg->current_table ts WHERE $filter_clause", 0 , 0 );
					$cache_countall[$SlimCfg->current_table][$filter_caluse] = $count;
				}
				if ( $count != null && $count > 0) 
					$value = round( ($value / $count ) * 100, 2);
			break;
			case "date":
				$value = $SlimCfg->sstime($value);
				$value = ($value >= $SlimCfg->_mktime(time())) ? 'Today, '.$SlimCfg->_date(__("H:i", 'wp-slimstat-ex'), $value) : $SlimCfg->_date(__("d M, H:i", 'wp-slimstat-ex'), $value);
			break;
			case "integer": default:
				return $value;
			break;
		}
		return $value;
	}
	// end getFormattedValue
	
	function getDataTable( $_query, $_titles, $_resource = false, $_desc,	$_value, $_filter = '' ) {
		global $wpdb, $SlimCfg;
		$_table  = "";
		if ( !(is_array($_query) || is_array($_titles) || is_array($_desc) || is_array($_value)) )
			return $_table;
		$countGoodQueries = 0;
		$_table .= "\n\t".'<table border="0" cellspacing="0" cellpadding="0">'."\n";
		$_table .= "\t".'<thead><tr>';
		$_table .= "\t\t".'<th class="first"'.($_resource?'colspan="2"':'').'>'.$_titles[0].'</th>';
		$_table .= '<th class="second">'.$_titles[1].'</th>';
		if ( count( $_titles ) > 2 ) {
			$_table .= '<th class="third">'.$_titles[2].'</th>';
		}
		$_table .= "\t".'</tr></thead>'."\n";
		$temp_strings = array('encode_prefix','remote_ip','long_locale','ip2host','p_id2string','b_id2string','resource2title','flag','basename');
		foreach ( $_query as $qkey => $query ) {
//			print $query[0]."<br />\n";
//			timer_start();
//			$ig_bots = " WHERE ts.browser NOT IN (".join(',', SSFunction::get_bot_array(true)).") AND ";
//			$query[0] = preg_replace('|\s+WHERE\s+|sU', $ig_bots, $query[0]);
			$results = $wpdb->get_results( $query[0], ARRAY_A );

			// Show all(common, feed) results on start page.
			$_show_all = $SlimCfg->get['pn'] == 1 || $SlimCfg->get['slim_table'] == 'all';
			if ($_show_all) {// result from both table common, feed.
				$query_feed = preg_replace('|\s+'.$SlimCfg->table_stats.'\s+ts\s+|isU', ' '.$SlimCfg->table_feed.' ts ', $query[0]);
				$results_feed = $wpdb->get_results($query_feed, ARRAY_A);
				if (!$results_feed) {
					if (!$results) continue;
					$results_feed = array();
				}
				foreach($results_feed as $rfeed) {
					$rfeed['__feed__'] = 1;
					$results[] = $rfeed;
				}
				if (!empty($results_feed) && preg_match('|\s+ORDER\s+BY\s+([^\s]+?)\s+([^\s]+?)\s+LIMIT|isU', $query[0], $m)) {
					$sortby = str_replace('ts.', '', $m[1]);
					$sortorder = strtolower($m[2]);
					__masort($results, $sortby);
					$results = 'desc' == $sortorder ? array_reverse($results) : $results;
					array_splice($results, $SlimCfg->option['limitrows']);
				}
			}

//			timer_stop(1);echo "<br />\n";
			if (!$results) continue;
			$countGoodQueries++;
			foreach( $results as $r ) {
				if ( $r[$query[1][0]] == 0 ) continue;
				if (isset($r['resource'])) $r['resource'] = SSFunction::_id2resource($r['resource']);
				$rowstyle = ($rowstyle == ' class="tbrow"') ? ' class="tbrow-alt"' : ' class="tbrow"';
				$_table .= "\t".'<tr'.$rowstyle.'>'."\n";
				$resource2title = "__none__";
				if ( $_resource ) {
					$_table .= "\t\t".'<td class="linkresource">';
					if ( '' != $r['resource'] && false !== $r['resource']) {
						$resource2title = SSFunction::_guessPostTitle($r['resource']);
						$_table .= '<a href="'.wp_specialchars($r['resource'],1).'" ';
						$_table .= 'title="'.__('Visit this resource', 'wp-slimstat-ex').': '.strip_tags($resource2title).'">';
						$_table .= '<img src="'.$SlimCfg->pluginURL.'/css/external.gif" alt="external" /></a>';
					} else $_table .= 'x';
					$_table .= '</td>'."\n";
				}
				$_table .= "\t\t".'<td class="first">';
				foreach( $_desc[$qkey] as $rkey => $format ) {
					$format = stripslashes($format);
					$_string = str_replace('%%short%%', wp_specialchars($SlimCfg->trimString( $r[$rkey] ),1), $format);
					$_string = str_replace('%%medium%%', wp_specialchars($SlimCfg->trimString( $r[$rkey], 60 ),1), $_string);
					$_string = str_replace('%%long%%', wp_specialchars($r[$rkey],1), $_string);
					$_string = str_replace('%%encode%%', urlencode(wp_specialchars($r[$rkey],1)), $_string);

					if ($_show_all && isset($r['__feed__']) && preg_match('|<img\s+src="[^"]*"\s+alt="Filter"\s+class="icons"|i', $_string, $match)) {
						$_string = str_replace('class="icons"', 'class="icons feed"', $_string);
						if ($SlimCfg->option['use_ajax']) {
							$_string = str_replace("SlimStat.panel('3',", "SlimStat.panel('2',", $_string);
						} else {
							$_string = str_replace("?page=wp-slimstat-ex&amp;panel=3&", "?page=wp-slimstat-ex&amp;panel=2&", $_string);
						}
					}

					for($i=0; $i < count($temp_strings); $i++) {
						if (strpos($_string, '%%') === false) break;
						$temp = '%%'.$temp_strings[$i].'%%';
						if (strpos($_string, $temp) === false) continue;
						switch($temp){
							case '%%encode_prefix%%':
							if ($r[$rkey] != "" && !preg_match('|^https?://|i', $r[$rkey])) $r[$rkey] = 'http://'.$r[$rkey];
							$_string = str_replace($temp, (($r[$rkey]=="")?"":' href="'.wp_specialchars($r[$rkey],1).'"'), $_string);
							break;
							case '%%remote_ip%%':
							$_string = str_replace($temp, SSFunction::_whoisLink($r[$rkey]), $_string);
							break;
							case '%%long_locale%%':
							$_string = str_replace($temp, SSFunction::_translateLocaleCode($r[$rkey]), $_string);
							break;
							case '%%ip2host%%':
							$_string = str_replace($temp, $SlimCfg->trimString(gethostbyaddr($r[$rkey])), $_string);
							break;
							case '%%p_id2string%%':
							$_string = str_replace($temp, SSFunction::_translatePlatformID($r[$rkey]), $_string);
							break;
							case '%%b_id2string%%':
							$_string = str_replace($temp, SSFunction::_translateBrowserID($r[$rkey]), $_string);
							break;
							case '%%resource2title%%':
							$r[$rkey] = ($resource2title == "__none__")?SSFunction::_guessPostTitle( $r[$rkey] ):$resource2title;
							$_string = str_replace($temp, $r[$rkey], $_string);
							break;
							case '%%flag%%':
							$_string = str_replace($temp, SSFunction::get_flag($r[$rkey]), $_string);
							break;
							case '%%basename%%':
							$_string = str_replace($temp, basename($r[$rkey]), $_string);
							break;
						}
					}
					$_table .= $_string;
				}
				$_table .= '</td>'."\n";
//			timer_start();
				$_table .= "\t\t".'<td class="second">'.SSFunction::getFormattedValue($_value[$qkey][0], $r[$query[1][0]], $_filter).'</td>'."\n";
				if ( count( $_titles ) > 2 ) {
					$_table .= "\t\t".'<td class="third">'.SSFunction::getFormattedValue($_value[$qkey][1], $r[$query[1][1]], $_filter).'</td>'."\n";
				}
//			timer_stop(1);echo "<br />\n";
				$_table .= "\t".'</tr>'."\n";
			}
		}
		$_table .= "\t".'</table>'."\n";
		if ( $countGoodQueries == 0 ) return "\n".'<div class="noresults-msg">&nbsp;&nbsp;'.__('No results found', 'wp-slimstat-ex').'</div>'."\n";
		return $_table;
	}
	// end getDataTable

	function _id2resource($id) {
		global $wpdb, $SlimCfg;
		$row = $wpdb->get_row("SELECT tr.rs_string FROM {$SlimCfg->table_resource} tr WHERE tr.id='{$id}' LIMIT 1");
		if ($row)
			return $row->rs_string;
		return "";
	}

	function _resource2id($resource) {
		global $wpdb, $SlimCfg;
		$resource = $wpdb->escape($resource);
		$row = $wpdb->get_row("SELECT tr.id FROM {$SlimCfg->table_resource} tr WHERE tr.rs_md5=MD5('{$resource}') LIMIT 1");
		if ($row)
			return $row->id;
		return false;
	}

	function _resourcefilter2id($resourcefilter, $get_fi='') {
		global $wpdb, $SlimCfg;
		if (strpos($resourcefilter, ' LIKE \'%') === false) {
			$query_where = "tr.rs_md5 = MD5('{$get_fi}')";
		} else {
			$query_where = "tr.rs_string {$resourcefilter}";
		}
		$resources = $wpdb->get_col("SELECT tr.id FROM {$SlimCfg->table_resource} tr WHERE {$query_where} ");
		if ($resources) {
			return 'IN ('.implode(',', $resources).')';
		}
		return 'IN (-2)';
	}

	function _getFilterForm() {
		global $SlimCfg;	
		$use_ajax = $SlimCfg->option['use_ajax'];

		$location = get_option('siteurl').'/wp-admin/index.php?page=wp-slimstat-ex';	 
		$_form = "\t".'<br />'."\n";
		$_form .= "\t".'<form method="get" action="'.($use_ajax?$SlimCfg->ajaxReq:$location).'" id="slimstat_filter"';
		$_form .= ($use_ajax ? ' onsubmit="SlimStat.filter(); return false;"' : "").'> '."\n";
		$_form .= $use_ajax?"":"\t\t".'<input type="hidden" name="page" value="wp-slimstat-ex" />'."\n";
		$_form .= "\t\t".'<input type="hidden" name="panel" id="panel" value="'.$SlimCfg->get['pn'].'" />'."\n";
		$_form .= "\t\t".'<input type="submit" id="submit_filter" value="'.__('Filter', 'wp-slimstat-ex').'" />&nbsp;&nbsp;'."\n";
		$_form .= "\t\t".'<input size="28" type="text" id="fi" name="fi" value="'.(($SlimCfg->get['ff'] < 4)?$SlimCfg->get['fi']:'').'" /> '."\n";
		$_form .= "\t\t".'&nbsp;<select name="ff" id="ff">'."\n";
		$_form .= "\t\t".'<option value="0"'.(($SlimCfg->get['ff']==0)?' selected="selected"':'').'>'.__('Domain', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'<option value="1"'.(($SlimCfg->get['ff']==1)?' selected="selected"':'').'>'.__('Search string', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'<option value="2"'.(($SlimCfg->get['ff']==2)?' selected="selected"':'').'>'.__('Resource', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'<option value="3"'.(($SlimCfg->get['ff']==3)?' selected="selected"':'').'>'.__('Remote IP', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'</select>'."\n";
		$_form .= "\t\t".'&nbsp;<select name="ft" id="ft">'."\n";
		$_form .= "\t\t".'<option value="0"'.(($SlimCfg->get['ft']==0)?' selected="selected"':'').'>'.__('Exact', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'<option value="1"'.(($SlimCfg->get['ft']==1)?' selected="selected"':'').'>'.__('Substring', 'wp-slimstat-ex').'</option>'."\n";
		$_form .= "\t\t".'</select>'."\n";
		if (isset($SlimCfg->get['fi'])) {
			if ($use_ajax) 
				$_form .= ' [ <a href="#" onclick="SlimStat.panel(\''.$SlimCfg->get['pn'].'\', \''.$SlimCfg->get['fd_encode'].'\'); return false;"';
			else
				$_form .= ' [ <a href="?page=wp-slimstat-ex&amp;panel='.$SlimCfg->get['pn'].$SlimCfg->get['fd_encode'].'"';
			$_form .= ' id="reset-filters" title="'.__('Reset filters', 'wp-slimstat-ex').'">'.__('Reset filters', 'wp-slimstat-ex').'</a> ]'."\n";
		}
		$_form .= (isset($SlimCfg->get['fd']))?'<input type="hidden" name="fd" id="fd" value="'.$SlimCfg->get['fi_encode'].'" />'."\n":'';
		$_form .= "\t".'</form>'."\n";
		if ( isset($SlimCfg->get['ff']) && $SlimCfg->get['ff'] > 3 ) {
			switch($SlimCfg->get['ff']) {
				case 4:
					$filter_type = __('Browser', 'wp-slimstat-ex');
					$filter_string = SSFunction::_translateBrowserID($SlimCfg->get['fi']);
				break;
				case 5:
					$filter_type = __('Platform', 'wp-slimstat-ex');
					$filter_string = SSFunction::_translatePlatformID($SlimCfg->get['fi']);
				break;
				case 6:
					$filter_type = __('Country', 'wp-slimstat-ex');
					$filter_string = __($SlimCfg->get['fi'], 'wp-slimstat-ex');
				break;
				case 7:
					$filter_type = __('Language', 'wp-slimstat-ex');
					$filter_string = __($SlimCfg->get['fi'], 'wp-slimstat-ex');
				break;
				default: //for debug
					$filter_type = __('Unkown', 'wp-slimstat-ex');
					$filter_string = $SlimCfg->get['fi'];
				break;
			}
			$_form .= "\t".'<br /><span class="filter_string">&nbsp; '.$filter_type.' :: '.$filter_string.' &nbsp;</span><br />';
		}
		if ( isset($SlimCfg->get['fd']) ) {
			$dt_start = date( __('d/m/Y H:i', 'wp-slimstat-ex'), $SlimCfg->sstime($SlimCfg->get['fd'][0]) );
			$dt_end = date( __('d/m/Y H:i', 'wp-slimstat-ex'), $SlimCfg->sstime($SlimCfg->get['fd'][1]) );
			$_form .= "\t".'<br /><span class="filter_string">';
			$_form .= __('Time interval', 'wp-slimstat-ex').': '.$dt_start.' - '.$dt_end.'</span>';
			if ($use_ajax) {
				$_form .= '[ <a href="#" onclick="SlimStat.panel(\''.$SlimCfg->get['pn'].'\', \''.$SlimCfg->get['fi_encode'].'\'); return false;"';
			} else {
				$_form .= '[ <a href="?page=wp-slimstat-ex&amp;panel='.$SlimCfg->get['pn'].$SlimCfg->get['fi_encode'].'"';
			}
			$_form .= ' id="reset-interval" title="'.__('Reset interval', 'wp-slimstat-ex').'">'.__('Reset interval', 'wp-slimstat-ex').'</a> ] <br />'."\n";
		}
		return $_form;
	}
	// end getFilterForm
	
	function getModule($id, $_titles, $_query, $_desc, $_value, $filter_clause='', $is_wide=false, $_resource=false, $links=false) {
		global $SlimCfg;
		$is_ajax = isset($_GET['ajt']);
		$filter = $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode'];
		$module = '';
		if (!$is_ajax){
			$other_links = '';
			if ($links && $SlimCfg->option['use_ajax'])
				$other_links = SSFunction::other_link_selector(SSFunction::fellow_links($id), $id);
			else 
				$other_links = SSFunction::get_title($id, true);
			$module .= "\n\t".'<div class="module'.($is_wide?' wide':'').'" id="module_'.$id.'">'."\n\t\t";
			$module .= '<h3><a href="#" class="backtotop"><img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="top" /></a>';
			$module .= $SlimCfg->option['use_ajax'] ? '<span class="reload" onmouseup="SlimStat.reloadmodule(\''.$id.'\', \''.$filter.'\', \''.$SlimCfg->get['pn'].'\'); return false;"><img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="reload" /></span>' : '';
			$module .= $other_links.'</h3>'."\n\t\t";
			$module .= '<div><div id="twraper_'.$id.'">';
		}
		$module .= SSFunction::getDataTable($_query,$_titles,$_resource,$_desc,$_value,$filter_clause);
		$module .= (!$is_ajax)?'</div></div>'."\n\t".'</div>':'';
		return $module;
	}
	// end getModule
	
	function get_module_custom( $id, $content, $width = '', $links = '', $wh=array() ) {
		global $SlimCfg;
		$is_ajax = isset($_GET['ajt']);
		$filter = $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode'];
		switch ($width) {
			case 'wide': $class = ' wide'; break;
			case 'full': $class = ' full'; break;
			default: $class = ''; break;
		}
		$style = $_style = '';
		if (!empty($wh)) {
			$style = ' style="';
			// height is always px.
			$style .= isset($wh['height']) ? "height:{$wh['height']};":'';
			if (isset($wh['width'])) {
				if (strpos($wh['width'], '%') === false)
					$wh['width'] = $wh['width'].'px';
				$style .= "width:{$wh['width']};";
			}
			$style .= '"';
			if (isset($wh['height'])) {
				$_style = ' style = "height:'.($wh['height']-36).'px;"';
			}
		}

		$output = '';
		if (!$is_ajax) {
			$other_links = '';
			if ($links && $SlimCfg->option['use_ajax'])
				$other_links = SSFunction::other_link_selector(SSFunction::fellow_links($id), $id);
			else 
				$other_links = SSFunction::get_title($id, true);
			$output .= "\n\t".'<div class="module'.$class.'" id="module_'.$id.'"'.$style.'>'."\n";
			$output .= "\t\t".'<h3><a href="#" class="backtotop">';
			$output .= '<img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="top" /></a>';
			$output .= $SlimCfg->option['use_ajax'] ? '<span class="reload" onmouseup="SlimStat.reloadmodule(\''.$id.'\', \''.$filter.'\', \''.$SlimCfg->get['pn'].'\'); return false;"><img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="reload" /></span>' : '';
//			$output .= SSFunction::module_link($id).$other_links.'</h3>'."\n";
			$output .= $other_links.'</h3>'."\n";
			$output .= "\t\t".'<div'.$_style.'><div id="twraper_'.$id.'">'."\n";
		}
		$output .= $content;
		if (!$is_ajax) {
			$output .= "\t\t</div></div>\n";
			$output .= "\t</div>\n";
		}
		return $output;
	}
	//end get_module_custom

	function get_flag($country='') {
		global $SlimCfg;
		//if is remote ip address
		if (strpos($country, '.') !== false) 
			$country = 'c-'.strtolower(SSTrack::_determineCountry($country));
		if (empty($country) || $country == 'c-') 
			$country = 'c-unknown';
		return '<img src="'.$SlimCfg->pluginURL.'/css/flags/'.$country.'.png" alt="'.$country.'" class="icons" />';
	}

	// IP-Lookup is powered by http://ip-lookup.net/
	function _whoisLink($ip) {
		global $SlimCfg;
		$output = '';
		if ($SlimCfg->option['whois']) {
			$link = $SlimCfg->option['whois_db'] == 'iplookup' ? 'http://ip-lookup.net/?'.$ip : 'http://private.dnsstuff.com/tools/ipall.ch?ip='.$ip.'#map';
			$w_h = $SlimCfg->option['whois_db'] == 'iplookup' ? 'width=550,height=600,scrollbars=yes' : 'width=550,height=800,scrollbars=yes';
			$output .= '<a href="'.$link.'" title="Who is?" onclick="window.open(this.href, \'whois\', \''.$w_h.'\'); return false;">';
		}
		$output .= ($SlimCfg->option['iptohost'])?$SlimCfg->trimString(gethostbyaddr($ip)):$ip;
		$output .= ($SlimCfg->option['whois'])?'</a>':'';
		return $output;
	}

	function module_link($module_id, $string='', $current='') {
		global $SlimCfg;
		$filter = $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode'];
		$class = ('' == $string)?"-title":"";	
		$current = ('' == $current)?$module_id:$current;	
		$string = ('' == $string)?SSFunction::get_title($module_id):$string;
		$result = '<span id="ml'.$current.'_'.$module_id.'" class="mod-link'.$class.'"';
		$result .= $SlimCfg->option['use_ajax'] ? ' onmouseup="SlimStat.module(\''.$module_id.'\', \''.$filter.'\', \''.$current.'\', \''.$SlimCfg->get['pn'].'\'); return false;"' : "";
		$result .= '>'.$string.'</span>';
		return $result;
	}

	function other_link($links, $current) {// deprecated
		global $SlimCfg;
		$result = '';
		if (!is_array($links) || empty($links))
			return $result;
		$result .= ' - [ ';
		$i=0;
		$c = count($links) - 1;
		foreach($links as $link) {
			if ($link == 6 && $SlimCfg->get['fd'] && ($SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0]) > 86400) {
				$i++; continue;
			}
			$result .= SSFunction::module_link($link, SSFunction::get_title($link, true), $current);
			$result .= ($i != $c) ? ', ':'';
			$i++;
		}
		$result .= ' ] ';
		return $result;
	}

	function other_link_selector($links, $current) {
		global $SlimCfg;
		$filter = $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode'];
		$result = '';
		$links = (array)$links;
		$result .= '<select id="mo_selector_'.$current.'" name="mo_selector_'.$current.'" onchange="SlimStat.module(this.options[this.selectedIndex].value, \''.$filter.'\', \''.$current.'\', \''.$SlimCfg->get['pn'].'\'); return false;">';
		$result .= '<option value="'.$current.'">'.SSFunction::get_title($current, true).'</option>';
		foreach($links as $link) {
			if (!$link || $link == 6 && $SlimCfg->get['fd'] && ($SlimCfg->get['fd'][1] - $SlimCfg->get['fd'][0]) > 86400) {
				continue;
			}
			$result .= '<option value="'.$link.'">'.SSFunction::get_title($link, true).'</option>';
		}
		$result .= '</select>';
		return $result;
	}

	function pin_mod_info($id) {
		global $wpdb, $SlimCfg;
		$row = $wpdb->get_row("SELECT modules, name, type, active FROM $SlimCfg->table_pins WHERE id = '$id' LIMIT 1", ARRAY_A);
		if ($row) {
			$row['modules'] = unserialize($row['modules']);
			return $row;
		}
		return false;
	}
	//end pin_mod_info

	function filterBtn($filter='', $panel='') {
		global $SlimCfg;
		$use_ajax = $SlimCfg->option['use_ajax'];
		$result = "";
		if ($panel == '')
			$panel = $SlimCfg->get['pn'];
		if ($panel == 1)
			$panel = 3;// show filtered results of common table by default.
		if ($panel == 2) $class = ' feed'; else if ($panel > 100) $class = ' self'; else $class = '';
		$thishref = ($use_ajax)?"#":"?page=wp-slimstat-ex&amp;panel=".$panel.$filter;
		$result .= '<a href="'.$thishref.'" title="Filter This" class="filter-link" ';
		$result .= (($use_ajax)?'onclick="SlimStat.panel(\''.$panel.'\', \''.$filter.'\'); return false;"':'').'>';
		$result .= '<img src="'.$SlimCfg->pluginURL.'/css/blank.gif" alt="Filter" class="icons'.$class.'" /></a>';
		return $result;
	}

	function get_filterBtns($filter, $custom_module=false) {
		global $SlimCfg;
		$btn = '';
		$panel = $SlimCfg->get['pn'];
		if ($panel == 1) {
			if ($custom_module)
				return SSFunction::filterBtn($filter, 3)." ".SSFunction::filterBtn($filter, 2);
			else 
				return SSFunction::filterBtn($filter, 3);
		}
		$slim_table = $SlimCfg->get['slim_table'] ? $SlimCfg->get['slim_table'] : ($panel > 100 ? 'common' : null);
		if ($slim_table && ($slim_table == 'common' || $slim_table == 'feed')) {
			$btn .= SSFunction::filterBtn($filter, ($_GET['slim_table'] == 'feed' ? 2 : 3));
		}
		$btn .= SSFunction::filterBtn($filter);
		return $btn;
	}

	function get_hvu( $_table, $interval="", $filters = "") {
		global $wpdb;
		if (is_array($_table)) {
			$hvu1 = SSFunction::get_hvu($_table[0], $interval, $filters);
			$hvu2 = SSFunction::get_hvu($_table[1], $interval, $filters);
			$hvu = array();
			foreach($hvu1 as $key=>$val)
				$hvu[$key] = (int)$hvu1[$key] + (int)$hvu2[$key];
			return $hvu;
		}
		$query = "SELECT COUNT(ts.id) AS hits, COUNT(DISTINCT ts.visit) AS visits, COUNT(DISTINCT ts.remote_ip) AS uniques";
		$query .= " FROM $_table ts WHERE ";
		$query .= (!empty($interval))?$interval:'(1 = 1)';
		$query .= (!empty($filters) && $filters != '(1 = 1)')?" AND ".$filters : "";
		if ( $result = $wpdb->get_row( $query, ARRAY_A ) ) {
			return $result;
		}
		return array( "hits" => 0, "visits" => 0, "uniques" => 0 );
	}
	//end get_hvu

	function deleted_hvu($type) {
		global $wpdb, $SlimCfg;
		$query = "SELECT hits, visits, uniques FROM $SlimCfg->table_dt ";
		switch ($type) {
			case 'common':
				$query .= " WHERE type = 11";
			break;
			case 'feed':
				$query .= " WHERE type = 12";
			break;
			case 'all': default:
				$query .= " WHERE type = 13";
			break;
		}
		$hits = 0;
		$visits = 0;
		$uniques = 0;
		if ($rs = $wpdb->get_results($query)) {
			for ($i =0; $i < count($rs); $i++) {
				$hits += (int)$rs[$i]->hits;
				$visits += (int)$rs[$i]->visits;
				$uniques += (int)$rs[$i]->uniques;
			}
		}
		return array( "hits" => $hits, "visits" => $visits, "uniques" => $uniques );
	}
	//end deleted_hvu

	function ins_dt( $_dt_start, $_dt_end = 0, $type = 0, $filters='' ) {
		global $SlimCfg;
		switch ($type) {
			case 2: case 12:
				$_table = $SlimCfg->table_feed;
			break;
			case 1: case 11: 
				$_table = $SlimCfg->table_stats;
			break;
			case 3: case 13:
				$_table = array($SlimCfg->table_stats,$SlimCfg->table_feed);
			break;
			default: break;
		}
		if ( $_dt_end == 0 || $_dt_end >= time()) {
			$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start , $filters );
			return $hvu;
		} else if ( !empty($filters) && $filters != '(1 = 1)' ) {
			$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start ." AND ts.dt<=".(int)$_dt_end , $filters );
			return $hvu;
		} else {
			global $wpdb;
			$query = "SELECT hits, visits, uniques ";
			$query .= " FROM $SlimCfg->table_dt ";
			$query .= " WHERE dt_start=".(int)$_dt_start ." AND dt_end=".(int)$_dt_end ." ";
			$query .= " AND type = '$type' LIMIT 1";
			if ( $hvu = $wpdb->get_row( $query, ARRAY_A ) ) {
					return $hvu;
			} else {
				if (is_array($_table)) {
					$hvu1 = SSFunction::ins_dt($_dt_start, $_dt_end, ($type-2), $filters);
					$hvu2 = SSFunction::ins_dt($_dt_start, $_dt_end, ($type-1), $filters);
					$hvu = array();
					foreach($hvu1 as $key=>$val)
						$hvu[$key] = (int)$hvu1[$key] + (int)$hvu2[$key];
				} else {
					$hvu = SSFunction::get_hvu( $_table, "ts.dt>=".(int)$_dt_start ." AND ts.dt<=".(int)$_dt_end  );
				}
				$query = "INSERT INTO $SlimCfg->table_dt ";
				$query .= " ( dt_start, dt_end, hits, visits, uniques, type ) VALUES ( ";
				$query .= (int)$_dt_start .", ".(int)$_dt_end .", ";
				$query .= "".(int)$hvu['hits'].", ".(int)$hvu['visits'].", ".(int)$hvu['uniques'].", ".$type." )";
				$wpdb->query( $query );
				return $hvu;
			}
		}
	}
	//end ins_dt

	function get_firsthit($type = 'common') {
		global $wpdb, $SlimCfg;
		switch ($type) {
			case 'common':
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_stats} ts ";
				$first_hit = (int)$wpdb->get_var( $query, 0, 0 );
			break;
			case 'feed':
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_feed} ts ";
				$first_hit = (int)$wpdb->get_var( $query, 0, 0 );
			break;
			case 'all': default:
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_stats} ts ";
				$time = (int)$wpdb->get_var($query);
				$first_hit_n = ($time) ? $time : time();
				$query = "SELECT MIN(ts.dt) FROM {$SlimCfg->table_feed} ts ";
				$time = (int)$wpdb->get_var($query);
				$first_hit_f = ($time) ? $time : time();
				$first_hit = min($first_hit_n, $first_hit_f);
			break;
		}
		return ( $first_hit ? $first_hit : time() );
	}
	//end get_firsthit

	function get_real_firsthit($type) {
		global $wpdb, $SlimCfg;
		$query = "SELECT MIN(dt_start) AS dt_start FROM {$SlimCfg->table_dt}";
		switch ($type) {
			case 'common':
				$query .= " WHERE type = 11";
			break;
			case 'feed':
				$query .= " WHERE type = 12";
			break;
			case 'all': default:
				$query .= " WHERE type > 10";
			break;
		}
		$query .= " LIMIT 1";
		if ($row = $wpdb->get_row($query)) {
			return (int)$row->dt_start;
		}
		return false;
	}
	//end get_real_firsthit

	function calc_hvu($dt_start, $dt_end, $type, $filters='') {
		switch($type) {
			case 'common':
				$hvu = SSFunction::ins_dt( $dt_start, $dt_end, 1, $filters );
				return $hvu;
			break;
			case 'feed':
				$hvu = SSFunction::ins_dt( $dt_start, $dt_end, 2, $filters );
				return $hvu;
			break;
			case 'all':
				$hvu = SSFunction::ins_dt( $dt_start, $dt_end, 3, $filters );
				return $hvu;
/*				$hvun = SSFunction::ins_dt( $dt_start, $dt_end, 1, $filters );
				$hvuf = SSFunction::ins_dt( $dt_start, $dt_end, 2, $filters );
				$hits = $hvun['hits'] + $hvuf['hits'];
				$visits = $hvun['visits'] + $hvuf['visits'];
				$uniques = $hvun['uniques'] + $hvuf['uniques'];*/
			break;
		}
		return array("hits"=>0, "visits"=>0, "uniques"=>0);
	}
	//end calc_hvu

	function get_bot_array($all = false) {
		global $SlimCfg;
		$bots = array('34,2000');
		if ($all || $SlimCfg->exclude['ig_bots'])
			$bots = array_merge($bots, $SlimCfg->bot_array['bots']);
		if ($all || $SlimCfg->exclude['ig_feeds'])
			$bots = array_merge($bots, $SlimCfg->bot_array['feeds']);
		if ($all || $SlimCfg->exclude['ig_validators'])
			$bots = array_merge($bots, $SlimCfg->bot_array['validators']);
		if ($all || $SlimCfg->exclude['ig_tools'])
			$bots = array_merge($bots, $SlimCfg->bot_array['tools']);
		return $bots;
	}

	// powered by parse_request in WP class (WP 2.1)
	function _guessPostTitle($resource='', $track=false) {
		global $SlimCfg;
		
		if (!$track && !$SlimCfg->option['guesstitle'])
			return wp_specialchars($SlimCfg->trimString($resource, 68), true);
/*
		$_resource = $resource;
		if (strpos($_resource, '/') === 0) {
			$_resource = '/'.ltrim($_resource, '/');
		}*/
		$before = '<span class="resource-type">';
		$after = '</span>';
		
		if ( !$track && ($_pre = SSFunction::_getSavedPostTitle($resource)) ) {
			if ($_pre->rs_condition == '') { 
				$before = ''; $after = '';
			} else {
				if (strpos($_pre->rs_condition, '][') !== false) {
					$_condition = explode('][', $_pre->rs_condition);
					$_pre->rs_condition = __($_condition[0].']', 'wp-slimstat-ex').__('['.$_condition[1], 'wp-slimstat-ex');
				} else {
					$_pre->rs_condition = __($_pre->rs_condition, 'wp-slimstat-ex');
				}
			}
			if ('' == $_pre->rs_title) 
				$_pre->rs_title = $resource;
			return $before.$_pre->rs_condition.$after.' '.wp_specialchars($SlimCfg->trimString($_pre->rs_title, 68), true);
		}

		$qv = array();
		$query_vars = array();
		$req_uri_array = explode('?', $resource);
		$req_uri = $req_uri_array[0];
		$home_path = $SlimCfg->web_path;
		if ( !empty($req_uri_array[1]) )
			parse_str($req_uri_array[1], $qv);
		$req_uri = trim($req_uri, '/');
		$req_uri = preg_replace("|^$home_path|", '', $req_uri);
		$req_uri = trim($req_uri, '/');

		$fullurl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($resource, '/');
		// if external tracking or not wp resource
		if ( !stristr($fullurl, get_option('home')) && !stristr($fullurl, get_option('siteurl')) ) {
			if ($track)
				return array('title'=>'', 'job'=>'[unusual]', 'type'=>'');
			SSFunction::_insertPostTitle('', '[unusual]', $resource);
			return $before.__('[unusual]', 'wp-slimstat-ex').$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		}

		global $wp, $wp_rewrite;
		$public_query_vars = $wp->public_query_vars;

		if ( empty($qv) && ($req_uri == '' || $req_uri == $wp_rewrite->index) ) {
			if ($track)
				return array('title'=>'', 'job'=>'', 'type'=>'[home]');
			SSFunction::_insertPostTitle('', '[home]', $resource);
			return $before.__('[home]', 'wp-slimstat-ex').$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		} elseif ( isset($qv['dl']) && !empty($qv['dl']) ) {
			if ($track)
				return array('title'=>$qv['dl'], 'job'=>'', 'type'=>'[download]');
			SSFunction::_insertPostTitle($qv['dl'], '[download]', $resource);
			return $before.__('[download]', 'wp-slimstat-ex').$after.' '.$qv['dl'];
		} elseif ( strpos($req_uri, '/wp-comments-post.php') !== false ) {
			if ($track)
				return array('title'=>'', 'job'=>'[add comment]', 'type'=>'');
			SSFunction::_insertPostTitle('', '[add comment]', $resource);
			return $before.__('[add comment]', 'wp-slimstat-ex').$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		} elseif ( strpos($req_uri, '/wp-cron.php') !== false ) {
			if ($track)
				return array('title'=>'', 'job'=>'', 'type'=>'[schedule]');
			SSFunction::_insertPostTitle('', '[schedule]', $resource);
			return $before.__('[schedule]', 'wp-slimstat-ex').$after.' '.$req_uri; // ignore check query
		}

		// Fetch the rewrite rules.
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		if ( empty($rewrite)) {
			$did_permalink = false;
			// if is not wp resource
			if ( $req_uri != $wp_rewrite->index && $req_uri != '' ) {
				if ($track)
					return array('title'=>'', 'job'=>'[unusual]', 'type'=>'');
				SSFunction::_insertPostTitle('', '[unusual]', $resource);
				return $before.__('[unusual]', 'wp-slimstat-ex').$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
			}
		} else {
			$did_permalink = true;
			// If the request uri is the index, blank it out so that we don't try to match it against a rule.
			if ( $req_uri == $wp_rewrite->index )
				$req_uri = '';
			$request = $req_uri;

			// Look for matches.
			$request_match = $request;
			foreach ($rewrite as $match => $query) {
				// If the requesting file is the anchor of the match, prepend it
				// to the path info.
				if ((! empty($req_uri)) && (strpos($match, $req_uri) === 0) && ($req_uri != $request)) {
					$request_match = $req_uri . '/' . $request;
				}
				if (preg_match("!^$match!", $request_match, $matches) ||
					preg_match("!^$match!", urldecode($request_match), $matches)) {
					// Trim the query of everything up to the '?'.
					$query = preg_replace("!^.+\?!", '', $query);
					// Substitute the substring matches into the query.
					eval("\$query = \"$query\";");
					// Parse the query.
					parse_str($query, $perma_query_vars);
					break;
				}
			}
			if ( isset($perma_query_vars) && strpos($request, 'wp-admin/') !== false )
				unset($perma_query_vars);
		}

		$public_query_vars = apply_filters('query_vars', $public_query_vars);
		if ( !in_array('tag', $public_query_vars) )
			$public_query_vars[] = 'tag';

		for ($i=0; $i<count($public_query_vars); $i += 1) {
			$wpvar = $public_query_vars[$i];
			if (!empty($qv[$wpvar]))
				$query_vars[$wpvar] = $qv[$wpvar];
			elseif (!empty($perma_query_vars[$wpvar]))
				$query_vars[$wpvar] = $perma_query_vars[$wpvar];
		}

		// if !is_home() && there is no public query vars
		if ( empty($query_vars) ) {
			if ($track)
				return array('title'=>'', 'job'=>'[unusual]', 'type'=>'');
			SSFunction::_insertPostTitle('', '[unusual]', $resource);
			return $before.__('[unusual]', 'wp-slimstat-ex').$after.' '.wp_specialchars($SlimCfg->trimString($resource, 68), true);
		}
		
		return SSFunction::_getPostTitle($query_vars, $before, $after, $resource, $track, $did_permalink);
	}

	function _getSavedPostTitle($resource) {
		global $wpdb, $SlimCfg;
		$resource = $wpdb->escape($resource);
		$query = "SELECT * FROM {$SlimCfg->table_resource} tr WHERE tr.rs_md5 LIKE MD5('{$resource}') AND (tr.rs_title <> '' OR tr.rs_condition <> '') LIMIT 1";
		if ($_pre = $wpdb->get_row($query)) {
			return $_pre;
		}
		return false;
	}

	function _insertPostTitle($title='', $condition='', $resource) {
		global $wpdb, $SlimCfg;
		$_resource = $wpdb->escape($resource);
		$_title = $wpdb->escape($title);
		$_condition = $wpdb->escape($condition);
		$insert = $wpdb->query("UPDATE {$SlimCfg->table_resource} tr SET tr.rs_title = '{$_title}', tr.rs_condition = '{$_condition}' WHERE tr.rs_md5 = MD5(TRIM('{$_resource}')) LIMIT 1");
		return $insert;
	}

	function _getPostTitle($query_vars, $before, $after, $resource, $track, $did_permalink=false) {
		global $SlimCfg, $wp_query, $wp_the_query;
		if ($track) {
			$_query =& $wp_the_query;
		} else {
			// build query string. powered by build_query_string in WP class (WP 2.1)
			$query_string = '';
			foreach (array_keys($query_vars) as $wpvar) {
				if ( '' != $query_vars[$wpvar] ) {
					$query_string .= (strlen($query_string) < 1) ? '' : '&';
					if ( !is_scalar($query_vars[$wpvar]) ) // Discard non-scalars.
						continue;
					$query_string .= $wpvar . '=' . rawurlencode($query_vars[$wpvar]);
				}
			}
			$_query = new WP_Query($query_string);
			// FROM WP::handle_404()
			// Issue a 404 if a permalink request doesn't match any posts.  Don't
			// issue a 404 if one was already issued, if the request was a search,
			// or if the request was a regular query string request rather than a
			// permalink request.
			if ( (0 == count($_query->posts)) && !$_query->is_404 && !$_query->is_search && ( $did_permalink || (!empty($query_string) && (false === strpos($resource, '?'))) ) ) {
				$_query->set_404();
			}
		}

		$_type = '';
		$_job = '';
		$_title = '';
		$_queried_object =& $_query->get_queried_object();

		if ($_query->is_trackback && $_queried_object->ID)
			$_job = '[trackback]';
		elseif ($_query->is_feed)
			$_job = '[feed]';
		elseif ($_query->is_paged)
			$_type = '[paged]';

		if (isset($query_vars['tag']) && !empty($query_vars['tag'])) {
			$_title = $query_vars['tag'];
			$_type = '[tag]';
		} elseif ($_query->is_404) {
			$_type = '[404 error]';
		} elseif ($_query->is_comment_feed) {// since WP 2.2
			if ( ($_query->is_singular/*since WP 2.1*/) && isset($_queried_object->post_title) )
				$_title = $_queried_object->post_title;
			if ($_query->is_attachment)
				$_type = '[attachment comments]';
			elseif ($_query->is_single)
				$_type = '[post comments]';
			elseif ($_query->is_page)
				$_type = '[page comments]';
			else 
				$_type = '[comments]';
		} elseif (isset($_queried_object->cat_name) && $_query->is_category) {
			$_title = $_queried_object->cat_name;
			$_type = '[category]';
		} elseif (isset($_queried_object->post_title) && $_query->is_posts_page) {
			$_title = $_queried_object->post_title;
			$_type = '[posts page]';
		} elseif ($_query->is_date) {
			$_type = '[date]';
		} elseif ($_query->is_search) {// maybe useless...
			$_title = $_query->get('s');
			$_type = '[search]';
		} elseif (isset($_queried_object->display_name) && $_query->is_author) {
			$_title = $_queried_object->display_name;
			$_type = '[author]';
		} elseif (isset($_queried_object->post_title) && $_query->is_attachment) {
			$_title = $_queried_object->post_title.' ('.$_queried_object->post_mime_type.')';
			$_type = '[attachment]';
		} elseif (isset($_queried_object->post_title) && ($_query->is_page || $_query->is_single)) {
			$_title = $_queried_object->post_title;
			$_type = ($_query->is_page)?'[page]':'[post]';
		} elseif ($_query->is_comments_popup) {
			$_type = '[comments popup]';
		} elseif ($_query->is_home) {
			$_type = '[home]';
		}/* elseif ($_query->is_404) {
			$_type = '[404 error]';
		}*/

		if (!$_title || empty($_title)) $_title = '';
		if ('' == $_title && '' == $_job && '' == $_type)
			$_job = '[unusual]';
		if ($_type == "" && $_job == "" ) {
			$before = ""; $after = "";
		}

		if ($track)
			return array('title'=>$_title, 'job'=>$_job, 'type'=>$_type);

		$insert = SSFunction::_insertPostTitle($_title, $_job.$_type, $resource);
		if ('' != $_type) $_type = __($_type, 'wp-slimstat-ex');
		if ('' != $_job) $_job = __($_job, 'wp-slimstat-ex');
		return $before.$_job.$_type.$after.' '.wp_specialchars($SlimCfg->trimString($_title, 68), true);
	}

	// To do... or remove
	function print_pages($query, $rows, $pinid) {
		global $wpdb, $SlimCfg;
		$offset = $_GET['offset'];
		$offset = (isset($offset) && !empty($offset))?(int)$offset:0;
		$use_ajax = $SlimCfg->option['use_ajax'];
		$location = get_option('siteurl').'/wp-admin/index.php?page=wp-slimstat-ex';

		$filter = $SlimCfg->get['fd_encode'].$SlimCfg->get['fi_encode'];
//		$query = "SELECT COUNT(DISTINCT ts.visit) AS counts FROM $SlimCfg->table_stats ts WHERE $filter_clause";
		$counts = (int)$wpdb->get_var($query);
		$count_pages = ceil($counts / $rows);
		if ($count_pages > 1) {
			$count_pages = ($count_pages>20)?20:$count_pages;
			$pnav = '<form method="get" action="'.($use_ajax?$SlimCfg->ajaxReq:$location).'" id="page_navi"';
			$pnav .= ($use_ajax?' onchange="SlimStat.nav();return false;" onsubmit="SlimStat.nav();return false;"':'').'>'; 
			$pnav .= $use_ajax?'':'<input type="hidden" name="page" value="wp-slimstat-ex" />';
			$pnav .= '<div class="page-navi">Pages : <select name="offset">';
			for($i=0;$i<$count_pages;$i++) {
				$pnav .= '<option value="'.$i.'"'.(($offset == $i )?'  selected="selected"':'').'>'.($i+1).'&nbsp;</option>';
//				$pnav .= ($offset != $i )?'<a href="#" onmouseup="SlimStat.panel(\''.$pinid.'\', \''.$filter.'&amp;offset='.($i).'\'); return false;">':'<strong>[';
//				$pnav .= ($i+1).(($offset != $i)?'</a>':']</strong>').', ';
			}
			$pnav .= '</select>';
			if (isset($SlimCfg->get['fi'])) {
				$pnav .= '<input type="hidden" name="ff" value="'.$SlimCfg->get['ff'].'" />';
				$pnav .= '<input type="hidden" name="fi" value="'.$SlimCfg->get['fi'].'" />';
				$pnav .= '<input type="hidden" name="ft" value="'.$SlimCfg->get['ft'].'" />';
			}
			if (isset($SlimCfg->get['fd'])) {
				$pnav .= '<input type="hidden" name="fd" value="'.$SlimCfg->get['fd'].'" />';
			}
			$pnav .= '<input type="hidden" name="panel" value="'.$SlimCfg->get['pn'].'" />';
			$pnav .= (($use_ajax)?'<!--[if IE]>':'').'&nbsp;<input type="submit" name="go_page" id="go_page" value="go" />'.(($use_ajax)?'<![endif]-->':'');
			$pnav .= '</div></form>';
			return $pnav;
		}
		return '';
	}

}//end of class
?>