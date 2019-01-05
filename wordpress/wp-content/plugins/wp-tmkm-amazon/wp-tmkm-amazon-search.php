<?php

/******************************************************************************
 * THIS FILE IS CALLED ONLY.
 *****************************************************************************/
include_once('wp-tmkm-amazon-function.php');
// ** 修正 (2009/6/22)
include_once('../../../wp-load.php');
// ここまで **
$amazonparse = & new GetAmazonXmlParse();

/******************************************************************************
 * INITIALIZE
 *****************************************************************************/
$AssociatesID = '';
$output = '';
$associatesid = !empty($_GET['AID']) ? $_GET['AID'] : $AssociatesID;

$html_head =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n" .
		'<html xmlns="http://www.w3.org/1999/xhtml" lang="ja">' . "\n" .
		'<head>' . "\n" .
		'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\n" .
		'<title>wp-tmkm-amazon( WordPress Plugin ) Amazon Search</title>' . "\n" .
		'<link rel="stylesheet" href="tmkm-amazon-search.css" type="text/css" />' . "\n" .
		'</head>' . "\n" .
		'<body>' . "\n";

$html_foot = '</body></html>' . "\n";

$search_action	= '<form action="wp-tmkm-amazon-search.php?AID=' . $associatesid . '" method="GET">' . "\n";
$search_option	= '<p style="display: inline;"><a id="searchpagetop">Amazon 検索</a></p>&nbsp;' . "\n" .
				'<select name="SearchIndex">' . "\n" .
				'	<option name="SearchIndex" value="All">Amazon.co.jp</option>' . "\n" .
				'	<option name="SearchIndex" value="Books">本</option>' . "\n" .
				'	<option name="SearchIndex" value="Magazines">雑誌</option>' . "\n" .
				'	<option name="SearchIndex" value="ForeignBooks">洋書</option>' . "\n" .
				'	<option name="SearchIndex" value="Music">ミュージック</option>' . "\n" .
				'	<option name="SearchIndex" value="MusicTracks">サウンドトラック</option>' . "\n" .
				'	<option name="SearchIndex" value="Classical">クラシック</option>' . "\n" .
				'	<option name="SearchIndex" value="DVD">DVD</option>' . "\n" .
				'	<option name="SearchIndex" value="VideoGames">ゲーム</option>' . "\n" .
				'	<option name="SearchIndex" value="Electronics">エレクトロニクス</option>' . "\n" .
				'	<option value="Kitchen">ホーム &amp; キッチン</option>' . "\n" .
				'	<option value="Toys">おもちゃ &amp; ホビー</option>' . "\n" .
				'	<option value="Kitchen">キッチン &amp; テーブルウェア</option>' . "\n" .
				'	<option value="GourmetFood">食品</option>' . "\n" .
				'	<option value="SportingGoods">スポーツ</option>' . "\n" .
				'	<option value="HealthPersonalCare">ヘルス &amp; ビューティー</option>' . "\n" .
				'</select>' . "\n";
$search_field_d	= '<input type="text" size="20" maxlength="50" value="" name="keyword" />&nbsp;<input type="submit" value="Go" />' . "\n";
$search_mode	= '<input type="hidden" name="mode" value="search" /></form>' . "\n";


/******************************************************************************
 * MAIN ROUTINE
 *****************************************************************************/
if( isset( $_GET['Page'] ) ){
	$PageNum = (int) $_GET['Page'];
	if( 0==$PageNum ){
		$PageNum = 1;
	}
}else{
	$PageNum = 1;
}

