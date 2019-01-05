<?php
/*
Plugin Name: wp-tmkm-amazon
Plugin URI: http://tomokame.moo.jp/
Description: ASIN を指定して Amazon から個別商品の情報を取出します。BOOKS, DVD, CD は詳細情報を取り出せます。
Author: ともかめ
Version: 1.1ja
Author URI: http://tomokame.moo.jp/
Special Thanks: Keith Devens.com (http://keithdevens.com/software/phpxml)
Special Thanks: websitepublisher.net (http://www.websitepublisher.net/article/aws-php/)
Special Thanks: hiromasa.zone :o) (http://hiromasa.zone.ne.jp/)
Special Thanks: PEAR :: Package :: Cache_Lite (http://pear.php.net/package/Cache_Lite)
*/

/********** Notes
 # ECS4.0 に対応しています。
 # PHP4.x で動作します。ただし Keith Devens.com の PHP XML Library が必要（ 同梱しています ）。
 # [tmkm-amazon]ASIN[/tmkm-amazon] または <?php tmkm_amazon_view('ASIN'); ?> という記述で動作します。
 # LGPL で提供されている Lite.php および Open Source License で提供されている xml.php を同梱しています。
 # 記事およびページ投稿／編集画面での Amazon 検索が可能です。
**********/

/********** Usage
 # 1. ダウンロードした zip ファイルを解凍します。
 # 2. wp-tmkm-amazon フォルダを wp-content/plugins フォルダに転送します。
 # 3. 管理画面から wp-tmkm-amazon を有効化します。
 # 4. 管理画面にある「設定」画面内の「Wp_Tmkm_Amazon」メニューで、ご自分のアソシエイト ID を入力します。
 # 5. 各テーマの php ファイル、もしくは記事本文中に以下を記載します。
 #### PHP 関数として呼び出す場合	...	<?php tmkm_amazon_view('ASIN'); ?>　：テーマファイルに記述
 #### 記事本文中にコードを書く場合	...	[tmkm-amazon]ASIN[/tmkm-amazon]
 
 ## 記事本文中で PHP コードを実行できるプラグインを導入していれば、PHP 関数として呼び出すこともできます。
 ## 書籍の場合、ASIN に 10 桁および 13 桁の ISBN を使用できます。
 ## 同梱の amazon_noimg.png と amazon_noimg_small.png を差し替えれば、商品画像がないときの代替画像を好きなものにできます。
**********/

/******************************************************************************
 * THIS FILE IS CALLED ONLY.
 *****************************************************************************/

if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'wp-tmkm-amazon.php' ) {
	die();
}
$tmkm_plugin_directory = get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__));
$tmkm_amazon_php = '/wp-tmkm-amazon.php';
$tmkm_amazon_search_php = '/wp-tmkm-amazon-search.php';

$tmkm_amazon_settings = get_option('wp_tmkm_admin_options');
$tmkm_amazon_config = array(
	'AssociatesID'	=> 'tomokametei-22',
	'DevToken'		=> '10J7BBWBHFNGXM612JR2',
	'Version'		=> '2007-01-15',
	'JPendpoint'	=> 'http://ecs.amazonaws.jp/onca/xml?Service=AWSECommerceService',
	'OperationLookup'	=> 'ItemLookup',
	'OperationSearch'	=> 'ItemSearch',
);


/******************************************************************************
 * wp-tmkm-amazon-function : amazon ECS search engine
 * WpBabel WordPress Plugin Framework Define
 *****************************************************************************/
include_once('wp-tmkm-amazon-function.php');


/******************************************************************************
 * 管理画面からオプションを設定
 *****************************************************************************/
class WpTmkmAmazonAdmin {
	var $tmkm_amazon_settings, $tmkm_amazon_options;

	function WpTmkmAmazonAdmin() {
		if ( !get_option('wp_tmkm_admin_options') ){
			// create default options
			$tmkm_amazon_options['associatesid'] = 'tomokametei-22';

			update_option('wp_tmkm_admin_options', $tmkm_amazon_options);
		}
	}

