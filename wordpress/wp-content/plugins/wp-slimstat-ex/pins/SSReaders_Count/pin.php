<?php
/*
Module Name : Readers Count
Module URI : http://082net.com/tag/wp-slimstat-ex/
Author : Cheon, Young-Min
Author URI : http://082net.com/
License : All Wp-SlimStat-Ex Pins are GPL

Description ::
	If you want to automatically display readers count on all posts or page ::
		:: just set $Readers_Count_Conf['use_filter'] to 1 or 2

	Else, you can manually display readers count by insert line below within '''wordpress loop''' :: ( replace &gt; with > )
		:: <?php if(function_exists('wpss_print_post_reader_count')) wpss_print_post_reader_count($text, $before, $after); ?&gt;
		:: $text => customize your message text, %count% will be replaced with it's readers count.
		:: $before => html tags or text before $text
		:: $after => html tags or text after $text
		:: e.g. wpss_print_post_reader_count('%count% people read this post', '<span class="readers_count">', '</span>');
*/

/* DO NOT EDIT BELOW LINES */
if (!defined('SLIMSTATPATH')) { header('Location:/'); }

class SSReaders_Count extends SSPins { // Just a dummy class
	// About this Pin
	var $Pinfo = array(
		'title' => 'Readers Count',
		'author' => '082net',
		'url' => 'http://082net.com',
		'text' => 'Display how many people read current post or page',
		'version' => '0.2',
		'type' => 1,
	);

	// About displayable modules of this Pin
	var $Moinfo = array();
	var $op;

	function SSReaders_Count() {
		/* nothing */
	}

	function pin_compatible() {
		global $SlimCfg;
		if($SlimCfg->version < '1.6') {
			return array	('compatible' => false, 'message' => 'Readers Count is only compatible with SlimStat-Ex 1.6 and above.');
		} else {
			return array('compatible' => true);
		}
	}

	function pin_actions() {
		return array( 'options' => 1, 'extra_table' => 0 );
	}

	function pin_update_options() {
		if(!isset($_POST['readers_count_pin']))
			return;
		$use_filter = (int)$_POST['readers_count_pin'];
		$this->update_option('readers_count_use_filter', $use_filter);
	}

	function pin_options() {
		$use_filter = $this->get_option('readers_count_use_filter');
?>
<table width="100%" cellspacing="2" cellpadding="5" class="editform"> 
<tr valign="top"> 
	<th width="20%" scope="row"><?php _e('Use Filter:', 'wp-slimstat-ex') ?></th> 
	<td><?php _e('Select display position of readers count', 'wp-slimstat-ex'); ?><br />
	<?php _e('You can manually insert &lt;?php wpss_print_post_reader_count(); ?&gt; on your template file', 'wp-slimstat-ex'); ?><br />
		<select name="readers_count_pin">
			<option value="0"<?php if(!$use_filter) { ?> selected="selected"<?php } ?>>NO</option>
			<option value="1"<?php if($use_filter==1) { ?> selected="selected"<?php } ?>>Top of content</option>
			<option value="2"<?php if($use_filter==2) { ?> selected="selected"<?php } ?>>Bottom of content</option>
		</select></td>
</tr>
</table>
<?php
	}

}//end of class

// This is real pin functions
function wpss_get_post_reader_count() {
	global $wpdb, $SlimCfg, $post;
	$link = apply_filters('the_permalink', get_permalink());
	$link = str_replace('http://'.$_SERVER['HTTP_HOST'], '', $link);
	$title = $wpdb->escape($post->post_title);
	if(get_option('permalink_structure') != '') {
		$link = rtrim($link, '/');
	}
	$count = $wpdb->get_var("SELECT COUNT(ts.resource) AS count 
			FROM $SlimCfg->table_stats ts, $SlimCfg->table_resource tr
			WHERE tr.id = ts.resource AND (tr.rs_string LIKE '{$link}' OR tr.rs_title LIKE '{$title}')");
	return $count;
}

function wpss_print_post_reader_count( $text = 'This post was %count% times read.',
						$before = '<p class="post-read-count">', 
						$after='</p>', 
						$echo = true ) {
	global $SlimCfg;
	$count = wpss_get_post_reader_count();
	$html = $before . str_replace('%count%', $count, $text) . $after;
	if($echo)
		echo $html;
	else
		return $html;
}

function wpss_post_reader_count_filter($content) {
	global $SlimCfg;
	$use_filter = (int)SSPins::get_option('readers_count_use_filter');
	if(!$use_filter)
		return $content;
	$count = wpss_get_post_reader_count();
//	$count = (0 == $count) ? 'no one':$count.' people';
	$count_msg = '<p class="post-read-count">'.sprintf(__('This post was read %s times until now', 'wp-slimstat-ex'), $count).'</p>';
	switch($use_filter) {
		case '1':default:
		$content = $count_msg.$content;
		break;
		case '2':
		$content = $content.$count_msg;
		break;
	}
	return $content;
}
add_filter('the_content', 'wpss_post_reader_count_filter');

?>