if( ( $_GET['mode'] ) == 'search' ){
	if( ( $_GET['keyword'] ) != '' ){

		if( !empty( $_GET['keyword'] ) ) { $keyword = $_GET['keyword']; }
		$searchindex = !empty( $_GET['SearchIndex'] ) ? $_GET['SearchIndex'] : 'Blended';

		$responsegroup = 'Images,Small';
		$AmazonXml = $amazonparse->getamazonxml( $associatesid, $keyword, 'plural', $searchindex, $responsegroup, $PageNum );
		// --- Call Amazon XML function ---

		$display_keyword = rawurldecode($keyword);
		$search_field_s	= '<input type="text" size="20" maxlength="50" value="' . $display_keyword . '" name="keyword" />&nbsp;<input type="submit" value="Go" />';
		$search_form = $search_action . $search_option . $search_field_s . $search_mode;

		echo $html_head;
		echo $search_form;

		// DEBUG
/*		echo '<pre>';
		print_r($AmazonXml);
		echo '</p>';
*/		// DEBUG

		if( false === $AmazonXml ){  // Amazon function was returned false, so AWS is down
			echo '<p>アマゾンのサーバでエラーが起こっているかもしれません。一度ページを再読み込みしてみてください。</p>' . "\n";
		}else{ // Amazon function returned XML data

			$status = $AmazonXml["ItemSearchResponse"]["Items"]["Request"];

			if( $status["IsValid"] == 'False' ){ // Request is invalid
				echo '<p>アマゾンの検索上限を超えたかも知れません。</p>' . "\n";
			}else{ // results were found, so display the products
	
				// --- Display the product data returned from the XML ---
				$item = $AmazonXml["ItemSearchResponse"]["Items"]["Item"];
				$totalresults = (int)$AmazonXml["ItemSearchResponse"]["Items"]["TotalResults"];
				$totalpages =  (int)$AmazonXml["ItemSearchResponse"]["Items"]["TotalPages"];

				if( $totalresults == 0 ){ // no result was found
					echo '<h1>「' . $display_keyword . '」の検索結果が見つかりませんでした。</h1>' . "\n";
				} elseif( $totalresults == 1 ) { // one result was found
					echo '<h1>「' .$display_keyword. '」の検索結果は ' .$totalresults. ' 件です。</h1>' . "\n";

					$smallimage = $amazonparse->get_goods_image($item,'small');
					if( $smallimage == '' ){ $smallimage = './amazon_noimg_small.png'; }
					$itemcount = $i + 1;
	
					$url = $amazonparse->get_amazon_text($item,'url');
					$Title = $amazonparse->get_amazon_text($item,'title');
					$ASIN = $amazonparse->get_amazon_text($item,'asincode');

					$output .=
						'<div id="amazon-search-result"><h2>' . $itemcount . '.</h2>'. "\n" . '<p>' .
						'<!-- product image --><img src="' . $smallimage . '" border="0" alt="" />' . "\n" .
						'<!-- product name -->' . $Title . '<br />' . "\n" .
						'<!-- ASIN CODE --><strong>[tmkm-amazon]' . $ASIN . '[/tmkm-amazon]</strong><br />' . "\n" .
						'<!-- Amazon Link --><a href="' . $url . '" target="_blank">Amazon で詳細をみる</a>' . "\n" .
						'</p></div>' . "\n";

				} else { // results were found
					echo '<h1>「' .$display_keyword. '」の検索結果のうち ' . $PageNum . ' ページ目の ' .count($item) . ' 件を表示しています</h1>' . "\n";

					//DEBUG
/*					echo '<pre>';
					print_r($item);
					echo '<p>CountItems : ' . count($item) . '</p>';
					echo '</pre>';
*/
				//DEBUG
		
					/**
					* Loop through each <Details> tag.
					* Assign each piece of data you want to use on your template to a variable, and then
					* echo that variable to display it on your site.
					*/

//					for( $i=0; $i<$totalresults; $i++ ) {
					for( $i=0; $i<count($item); $i++ ) {
						$smallimage = $amazonparse->get_goods_image($item[$i],'small');
						if( $smallimage == '' ){ $smallimage = './amazon_noimg_small.png'; }
						$itemcount = $i + 1;
		
						$url = $amazonparse->get_amazon_text($item[$i],'url');
						$Title = $amazonparse->get_amazon_text($item[$i],'title');
						$ASIN = $amazonparse->get_amazon_text($item[$i],'asincode');

						$output .=
							'<div id="amazon-search-result"><h2>' . $itemcount . ".</h2>\n<p>" .
							'<!-- product image --><img src="' . $smallimage . '" border="0" alt="" />' . "\n" .
							'<!-- product name -->' . $Title . '<br />' . "\n" .
							'<!-- ASIN CODE --><strong>[tmkm-amazon]' . $ASIN . '[/tmkm-amazon]</strong><br />' . "\n" .
							'<!-- Amazon Link --><a href="' . $url . '" target="_blank">Amazon で詳細をみる</a>' . "\n" .
							'</p></div>' . "\n" . "\n";
					}

					// Pagenation
					if( $totalpages > 1 ) {
						$prevpage = $PageNum - 1;
						$nextpage = $PageNum + 1;
						$prevlink = '<li><a href="?SearchIndex=' .$searchindex. '&keyword=' .$keyword. '&mode=search' . '&Page=' .$prevpage. '" class="pagenation">前のページ</a></li>' . "\n";
						$nextlink = '<li><a href="?SearchIndex=' .$searchindex. '&keyword=' .$keyword. '&mode=search' . '&Page=' .$nextpage. '" class="pagenation">次のページ</a></li>' . "\n"; 
						if( $PageNum == 1 ) {
							$prevlink = '<li>前のページ</li>' . "\n";
						} elseif( $PageNum == $totalpages ) {
							$nextlink = '<li>次のページ</li>' . "\n";
						}
						$pagelink = '<ul class="wp-tmkm-amazon-search-guide">' . $prevlink . '<li> &laquo;　' . $PageNum . ' / ' . $totalpages . 'ページ　&raquo;</li>' . $nextlink . '</ul>' . "\n";
					}
				}
				echo $pagelink;
				echo $output;
				echo '<hr />' . "\n";
				echo $pagelink;
			}
		}
		echo '<p><a href="#searchpagetop">↑ このページの TOP へ</a></p>' . "\n";
		echo $html_foot;

	} else {

		echo $html_head;
		echo "<p>No Keyword.</p>" . "\n";
		echo '<a href="wp-tmkm-amazon-search.php">Back To Search</p>' . "\n";
		echo $html_foot;

	}


} else {
		$search_form = $search_action . $search_option . $search_field_d . $search_mode;
		echo $html_head;
		echo $search_form;
		echo $html_foot;
}

?>