	function tmkm_amazon_options_page() {
		global $tmkm_amazon_settings;
		$tmkm_amazon_options = get_option('wp_tmkm_admin_options');

		$tmkm_amazon_admin_html =
			'<div class="wrap" id="footnote-options">' . "\n".
			'<h2>Wp-Tmkm-Amazon プラグイン設定</h2>' . "\n";

		if ( $_POST['action'] ){
			$tmkm_amazon_admin_html .= '<div class="updated"><p><strong>設定を保存しました。</strong></p></div>'; 
		}

		$tmkm_amazon_admin_html .=
			'<form method="post" action="' . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . '">' .
			'<input type="hidden" name="action" value="save_options" />' .
			'<table class="form-table">' .
			'<tr>' .
			'<th>あなたのアソシエイト ID</th>' .
			'<td><input type="text" name="associatesid" value="' . $tmkm_amazon_options['associatesid'] . '"  /></td>' .
			'</tr>' .
			'</table>' .
			'<p class="submit"><input type="submit" name="Submit" value="設定を保存する &raquo;" /></p>' .
			'</form>' .
			'</div>';

			echo $tmkm_amazon_admin_html;
	}

	function tmkm_amazon_add_options() {
		global $tmkm_amazon_php;
		// Add a new menu under Options:
		add_options_page(
			'Wp_Tmkm_Amazon',
			'Wp_Tmkm_Amazon',
			8,
			$tmkm_amazon_php,
			array(&$this, 'tmkm_amazon_options_page')
		);
	}
	
	function tmkm_amazon_save_options() {
		// create array
		$tmkm_amazon_options['associatesid'] = $_POST['associatesid'];
		
		update_option('wp_tmkm_admin_options', $tmkm_amazon_options);
		$options_saved = true;
	}

}


/******************************************************************************
 * 記事本文中に Amazon から取得した商品情報を表示
 *****************************************************************************/
class WpTmkmAmazonView {

	/**
	 * The Constructor
	 * 
	 * @param none
	 * @return none
	 */

	function WpTmkmAmazonView() {

		$this->makedetailview = & new MakeAmazonHtml();

	}

	/**
	 * 記事本文中のコードを個別商品表示 HTML に置き換える
	 * 
	 * @param $content
	 * @return $transformedstring
	 */
	function _replacestrings($content) { // 記事本文中の呼び出しコードを変換

		global $post;

//		$poststring = '/\[tmkm-amazon type\=([a-z]+)\]([a-zA-Z0-9,]+)\[\/tmkm-amazon\]/';
		$poststring = '/\[tmkm-amazon\]([a-zA-Z0-9,]+)\[\/tmkm-amazon\]/';
		$transformedstring = $content;
		if( preg_match_all($poststring, $content, $regs1) ) {

			for ($i=0; $i<count($regs1[0]); $i++) {
//				$type = $regs1[1][$i];
				$SearchString = $regs1[1][$i];

//				$display = $this->makedetailview->format_amazon( $SearchString, $type );
				$display = $this->makedetailview->format_amazon( $SearchString, 'detail' );

				if( ereg( $SearchString, $regs1[0][$i], $str ) ) { // ASINコードの置換
					$transformedstring = str_replace($regs1[0][$i], $display, $transformedstring);
				}
			}
		}

		$transformedstring = str_replace('<p><div id="tmkm-amazon-view">', '<div id="tmkm-amazon-view">', $transformedstring);
		$transformedstring = str_replace('<hr class="tmkm-amazon-clear" /></div></p>', '<hr class="tmkm-amazon-clear" /></div>', $transformedstring);

		return $transformedstring;

	}


	/**
	 * PHP 関数として Amazon の個別商品 HTML を呼び出す
	 * 
	 * @param $SearchString ( ASIN )
	 * @param $type ( book / dvd / cd )
	 * @return echo $display ( HTML )
	 */
	function tmkm_amazon_view($SearchString) { // PHP テ−マファイル中に記述する関数
		$display = $this->makedetailview->format_amazon( $SearchString, 'detail' );
		echo $display;
	}


}


/******************************************************************************
 * PHP を記述した箇所にブログの記事で参照している Amazon の商品一覧を表示
 *****************************************************************************/
class WpTmkmAmazonList {

	var $sql;
	var $ordersql;

	function WpTmkmAmazonList() {

		$this->makedetailview = & new MakeAmazonHtml();
		$this->sql = '';
		$this->ordersql = '';

	}

