<?php
/*
Track vistors
*/
if (!defined('SLIMSTATPATH')) { return false; }

class SSTrack {
	var $tracked = false;
	// Ignore same access(ip, resource, referer....) within xx millisecond
	// Please DO NOT CHAGNE THIS if you don't know exactly what it means
	var $track_interval = 0;// (1000 = 1sec) - disabled by default

	function SSTrack() {
		$this->track_interval = (int)$this->track_interval;
		$this->remote_addr = $this->get_remote_addr();
	}

	function feed_track($req) {
		global $doing_rss;
		if (defined('SLIMSTAT_EXTRACK')) 
			return false;
		if ( is_feed() || $doing_rss || (isset($_GET['feed']) && !empty($_GET['feed'])) ) 
			return true;
		// for redirected feed and WP older versions that miss feed request
		if ( !preg_match('/(tag|search)\/(feed|rdf|rss|rss2|atom)\/?$/i', $req) && (
				preg_match('/\/(feed|rdf|rss|rss2|atom)\/?(feed|rdf|rss|rss2|atom)?\/?$/i', $req) ||
				preg_match('/\/wp-(feed|rdf|rss|rss2|atom|comments-rss2).php/i', $req) ) )
			return true;
		return false;
	}

	function is_ignored() {
		global $SlimCfg;
		if (!$SlimCfg->option['tracking']) 
			return true;
		if (defined('SLIMSTAT_EXTRACK')) { // if external tracking
			global $slimtrack_ext;
		}
		// Do not track the admin pages and direct access to plugin or theme folder(css, javascript(AJAX) loading).
		if ( (function_exists('is_admin') && is_admin()) || 
		    (function_exists('is_404') && is_404()) || 
			 (!defined('SLIMSTAT_EXTRACK_JS') && strpos($_SERVER['PHP_SELF'], 'wp-content/plugins') !== false) ||
			 strpos($_SERVER['PHP_SELF'], 'wp-content/themes') !== false ||
			 strpos($_SERVER['PHP_SELF'], 'wp-includes/') !== false ||
//			 isset($_COOKIE["wordpressuser_".$cookiehash]) || // is user logged in? will be removed on future release (ignore user by user capabilities)
//			 isset($_COOKIE["wordpressuser"]) || // wordpress-mu
			 strpos($_SERVER['PHP_SELF'], 'wp-cron.php') !== false ||// ignore cron job
			 strpos($_SERVER["PHP_SELF"], "wp-register.php") !== false ||
			 strpos($_SERVER["PHP_SELF"], "wp-login.php") !== false ) 
			return true;

		if (!defined('SLIMSTAT_EXTRACK')) {// external track does not supports WP USER
			if ($SlimCfg->has_cap('ignore_slimstat_track'))
				return true;
		}

		if ( $this->_checkIgnoreList($this->remote_addr) )
			return true;
		return false;
	}

