<?php
/*********************************************************
  Filename : wp-tmkm-amazon-function.php
　　【2009/07/03】
　　　管理画面から下記を設定する場合、本ファイルへの入力は不要です。

	設置前に以下を入力します。
	$tmkm_amazon_config 内の
		AssociatesID : ご自身のAssociatesID を入力します。
		DevToken : Access Key ID を入力します。
		SecretKey : Secret Access Key を入力します
	それぞれの値はAmazon Web Servicesのサイトで取得
	できます。
	Amazon Web Services http://aws.amazon.com/
	具体的な取得方法はgoogle等で検索して下さい。
**********************************************************/

/******************************************************************************
 * INITIALIZE
 *****************************************************************************/
$tmkm_amazon_config = array(
	//	以下、入力してください
	'AssociatesID'	=> '',
	'DevToken'		=> '',
	'SecretKey' => '',
	//
	'JPendpoint'	=> 'http://webservices.amazon.co.jp/onca/xml',
	'Services' => 'AWSECommerceService' ,
	'Url' => 'webservices.amazon.co.jp',
	'Endpoint' => '/onca/xml',
	'Version'		=> '2009-03-31',
	'ContentType'	=> 'text/xml',
	'OperationLookup'	=> 'ItemLookup',
	'OperationSearch'	=> 'ItemSearch',
	'PageNum'		=> 1,
	'litephp_path'	=> 'Lite.php',
	'xmlphp_path'	=> 'xml.php',
	'cache_dir'		=> '/tmp/',
);


/******************************************************************************
 * MAIN CLASS
 *****************************************************************************/
class GetAmazonXmlParse {

	var $AssociatesID;
	var $getmode;
	var $SearchString;
	var $SearchIndex;
	var $ResponseGroup;

	/**
	 * The Constructor
	 * 
	 * @param none
	 * @return Object reference
	 */

	function GetAmazonXmlParse() {
		global $tmkm_amazon_config;
	}
	// RFC3986 形式で URL エンコードする関数
	function urlencode_rfc3986($str)
	{
		return str_replace('%7E', '~', rawurlencode($str));
	}
	/**
	 * Get Amazon Image.
	 * 
	 * @param $item (Amazon Xml)
	 * @param $size (Image Size)
	 */
	function get_goods_image($item,$imgsize) {

		if( $imgsize == 'medium' ) {
			$amazon_imgurl = $item["MediumImage"]["URL"];
		} elseif( $imgsize == 'small' ) {
			$amazon_imgurl = $item["SmallImage"]["URL"];
		}
		return $amazon_imgurl;
	}
	/**
	 * Get Amazon Text.
	 * 
	 * @param $item (Amazon Xml)
	 * @param $flag (Text Type)
	 */
	function get_amazon_text($item, $flag=''){
		switch ( $flag ) {
			case url: $textdata = $item["DetailPageURL"]; break;
			case title: $textdata = $item["ItemAttributes"]["Title"]; break;
			case manufacturer: $textdata = $item["ItemAttributes"]["Manufacturer"]; break;
			case asincode: $textdata = $item["ASIN"]; break;
			case eancode: $textdata = $item["ItemAttributes"]["EAN"]; break;
			case price: $textdata = $item["ItemAttributes"]["ListPrice"]["FormattedPrice"]; break;
			case ourprice: $textdata = $item["OfferSummary"]["LowestNewPrice"]["FormattedPrice"]; break;
			case lowestusedprice: $textdata = $item["OfferSummary"]["LowestUsedPrice"]["FormattedPrice"]; break;
			case releasedate: $textdata = $item["ItemAttributes"]["ReleaseDate"]; break;
			case runningtime: $textdata = $item["ItemAttributes"]["RunningTime"]; break;
			case binding:  $textdata = $item["ItemAttributes"]["Binding"]; break;
			case numofdisc: $textdata = $item["ItemAttributes"]["NumberOfDiscs"]; break;
			case pages: $textdata = $item["ItemAttributes"]["NumberOfPages"]; break;
			case role: $textdata = $item["ItemAttributes"]; break;
			case author: $textdata = $item["ItemAttributes"]["Author"]; break;
			case isbn10: $textdata = $item["ItemAttributes"]["ISBN"]; break;
			case publicationdate: $textdata = $item["ItemAttributes"]["PublicationDate"]; break;
			case format: $textdata = $item["ItemAttributes"]["Format"]; break;
			case artist: $textdata = $item["ItemAttributes"]["Artist"]; break;
			case productgroup: $textdata = $item["ItemAttributes"]["ProductGroup"]; break;
		}
		return $textdata;
	}

