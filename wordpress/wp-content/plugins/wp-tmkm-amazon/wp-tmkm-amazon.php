<?php
/*
Plugin Name: wp-tmkm-amazon
Plugin URI: http://blog.openmedialabo.net/
Description: ASIN を指定して Amazon から個別商品の情報を取出します。BOOKS, DVD, CD は詳細情報を取り出せます。
Author: ともかめ / Romeo
Version: 1.5b
Author URI: http://blog.openmedialabo.net/
Special Thanks: Keith Devens.com (http://keithdevens.com/software/phpxml)
Special Thanks: websitepublisher.net (http://www.websitepublisher.net/article/aws-php/)
Special Thanks: hiromasa.zone :o) (http://hiromasa.zone.ne.jp/)
Special Thanks: みやび ( http://shinonon-web.net/ )
Special Thanks: PEAR :: Package :: Cache_Lite (http://pear.php.net/package/Cache_Lite)
*/

/******************************************************************************
 * wp-tmkm-amazon-function : amazon ECS search engine
 *****************************************************************************/
if( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'wp-tmkm-amazon.php' ) {
	die();
}
$tmkm_plugin_directory = get_option('siteurl') . '/wp-content/plugins/' . basename(dirname(__FILE__));
$tmkm_amazon_php = '/wp-tmkm-amazon.php';
$tmkm_amazon_search_php = '/wp-tmkm-amazon-search.php';
$tmkm_amazon_settings = get_option('wp_tmkm_admin_options');