	function tmkm_amazon_list( $orderby = 'post_id', $order = 'asc' ) {
	
	    global $wpdb, $tmkm_amazon_settings;

		switch( $orderby ) {
			case post_id: $this->ordersql = "$wpdb->posts.ID " . $order; break;
			case post_title: $this->ordersql = "$wpdb->posts.post_title " . $order; break;
			case post_date: $this->ordersql = "$wpdb->posts.post_date " . $order; break;
			case modified_date: $this->ordersql = "$wpdb->posts.post_modified " . $order; break;
		}

	    $this->sql =
			"SELECT " .
				"ID, post_title, " .
				"DATE_FORMAT(post_date, '%Y/%m/%d') as mdate, " .
				"meta_key, meta_value " .
			"FROM " .
				"$wpdb->posts, $wpdb->postmeta " .
			"WHERE " .
				"$wpdb->posts.ID = $wpdb->postmeta.post_id AND " .
				"$wpdb->posts.post_date <= NOW() AND " .
				"$wpdb->posts.post_status = 'publish' AND " .
				"$wpdb->postmeta.meta_key = 'tmkm-amazon' " .
			"ORDER BY " .
				$this->ordersql;

	    $this->countsql =
			"SELECT " .
			"COUNT(*) " .
			"FROM " .
				"$wpdb->posts, $wpdb->postmeta " .
			"WHERE " .
				"$wpdb->posts.ID = $wpdb->postmeta.post_id AND " .
				"$wpdb->posts.post_date <= NOW() AND " .
				"$wpdb->posts.post_status = 'publish' AND " .
				"$wpdb->postmeta.meta_key = 'tmkm-amazon' ";

	    $PostRetainAsin = $wpdb->get_results($this->sql);
	    $postcount = $wpdb->get_var($this->countsql);
	    $itemnum = '1';
//	    $itemperpage = $tmkm_amazon_settings['itemperpage'];

	    if( $PostRetainAsin ) {
	    	$heredoc = '<div id="amazonlist">' . "\n";
	    	foreach( $PostRetainAsin as $asinlist ) {
	    		$asins = explode(',',$asinlist->meta_value);
	    		foreach( $asins as $asin ) {
	    			$permalink = get_permalink($asinlist->ID);
	    			$display = $this->makedetailview->format_amazon( $asin, 'list' );
	    			$pagenum = (int)( $itemnum/6 );

					$heredoc .=
						'<dl>' . "\n" .
						"<dt><a href=\"$permalink\">$asinlist->post_title</a></dt>\n" .
	    				'<dd>' . "\n" .
						"<p class=\"p-date\">記事投稿日：$asinlist->mdate</p>\n" .
	    				$display . "\n" .
//	    				"<p>$itemnum / $postcount</p>\n" . 
	    				'</dd>' . "\n" .
	    				'</dl>' . "\n";
	    			if( $itemnum == $pagenum*5 ) {
	    				$heredoc .= '<!--nextpage-->' . "\n";
	    			}
	    			$itemnum ++;
	    		}
	    	}
	    	$heredoc .= '</div>' . "\n";
	    } else {
	    	$heredoc = "<p>まだブログで書籍が紹介されていません。</p>\n";
	    }
		echo $heredoc;
	}

}

/******************************************************************************
 * Amazon ECS から取得した XML から HTML を生成
 *****************************************************************************/
class MakeAmazonHtml {
	var $plugin_path;
	var $mediumimgfile;
	var $smallimgfile;
	var $associatesid;

	function MakeAmazonHtml() {

		$this->amazonparse = & new GetAmazonXmlParse();
		$this->generalfunclib = & new generalFuncLibrary();

	}