	/**
	 * Get ECS XML.
	 * 
	 * @param $SearchString (ASIN)
	 * @param $ResponseGroup (Small / Medium / Large : 情報量)
	 * @param $Page (詳細情報ページ)
	 */
	function getamazonxml( $AssociatesID, $SearchString, $getmode, $SearchIndex, $ResponseGroup, $PageNum ) {
		global $tmkm_amazon_config, $tmkm_amazon_settings;
		
		// --- Build XML Link ---
		// ** 追加 (2009/6/22)
		$tmkm_amazon_setting = get_option('wp_tmkm_admin_options');
		// ここまで **

		$options['Service'] = $tmkm_amazon_config['Services'];
		// ** 追加 (2009/6/22)
		$options['AWSAccessKeyId'] = $tmkm_amazon_settings['devtoken'];
		// ここまで **
		$options['Version'] = $tmkm_amazon_config['Version'];
		// ** 修正 (2009/6/22)
		if( $AssociatesID == '' ) { $options['AssociateTag'] = $tmkm_amazon_settings['associatesid']; }
		// ここまで **
		else $options['AssociateTag'] = $AssociatesID;

		if( $getmode == 'single' ) {
			$options['Operation'] = $tmkm_amazon_config['OperationLookup'];
			$options['ResponseGroup'] = $ResponseGroup;
			$options['ItemId'] = $SearchString;
		} elseif( $getmode == 'plural' ) {
			$options['Operation'] = $tmkm_amazon_config['OperationSearch'];
			$options['ResponseGroup'] = $ResponseGroup;
			$options['SearchIndex'] = $SearchIndex;
			$options['Keywords'] = $SearchString;
		}
		if( $PageNum == '' ) { $PageNum = $tmkm_amazon_config['Pagenum']; }
		$options['ItemPage'] = $PageNum;
		
		$options['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		ksort($options);
		
		foreach($options as $key => $val) {
			$canonical_string .= "&" . $key . "=" . rawurlencode($val);
			if($key != 'Timestamp')
				$id .= "&" . $key . "=" . rawurlencode($val);
		}
		$canonical_string = substr($canonical_string, 1);
		$id = substr($id, 1);
		$string_to_sign = "GET\n" . $tmkm_amazon_config['Url'] . "\n" . $tmkm_amazon_config['Endpoint'] . "\n" . $canonical_string;
		
		$signature = base64_encode(
			hash_hmac('sha256', $string_to_sign, $tmkm_amazon_settings['secretkey'], true)
		);
		
		$url = $tmkm_amazon_config['JPendpoint'] . '?' . $canonical_string;

		$xmlFeed = $url . '&Signature=' . str_replace('%7E', '~', rawurlencode($signature));
		// --- Include the cache package ---
		// IMPORTANT - enter the path to Lite.php
		include_once( $tmkm_amazon_config['litephp_path'] );

		// --- Set an ID for this cache ---
		$id = $tmkm_amazon_config['JPendpoint'] . '?' . $id;
		
		/**
		* Cache options
		* "lifeTime" is in seconds, so 1 hour would be 60*60*1
		*
		* IMPORTANT - enter the path to your cache directory (a new directory with full permissions)
		*/
		
		$cacheoption = array(
			"cacheDir" => $tmkm_amazon_config['cache_dir'],
			"lifeTime" => 60*120*1
			);
		
		$objCache = new Cache_Lite($cacheoption);
	
		// Check to see if there is a valid cache of xml
		if ($xmlCache = $objCache->get($id)) {  // there is a cache, so parse cached xml
			include_once( $tmkm_amazon_config['xmlphp_path'] );
			$parsedata = XML_unserialize($xmlCache);
			return $parsedata;
		}else{
			$data = @implode("",file($xmlFeed));

			if(!strpos($data, 'xml')){ // there is no XML data in the string (Amazon's Web Services are down)
				return false;
			} else { // there is XML data, so parse the XML

				include_once( $tmkm_amazon_config['xmlphp_path'] );
				$parsedata = XML_unserialize($data);
				// --- Cache the XML ---
				$ret = $objCache->save($data, $id);
				
				return $parsedata;
			}
		}
		
	}
}

class generalFuncLibrary {

	function calc_chkdgt_mod10( $val ){
		$f = 0;
		$g = 0;
		$k = 0;

		$mod_res = explode( ',',chunk_split( $val, 1, ',' ) );
		for( $ii=count( $mod_res )-1; $ii>-1; $ii-- ){
			$x=intval( $mod_res[$ii] );
			if( $f == 0 ){
				$k += $x;
				$f = 1;
			} else {
				$g += $x;
				$f = 0;
			}
		}
		$chkdgt = substr( strval( 10-intval( substr( strval( $g*3 + $k ), -1 ) ) ),-1 );
		return $val.$chkdgt;
	}

	function calc_chkdgt_isbn10( $val ){
		$g = 0;

		$mod_res = explode( ',',chunk_split( $val, 1, ',' ) );
		for( $ii=count( $mod_res )-1; $ii>-1; $ii-- ){
			$x=intval( $mod_res[$ii] );
			$g += $x*( 11-( $ii+1 ));
		}

		$checksum = (( (int)( $g/11 ) )+1)*11 - $g;

		if( $checksum == 11 ){
			$chkdgt = 0;
		} elseif( $checksum == 10 ){
			$chkdgt = 'X';
		} else {
			$chkdgt = $checksum;
		}

		return $val.$chkdgt;
	}

}
?>
