<?php
/*
Module Name : Comment Flag
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL
*/

if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSComment_Flag extends SSPins { // Just a dummy class
	// About this Pin
	var $Pinfo = array(
		'title' => 'Comment Flag',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Display country flag icon after comment author link',
		'version' => '0.2',
		'type' => 1,
	);
	// About displayable modules of this Pin
	var $Moinfo = array();

	function pin_compatible() {
		return array('compatible' => true);
	}

}//end of class

// This is real pin function
function wpSSCommenterCountryFlag($link) {
	global $withcomments;
	if((!is_single() && is_page() && !$withcomments) || is_admin())
		return $link;
	$ip = get_comment_author_IP();
	$flag = SSFunction::get_flag($ip);
	return $link.' <span class="country-flag">'.$flag.'</span>';
}
add_filter('get_comment_author_link', 'wpSSCommenterCountryFlag');

?>