/******************************************************************************
 * THIS FILE IS CALLED ONLY.
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
			$tmkm_amazon_options = array(
				'associatesid' => '',
				'windowtarget' => 'self',
				'jacketimgsize' => 'small',
				'goodsimgsize' => 'small',
				'layout_type' => '0',
				// ** 追加 (2009/6/22)
				'devtoken' => '',
				'secretkey' => '',
				// ここまで **
				// ** 追加 (2011/9/5)
				'homedisp' => '0',
				// ここまで
			);
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
		switch( $tmkm_amazon_options['homedisp']) {
			case '0': $dispon = ' checked'; $dispoff = ''; break;
			case '1': $dispon = ''; $dispoff = ' checked'; break;
		}

		switch( $tmkm_amazon_options['windowtarget']) {
			case 'newwin': $newwindow = ' checked'; $selfwindow = ''; break;
			case 'self': $newwindow = ''; $selfwindow = ' checked'; break;
		}

		switch( $tmkm_amazon_options['jacketimgsize'] ) {
			case 'medium': $m_jacketsize = ' checked'; $s_jacketsize = ''; break;
			case 'small': $m_jacketsize = ''; $s_jacketsize = ' checked'; break;
		}

		switch( $tmkm_amazon_options['goodsimgsize'] ) {
			case 'medium': $m_goodssize = ' checked'; $s_goodssize = ''; break;
			case 'small': $m_goodssize = ''; $s_goodssize = ' checked'; break;
		}

		switch( $tmkm_amazon_options['layout_type'] ) {
			case 0: $default_layout  = ' checked';$medium_layout = '';  $simple_layout = ''; $noimage_layout = ''; break;
			case 1: $default_layout = ''; $medium_layout = ' checked'; $simple_layout = ''; $noimage_layout = ''; break;
			case 2: $default_layout  = ''; $medium_layout = ''; $simple_layout = ' checked'; $noimage_layout = ''; break;
			case 3: $default_layout  = ''; $medium_layout = ''; $simple_layout = ''; $noimage_layout = ' checked'; break;
		}

		$tmkm_amazon_admin_html .=
			'<form method="post" action="' . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . '">' .
			'<input type="hidden" name="action" value="save_options" />' .
			'<table class="form-table">' .
			'<tr>' . "\n" .
			'<th>あなたのアソシエイト ID</th>' . "\n" .
			'<td><input type="text" name="associatesid" value="' . $tmkm_amazon_options['associatesid'] . '"  /></td>' . "\n" .
			// ** 追加 (2009/6/22)
			'</tr>' . "\n" .
			'<tr>' . "\n" .
			'<th>あなたのAccess Key ID</th>' . "\n" .
			'<td><input type="text" name="devtoken" value="' . $tmkm_amazon_options['devtoken'] . '"  /></td>' . "\n" .
			'</tr>' . "\n" .
			'<tr>' . "\n" .
			'<th>あなたのSecret Access Key</th>' . "\n" .
			'<td><input type="text" name="secretkey" value="' . $tmkm_amazon_options['secretkey'] . '"  /></td>' . "\n" .
			'</tr>' . "\n" .
			// ここまで **
			// ** 追加 (2011/9/5)
			'<tr><th>HOME画面に適用するか(default:適用する)</th>' . "\n" .
			'<td><input type="radio" name="homedisp" value="0"' . $dispon . ' />&nbsp;適用する<br />' . "\n" .
			'<input type="radio" name="homedisp" value="1"' . $dispoff . ' />&nbsp;適用しない</td>' . "\n" .
			'</tr>' . "\n" .

			//
			'<tr>' . "\n" .
			'<tr><th>商品リンクの動作</th>' . "\n" .
			'<td><input type="radio" name="windowtarget" value="self"' . $selfwindow . ' />&nbsp;同じウィンドウ（ target 指定なし ）<br />' . "\n" .
			'<input type="radio" name="windowtarget" value="newwin"' . $newwindow . ' />&nbsp;新規ウィンドウ（ target="_blank" ）</td>' . "\n" .
			'</tr>' . "\n" .
			'<tr><th>商品詳細の表示スタイル</th>' . "\n" .
			'<td><input type="radio" name="layout_type" value="0"' . $default_layout . ' />&nbsp;画像、タイトル、出版社、発売時期、著者、価格、本のタイプ、ページ数、ISBN（ 初期設定。本以外はこれに準ずる項目 ）<br />' . "\n" .
			'<input type="radio" name="layout_type" value="1"' . $medium_layout . ' />&nbsp;画像、タイトル、出版社、著者、発売時期（ 初期設定から価格情報とコード情報を省略 ）<br />' . "\n" .
			'<input type="radio" name="layout_type" value="2"' . $simple_layout . ' />&nbsp;画像とタイトルのみ<br />' . "\n" .
			'<input type="radio" name="layout_type" value="3"' . $noimage_layout . ' />&nbsp;タイトルのみ</td>' . "\n" .
			'</tr>' . "\n" .
			'<tr><th>CD ジャケットの画像サイズ</th>' . "\n" .
			'<td><input type="radio" name="jacketimgsize" value="small"' . $s_jacketsize . ' />&nbsp;小サイズ（ 初期設定 ）<br />' . "\n" .
			'<input type="radio" name="jacketimgsize" value="medium"' . $m_jacketsize . ' />&nbsp;中サイズ</td>' . "\n" .
			'</tr>' . "\n" .
			'<tr><th>その他（ 本、DVD、CD 以外 ）の画像サイズ</th>' . "\n" .
			'<td><input type="radio" name="goodsimgsize" value="small"' . $s_goodssize . ' />&nbsp;小サイズ（ 初期設定 ）<br />' . "\n" .
			'<input type="radio" name="goodsimgsize" value="medium"' . $m_goodssize . ' />&nbsp;中サイズ</td>' . "\n" .
			'</tr>' . "\n" .
			'</table>' . "\n" .
			'<p class="submit"><input type="submit" name="Submit" value="設定を保存する &raquo;" /></p>' . "\n" .
			'</form>' . "\n" .
			'</div>' . "\n";

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
		$tmkm_amazon_options = array(
			'associatesid' => $_POST['associatesid'],
			'windowtarget' => $_POST['windowtarget'],
			'jacketimgsize' => $_POST['jacketimgsize'],
			'goodsimgsize' => $_POST['goodsimgsize'],
			'layout_type' => $_POST['layout_type'],
			// ** 追加 (2009/6/22)
			'devtoken' => $_POST['devtoken'],
			'secretkey' => $_POST['secretkey'],
			// ここまで **
			// ** 追加 (2011/9/5)
			'homedisp' => $_POST['homedisp'],
			// ここまで **
		);

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

		global $post,$tmkm_amazon_settings;

//		$poststring = '/\[tmkm-amazon type\=([a-z]+)\]([a-zA-Z0-9,]+)\[\/tmkm-amazon\]/';
		$poststring = '/\[tmkm-amazon\]([a-zA-Z0-9,]+)\[\/tmkm-amazon\]/';
		$transformedstring = $content;
		if( !is_home() || ( is_home() && $tmkm_amazon_settings['homedisp'] == 0 ) ){

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
		}
		
		return $transformedstring;

	}


	/**
	 * PHP 関数として Amazon の個別商品 HTML を呼び出す
	 * 
	 * @param $SearchString ( ASIN )
	 * @param $type ( book / dvd / cd )
	 * @return echo $display ( HTML )
	 */
	function tmkm_amazon_view( $SearchString ) { // PHPファイル中に記述する関数
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

	function tmkm_amazon_list( $attr ) {
	
	    global $wpdb, $tmkm_amazon_settings;

		extract( shortcode_atts( array(
			'orderby' 	=> 'post_id',
			'order'		=> 'asc',
		), $attr ));

		$orderby = strval( $orderby );
		$order = strval( $order );

		$output = '';

		switch( $orderby ) {
			case 'post_id': $this->ordersql = "$wpdb->posts.ID " . $order; break;
			case 'post_title': $this->ordersql = "$wpdb->posts.post_title " . $order; break;
			case 'post_date': $this->ordersql = "$wpdb->posts.post_date " . $order; break;
			case 'modified_date': $this->ordersql = "$wpdb->posts.post_modified " . $order; break;
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

		$output .= $heredoc;
		return $output;

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

        switch( $tmkm_amazon_settings['windowtarget']) {
        	case 'newwin': $windowtarget = ' target="_blank"'; break;
        	case 'self': $windowtarget = '';
        }

		$output = '';
		if( strlen( $SearchString ) == 13 ){
			$SearchString = $this->generalfunclib->calc_chkdgt_isbn10( substr( $SearchString, 3, 9 ) );
		}

		// --- Call Amazon XML function ---
		if(( $formattype == 'list' ) || ( $tmkm_amazon_settings['layout_type'] == '2' )) {
			$AmazonXml = $this->amazonparse->getamazonxml( $associatesid, $SearchString, 'single', '', 'Images,Small', '' );
		} elseif( $tmkm_amazon_settings['layout_type'] == '3' ) {
			$AmazonXml = $this->amazonparse->getamazonxml( $associatesid, $SearchString, 'single', '', 'ItemAttributes', '' );
		} else {
			$AmazonXml = $this->amazonparse->getamazonxml( $associatesid, $SearchString, 'single', '', 'Medium,Offers', '' );
		}

//		DEBUG
/*
		echo "<pre>\n";
		print_r($AmazonXml);
		echo "</pre>\n";
*/
		// --- Get results of the Amazon function ---
		if( false === $AmazonXml ){  // Amazon function was returned false, so AWS is down

			$output = '<p>アマゾンのサーバでエラーが起こっているかもしれません。<br />一度ページを再読み込みしてみてください。</p>';

		}else{ // Amazon function returned XML data

			$status = $AmazonXml["ItemLookupResponse"]["Items"]["Request"];

			if( $status["IsValid"] == 'False' ){ // Request is invalid

				$output = '<p>与えられたリクエストが正しくありません</p>';

			}else{ // results were found, so display the products
	
		// --- Display the product data returned from the XML ---
				$item = $AmazonXml["ItemLookupResponse"]["Items"]["Item"];

				$url = $this->amazonparse->get_amazon_text($item,'url');
				$Title = $this->amazonparse->get_amazon_text($item,'title');
				$ProductGroup = $this->amazonparse->get_amazon_text($item,'productgroup');

				if( $tmkm_amazon_settings['layout_type'] != '3' ) {

					$mediumimage = $this->amazonparse->get_goods_image( $item,'medium' );
					if( $mediumimage == '' ) { $mediumimage = $tmkm_plugin_directory . $this->mediumimgfile; }
					$smallimage = $this->amazonparse->get_goods_image( $item,'small' );
					if( $smallimage == '' ) { $smallimage = $tmkm_plugin_directory . $this->smallimgfile; }
	
			        switch( $tmkm_amazon_settings['jacketimgsize'] ) {
			        	case 'small': $cdjacketimg = $smallimage; break;
			        	case 'medium': $cdjacketimg = $mediumimage; break;
			        }
			        switch( $tmkm_amazon_settings['goodsimgsize'] ) {
			        	case 'small': $goodsimage = $smallimage; break;
			        	case 'medium': $goodsimage = $mediumimage; break;
			        }
				}

				if( $tmkm_amazon_settings['layout_type'] < 2 ) {
					$Manufacturer = $this->amazonparse->get_amazon_text($item,'manufacturer');
					$Binding = $this->amazonparse->get_amazon_text($item,'binding');
					$ReleaseDate = $this->amazonparse->get_amazon_text($item,'releasedate');

					$ASIN = $this->amazonparse->get_amazon_text($item,'asincode');
					$EANcode = $this->amazonparse->get_amazon_text($item,'eancode');

					if( $tmkm_amazon_settings['layout_type'] == 0 ) {
						$Price = $this->amazonparse->get_amazon_text($item,'price');
						$OurPrice = $this->amazonparse->get_amazon_text($item,'ourprice');
						$LowestUsedPrice = $this->amazonparse->get_amazon_text($item,'lowestusedprice');
						$ISBN10 = $this->amazonparse->get_amazon_text($item,'isbn10');
						if( $LowestUsedPrice != '' ) {
							$usedpricememo = ' ( 中古価格 '. $LowestUsedPrice . ' より )';
						} else {
							$usedpricememo = '';
						}
					}

					switch( $ProductGroup ){
						case Book:
							$Role = $this->amazonparse->get_amazon_text($item,'role');
							$Pages = $this->amazonparse->get_amazon_text($item,'pages');
							$Author = $this->amazonparse->get_amazon_text($item,'author');
							$PublicationDate = $this->amazonparse->get_amazon_text($item,'publicationdate');
							break;
						case DVD:
							$RunningTime = $this->amazonparse->get_amazon_text($item,'runningtime');
							$numofdisc = $this->amazonparse->get_amazon_text($item,'numofdisc');
							break;
						case Music:
							$Format = $this->amazonparse->get_amazon_text($item,'format');
							$Artist = $this->amazonparse->get_amazon_text($item,'artist');
							break;
					}
				}

				if( $tmkm_amazon_settings['layout_type'] != 3 ) { $output = '<div id="tmkm-amazon-view">' . "\n"; }
				
				if( $formattype == 'detail' ) {
					if( $ProductGroup == 'Book' ){
						switch ( $tmkm_amazon_settings['layout_type'] ) {
							case 3: // noimage( Title only )
								$output .= '<a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								break;
							case 2: // simple( Image + Title )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								break;
							case 1: // medium( Fully - Price & Code )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								if( $Author !="" ) {
									$output .= "\t" . "<p><em>著者／訳者：</em>";
									if( count($Author) == 1 ) {
										$output .= $Author; 
									} else {
										foreach($Author as $auth){ $output .= $auth.' '; }
									}
									$output .= '</p>' . "\n";
								}
								$output .= "\t" . "<p><em>出版社：</em>$Manufacturer( $PublicationDate )</p>" . "\n";
								$output .= "\t" . "<p>$Binding ( $Pages ページ )</p>" . "\n";
								break;
							case 0: // Fully
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								if( $Author !="" ) {
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
								if( $OurPrice != '' ) {
									$output .= "\t" . "<p><em>Amazon価格：</em>$OurPrice</p>" . "\n";
								}
								$output .= "\t" . "<p>$Binding ( $Pages ページ )</p>" . "\n";
								$output .= "\t" . "<p>ISBN-10 : $ISBN10</p>" . "\n";
								$output .= "\t" . "<p>ISBN-13 : $EANcode</p>" . "\n";
								break;
						}

					} elseif( $ProductGroup == 'DVD' ) {
						switch ( $tmkm_amazon_settings['layout_type'] ) {
							case 3: // noimage( Title only )
								$output .= '<a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								break;
							case 2: // simple( Image + Title )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								break;
							case 1: // medium( Fully - Price & Code )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								$output .= "\t" . "<p><em>販売元：</em>$Manufacturer( $ReleaseDate )</p>" . "\n";
								$output .= "\t" . "<p><em>時間：</em>$RunningTime 分</p>" . "\n";
								$output .= "\t" . "<p>$numofdisc" . " 枚組 ( " . $Binding . " )</p>" . "\n";
								break;
							case 0: // Fully
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $mediumimage . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								$output .= "\t" . "<p><em>販売元：</em>$Manufacturer( $ReleaseDate )</p>" . "\n";
								$output .= "\t" . "<p><em>定価：</em>$Price$usedpricememo</p>" . "\n";
								if( $OurPrice != '' ) {
									$output .= "\t" . "<p><em>Amazon価格：</em>$OurPrice</p>" . "\n";
								}
								$output .= "\t" . "<p><em>時間：</em>$RunningTime 分</p>" . "\n";
								$output .= "\t" . "<p>$numofdisc" . " 枚組 ( " . $Binding . " )</p>" . "\n";
								break;
						}
	
					} elseif( $ProductGroup == 'Music' ) {
						switch ( $tmkm_amazon_settings['layout_type'] ) {
							case 3: // noimage( Title only )
								$output .= '<a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								break;
							case 2: // simple( Image + Title )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $cdjacketimg . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								break;
							case 1: // medium( Fully - Price & Code )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $cdjacketimg . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								$output .= ' / ' . $Artist . "</p>" . "\n";
								$output .= "\t" . "<p>$Manufacturer( $ReleaseDate )</p>" . "\n";
								break;
							case 0: // Fully
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $cdjacketimg . '" border="0" alt="" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								$output .= ' / ' . $Artist;
								$output .= " / $Binding ( $ProductGroup )</p>\n";
								$output .= "\t" . "<p>$Manufacturer( $ReleaseDate )</p>" . "\n";
								$output .= "\t" . "<p><em>定価：</em>$Price$usedpricememo</p>" . "\n";
								break;
						}
	
					} else {
						switch ( $tmkm_amazon_settings['layout_type'] ) {
							case 3: // noimage( Title only )
								$output .= '<a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								break;
							case 2: // simple( Image + Title )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $goodsimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
								break;
							case 1: // medium( Fully - Price & Code )
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $goodsimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								if( $ReleaseDate != '' ) { $output .= "\t" . "<p><em>発売日：</em>$ReleaseDate</p>" . "\n"; }
								break;
							case 0: // Fully
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $goodsimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
								$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a>';
								if( $Price != '' ) {
									$output .= "\t" . "<p><em>定価：</em>$Price</p>" . "\n";
								} elseif( $LowestUsedPrice != '' ) {
									$output .= "\t" . "<p><em>中古価格: </em>$LowestUsedPrice より</p>" . "\n";
								}
								if( $OurPrice != '' ) {
									$output .= "\t" . "<p><em>Amazon価格：</em>$OurPrice</p>" . "\n";
								}
								$output .= "\t" . "<p>カテゴリ：$Binding</p>\n";
								if( $ReleaseDate != '' ) { $output .= "\t" . "<p><em>発売日：</em>$ReleaseDate</p>" . "\n"; }
								break;
						}
					}

					if( $tmkm_amazon_settings['layout_type'] != 3 ) { $output .= '<hr class="tmkm-amazon-clear" /></div>'; }

				} elseif( $formattype == 'list' ) {
					$output = "\t" . '<p><a href="'.$url.'"' . $windowtarget . '><img src="' . $smallimage . '" border="0" alt="' . $Title . '" /></a></p>' . "\n";
					$output .= "\t" . '<p><a href="'.$url.'"' . $windowtarget . '>' . $Title . '</a></p>' . "\n";
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

		?>
		<div id="wptmkmamazon" class="postbox if-js-closed">
		<h3>wp-tmkm-amazon</h3>
		<div class="inside">
			<p>iframe 内に検索フォームが表示されない方は<a href="<?php echo $this->searchphp_path; ?>" target="_blank">別ウィンドウ</a>で検索をお願いします。</p>
			<iframe id="uploading" frameborder="0" width="600" height="500" src="<?php echo $this->searchphp_path; ?>">IFRAME による表示がサポートされている環境が必要です。</iframe>
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

function add_tmkmamazon_stylesheet(){
	global $tmkm_plugin_directory;
	?>
		<link rel="stylesheet" href="<?php echo $tmkm_plugin_directory; ?>/tmkm-amazon.css" type="text/css" media="screen" />
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
add_action('admin_menu',		array(&$wpTmkmAmazonAdmin, 'tmkm_amazon_add_options')); 		// Insert the Admin panel.
if ( $_POST['action'] == 'save_options' ){
	add_action('admin_menu',	array(&$wpTmkmAmazonAdmin, 'tmkm_amazon_save_options'));
}

add_shortcode('tmkm-amazon-list', array (&$wpTmkmAmazonList, 'tmkm_amazon_list'));

add_filter('the_content',		array(&$wpTmkmAmazonView, '_replacestrings'));
add_filter('edit_form_advanced',	array(&$wpTmkmAmazonFind, 'InsertSearchForm'));
add_filter('edit_page_form',		array(&$wpTmkmAmazonFind, 'InsertSearchForm'));

?>