	/**
	 * Amazon 商品の HTML ソースを生成
	 * @param $SearchString ( ASIN )
	 * @param $mediatype ( book / dvd / cd )
	 * @return $output ( HTML )
	 */
	function format_amazon( $SearchString, $formattype ) {
		global $tmkm_amazon_settings, $tmkm_plugin_directory;

        $this->mediumimgfile = '/amazon_noimg.png';
        $this->smallimgfile = '/amazon_noimg_small.png';
        $associatesid = $tmkm_amazon_settings['associatesid'];

		$output = '';
		if( strlen( $SearchString ) == 13 ){
			$SearchString = $this->generalfunclib->calc_chkdgt_isbn10( substr( $SearchString, 3, 9 ) );
		}

		// --- Call Amazon XML function ---
		if( $formattype == 'list' ) {
			$AmazonXml = $this->amazonparse->getamazonxml( $associatesid, $SearchString, 'single', '', 'Images,Small' );
		} else {
			$AmazonXml = $this->amazonparse->getamazonxml( $associatesid, $SearchString, 'single', '', 'Medium,Offers' );
		}

//		DEBUG
/*		echo "<pre>\n";
		print_r($AmazonXml);
		echo "</pre>\n";
*/

		// --- Get results of the Amazon function ---
		if( false === $AmazonXml ){  // Amazon function was returned false, so AWS is down

			$output = '<p>アマゾンのサーバでエラーが起こっているかもしれません。一度ページを再読み込みしてみてください。</p>';

		}else{ // Amazon function returned XML data

			$status = $AmazonXml["ItemLookupResponse"]["Items"]["Request"];

			if( $status["IsValid"] == 'False' ){ // Request is invalid

				$output = '<p>与えられたリクエストが正しくありません</p>';

			}else{ // results were found, so display the products
	
		// --- Display the product data returned from the XML ---
				$item = $AmazonXml["ItemLookupResponse"]["Items"]["Item"];

				$mediumimage = $this->amazonparse->get_goods_image($item,'medium');
				if( $mediumimage == '' ) { $mediumimage = $tmkm_plugin_directory . $this->mediumimgfile; }
				$smallimage = $this->amazonparse->get_goods_image($item,'small');
				if( $smallimage == '' ) { $smallimage = $tmkm_plugin_directory . $this->smallimgfile; }

				$url = $this->amazonparse->get_amazon_text($item,'url');
				$Title = $this->amazonparse->get_amazon_text($item,'title');
				$Manufacturer = $this->amazonparse->get_amazon_text($item,'manufacturer');
				$Binding = $this->amazonparse->get_amazon_text($item,'binding');
				$EANcode = $this->amazonparse->get_amazon_text($item,'eancode');

				$Price = $this->amazonparse->get_amazon_text($item,'price');
				$LowestUsedPrice = $this->amazonparse->get_amazon_text($item,'lowestusedprice');
				$ASIN = $this->amazonparse->get_amazon_text($item,'asincode');
				$ReleaseDate = $this->amazonparse->get_amazon_text($item,'releasedate');

				$ProductGroup = $this->amazonparse->get_amazon_text($item,'productgroup');

				if( $LowestUsedPrice != '' ) {
					$usedpricememo = ' ( 中古価格 '. $LowestUsedPrice . ' より )';
				} else {
					$usedpricememo = '';
				}
				$output = '<div class="tmkm-amazon-view">' . "\n";
				
				if( $formattype == 'detail' ) {
					if( $ProductGroup == 'Book' ){
	
						$Role = $this->amazonparse->get_amazon_text($item,'role');
						$Pages = $this->amazonparse->get_amazon_text($item,'pages');
						$Author = $this->amazonparse->get_amazon_text($item,'author');
						$ISBN10 = $this->amazonparse->get_amazon_text($item,'isbn10');
						$PublicationDate = $this->amazonparse->get_amazon_text($item,'publicationdate');
	
						$output .= "\t" . '<p><a href="'.$url.'"><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
						$output .= "\t" . '<p><a href="'.$url.'">' . $Title . '</a></p>' . "\n";
						if( $Author != "" ) {
							$output .= "\t" . "<p><em>著者／訳者：</em>";
							if( count($Author) == 1 ) {
								$output .= $Author; 
							} else {
								foreach($Author as $auth){ $output .= $auth.' '; }
							}
							$output .= '</p>' . "\n";
						}
						$output .= "\t" . "<p><em>出版社：</em>$Manufacturer( $PublicationDate )</p>" . "\n";
						$output .= "\t" . "<p><em>定価：</em>$Price</p>" . "\n";
						$output .= "\t" . "<p>$Binding ( $Pages ページ )</p>" . "\n";
						$output .= "\t" . "<p>ISBN-10 : $ISBN10</p>" . "\n";
						$output .= "\t" . "<p>ISBN-13 : $EANcode</p>" . "\n";
	
					} elseif( $ProductGroup == 'DVD' ) {
	
						$RunningTime = $this->amazonparse->get_amazon_text($item,'runningtime');
						$numofdisc = $this->amazonparse->get_amazon_text($item,'numofdisc');
	
						$output .= "\t" . '<p><a href="'.$url.'"><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
						$output .= "\t" . '<p><a href="'.$url.'">' . $Title . '</a></p>' . "\n";
						$output .= "\t" . "<p><em>販売元：</em>$Manufacturer( $ReleaseDate )</p>" . "\n";
						$output .= "\t" . "<p><em>定価：</em>$Price$usedpricememo</p>" . "\n";
						$output .= "\t" . "<p><em>時間：</em>$RunningTime 分</p>" . "\n";
						$output .= "\t" . "<p>$numofdisc" . " 枚組 ( " . $Binding . " )</p>" . "\n";
	
					} elseif( $ProductGroup == 'Music' ) {
	
						$Format = $this->amazonparse->get_amazon_text($item,'format');
						$Artist = $this->amazonparse->get_amazon_text($item,'artist');
	
						$output .= "\t" . '<p><a href="'.$url.'"><img src="' . $smallimage . '" border="0" alt="" /></a></p>' . "\n";
						$output .= "\t" . '<p><a href="'.$url.'">' . $Title . ' / ' . $Artist . "</a> / $Binding ( $ProductGroup )</p>" . "\n";
						$output .= "\t" . "<p>$Manufacturer</p>" . "\n";
						$output .= "\t" . "<p><em>定価：</em>$Price$usedpricememo</p>" . "\n";
						$output .= "\t" . "<p><em>リリース: </em>$ReleaseDate</p>" . "\n";
	
					} else {
						$output .= "\t" . '<p><a href="'.$url.'"><img src="' . $smallimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
						$output .= "\t" . '<p><a href="'.$url.'">' . $Title . '</a></p>' . "\n";
						if( $Price != '' ) {
							$output .= "\t" . "<p><em>定価：</em>$Price</p>" . "\n";
						} elseif( $LowestUsedPrice != '' ) {
							$output .= "\t" . "<p><em>中古価格: </wm>$LowestUsedPrice より</p>" . "\n";
						}
						if( $ReleaseDate != '' ) { $output .= "\t" . "<p><em>発売日：</em>$ReleaseDate</p>" . "\n"; }
						$output .= "\t" . "<p>カテゴリ: $Binding</p>\n";
					}
					$output .= '<hr class="tmkm-amazon-clear" /></div>';

				} elseif( $formattype == 'list' ) {
					$output = "\t" . '<p><a href="'.$url.'"><img src="' . $smallimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
					$output .= "\t" . '<p><a href="'.$url.'">' . $Title . '</a></p>' . "\n";
				}
			}
		}
		return $output;
	}

}