	function slimtrack() {
		global $wpdb, $SlimCfg;

		// track visitor only once
		if ($this->tracked)
			return;
		$this->tracked = true;
		if ($this->is_ignored())
			return;

		$localsearch = !empty( $_GET['s'] ) ? urldecode(trim($_GET['s'])) : false;

		$stat = array();
		$stat["remote_ip"] = sprintf( "%u", ip2long( $this->remote_addr ) );
		$stat["language"]	= $this->_determineLanguage();
		$stat["country"]	= $this->_determineCountry( $this->remote_addr );
		$stat["referer"] = "";
		$stat["domain"] = "";
		$url = "";
		if ( isset( $_SERVER["HTTP_REFERER"] ) ) {
			$stat["referer"] = preg_replace( '|^https?://|i', '', $_SERVER["HTTP_REFERER"] );
			$_host = explode('/', $stat["referer"]);
			if ('' == $_host[0] || !preg_match('|^(([a-z0-9_]+):([a-z0-9-_]*)@)?(([a-z0-9_-]+\.)*)(([a-z0-9-]{2,})\.?)$|iU', $_host[0])) {
				$stat["referer"] = "";
			} elseif ( false === $url = @parse_url( 'http://' . $stat["referer"] )) {
				$stat["referer"] = "";
			} else {
				$stat["domain"] = isset($url["host"]) ? $url["host"] : "";
				$stat["referer"] = ('' == $stat["domain"]) ? '' : $stat["referer"];
			}
		}
		$stat["searchterms"] = $localsearch ? $localsearch : $this->_determineSearchTerms( $url );
		if ($localsearch) { // Mark the resource to remember that this is a 'local search'
			$stat["resource"] = '__localsearch__';
		} elseif ( isset( $_SERVER["REQUEST_URI"] ) ) {
			$stat["resource"] = $_SERVER["REQUEST_URI"];
		} elseif ( isset($_SERVER["SCRIPT_NAME"]) ) {
			$stat["resource"] = isset( $_SERVER["QUERY_STRING"]) ? $_SERVER["SCRIPT_NAME"]."?".$_SERVER["QUERY_STRING"] : $_SERVER["SCRIPT_NAME"];
		} else {
			$stat["resource"] = isset( $_SERVER["QUERY_STRING"] ) ? $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"] : $_SERVER["PHP_SELF"];
		}
		if (strpos($stat["resource"], '/') === 0) {
			// treat trailing or no-trailing slash as the same (save DB space)
			$stat["resource"] = '/'.trim($stat["resource"], '.:/');
		}
		$target_table = $this->feed_track($stat['resource']) ? $SlimCfg->table_feed : $SlimCfg->table_stats;
		$info = $this->_parseUserAgent( $_SERVER["HTTP_USER_AGENT"] );
		$stat["platform"] = $info["platform"];
		$stat["browser"] = $info["browser"];
		$stat["version"] = $info["version"];

		if ($SlimCfg->uaOption && $stat["browser"] == '-1' && "" != trim($_SERVER["HTTP_USER_AGENT"]))
			$stat["user_agent"] = $this->insUA($_SERVER["HTTP_USER_AGENT"], $stat["platform"]);// for dev
		// ignore bots and unknown user_agent
		if ( ($SlimCfg->exclude['ignore_bots'] == 1 || $SlimCfg->exclude['ignore_bots'] == 3) 
				&& $this->is_bot($stat['browser'], $_SERVER["HTTP_USER_AGENT"]) ) {
			return;
		}
		$stat["dt"] = time();
		$stat["visit"] = $this->_determineVisit( $stat["remote_ip"], $stat["browser"], $stat["version"], $stat["platform"], $target_table, $stat["dt"] );

		// There was so may stripslashes and escape. 
		// First, strip all slashes since wordpress already fixed magic_quotes_gpc.
		$stat = array_map('stripslashes', $stat);

		// resource
		$stat["resource"] = $this->insResource($stat["resource"]);// incRescoure needs un-escaped value
		if ($stat["resource"] === false || $stat["resource"] == 0) {
			if (mysql_error($wpdb->dbh))
				$wpdb->print_error();
			return;
		}
		// escape values with $wpdb->escape
		// You need to apply wp_specialchars($string, true) on output html (attribute values)
		$stat = add_magic_quotes($stat);

		if ($this->track_interval > 0) {
			$last_vist = $wpdb->get_row("SELECT * FROM $target_table 
					WHERE remote_ip = '{$stat['remote_ip']}'
					AND resource = '{$stat['resource']}'
					AND dt >= '".($stat['dt'] - $this->trac_interval)."'
					ORDER  BY dt desc LIMIT 1");
			if ($last_visit)
					return;
		}

		$myQuery = "INSERT INTO $target_table ( `" .
					implode( "`, `", array_keys( $stat ) ) .
					"` ) VALUES ( \"" .
					implode( "\", \"", array_values( $stat ) ) .
					"\" )";
		$insert_row = $wpdb->query($myQuery);
	}
	// end slimtrack

	function insResource($resource) {
		global $wpdb, $SlimCfg;
		$rs_esc = $wpdb->escape(trim($resource));
		$query = "SELECT tr.id FROM $SlimCfg->table_resource tr WHERE tr.rs_md5 = MD5('{$rs_esc}') LIMIT 1";
		if ($_pre = $wpdb->get_row($query))
			return $_pre->id;

		if ( defined('SLIMSTAT_EXTRACK') || !defined('ABSPATH') || !class_exists('SSFunction') )
			$_rstitle = array('title'=>'', 'job'=>'[external]', 'type'=>'');
		else
			$_rstitle = SSFunction::_guessPostTitle($resource, true);
		$ins_query = "INSERT IGNORE INTO $SlimCfg->table_resource (rs_string, rs_md5, rs_title, rs_condition) 
				VALUES ('{$rs_esc}', MD5('{$rs_esc}'), '".$wpdb->escape($_rstitle['title'])."', '".$_rstitle['job'].$_rstitle['type']."') ";
		if (false === $wpdb->query($ins_query))
			return false;
		return (int)$wpdb->get_var($query);
	}

	function insUA($ua, $platform=-1) {
		global $wpdb;
		$ua_esc = $wpdb->escape(trim($ua));
		$query = "SELECT tu.id FROM $SlimCfg->table_ua tu WHERE tu.ua_md5 = MD5('{$ua_esc}') LIMIT 1";
		if ($_pre = $wpdb->get_row($query))
			return $_pre->id;
		$ins_query = "INSERT IGNORE INTO $SlimCfg->table_ua (ua_string, ua_md5, platform)
			VALUES ('{$ua_esc}', MD5('{$ua_esc}'), {$platform}) ";
		if (false === $wpdb->query($ins_query))
			return 0;
		return (int)$wpdb->get_var($query);
	}

	function _determineCountry( $ip='' ) {
		global $wpdb, $SlimCfg;
		if ( '' == $ip) $ip = $this->remote_addr;
		if ($SlimCfg->external_iptc == 'external')
			return SSTrack::_determineCountry_external($ip);
		if ($SlimCfg->geoip != 'mysql')
			return $SlimCfg->geoip_country($ip);

		if (strpos($ip, '.') !== false)
			$myIp = sprintf( "%u", ip2long( $ip ) );
		else 
			$myIp = $ip;
		$myQuery = "SELECT `country_code` FROM `$SlimCfg->table_countries` WHERE `ip_from` <= $myIp AND `ip_to` >= $myIp";
		$myCountryCode = $wpdb->get_row( $myQuery );
		if ( $myCountryCode ) {
			return $myCountryCode->country_code;
		}
		return '';
	}

	function _determineCountry_external($ip) {
		$coinfo = @file('http://www.hostip.info/api/get.html?ip=' . $ip);
		if ($coinfo) {
			if (preg_match('/Country:(.*?)\((\w*?)\)/isU', $coinfo[0], $match)) {
				$country = trim($match[2]);
				if ($country == "XX" || $country == "xx" || $country == "" || !$country)
					return "";
				return $country;
			}
		}
		return "";
	}

	function _determineLanguage() {
		global $SlimCfg;
		$myLangList = array(); 
		if ( isset( $_SERVER["HTTP_ACCEPT_LANGUAGE"] ) ) {
			preg_match( "/([^,;]*)/", $_SERVER["HTTP_ACCEPT_LANGUAGE"], $myLangList );
			$l = str_replace( "_", "-", strtolower( $myLangList[0] ) );
			$l = $this->langoverlap($l);
			return $l;
		}
		return '';  // Indeterminable language
	}

	function langoverlap($lang) {
		if (empty($lang)) 
			return '';
		$langs = array("aa","ab","ae","af","ak","am","an","anp","ar","as","av","ay","az","ba","be","bg","bh","bi","bm","bn","bo","br","bs","ca","ce","ch","co","cr","cs","cu","cv","cy","da","de","dv","dz","ee","el","en","eo","es","et","eu","fa","ff","fi","fj","fo","fr","frr","fy","ga","gd","gl","gn","gu","gv","ha","he","hi","ho","hr","ht","hu","hy","hz","ia","id","ie","ig","ii","ik","in","io","is","it","iu","iw","ja","ji","jv","jw","ka","kg","ki","kj","kk","kl","km","kn","ko","kr","ks","ku","kv","kw","ky","la","lb","lg","li","ln","lo","lt","lu","lv","mg","mh","mi","mk","ml","mn","mo","mr","ms","mt","my","na","nb","nd","ne","ng","nl","nn","no","nr","nv","ny","oc","oj","om","or","os","pa","pi","pl","ps","pt","qu","rm","rn","ro","ru","rw","sa","sc","sd","se","sg","sh","si","sk","sl","sm","sn","so","sq","sr","ss","st","su","sv","sw","ta","te","tg","th","ti","tk","tl","tn","to","tr","ts","tt","tw","ty","ug","uk","ur","uz","ve","vi","vo","wa","wo","xh","yi","yo","za","zh","zu");
		$regions = array("ad","ae","af","ag","ai","al","am","an","ao","aq","ar","as","at","au","aw","ax","az","ba","bb","bd","be","bf","bg","bh","bi","bj","bm","bn","bo","br","bs","bt","bu","bv","bw","by","bz","ca","cc","cd","cf","cg","ch","ci","ck","cl","cm","cn","co","cr","cs","cu","cv","cx","cy","cz","dd","de","dj","dk","dm","do","dz","ec","ee","eg","eh","er","es","et","fi","fj","fk","fm","fo","fr","fx","ga","gb","gd","ge","gf","gg","gh","gi","gl","gm","gn","gp","gq","gr","gs","gt","gu","gw","gy","hk","hm","hn","hr","ht","hu","id","ie","il","im","in","io","iq","ir","is","it","je","jm","jo","jp","ke","kg","kh","ki","km","kn","kp","kr","kw","ky","kz","la","lb","lc","li","lk","lr","ls","lt","lu","lv","ly","ma","mc","md","me","mg","mh","mk","ml","mm","mn","mo","mp","mq","mr","ms","mt","mu","mv","mw","mx","my","mz","na","nc","ne","nf","ng","ni","nl","no","np","nr","nt","nu","nz","om","pa","pe","pf","pg","ph","pk","pl","pm","pn","pr","ps","pt","pw","py","qa","qm..qz","re","ro","rs","ru","rw","sa","sb","sc","sd","se","sg","sh","si","sj","sk","sl","sm","sn","so","sr","st","su","sv","sy","sz","tc","td","tf","tg","th","tj","tk","tl","tm","tn","to","tp","tr","tt","tv","tw","tz","ua","ug","uk","um","us","uy","uz","va","vc","ve","vg","vi","vn","vu","wf","ws","yd","ye","yt","yu","za","zm","zr","zw");
		$langoverlap = array("ie-ee"=>"","ko-kr"=>"ko","zh-ch"=>"zh-cn","ja-jp"=>"ja","english"=>"en","*"=>"");
		$lang = (isset($langoverlap[$lang]))?$langoverlap[$lang]:$lang;
		$l_split = split('-', $lang);
		$c = count($l_split);
		if (!in_array($l_split[0], $langs))
			return '';
		if ($c < 2)
			return $l_split[0];
		$last = $c-1;
		if (in_array($l_split[$last], $regions))
			return $l_split[0].'-'.$l_split[$last];
		return $l_split[0];
	}

	function _determineSearchTerms( $url = '' ) { 
		global $SlimCfg;
		// check url
		if (empty($url))
			return "";
		if ( !is_array( $url ) ) $myUrl = parse_url( $url );
		else $myUrl = $url;
		if ( !isset($myUrl["host"]) || !isset($myUrl["query"]) )
			return "";

		// Host regexp, query portion containing search terms
		$sniffs = array( 
			array( "/google\./i", "q" ),
			array( "/alltheweb\./i", "q" ),
			array( "/yahoo\./i", "p" ),
			array( "/search\.aol\./i", "query" ),
			array( "/search\.looksmart\./i", "p" ),
			array( "/gigablast\./i", "q" ),
			array( "/s\.teoma\./i", "q" ),
			array( "/clusty\./i", "query" ),
			array( "/yandex\./i", "text" ),
			array( "/rambler\./i", "words" ),
			array( "/aport\./i", "r" ),
			array( "/search\.naver\./i", "query" ),
			array( "/search\.cs\./i", "query" ),
			array( "/search\.netscape\./i", "query" ),
			array( "/hotbot\./i", "query" ),
			array( "/search\.msn\./i", "q" ),
			array( "/altavista\./i", "q" ),
			array( "/web\.ask\./i", "q" ),
			array( "/search\.wanadoo\./i", "q" ),
			array( "/www\.bbc\./i", "q" ),
			array( "/tesco\.net/i", "q" ),
			array( "/.*/", "search" ),
			array( "/.*/", "query" ),
			array( "/.*/", "q" )
		);
		foreach ( $sniffs as $sniff ) {
			if ( preg_match( $sniff[0], $myUrl["host"] ) ) {
				parse_str( $myUrl["query"], $q );
				if ( !isset($q[$sniff[1]]) )
					continue;
				$mySearchTerms = urldecode($q[$sniff[1]]);
				// get search string from google cached page addr
				if ($sniff[1] == 'q' && strpos($mySearchTerms, 'cache:') === 0) {
					$temp_str = explode('+', $mySearchTerms);
					unset($temp_str[0]);
					$mySearchTerms = implode(' ', $temp_str);
				}
				// Convert international encodings to UTF-8 only when blog charset is UTF-8.
				$blog_charset = strtoupper( get_option('blog_charset') );
				if ( $blog_charset == 'UTF-8' && !seems_utf8($mySearchTerms) ) { 
					if (strpos($mySearchTerms, '%u') === 0) { // UTF16-LE
						$mySearchTerms = $this->urlutfchr($mySearchTerms);
					}
					$mySearchTerms = $SlimCfg->convert_encoding($mySearchTerms);
				}
				return stripslashes($mySearchTerms);
			}
		}
		return "";
	}
	
	function _parseUserAgent( $ua = '' ) {
		$_m = array();
		$info = array(
			'platform' => '-1',
			'browser'  => '-1',
			'version'  => '',
//			'majorver' => '',
//			'minorver' => ''
		);
		if (empty($ua))
			return $info;
		$_ua = strtolower( $ua );
		$_ua = str_replace("funwebproducts", "", $_ua);

		// Browser type
		// Browser to OS 
		// Mobile Browser ID :: bigger than 1000
		// Generic Bots :: 34, Web Downloaders(getright, flashget...) :: 2000
		$info = $this->_determineBrowser($_ua, $info);

		if ($info['browser'] > '1000') {
			$info['platform'] = $this->_determineMobileOS($_ua);// Mobile browsers
		}

		switch($info['browser']) {
			case '28':case '95':case '100':case '158':
				$info['platform'] = $this->_determineMacOS($_ua);// Mac only browsers
			break;
			case '30':case '32':case '36':case '36':case '37':
				$info['platform'] = $this->_determineUnixOS($_ua);// Unix only browsers
			break;
			default:break;
		}

		/* Platform */// Mobile OS ID :: bigger than 70
		if ($info['platform'] == '-1') { // If not defiened by browser
			if ( preg_match( '/([^dar]win[dows]*)[\s]?([0-9a-z]*)[\w\s]?([a-z0-9.]*)/i', $_ua, $_m ) ) {
				// Analyze weird Microsoft user agent
				$info['platform'] = $this->_determineWinOS($_ua, $_m);
			} elseif ( preg_match( '/(macintosh|mac_powerpc|ppc mac os|intel mac os|darwin)/i', $_ua ) ) {
				$info['platform'] = $this->_determineMacOS($_ua);
			} else {
				$info['platform'] = $this->_determineMobileOS($_ua);
				// Other Unix OS and rest.
				$info['platform'] = ($info['platform'] == '-1') ? $this->_determineUnixOS($_ua) : $info['platform'];
			}
		}
		return $info;
	}

	// lower case user-agent only
	function _determineBrowser($_ua, $info = array('platform'=>'-1', 'browser'=>'-1', 'version'=>'', 'majorver'=>'', 'minorver'=>'') ) {
		// User defined browsers: name regexp, browser id, version regexp, version match, platform (optional)
		$sniffs = array(
			array( 'netscape', '1', 'netscape[0-9]?/([[:digit:]\.]+)', 1 ),
			array( 'safari', '2', 'safari/([[:digit:]\.]+)', 1, '10' ),
			array( 'icab', '3', 'icab/([[:digit:]\.]+)', 1,  '10' ),
			array( 'firebird', '5', 'firebird/([[:digit:]\.]+)', 1 ),
			array( 'phoenix', '6', 'phoenix/([[:digit:]\.]+)', 1 ),
			array( 'camino', '7', 'camino/([[:digit:]\.]+)', 1, '10' ),
			array( 'chimera', '8', 'chimera/([[:digit:]\.]+)', 1, '10' ),
			array( 'msn explorer', '10', 'msn explorer ([[:digit:]\.]+)', 1 ),
			array( 'wordpress/', '11', 'wordpress/([[:digit:]\.]+)', 1 ),
			array( 'blogsearch', '12', 'blogsearch/([[:digit:]\.]+)', 1 ),
			array( 'allblog.net', '13', 'allblog.net ([[:digit:]\.]+)', 1 ),
			array( 'hanrss', '14', 'hanrss/([[:digit:]\.]+)', 1 ),
			array( 'xml-rpc.net', '15', 'xml-rpc.net ([[:digit:]\.]+)', 1 ),
			array( 'w3c_validator', '16', 'w3c_validator/([[:digit:]\.]+)', 1 ),
			array( 'w3clinemode', '16', 'w3clinemode/([[:digit:]\.]+)', 1),// same as above
			array( 'feedvalidator', '17', 'feedvalidator/([[:digit:]\.]+)', 1 ),
			array( 'jigsaw', '18', 'jigsaw/([[:digit:]\.]+)', 1 ),
			array( 'python-urllib', '19', 'python-urllib/([[:digit:]\.]+)', 1 ),
			array( 'newsgatoronline', '20', '', 0 ),
			array( 'newsgator', '20', 'newsgator/([[:digit:]\.]+)', 1 ),// same as above
			array( '(compatible; google desktop', '21', '', ''),// google search appliance
			array( 'java', '22', 'java/([[:digit:]\.]+)', 1 ),
			array( 'aol', '23', 'aol ([[:digit:]\.]+)', 1 ),
			array( 'america online browser', '24', 'america online browser ([[:digit:]\.]+)', 1 ),
			array( 'k-meleon', '25', 'k-meleon/([[:digit:]\.]+)', 1 ),
			array( 'kmeleon', '25', 'kmeleon/([[:digit:]\.]+)', 1 ),// same as above
			array( 'beonex', '26', 'beonex/([[:digit:]\.]+)', 1 ),
			array( 'opera mini', '1033', 'opera mini/([[:digit:]\.]+)', 2 ),// added later but checked first
			array( 'opera', '27', 'opera( |/)([[:digit:]\.]+)', 2 ),
			array( 'omniweb', '28', 'omniweb/v([[:digit:]\.]+)', 1 ),
			array( 'konqueror', '29', 'konqueror/([[:digit:]\.]+)', 1, '20' ),
			array( 'galeon', '30', 'galeon/([[:digit:]\.]+)', 1 ),
			array( 'epiphany', '31', 'epiphany/([[:digit:]\.]+)', 1 ),
			array( 'kazehakase', '32', 'kazehakase/([[:digit:]\.]+)', 1 ),
			array( 'amaya', '33', 'amaya/([[:digit:]\.]+)', 1 ),
			// 34 is below there (bot or crawler)
			array( 'lynx', '35', 'lynx/([[:digit:]\.]+)', 1 ),
			array( 'links', '36', '\(([[:digit:]\.]+)', 1 ),
			array( 'elinks', '37', 'elinks[/ ]([[:digit:]\.]+)', 1 ),
			array( 'thunderbird', '38', 'thunderbird/([[:digit:]\.]+)',  1 ),
			array( 'flock', '39', 'flock/([[:digit:]\.]+)',  1 ),
			array( 'lwp-request', '40', 'lwp-request/(.*)$', 1 ),
			array( 'apachebench', '41', 'apachebench/(.*)$', 1 ),
			array( 'seamonkey', '42', 'seamonkey/([[:digit:]\.]+)', 1 ),
			array( 'mediapartners-google', '43', 'mediapartners-google/([[:digit:]\.]+)', 1 ),
			array( 'feedfetcher-google', '44', '', 0 ),
			// Google
			array( 'googlebot-image', '108', '', '' ),// google related.
			array( 'adsbot-google', '108', '', '' ),// google related.
			array( 'google-sitemaps', '108', '', '' ),// google related.
			array( 'googlebot-urlconsole', '108', '', '' ),// google related.
			array( 'googlebot/test', '108', '', '' ),// google related.
			array( 'mediapartners-google/', '109', '', ''),// google appliance
			array( 'gsa-crawler', '109', '', ''),// google search appliance
			array( '(compatible; googletoolbar', '109', '', ''),// google search appliance
			array( 'googlebot/', '45', 'googlebot/([[:digit:]\.]+)', 1 ),

			array( 'lanshanbot/', '121', '', ''),// msn bots.
			array( ' ms search ', '121', '', ''),// msn bots.
			array( 'msnbot-media', '121', '', ''),// msn bots.
			array( 'msnbot-news', '121', '', ''),// msn bots.
			array( 'msnbot-newsblogs', '121', '', ''),// msn bots.
			array( 'msnbot-products', '121', '', ''),// msn bots.
			array( 'msnbot/', '46', 'msnbot/([[:digit:]\.]+)', 1 ),

			array( 'yahoo-blogs', '47', 'yahoo-blogs/([[:digit:]\.]+)', 1 ),
			array( 'gigabot', '48', 'gigabot/([[:digit:]\.]+)', 1 ),
			array( 'zyborg', '49', 'zyborg/([[:digit:]\.]+)', 1 ),
			array( 'nutchcvs', '50', 'nutchcvs/([[:digit:]\.]+)', 1 ),
			array( 'ichiro', '51', 'ichiro/([[:digit:]\.]+)', 1 ),
			array( 'technoratibot', '52', 'technoratibot/([[:digit:]\.]+)', 1 ),
			array( 'heritrix', '53', 'heritrix/([[:digit:]\.]+)', 1 ),
			array( 'feedburner', '54', 'feedburner/([[:digit:]\.]+)', 1 ),
			array( 'feedpath', '55', 'feedpath/([[:digit:]\.]+)', 1 ),
			array( 'netvibes', '56', 'netvibes/([[:digit:]\.]+)', 1 ),
			array( 'greatnews', '57', 'greatnews/([[:digit:]\.]+)', 1 ),
			array( 'magpierss', '58', 'magpierss/([[:digit:]\.]+)', 1 ),
			array( 'livedoor feedfetcher', '59', 'livedoor feedfetcher/([[:digit:]\.]+)', 1 ),
			array( 'livedoor httpclient', '59', 'livedoor httpclient/([[:digit:]\.]+)', 1),// just treat as the same :-|
			array( 'snoopy', '60', 'snoopy v([[:digit:]\.]+)', 1 ),
			array( ' eolin', '61', '', 0 ),
			// Since 1.5
			array( 'mac finder', '62', 'mac finder ([[:digit:]\.]+)', 1, '12'),
			array( 'movabletype', '63', 'movabletype[/ ]([[:digit:]\.]+)', 1),
			array( 'typepad', '64', 'typepad[/ ]([[:digit:]\.]+)', 1),
			array( 'drupal', '65', '', ''),
			array( 'anonymouse', '66', '', ''),
			array( 'bluefish', '67', 'bluefish ([[:digit:]\.]+)', 1),
			array( 'amiga-aweb', '68', 'amiga-aweb/([[:digit:]\.]+)', 1),
			array( 'avantbrowser.com', '69', '', ''),
			array( 'amigavoyager', '70', 'amigavoyager/([[:digit:]\.]+)', 1),
			array( 'emacs-w3', '71', 'emacs-w3/([[:digit:]\.]+)', 1),
			array( 'curl', '72', 'curl/([[:digit:]\.]+)', 1),
			array( 'democracy', '73', 'democracy/([[:digit:]\.]+)', 1),
			array( 'dillo', '74', 'dillo/([[:digit:]\.]+)', 1),
			array( 'doczilla', '75', 'doczilla/([[:digit:]\.]+)', 1),
			array( 'edbrowse', '76', 'edbrowse/([[:digit:]\.]+)', 1),
			array( 'libfetch', '77', 'libfetch/([[:digit:]\.]+)', 1),//Fetch
			array( 'iceweasel', '78', 'iceweasel/([[:digit:]\.]+)', 1),//Gnuzilla and IceWeasel
			array( 'ibrowse', '79', 'ibrowse/([[:digit:]\.]+)', 1),
			array( 'ice browser', '80', 'ice browser/([[:digit:]\.]+)', 1),
			array( 'danger hiptop', '81', '', ''),
			array( 'kkman', '82', 'kkman([[:digit:]\.]+)', 1),
			array( 'mosaic', '83', 'mosaic/([[:digit:]\.]+)', 1),
			array( 'netpositive', '84', 'netpositive/([[:digit:]\.]+)', 1, '45'),
			array( 'songbird', '85', 'songbird/([[:digit:]\.]+)', 1),
			array( 'sylera', '86', 'sylera/([[:digit:]\.]+)', 1),
			array( 'shiira', '87', 'shiira/([[:digit:]\.]+)', 1, '10'),
			array( 'webtv', '88', 'webtv/([[:digit:]\.]+)', 1),//WebTV(MS)
			array( 'w3m/', '89', 'w3m/([[:digit:]\.]+)', 1),
			array( 'historyhound', '90', '', ''),
			array( 'blogkorea', '91', 'blogkorea/([[:digit:]\.]+)', 1 ),
			array( 'oregano', '92', 'oregano ([[:digit:]\.]+)', 1),
			array( 'wdg_validator', '93', 'wdg_validator/([[:digit:]\.]+)', 1),
			array( 'docomo', '94', 'docomo/([[:digit:]\.]+)', 1),
			array( 'newsfire', '95', 'newsfire/([[:digit:]\.]+)', 1),
			array( 'newsalloy', '96', 'newsalloy/([[:digit:]\.]+)', 1),
			array( 'liferea', '97', 'liferea/([[:digit:]\.]+)', 1),
			array( 'hatena rss', '98', 'hatena rss/([[:digit:]\.]+)', 1),
			array( 'feedshow', '99', 'feedshow/([[:digit:]\.]+)', 1),
			array( 'feedshowonline', '99', '', ''),// same as above
			array( 'netnewswire', '100', 'netnewswire/([[:digit:]\.]+)', 1),
			array( 'acorn browse', '101', 'acorn browse ([[:digit:]\.]+)', 1),
			// user agent from firestats (http://firestats.cc/)
			array( 'bonecho', '102', 'bonecho/([[:digit:]\.]+)', 1),
			array( 'lycos', '103', '', ''),
			array( 'multizilla ', '104', 'multizilla v([[:digit:]\.]+)', 1),
			array( 'multizilla', '104', 'multizilla/v?([[:digit:]\.]+)', 1),// same as above
			array( 'firefox', '4', 'firefox/([[:digit:]\.]+)',  1 ),// check after brother
			array( 'j2me', '105', '', ''),// j2me/midp browser
			array( 'midp', '105', '', ''),// j2me/midp browser
			array( 'php', '106', '', ''),
			// Inktomi & Yahoo
			array( 'slurp/si', '111', '', ''),// inktomi bots
			array( 'slurp/cat', '111', '', ''),// inktomi bots
			array( 'scooter/', '111', '', ''),// inktomi bots
			array( 'y!j-', '111', '', ''),// inktomi bots
			array( 'yahoo japan; for robot', '111', '', ''),// inktomi bots
			array( 'yahooseeker', '110', 'yahooseeker/([[:digit:]\.]+)', 1),
			array( 'yahoo pipes', '110', '', ''),// yahoo bots
			array( 'yahoo mindset', '110', '', ''),// yahoo bots
			array( 'yahoo! mindset', '110', '', ''),// yahoo bots
			array( 'yahoo-', '110', '', ''),// yahoo bots
			array( 'yahooysmcm', '110', '', ''),// yahoo bots
			array( 'yrl_odp_crawler', '110', '', ''),// yahoo bots
			array( 'yahoovideosearch', '110', '', ''),// yahoo bots
			array( 'yahoo! slurp/site', '110', '', ''),// yahoo bots
			array( 'yahooysmcm', '110', '', ''),// yahoo bots
			array( 'y!j', '110', '', ''),// yahoo bots
			array( 'yahoo! slurp', '107', '', ''),// yahoo main bot
			array( 'yahoo! de slurp', '107', '', ''),// yahoo main bot
			array( 'slurp', '111', '', ''),// inktomi bots
			array( 'cheshire', '112', 'cheshire/([[:digit:]\.]+)', 1, '10'),
			array( 'crazy browser', '113', 'crazy browser ([[:digit:]\.]+)', 1),
			array( 'enigma browser', '114', '', ''),
			array( 'granparadiso', '115', 'granparadiso/([[:digit:]\.]+)', 1),
			array( 'iceape', '116', 'iceape/([[:digit:]\.]+)', 1),
			array( 'k-ninja', '117', 'k-ninja/([[:digit:]\.]+)', 1),
			array( 'maxthon', '118', 'maxthon ([[:digit:]\.]+)', 1),
			array( 'minefield', '119', 'minefield/([[:digit:]\.]+)', 1),
			array( 'myie2', '120', '', ''),

			array( 'wii libnup', '122', 'wii libnup/([[:digit:]\.]+)', 1),
			array( 'w3c-checklink', '123', 'w3c-checklink/([[:digit:]\.]+)', 1),
			array( 'xenu link sleuth', '124', 'xenu link sleuth ([[:digit:]\.]+)', 1),
			array( 'cse html validator', '125', '', ''),
			array( 'csscheck', '126', 'csscheck/([[:digit:]\.]+)', 1),
			array( 'cynthia', '127', 'cynthia ([[:digit:]\.]+)', 1),
			array( 'htmlparser', '128', 'htmlparser/([[:digit:]\.]+)', 1),
			array( 'p3p validator', '129', '', ''),
			array( 'gregarius', '130', 'gregarius/([[:digit:]\.]+)', 1),
			array( 'bloglines', '131', 'bloglines/([[:digit:]\.]+)', 1),
			array( 'everyfeed-spider', '132', 'everyfeed-spider/([[:digit:]\.]+)', 1),
			array( '!susie', '133', '', ''),
			array( 'cocoal.icio.us', '134', 'bonecho/([[:digit:]\.]+)', 1),
			array( 'domainsdb.net metacrawler', '135', 'domainsdb\.net metacrawler v([[:digit:]\.]+)', 1),
			array( 'gsitecrawler', '136', 'gsitecrawler/([[:digit:]\.]+)', 1),
			array( 'feeddemon', '137', 'feeddemon/([[:digit:]\.]+)', 1),
			array( 'zhuaxia', '138', 'zhuaxia/([[:digit:]\.]+)', 1),
			array( 'akregator', '139', 'akregator/([[:digit:]\.]+)', 1),
			array( 'applesyndication', '140', 'applesyndication/([[:digit:]\.]+)', 1, '10'),
			array( 'blog conversation project', '141', '', ''),
			array( 'bottomfeeder', '142', '', ''),
			array( 'jetbrains omea reader', '143', 'jetbrains omea reader ([[:digit:]\.]+)', 1),
			array( 'ping.blo.gs', '144', 'ping.blo.gs/([[:digit:]\.]+)', 1),
			array( 'raggle', '145', 'raggle/([[:digit:]\.]+)', 1),
			array( 'rssbandit', '146', 'rssbandit ([[:digit:]\.]+)', 1),
			array( 'sharpreader', '147', 'sharpreader/([[:digit:]\.]+)', 1),
			array( 'yahoofeedseeker', '148', 'yahoofeedseeker/([[:digit:]\.]+)', 1),
			array( 'rojo', '149', 'rojo ([[:digit:]\.]+)', 1),
			array( 'kb.rmail', '150', '', ''),
			array( '(sage)', '151', '', ''),
			array( 'daum rss robot', '152', '', ''),
			array( 'thunderbird', '153', 'thunderbird/([[:digit:]\.]+)', 1),
			array( 'windows-rss-platform', '154', 'windows-rss-platform/([[:digit:]\.]+)', 1),
			array( 'universalfeedparser', '155', 'universalfeedparser/([[:digit:]\.]+)', 1),
			array( 'livejournal.com', '156', '', ''),
			array( 'vienna', '157', 'vienna/([[:digit:]\.]+)', 1, '10'),
			array( 'itunes', '158', 'itunes/([[:digit:]\.]+)', 1),
			array( 'quicktime', '159', 'qtver=([[:digit:]\.]+)', 1),
			array( 'realplayer', '160', '', ''),
			array( 'webindexer', '161', '', ''),
			array( 'xmind/xmind', '162', 'xmind/xmind-?([[:digit:]\.]+)', 1),
			array( 'hp web printsmart', '163', 'hp web printsmart [a-z0-9]*? ([[:digit:]\.]+)', 1),
			array( 'plagger/', '164', 'plagger/([[:digit:]\.]+)', 1),
			array( 'blogbridge', '165', '', ''),
			array( 'fastladder', '166', '', ''),
			array( 'newslife', '167', '', ''),
			array( 'rssowl', '168', '', ''),
			array( 'yeonmo', '169', '', ''),
			array( 'rmom', '170', '', ''),
			array( 'feedonfeeds', '171', '', ''),
			array( 'technoratisnoop', '172', '', ''),
			array( 'cazoodlebot', '173', '', ''),
			array( 'snapbot', '174', '', ''),
			array( 'ucla cs dept', '175', '', ''),
			array( 'httpclientbox', '176', '', ''),
			array( 'onnet-openapi', '177', '', ''),
			array( 's20 wing', '178', '', ''),
			array( 'openmaru feed aggregator', '179', '', ''),
			array( 'webscouter', '180', '', ''),
			// OpenID relative
			array( '-openid', '181', '', ''),
			array( ' openid', '181', '', ''),
			array( 'openod-', '181', '', ''),

			array( 'rebi-shoveler', '182', '', ''),
			array( 'mixsh rsssync', '183', '', ''),
			array( 'feedwave', '184', 'feedwave/([[:digit:]\.]+)', 1),

			array( 'msie', '9', 'msie ([[:digit:]\.]+)', 1 ),// check at last

			// Mobile Browsers	
			array( 'psp (playstation portable); ', '1001', 'psp (playstation portable); ([[:digit:]\.]+)', 1, '78'),// Sony PSP
			array( 'webpro', '1002', '', '', '74'),
			array( 'netfront', '1003', 'netfront/([[:digit:]\.]+)', 1),
			array( 'xiino', '1004', 'xiino/([[:digit:]\.]+)', 1),
			array( 'blackberry/', '1005', 'blackberry/([[:digit:]\.]+)', 1),// pda
			array( 'blackberry', '1005', 'blackberry[^/]*/([[:digit:]\.]+)', 1),// pda
			array( 'orange spv', '1006', '', 1),//pda // orange spv
			array( 'lg-lx', '1007', '',''),// LG Telecom
			array( 'lge-', '1007', '', ''),// LG Telecom
			array( 'lg/u', '1007', '', ''),// LG UXXXX series
			array( 'mot-', '1008', '', ''),//pda // motorola
			array( 'nokia', '1009', '', ''),//pda Nokia
			array( 'blazer', '1010', '', '', '74'),
			array( 'sie-', '1011', '', ''),//pda // siemens
			array( 'sec-', '1012', '', ''),//pda // SamSung
			array( 'samsung-', '1012', '', ''),//pda samsung
			array( 'sonyericsson', '1013', '', ''),
			array( 'dopod', '1014', '', ''),
			array( 'o2 xda', '1015', '', '', '71'),
			array( 'doris/', '1016', '', '', '77'),
			array( 'doris ', '1016', '', '', '77'),
			array( 'iphone', '1017', '', '', '10'),
			array( 'jig browser', '1018', '', ''),
			array( 'kddi-', '1019', '', ''),
			array( 'openwave mobile browser', '1020', '', ''),//openwave up.browser
			array( 'up.browser', '1020', 'up.browser/([[:digit:]\.]+)', 1),//openwave up.browser
			array( 'up.link/', '1020', 'up.link/([[:digit:]\.]+)', 1),//openwave up.browser
			array( 'obigo', '1021', '',''),
			array( 'au-mic/', '1021', '',''),
			array( 'playstation 3', '1022', '', '3', '78'),
			array( 'playstation', '1022', '', '1', '78'),
			array( 'sony ps2', '1022', '', '2', '78'),
			array( 'pocket pc', '1023', '', '', '71'),
			array( 'htc-', '1023', '', '', '71'),
			array( 'mspie ', '1023', '', '', '71'),
			array( 't-mobile', '1023', '', '', '71'),
			array( 'semc browser', '1024', '', ''),
			array( 'semc-browser', '1024', '', ''),
			array( 'vodafone', '1025', '', '', '71'),
			array( 'j-phone/', '1025', '', ''),
			array( 'ddipocket', '1026', '', ''),
			array( 'pdxgw/', '1026', '', ''),
			array( 'astel/', '1026', '', ''),
			array( 'eudoraweb', '1027', '', '', '74'),
			array( 'minimo', '1028', 'minimo/([[:digit:]\.]+)', 1),
			array( 'plucker/', '1029', '', ''),
			array( 'hp ipaq', '1030', '', ''),
			array( 'portalmmm/', '1031', '', ''),
			array( 'nintendo wii', '1032', '', ''),
			array( 'nitro)', '1032', '', ''),
			// 1033 : opera mini
			array( 'palmsource', '1034', '', ''),
			array( 'epoc', '1035', '', ''),
			array( 'sprint:', '1036', '', '', '71'),
			array( 'ibisbrowser:', '1037', '', '', '71'),

			);
		foreach ( $sniffs as $sniff ) {
			if ( strpos( $_ua, $sniff[0] ) === false )
				continue;
			$info['browser'] = $sniff[1];
			if ( $sniff[2] != '' ) {
				if ( preg_match( '#'.$sniff[2].'#', $_ua, $_m ) || ereg( $sniff[2], $_ua, $_m ) ) {// first preg_match after ereg
					$info['version'] = $_m[ $sniff[3] ];
				} else {
					$info['version'] = '';
				}
			} else {
				$info['version'] = $sniff[3];
			}
			if ( sizeof( $sniff ) == 5 ) {
				$info['platform'] = $sniff[4];
			}
			break;
		}
		// Safari uses a strange versioning system
		if ( $info['browser'] == '2' ) {
			$ver = explode('.', trim($info['version']));
			if ($ver > '522.12.13' || $ver == '522.12') {
				$info['version'] = '3.0.2';
			} elseif ($ver == '522.12.2') {
				$info['version'] = '3.0.1';
			} elseif ($ver > '522.8') {
				$info['version'] = '3.0';
			} elseif ($ver > '418.8') {
				$info['version'] = '2.0.4';
			} elseif ($ver > '417') {
				$info['version'] = '2.0.3';
			} elseif ($ver > '416') {
				$info['version'] = '2.0.2';
			} elseif ($ver > '412.4') {
				$info['version'] = '2.0.1';
			} elseif ($ver > '412') {
				$info['version'] = '2.0';
			} elseif ($ver > '312.4') {
				$info['version'] = '1.3.2';
			} elseif ($ver > '312.1') {
				$info['version'] = '1.3.1';
			} elseif ($ver > '311.9') {
				$info['version'] = '1.3';
			} elseif ($ver > '125.10') {
				$info['version'] = '1.2.4';
			} elseif ($ver >= '125.9') {
				$info['version'] = '1.2.3';
			} elseif ($ver >= '125.7') {
				$info['version'] = '1.2.2';
			} elseif ($ver >= '125') {
				$info['version'] = '1.2';
			} elseif ($ver > '85.7') {
				$info['version'] = '1.0.3';
			} elseif ($ver >= '85.5') {
				$info['version'] = '1.0';
			} elseif ($ver >= '85') {
				$info['version'] = '1.0b';
			} else {
				$info['version'] = '';
			}
		}
		// web downloader? or bot(crawler)?
		if ($info['browser'] == '-1') {
			$info['browser'] = $this->_determineBot($_ua);
		}
		// Mozilla browser check must after bot check. Sooooo many bots use "mozilla/xxxxx"
		// Mozilla can be used as a 'compatible' browsers
		if ( $info['browser'] == "-1" ) {
			if ( strpos( $_ua, 'mozilla/4') !== false || strpos($_ua, 'mozilla/5') !== false ) {
				if ( strpos( $_ua, 'compatible' ) === false ) {
					$info['browser'] = '1';
					ereg( 'mozilla/([[:digit:]\.]+)', $_ua, $_m );
					$info['version'] = $_m[1];
				} elseif ( strpos( $_ua, 'google desktop' ) !== false ) {
					$info['browser'] = '21';
				}
			} elseif ( (strpos($_ua, 'mozilla/5') !== false && strpos($_ua, 'compatible') === false) || strpos($_ua, 'gecko') !== false ) {
				$info['browser'] = '0';
				ereg( 'rv(:| )([[:digit:]\.]+)', $_ua, $_m );
				$info['version'] = $_m[2];
			}
		}
		// Browser version
/*		if ( $info['browser'] != '-1' && $info['browser'] != '34' && $info['version'] != '' ) {
			// Make sure we have at least .0 for a minor version
			$info['version'] = ( !ereg( '\.', $info['version'] ) ) ? $info['version'].'.0' : $info['version'];
			ereg( '^([0-9]*).(.*)$', $info['version'], $v );
			$info['majorver'] = $v[1];
			$info['minorver'] = $v[2];
		}
		if ( $info['version'] == '.0' ) {
			$info['version'] = '';
		}*/
		return $info;
	}

	function _determineBot($_ua) {// lower case user-agent only
		// exact matches
		$i_am_bot = array('mozilla', 'geturl', 'mozilla/4.0 (compatible;)');
		//downloaders
		$download_tools = array('check&get', 'check&amp;get', 'download_express', 'downloader', 'download ', 'getright', 'flashget', 'scraper', 'webcapture', 'wget', 'xget', 'webcopier', 'webzip', 'easydl', 'frontpage', 'recoder', 'fdm ');
		foreach ($download_tools as $dtool) {
			if (strpos($_ua, $dtool) !== false) {
				return '2000';
			}
		}
		//validators
		$validators = array('link valet', 'validity', 'linksmanager', 'mojoo robot', 'validator', 'link system', 'link checker', 'sitebar/', 'checker', 'deadlinkcheck');
		foreach ($validators as $val) {
			if (strpos($_ua, $val) !== false) {
				return '1999';
			}
		}
		//rss readers
		$readers = array('rss-bot', 'rss-spider', 'rss2email', 'reader', 'syndic', 'aggregat', 'subscriber', 'marsedit', 'netvisualize', 'omnipelagos', 'protopage', 'simplepie', 'touchstone', 'feed::find/');
		foreach ($readers as $read) {
			if (strpos($_ua, $read) !== false) {
				return '1998';
			}
		}
		//general bots
		// cause we filtered known user agent already, just find words widely.
		$bots = array('bot.', 'bot ', 'bot/', 'bot(', 'bot;', 'b-o-t', 'bot@', 'bot)', 'bot-', '-bot', 'robots', 'spider.', 'spider ', 'spider/', 'spider(', 'spider;', 'spider@', 'spider)', 'spider_', ' spider', '-spider', 'spider-', 'spider+', 'get/', 'get(', 'crawl', 'grabber', 'yeti', 'wisenut', 'msnbot', '1noon', 'seeker', 'java ', 'java/', 'fetch', 'collector', 'email ', 'e-mail ', 'machine', 'wisebot', 'capture', 'scrap', 'daum', 'empas', 'phantom', 'harvest', 'yandex', 'rambler', 'aport', 'naverbot', 'nhnbot', 'altavista', 'wanadoo', 'bbc.', 'alltheweb', 'looksmart', 'gigablast', 'teoma', 'clusty', 'hotbot', 'tesco', 'fantomas', 'godzilla', 'greenbrowser', 'surf', 'search', 'engine', 'spider', 'traq', 'track', 'college', 'collage', 'proxy', 'find', 'updater', 'snoop', 'digg', 'hatena', 'libw', 'tool', 'scan', 'monitor', 'activex', 'loader', 'download', 'retrieve', 'ripper', 'snatch', 'control', 'hacker', 'extractor');
		foreach ($bots as $bot) {
			if (strpos($_ua, $bot) !== false) {
				return '34';
			}
		}
		//miscellaneous bots
		$bots2 = array('ask ', 'ask.', 'fast ', 'fast-', 'szukaj', 'boitho', 'envolk', 'ingrid', '/dmoz', 'accoona', 'arachmo', 'b-o-t', 'htdig', 'archive', 'larbin', 'linkwalker', 'lwp-trivial', 'mabontland', 'mvaclient', 'nicebot', 'oegp', 'pompos', 'pycurl', 'sbider', 'scrubby', 'discovery', 'silk/', 'snappy', 'sqworm', 'updated', 'voyager', 'vyu2', 'zao', 'missigua', 'pussycat', 'psycheclone', 'shockwave', 'www-form-urlencoded', 'jakarta', 'adwords', 'grub', 'hanzoweb', 'indy library', 'murzillo', 'poe-component', 'webster', 'yoono', 'browsex', 'htmlgobble', 'httpcheck', 'httpconnect', 'httpget', 'imagelock', 'incywincy', 'informant', 'carp', 'blogpulse', 'blogssay', 'edgeio', 'pubsub', 'pulpfiction', 'youreadme', 'pluck', 'justview', 'antenna', 'walker', 'sucker', 'catch', 'webcopy', 'linker', 'worm', 'jeeves', 'javabee', 'abacho', 'agentname', 'become', 'best whois', 'bookdog', 'bravobrian bstop', 'ccubee', 'cjnetworkquality', 'conexcol.com', 'convera', 'cyberspyder link test', 'deepindex', 'depspid', 'directories', 'dlc', 'domain dossier', 'dtaagent', 'earthcom', 'earthcom', 'eventax', 'excite', 'favorg', 'favorites sweeper', 'filangy', 'galaxy', 'gazz', 'gjk_browser_check', 'hotzonu', 'http/', 'iecheck', 'iltrovatore-setaccio', 'internetlinkagent', 'internetseer', 'isilox', 'jrtwine', 'keyword density', 'linkalarm', 'linklint', 'linkman', 'lycoris desktop/lx', 'mackster', 'mail.ru', 'medhunt', 'metaspinner', 'minirank', 'mozdex', 'n-stealth', 'netpromoter', 'netvision', 'ocelli', 'octopus', 'omea pro', 'orbiter', 'pagebull', 'poirot', 'poodle', 'popdex', 'powermarks', 'rawgrunt', 'redcell', 'rlinkcheker', 'robozilla', 'sagoo', 'sensis', 'shopwiki', 'shunix', 'singing fish', 'spinne', 'sproose', 'subst?cia', 'supercleaner', 'syncmgr', 'szukacz', 'tagyu', 'tkensaku', 'twingly recon', 'ucmore', 'updatebrowscap', 'urlbase', 'vagabondo', 'vermut', 'vse link tester', 'w3c-webcon', 'walhello', 'webbug', 'weblide', 'webox', 'webtrends', 'whizbang', 'worqmada', 'wotbox', 'xml sitemaps generator', 'xyleme', 'zatka', 'zibb', 'ogeb', 'www_browser', 'blogdimension', 'gm rss panel', 'planetweb', 'jobo/', 'tycoon', 'html get', 'yodao', 'hmsebot', 'litefinder', 'darxi', 'cr4nk.ws','camelstampede', 'search project', 'rome client', 'webelixir', 'pathtraq/', 'newmoni', 'veoh-', 
		/* Bad bots */
		'3d-ftp', 'activerefresh', 'amazon.com', 'amic', 'anonymizer', 'anonymizied', 'anonymous', 'artera', 'asptear', 'autohotkey', 'autokrawl', 'automate5', 'b2w', 'backstreet browser', 'basichttp', 'beamer', 'bitbeamer', 'bits', 'bittorrent', 'blocknote.net', 'blue coat', 'bluecoat', 'brand protect', 'ce-preload', 'cerberian', 'cfnetwork', 'changedetection', 'charlotte', 'cherrypickerelite', 'chilkat', 'cobweb', 'coldfusion', 'copyright/plagiarism', 'copyrightcheck', 'custo', 'datacha0s', 'disco pump', 'dynamic+', 'easyrider', 'ebingbong', 'emeraldshield', 'ezic.com', 'fake ie', 'flatland', 'forschungsportal', 'locator', 'gamespyhttp', 'gnome-vfs', 'got-it', 'gozilla', 'hcat', 'market', 'holmes', 'hoowwwer', 'html2jpg', 'http generic', 'httperf', 'httpsession', 'httpunit', 'hyperestraier', 'eureka', 'ineturl', 'intelix', 'ninja', 'ip*works', 'ipcheck', 'kapere', 'kevin', 'lachesis', 'leechftp', 'lftp', 'linktiger', 'looq', 'lorkyll', 'mailmunky', 'mapoftheinternet', 'metatagsdir', 'foundation', 'mfc_tear_sample', 'microsoft', 'mister pix', 'moozilla', 'ms ipp', 'ms opd', 'myzilla', 'naofavicon4ie', 'net probe', 'net vampire', 'net_vampire', 'netants', 'netcarta_webmapper', 'netmechanic', 'netprospector', 'netpumper', 'netreality', 'nextthing.org', 'nozilla', 'nudelsalat', 'nutch', 'ocn-soc', 'octopodus', 'offline browsers', 'pagedown', 'pageload', 'pajaczek', 'antivirus', 'panscient', 'pavuk', 'peerfactory', 'photostickies', 'pigblock', 'pingdom', 'plinki', 'pogodak!', 'privoxy', 'proxomitron', 'prozilla', 'python', 'relevare', 'repomonkey', 'scoutabout', 'computing', 'shareaza', 'shelob', 'sherlock', 'showxml', 'siteparser', 'sitesnagger', 'sitewinder', 'steeler', 'sunrise', 'superhttp', 'tarantula', 'teleport', 'texis', 'theophrastus', 'thunderstone', 'trend micro', 'tweakmaster', 'twiceler', 'uoftdb experiment', 'url control', 'url2file', 'urlcheck', 'urly warning', 'vegas', 'versatel', 'vobsub', 'vortex', 'magnet', 'webbandit', 'webcheck', 'webclipping', 'webcorp', 'webenhancer', 'webgatherer', 'webinator', 'webminer', 'webmon', 'webpatrol', 'webreaper', 'websauger', 'quester', 'winhttp', 'www-mechanize', 'www4mail', 'wwwster', 'xenu', 'y!oasis', 'yoow!', 'zeus', '(compatible)', '(compatible):', '(compatible; ):', 'compatible; IDZap', 'compatible; ics ', 'zuzara/', 'db browse ', 'blogging to the bank', 'sot 5.1 security kol', 'rulinki.ru', 'megabrowser',
		);

		foreach ($bots2 as $bot2) {
			if (strpos($_ua, $bot2) !== false) {
				return '34';
			}
		}
		return '-1';
	}

	function _determineMacOS($_ua) {
		// Mac OS - Mac computers have different versions
		if ( strpos($_ua, 'intel mac') !== false ) {
			return '22';// Intel Mac
		} elseif ( strpos( $_ua, 'ppc mac os x' ) !== false || ereg('mac os x',$_ua) ) {
			return '10';// Mac OS X
		} elseif ( strpos( $_ua, 'powerpc' ) !== false || stristr( $_ua, 'ppc' ) !== false ) {
			return '11';// Mac PPC
		} elseif ( strpos( $_ua, '680' ) !== false || stristr( $_ua, '68k' ) !== false ) {
			return '9';// Mac 68k
		}
		return '12';// Generic Mac 
	}

	function _determineWinOS($_ua, $_m) {// lower case user-agent only
		$version = trim( $_m[2] );
		if ( strpos($_ua, 'windows nt 5.0') !== false || strpos($_ua,'windows 2000') !== false ) {
			return '0';// Windows 2000
		} elseif ( strpos($_ua, 'windows nt 5.1') !== false || strpos($_ua,'windows xp') !== false ) {
			return '1';// Windows XP
		} elseif ( strpos($_ua, 'windows nt 5.2') !== false || strpos($_ua,'windows 2003') !== false ) {
			if (strpos($_ua, 'win64') !== false)
				return '25';// Windows XP 64bit
			else
				return '2';// Windows 2003
		} elseif ( strpos($_ua, 'windows nt 6.0') !== false || strpos($_ua,'windows vista') !== false ) {
			return '3';// Windows Vista
		} elseif ( strpos($_ua, 'windows nt 4.0') !== false || strpos($_ua,'winnt4.0') !== false ) {
			return '27';// Windows NT 4.0
		} elseif ( strpos( $version, 'nt' ) !== false ) {
			return '4';// Windows NT
		} elseif (strpos($_ua, 'win 9x 4.90') !== false || strpos($_ua, 'windows me') !== false) {
			return '26';// Windows ME
		} elseif (strpos($_ua, 'windows 98') !== false || strpos($_ua, 'win98') !== false ) {
			return '5';// Windows 98
		} elseif ( strpos($version, '95') !== false || strpos($_ua, 'win95') !== false ) {
			return '6';// Windows 95
		} elseif ( strpos($version, '3.1') !== false || strpos($version, '16') !== false || strpos($version, '16bit') !== false ) {
			return '7';// Windows 3.1
		} elseif ( strpos($_ua, 'windows ce') !== false ) {
			return $this->_determineMobileOS($_ua);
		}
		return '8';// No one matched, it's a generic Windows version
	}

	// powered by firestats(http://firestats.cc)
	function _determineUnixOS($_ua) {// lower case user-agent only
		if ( strpos($_ua, 'linux') !== false ) {
			if ( strpos($_ua, 'debian') !== false ) {
				return '29';//Debian GNU/Linux
			} elseif ( strpos($_ua, 'Mandrake') !== false ) {
				return '30';//Mandrake Linux
			} elseif ( strpos($_ua, 'SuSE') !== false ) {
				return '31';//SuSE Linux
			} elseif ( strpos($_ua, 'Novell') !== false ) {
				return '32';//Novell Linux
			} elseif ( strpos($_ua, 'Ubuntu') !== false ) {
				return '33';//Ubuntu Linux
			} elseif ( preg_match('#red ?hat#i', $_ua) ) {
				return '34';//RedHat Linux
			} elseif ( strpos($_ua, 'Gentoo') !== false ) {
				return '35';//Gentoo Linux
			} elseif ( strpos($_ua, 'Fedora') !== false ) {
				return '36';//Fedora Linux
			} elseif ( strpos($_ua, 'MEPIS') !== false ) {
				return '37';//MEPIS Linux
			} elseif ( strpos($_ua, 'Knoppix') !== false ) {
				return '38';//Knoppix Linux
			} elseif ( strpos($_ua, 'Slackware') !== false ) {
				return '39';//Slackware Linux
			} elseif ( strpos($_ua, 'Xandros') !== false ) {
				return '40';//Xandros Linux
			} elseif ( strpos($_ua, 'Kanotix') !== false ) {
				return '41';//Kanotix Linux
			}
			return '20'; // Linux generic version
		} elseif ( preg_match( '/x11|inux/i', $_ua ) ) {
			return '20';// Linux, generic
		} // Others...
		elseif ( strpos($_ua, 'freebsd') !== false ) {
			return '21';//FreeBSD
		} elseif ( strpos($_ua, 'netbsd') !== false ) {
			return '42';//NetBSD
		} elseif ( strpos($_ua, 'openbsd') !== false ) {
			return '43';//OpenBSD
		} elseif ( preg_match( '/(irix)[\s]*([0-9]*)/i', $_ua ) ) {
			return '15';// Unix Irix (SGI Irix)
		} elseif ( preg_match( '/(sun|i86)[os\s]*([0-9]*)/i', $_ua ) ) {
			return '14';// Sun OS (Solaris)
		} elseif ( preg_match( '/(hp-ux)[\s]*([0-9]*)/i', $_ua ) ) {
			return '16';// HP Unix
		} elseif ( strpos($_ua, 'unix') !== false ) {
			return '44';//UNIX generic version
		} elseif ( preg_match( '/os\/2|ibm-webexplorer/i', $_ua ) ) {
			return '13';// OS2, there is still someone out there?
		} elseif ( preg_match( '/aix([0-9]*)/i', $_ua ) ) {
			return '17';// Aix
		} elseif ( preg_match( '/dec|osfl|alphaserver|ultrix|alphastation/i', $_ua ) ) {
			return '18';// Dec Alpha
		} elseif ( preg_match( '/vax|openvms/i', $_ua ) ) {
			return '19';// Vax, are browsing a blog with a VAX computer?!
		} elseif ( preg_match( '/(free)?(bsd)/i', $_ua ) ) {
			return '21';// Free BSD
		} elseif ( strpos($_ua, 'amigaos') !==false ) {
			return '23';// AmigaOS (maybe mobile)
		} elseif ( strpos($_ua, 'commodore 64') !== false ) {
			return '24';// C-64	(server)
		} elseif ( strpos($_ua, 'risc os') !== false ) {
			return '28';// RISC OS
		} elseif ( strpos($_ua, 'beos') !== false ) {
			return '45';// BeOS
		}
		return '-1';
	}

	// powered by firestats(http://firestats.cc)
	function _determineMobileOS( $_ua ) {
		if (preg_match('#palmos#i', $_ua)) {
			return '74';// Palm OS
		} elseif (preg_match('#windows ce#i', $_ua)) {
			$platform = '71';// Generic Windows CE
			if ( strpos($_ua, 'ppc') !== false )
				$platform = '72';// Microsoft PocketPC
			if ( strpos($ua, 'smartphone') !== false )
				$platform = '73'; // Microsoft Smartphone
			return $platform;
		} elseif (preg_match('#qtembedded#i', $_ua)) {
			return '75';// Qtopia
		} elseif (preg_match('#zaurus#i', $_ua)) {
			return '76';// Linux Zaurus
		} elseif (preg_match('#symbian#i', $_ua)) {
			return '77';// Symbian OS
		} elseif (preg_match('#playstation#i', $_ua)) {
			return '78';// Linux WAP
		}
		return '-1';
	}

	function _determineVisit( $_remote_ip, $_browser, $_version, $_platform, $_table, $time=0 ) {
		global $wpdb, $SlimCfg;
		if (!$time)
			$time = time();
		$query = "SELECT `visit` FROM `".$_table."`
			WHERE `remote_ip`='".$SlimCfg->my_esc( $_remote_ip )."' 
				AND `browser`='".$SlimCfg->my_esc( $_browser )."' 
				AND `version`='".$SlimCfg->my_esc( $_version )."' 
				AND `platform`='".$SlimCfg->my_esc( $_platform )."'
				AND `dt` >= '".( $time - 1800 )."'
			ORDER BY `dt` LIMIT 1 ";
		$row = $wpdb->get_row( $query );
		if ($row) {
			return (int)$row->visit;
		}
		$query = "SELECT MAX(`visit`) AS `visit` FROM `".$_table."` ";
		$row = $wpdb->get_row( $query );
		if ($row) {
			return ((int)$row->visit + 1);
		}
		return 1;
	}

	// borrowed from Bad Behavior's match_cidr(); (http://www.homelandstupidity.us/software/bad-behavior/)
	// IP address ranges use the CIDR format.
	// CIDR : http://en.wikipedia.org/wiki/CIDR, http://member.dnsstuff.com/rc/index.php?option=com_content&task=view&id=24&Itemid=5
	function _checkIgnoreList($ip) {
		global $SlimCfg;
			$isIgnored = false;
			if (!empty($SlimCfg->exclude['ignore_ip'])) {
				$lists = explode(';', $SlimCfg->exclude['ignore_ip']);
				foreach($lists as $ignore) {
					$ignore = trim($ignore);
					list($addr, $mask) = explode('/', $ignore);
					$mask = 0xffffffff << (32 - $mask);
					$output = ((ip2long($ip) & $mask) == (ip2long($addr) & $mask));
					if ($output) {
						$isIgnored = true;
						break;
					}
				}
			}
		return $isIgnored;
	}

	// borrowd form firestats(http://firestats.cc)
	function get_remote_addr() {
		// obtain the X-Forwarded-For value.
		$headers = function_exists('getallheaders') ? getallheaders() : null;
		$xf = isset($headers['X-Forwarded-For']) ? $headers['X-Forwarded-For'] : "";
		if (empty($xf)) {
			$xf = isset($GLOBALS['FS_X-Forwarded-For']) ? $GLOBALS['FS_X-Forwarded-For'] : "";
		}
		$xf .= (empty($xf)) ? '' : ',';
		$xf .= $_SERVER["REMOTE_ADDR"];

		$fwd = explode(",",$xf);
		foreach($fwd as $ip) {
			$ip = trim($ip);
			if ($this->is_public_ip($ip)) 
				return $ip;
		}

		// if we got this far and still didn't find a public ip, just use the first ip address in the chain.
		return $fwd[0];
	}

	function is_public_ip($ip) {
		$long = ip2long($ip);
		if ( ($long >= 167772160 && $long <= 184549375) ||
			($long >= -1408237568 && $long <= -1407188993) ||
			($long >= -1062731776 && $long <= -1062666241) ||
			($long >= 2130706432 && $long <= 2147483647) || 
			$long == -1) {
			return false;
		}
		return true;
		// 167772160 - 10.0.0.0
		// 184549375 - 10.255.255.255
		//
		// -1408237568 - 172.16.0.0
		// -1407188993 - 172.31.255.255
		//
		// -1062731776 - 192.168.0.0
		// -1062666241 - 192.168.255.255
		//
		// -1442971648 - 169.254.0.0
		// -1442906113 - 169.254.255.255
		//
		// 2130706432 - 127.0.0.0
		// 2147483647 - 127.255.255.255 (32 bit integer limit!!!)
		//
		// -1 is also b0rked
	}

	function find_matches($list, $text) {
		foreach($list as $word) {
			$word = trim($word);
			if (empty($word)) { continue; }
			$word = preg_quote($word, '#');
			$word = (strpos($word, '\^') === 0) ? '^'.substr($word, 2):$word;
			$word = (strpos($word, '\$') === (strlen($word) -2)) ? substr($word, 0, strlen($word)-2).'$':$word;
			$word = str_replace('\*', '\S*?', $word);
			if (preg_match('#'.$word.'#i', $text))
				return true;
		}
		return false;
	}

	function is_bot($b_id, $ua, $force=array()) {
		global $SlimCfg;
		if (!empty($SlimCfg->exclude['white_ua'])) {
			$w_list = explode("\n", $SlimCfg->exclude['white_ua']);
			if ($this->find_matches($w_list, $ua))
				return false;
		}
		if ($b_id == '34' || $b_id == '2000' || empty($ua))
			return true;
		if ( ($force['bots'] || $SlimCfg->exclude['ig_bots']) && in_array($b_id, $SlimCfg->bot_array['bots']) )
			return true;
		if ( ($force['feeds'] || $SlimCfg->exclude['ig_feeds']) && in_array($b_id, $SlimCfg->bot_array['feeds']) )
			return true;
		if ( ($force['validators'] || $SlimCfg->exclude['ig_validators']) && in_array($b_id, $SlimCfg->bot_array['validators']) )
			return true;
		if ( ($force['tools'] || $SlimCfg->exclude['ig_tools']) && in_array($b_id, $SlimCfg->bot_array['tools']) )
			return true;
		if ( !empty($SlimCfg->exclude['black_ua']) ) {
			$b_list = explode("\n", $SlimCfg->exclude['black_ua']);
			if ($this->find_matches($b_list, $ua))
				return true;
		}
		return false;
	}

	function tostring($text) {
		if (function_exists('mb_convert_encoding'))
			return mb_convert_encoding(chr(hexdec(substr($text[1], 2, 2))).chr(hexdec(substr($text[1], 0, 2))), 'UTF-8', 'UTF-16LE');
		elseif (function_exists('iconv'))
			return iconv('UTF-16LE', 'UTF-8', chr(hexdec(substr($text[1], 2, 2))).chr(hexdec(substr($text[1], 0, 2))));
		else return $text;
	}

	function urlutfchr($text) {
		return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', array(&$this, 'tostring'), $text));
	}

	function get_out_now() { exit; }

	function remove_shutdown_hooks($location='', $status='' ) {
		add_action( 'shutdown', array(&$this, 'get_out_now'), -10 );
		return $location;
	}

	function feed_track_footer() {
		if ( is_feed() ) {
			// Disable track when redirecting
			add_filter('wp_redirect', array(&$this, 'remove_shutdown_hooks'));
			// try to track standard resources
			add_action( 'shutdown', array( &$this, 'slimtrack' ) );
		}
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new SSTrack();
		}
		return $instance[0];
	}

}
// end of SSTrack

if (!isset($SSTrack))
	$SSTrack =& SSTrack::get_instance();
?>