/******************************************************************************
 * 記事投稿画面に検索フォームを追加
 *****************************************************************************/
class WpTmkmAmazonFind {

	var $searchphp_path;

	function InsertSearchForm() {

		global $wpdb, $tmkm_amazon_settings, $tmkm_plugin_directory, $tmkm_amazon_search_php;

		$associatesid = $tmkm_amazon_settings['associatesid'];
        $this->searchphp_path = $tmkm_plugin_directory . $tmkm_amazon_search_php;
        $this->searchphp_path .= '?AID=' . $associatesid;

		$search_action	= '<form onsubmit="doItemSearchNew(); return false;">';
		$search_option	= '<p style="display: inline;"><a id="searchpagetop">Amazon 検索</a></p>&nbsp;' .
				'<select id="g">' .
				'	<option value="Blended">Amazon.co.jp</option>' .
				'	<option value="Books">和書</option>' .
				'	<option value="ForeignBooks">洋書</option>' .
				'	<option value="Music">音楽</option>' .
				'	<option value="MusicTracks">サウンドトラック</option>' .
				'	<option value="Classical">クラシック</option>' .
				'	<option value="DVD">DVD</option>' .
				'	<option value="VideoGames">ゲーム</option>' .
				'	<option value="Electronics">エレクトロニクス</option>' .
				'	<option value="Software">ソフトウェア</option>' .
				'	<option value="Kitchen">ホーム &amp; キッチン</option>' .
				'	<option value="Toys">おもちゃ・ホビー</option>' .
				'	<option value="SportingGoods">スポーツ</option>' .
				'	<option value="HealthPersonalCare">ヘルス &amp; ビューティー</option>' .
				'</select>';
		$search_field_d	= '<input type="text" name="q" size="24" id="q" />' .
				'<input type="hidden" name="i" value="1" id="i" />' .
				'<input type="hidden" name="currentnode" value="node-0" id="currentnode" />' .
				'<input type="submit" value="検索" class="btn" />';

		?>
		<div id="wptmkmamazon" class="postbox if-js-closed">
		<h3>wp-tmkm-amazon</h3>
		<div class="inside">
<?php echo $search_action . $search_option . $search_field_d; ?>
			<div id="message"><p>プルダウン・メニューからストアを選択した上で、キーワードを入力し、<em>検索ボタン</em>または<em><kbd>Enter</kbd></em>を押してください。</p></div>

			<div id="results"></div>
			<p>Copyright (c) 2005-2006 Kyo Nagashima &lt;kyo&#64;hail2u.net&gt;</p>
		</div>
		</div>
		<?php 

	}
}

if(!function_exists('tmkm_amazon_view')) {
	function tmkm_amazon_view($Searchstring) {
		global $wpTmkmAmazonView;
		$wpTmkmAmazonView->tmkm_amazon_view($Searchstring);
		
	}
}

if(!function_exists('tmkm_amazon_list')) {
	function tmkm_amazon_list($orderby,$order) {
		global $wpTmkmAmazonList;
		$wpTmkmAmazonList->tmkm_amazon_List($orderby,$order);
		
	}
}

function add_tmkmamazon_stylesheet(){
	global $tmkm_plugin_directory;
	?>
		<link rel="stylesheet" href="<?php echo $tmkm_plugin_directory; ?>/tmkm-amazon.css" type="text/css" media="screen" />
	<?php
}

function add_tmkmamazon_searchjs() {
	global $tmkm_plugin_directory, $tmkm_amazon_config;


?>

<!-- Start Of Script Generated By WP-Tmkm-Amazon -->
<meta http-equiv="Content-Script-Type" content="text/javascript" />

<script type="text/javascript" src="<?php echo $tmkm_plugin_directory; ?>/jsr.js"></script>
<script type="text/javascript">
var xslUrlItemSearch       = '<?php echo $tmkm_plugin_directory; ?>/itemsearch.xsl';
var xslUrlSimilarityLookup = '<?php echo $tmkm_plugin_directory; ?>/similaritylookup.xsl';

var AccessKeyId  = '<?php echo $tmkm_amazon_config['DevToken']; ?>';
var AssociateTag = '<?php echo $tmkm_amazon_config['AssociatesID']; ?>';

var oJsr = new jsonScriptRequest();
var data = new Object();

function init() {
  if (location.hash.match(/^#(\w+):(.+?):?([1-9]\d*)?$/)) {
    var g = RegExp.$1 ? decodeURIComponent(RegExp.$1) : 'Blended';
    var q = RegExp.$2 ? decodeURIComponent(RegExp.$2) : '';
    var i = RegExp.$3 ? RegExp.$3                     : 1;
    $('g').value = g;
    $('q').value = q;
    $('i').value = i;
    doItemSearch();
  }

  $('q').focus();
  $('q').select();
}

function doItemSearchNew() {
  $('i').value = 1;
  doItemSearch();
}

function doItemSearchPrevious() {
  var i = $F('i');
  i--;
  $('i').value = i;
  doItemSearch();
}

function doItemSearchNext() {
  var i = $F('i');
  i++;
  $('i').value = i;
  doItemSearch();
}

function doItemSearch() {
  window.scrollTo(0, 0);
  $('message').innerHTML = '<p><em>検索中･･･<em></p>';
  var g = encodeURIComponent($F('g'));
  var q = encodeURIComponent($F('q'));
  var i = $F('i');
  location.hash = '#' + g + ':' + q;
  if (i > 1) location.hash = location.hash + ':' + i;
  q = '<?php echo $tmkm_amazon_config['JPendpoint']; ?>' +
    '?AWSAccessKeyId=' + AccessKeyId +
    '&AssociateTag=' + AssociateTag +
    '&Service=AWSECommerceService' +
    '&Version=<?php echo $tmkm_amazon_config['Version']; ?>' +
    '&ContentType=text/javascript' +
    '&Style=' + xslUrlItemSearch +
    '&Operation=<?php echo $tmkm_amazon_config['OperationSearch']; ?>' +
    '&ResponseGroup=Medium' +
    '&SearchIndex=' + g +
    '&Keywords=' + q +
    '&ItemPage=' + i;
  oJsr.build(q);
  oJsr.add();
}

function cbItemSearch(ItemSearchResponse) {
  var message = $('message');
  var results = $('results');

  try {
    var items = ItemSearchResponse.Items;
  } catch (e) {
    message.innerHTML = '<p>処理中にエラーが発生しました: <em>' + e + '</em></p>';
    oJsr.remove();
    return;
  }

  if (ItemSearchResponse.Error > 0) {
    message.innerHTML = '<p>' + ItemSearchResponse.ErrorMessage + ': <em>' + ItemSearchResponse.ErrorCode + '</em></p>';
    oJsr.remove();
    return;
  }

  var i = $F('i') * 10;
  var j = i - 9;
  i = (i > ItemSearchResponse.TotalResults) ? ItemSearchResponse.TotalResults : i;
  results.innerHTML = '<p>検索結果 <em>' + ItemSearchResponse.TotalResults +'</em> 件中 <em>' + j + '</em> - <em>' + i + '</em> 件目を表示しています。</p>';

  for (var i = 0; i < ItemSearchResponse.Items.length; i++) {
    var item = ItemSearchResponse.Items[i];

    data['node-' + i] = item.ASIN;

    var div = document.createElement('div');
    div.className = 'result';

    var p = document.createElement('p');
    p.className = 'image';
    a = document.createElement('a');
    a.setAttribute('href', item.URL);
    var img = document.createElement('img');

    if (item.MediumImage) {
      img.setAttribute('src', item.MediumImage.URL);
      img.setAttribute('alt', item.Title);
      img.setAttribute('width', item.MediumImage.Width);
      img.setAttribute('height', item.MediumImage.Height);
    } else {
      img.setAttribute('src', 'http://labs.hail2u.net/amazon/no-image.png');
      img.setAttribute('alt', item.Title);
      img.setAttribute('width', '60');
      img.setAttribute('height', '40');
    }

    a.appendChild(img);
    p.appendChild(a);
    div.appendChild(p);

    var h2 = document.createElement('h2');
    var a = document.createElement('a');
    a.setAttribute('href', item.URL);
    a.appendChild(document.createTextNode(item.Title));
    h2.appendChild(a);
    div.appendChild(h2);

    var ul = document.createElement('ul');
    var li = document.createElement('li');

    for (var j = 0; j < item.Details.length; j++) {
      li.appendChild(document.createTextNode(item.Details[j]));
      ul.appendChild(li);
      li = document.createElement('li');
    }

    li.appendChild(document.createTextNode(item.ASIN));
    ul.appendChild(li);
    li = document.createElement('li');
    li.setAttribute('id', 'node-' + i);
    li.className = 'node';
    img = document.createElement('img');
    img.className = 'more-like-this';
    img.setAttribute('src', 'more-like-this.png');
    img.setAttribute('alt', '関連商品を見る');
    img.onclick = function(){doSimilarityLookup(this.parentNode.id);};
    li.appendChild(img);
    ul.appendChild(li);

    div.appendChild(ul);
    results.appendChild(div);
  }

  var i = $F('i');

  var p = document.createElement('p');
  p.className = 'previous';

  if (i > 1) {
    i--;
    a = document.createElement('a');
    a.setAttribute('href', location.href.replace(/:[1-9]\d*$/, '') + ':' + i);
    a.onclick = function(){doItemSearchPrevious();};
    a.appendChild(document.createTextNode('« Previous Results'));
    p.appendChild(a);
    i++;
  } else {
    p.appendChild(document.createTextNode('« Previous Results'));
  }

  results.appendChild(p);

  var p = document.createElement('p');
  p.className = 'next';

  if (i < ItemSearchResponse.TotalPages) {
    i++;
    a = document.createElement('a');
    a.setAttribute('href', location.href.replace(/:[1-9]\d*$/, '') + ':' + i);
    a.onclick = function(){doItemSearchNext();};
    a.appendChild(document.createTextNode('Next Results »'));
    p.appendChild(a);
  } else {
    p.appendChild(document.createTextNode('Next Results »'));
  }

  results.appendChild(p);

  $('message').innerHTML = '<p>プルダウン・メニューからストアを選択した上で、キーワードを入力し、<em>検索ボタン</em>または<em><kbd>Enter</kbd></em>を押してください。</p>';

  oJsr.remove();
}

function doSimilarityLookup(node) {
  $('message').innerHTML = '<p><em>検索中･･･<em></p>';
  $('currentnode').value = node;
  var q = data[node];
  q = 'http://webservices.amazon.co.jp/onca/xml' +
    '?AWSAccessKeyId=' + AccessKeyId +
    '&AssociateTag=' + AssociateTag +
    '&Service=AWSECommerceService' +
    '&Version=2006-06-28' +
    '&ContentType=text/javascript' +
    '&Style=' + xslUrlSimilarityLookup +
    '&Operation=SimilarityLookup' +
    '&ResponseGroup=Medium' +
    '&ItemId=' + q;
  oJsr.build(q);
  oJsr.add();
}

function cbSimilarityLookup(SimilarityLookupResponse) {
  var message = $('message');
  var node = $F('currentnode');
  var currnode = $(node);

  try {
    var items = SimilarityLookupResponse.Items;
  } catch (e) {
    currnode.innerHTML += '<p>処理中にエラーが発生しました: <em>' + e + '</em></p>';
    oJsr.remove();
    return;
  }

  if (SimilarityLookupResponse.Error > 0) {
    currnode.innerHTML += '<p>' + SimilarityLookupResponse.ErrorMessage + '</p>';
    oJsr.remove();
    return;
  }

  var ul = document.createElement('ul');

  for (var i = 0; i < SimilarityLookupResponse.Items.length; i++) {
    var item = SimilarityLookupResponse.Items[i];

    data[node + '-' + i] = item.ASIN;

    var li = document.createElement('li');
    li.setAttribute('id', node + '-' + i);

    var a = document.createElement('a');
    a.setAttribute('href', item.URL);
    a.appendChild(document.createTextNode(item.Title));
    li.appendChild(a);

    li.appendChild(document.createTextNode(' ' + item.Creator + ' '));

    img = document.createElement('img');
    img.className = 'arrow';
    img.setAttribute('src', '<?php echo $tmkm_plugin_directory; ?>/amazon_noimg_small.png');
    img.setAttribute('alt', '関連商品を見る');
    img.onclick = function(){doSimilarityLookup(this.parentNode.id);};
    li.appendChild(img);

    ul.appendChild(li);
  }

  currnode.appendChild(ul);

  message.innerHTML = '<p>ブランチを作るには各検索の最後についている小さな三角の画像をクリックしてください。</p><p>新たに検索し直す場合は、改めてプルダウン・メニューからストアを選択した上で、キーワードを入力し、<em>検索ボタン</em>または<em><kbd>Enter</kbd></em>を押してください。</p>';

  oJsr.remove();
}

Event.observe(window, 'load', init, false);
</script>

<!--
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:foaf="http://xmlns.com/foaf/0.1/">
  <rdf:Description rdf:about="http://labs.hail2u.net/amazon/">
    <foaf:maker rdf:parseType="Resource">
      <foaf:holdsAccount>
        <foaf:OnlineAccount foaf:accountName="h2u">
          <foaf:accountServiceHomepage rdf:resource="http://www.hatena.ne.jp/"/>
        </foaf:OnlineAccount>
      </foaf:holdsAccount>
    </foaf:maker>
  </rdf:Description>
</rdf:RDF>
-->
<!-- End Of Script Generated By WP-Tmkm-Amazon -->

<?php	
}

/******************************************************************************
 * WpTmkmAmazon WordPress Plugin Class & Funtcion Define
 *****************************************************************************/

$wpTmkmAmazonView = & new WpTmkmAmazonView();
$wpTmkmAmazonList = & new WpTmkmAmazonList();
$wpTmkmAmazonFind = & new WpTmkmAmazonFind();
$wpTmkmAmazonAdmin = & new WpTmkmAmazonAdmin();

add_action('wp_head', 'add_tmkmamazon_stylesheet');
add_filter('admin_head', 'add_tmkmamazon_searchjs');
add_action('admin_menu',		array(&$wpTmkmAmazonAdmin, 'tmkm_amazon_add_options')); 		// Insert the Admin panel.
if ( $_POST['action'] == 'save_options' ){
	add_action('admin_menu',	array(&$wpTmkmAmazonAdmin, 'tmkm_amazon_save_options'));
}

add_filter('the_content',		array(&$wpTmkmAmazonView, '_replacestrings'));
add_filter('edit_form_advanced',	array(&$wpTmkmAmazonFind, 'InsertSearchForm'));
add_filter('edit_page_form',		array(&$wpTmkmAmazonFind, 'InsertSearchForm'));


?>