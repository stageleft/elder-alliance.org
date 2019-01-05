<?php
/*
Plugin Name: WPLite
Plugin URI: http://mahalkita.nanogeex.com/wplite/
Description: Wordpress, without the fat.
Version: 1.3.1
Author: introspectif
Author URI: http://www.nanogeex.com
*/
/*  Copyright 2008  WPlite  (email: introspectif@nanogeex.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (isset($_POST['update_wplite_options']))
	update_wplite_options();

add_action('admin_menu', 'wplite_init');
add_action('admin_menu', 'wplite_disable_menus');
add_action('admin_head', 'wplite_disable_metas');

register_activation_hook(basename(__FILE__), 'set_wplite_options');
register_deactivation_hook(basename(__FILE__), 'unset_wplite_options');

wp_enqueue_script('jquery');

function wplite_init() {
	add_options_page('WPlite', 'WPlite', 8, basename(__FILE__), 'wplite_options_page');

	// save default menu and submenu, otherwise it will be permanently hidden
	global $menu, $submenu;
	update_option('wplite_default_menu', $menu);
	update_option('wplite_default_submenu', $submenu);

	$disabled_menu_items = get_option('wplite_disabled_menu_items');
	if ($disabled_menu_items === false) {
		add_option('wplite_disabled_menu_items');
		update_option('wplite_disabled_menu_items', array());
	} else if (is_string($disabled_menu_items) && is_array(unserialize($disabled_menu_items)))
		update_option('wplite_disabled_menu_items', unserialize($disabled_menu_items));

	$disabled_submenu_items = get_option('wplite_disabled_submenu_items');
	if ($disabled_submenu_items === false) {
		add_option('wplite_disabled_submenu_items');
		update_option('wplite_disabled_submenu_items', array());
	} else if (is_string($disabled_submenu_items) && is_array(unserialize($disabled_submenu_items)))
		update_option('wplite_disabled_submenu_items', unserialize($disabled_submenu_items));

	$disabled_metas = get_option('wplite_disabled_metas');
	if ($disabled_metas === false) {
		add_option('wplite_disabled_metas');
		update_option('wplite_disabled_metas', array());
	} else if (is_string($disabled_metas) && is_array(unserialize($disabled_metas)))
		update_option('wplite_disabled_metas', unserialize($disabled_metas));
}

function wplite_disable_menus() {

	if (current_user_can('manage_options') && get_option('wplite_admins_see_everything'))
		return;
	
	global $menu, $submenu;
	$disabled_menu_items = get_option('wplite_disabled_menu_items');
	$disabled_submenu_items = get_option('wplite_disabled_submenu_items');

	if (in_array('index.php', $disabled_menu_items))
		remove_the_dashboard(); 

	foreach ($menu as $index => $item) {
		if ($item == 'index.php')
			continue;

		if (in_array($item[2], $disabled_menu_items))
			unset($menu[$index]);
	
		if (!empty($submenu[$item[2]]))
			foreach ($submenu[$item[2]] as $subindex => $subitem) 
				if (in_array($subitem[2], $disabled_submenu_items))
					unset($submenu[$item[2]][$subindex]);
	}
}

function wplite_disable_metas() {

	if (current_user_can('manage_options') && get_option('wplite_admins_see_everything'))
		return;

	remove_action('admin_head', 'index_js');

	$disabled_metas = get_option('wplite_disabled_metas');
	if (!empty($disabled_metas)) {
		$metas = implode(',', $disabled_metas);
		echo '<style type="text/css">'.$metas.' {display: none !important}</style>';
	}

}

function update_wplite_options() {
	update_option('wplite_disabled_menu_items', isset($_POST['disabled_menu_items']) ? $_POST['disabled_menu_items'] : array()
	);
	update_option('wplite_disabled_submenu_items', 
		isset($_POST['disabled_submenu_items']) ? $_POST['disabled_submenu_items'] : array()
	);
	update_option('wplite_disabled_metas', 
		isset($_POST['disabled_metas']) ? $_POST['disabled_metas'] : array()
	);
	update_option('wplite_admins_see_everything',
		isset($_POST['wplite_admins_see_everything']) ? true : false
	);
}

function wplite_options_page() {

	if (isset($_POST['update_wplite_options']))
		echo '<div id="message" class="updated fade"><p>Options saved.</p></div>';

	?>
	
	<div class="wrap">
	
	<h2>WPlite</h2>
	<p><strong>WARNING:</strong> Do not change anything on this page unless you know what you are doing.

	<table border="0">
	<tr>
		<td valign="top" width="60%">

			<h3>Disable Menu Items</h3>
			<form method="post">
			
			<div style="background-color: #ccc; padding: 1em; margin-right: 1em; margin-bottom: 1em">
			<strong>Disable by Feature:</strong><br />
			<span style="white-space: nowrap">
				<input id="wplitepages" type="checkbox" onclick="wpliteDoGroup('pages')" />&nbsp;Pages
			</span>
			<span style="white-space: nowrap">
				<input id="wplitecomments" type="checkbox" onclick="wpliteDoGroup('comments')" />&nbsp;Comments/Trackbacks
			</span>
			<span style="white-space: nowrap">
				<input id="wplitelinks" type="checkbox" onclick="wpliteDoGroup('links')" />&nbsp;Links
			</span>
			<span style="white-space: nowrap">
				<input id="wplitecategories" type="checkbox" onclick="wpliteDoGroup('categories')" />&nbsp;Categories
			</span>
			<span style="white-space: nowrap">
				<input id="wplitethemes" type="checkbox" onclick="wpliteDoGroup('themes')" />&nbsp;Themes
			</span>
			<span style="white-space: nowrap">
				<input id="wplitetags" type="checkbox" onclick="wpliteDoGroup('tags')" />&nbsp;Tags
			</span>
			</div>
		
			<table border="0" style="width: 100%">
			<tr>
				<td valign="top" nowrap="nowrap" width="50%">

	<?php

	// MENU ITEMS 

	$menu              = get_option('wplite_default_menu');
	$submenu           = get_option('wplite_default_submenu');
	$disabled_items    = get_option('wplite_disabled_menu_items');
	$disabled_subitems = get_option('wplite_disabled_submenu_items');
	  
	$i = 0;
	foreach ($menu as $item) {

		if ( $i == (int) (count($menu) / 2) )
			echo '</td><td valign="top" nowrap="nowrap" width="50%">';

		// menu items
		
		$checked = (in_array($item[2], $disabled_items)) ? ' checked="checked"' : '';

		echo '<input type="checkbox"' .	$disabled .	$checked . 
			' name="disabled_menu_items[]"  value="'.$item[2].'" />&nbsp;' .	$item[0] . "<br />\n";

		if (!isset($submenu[$item[2]])) // if no submenu items, exit
			continue;

		// submenu items

		foreach ($submenu[$item[2]] as $subitem) {

			$asterisk = ($subitem[2] == basename(__FILE__)) ? 
				' <span style="color: red; font-weight: bold; font-size: 150%">*</span>' : '';
			
			$checked = (in_array($subitem[2], $disabled_subitems)) ? ' checked="checked"' : '';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox"' . $checked .
				' name="disabled_submenu_items[]" value="' . $subitem[2] . '" />&nbsp;' . $subitem[0] . 
				$asterisk . "<br />\n";

		}

		$i++;

	}

	?>
	
				</td>
			</tr>
			</table>
			
		</td>
		<td valign="top">
	
	<?php

	// PAGE/POST META ITEMS
	
	$metas = array(
		'#authordiv,#pageauthordiv',
		'#pagecommentstatusdiv,#commentstatusdiv',
		'#pagepassworddiv,#passworddiv',
		'#pageslugdiv,slugdiv',
		'#uploading',
		'#pagepostcustom,#postcustom',
		'#categorydiv',
		'#posttimestampdiv',
		'#postexcerpt',
		'#trackbacksdiv',
		'#pageparentdiv',
		'#pagetemplate,#pagetemplatediv',
		'#pageorder,#pageorderdiv',
		'#tagsdiv',
		'div.side-info'
	);
 
	$meta_names = array(
		'Author',
		'Discussion',
		'Password',
		'Slug (< 2.5)',
		'Uploading (< 2.5)',
		'Custom Fields',
		'Post Category',
		'Post Timestamp (< 2.5)',
		'Post Excerpt',
		'Post Trackbacks',
		'Page Parent',
		'Page Template',
		'Page Order', 
		'Post Tags (2.5+)', 
		'\'Related\' Links (2.5+)'
	);
	  
	$disabled_metas = get_option('wplite_disabled_metas');

	echo '<h3>Disable Page/Post Meta</h3>';
	foreach ($metas as $index => $meta) {
		$checked = (in_array($meta, $disabled_metas)) ? ' checked="checked"' : '';
		echo '<input type="checkbox"' . $checked . ' name="disabled_metas[]" value="' . $meta . '" />&nbsp;' .
			$meta_names[$index] . "<br />\n";
	}

	?>

			<h3>WPlite bookmarklet</h3>

			<p><span style="color: red; font-weight: bold; font-size: 150%">*</span>
			If you are disabling the WPlite options page, bookmark the following link 
			(a 'bookmarklet') to help you open the WPlite options page directly from anywhere within the 
			Wordpress administration area.</p>
			
			<a href="javascript:l=location.href;n=l.indexOf('wp-admin');
			if(n>0)location=l.substring(0,n+9)+'options-general.php?page=wplite.php';
			void(0);">WPlite options page</a>
	
		</td>
	</tr>
	</table> 
	
	<p class="submit">
		<input name="submit" type="submit" value="Save Options" />
		<input type="checkbox" name="wplite_admins_see_everything" value="1" <?php
			if (get_option('wplite_admins_see_everything')) echo 'checked="checked"'; ?> />
		<label for="wplite_admins_see_everything">Administrators see everything</label></p> 
	
	<input type="hidden" name="update_wplite_options" value="1" /></form></div>

	<script type="text/javascript">
	$j=jQuery.noConflict();
	var wpliteGroups = { 
		'pages' : [
			'page-new.php',
			'edit-pages.php',
			'#pageparentdiv',
			'#pagetemplate,#pagetemplatediv',
			'#pageorder,#pageorderdiv'
		],
		'comments' : [
			'edit-comments.php',
			'moderation.php',
			'options-discussion.php',
			'#trackbacksdiv',
			'#pagecommentstatusdiv,#commentstatusdiv'
		],
		'links' : [
			'link-manager.php',
			'link-add.php',
			'link-import.php',
			'edit-link-categories.php'
		],
		'categories' : [
			'categories.php',
			'edit-link-categories.php',
			'#categorydiv',
			'div.side-info'
		],
		'themes' : [
			'themes.php',
			'widgets.php',
			'theme-editor.php'
		],
		'tags' : [
			'edit-tags.php',
			'#tagsdiv',
			'div.side-info'
		]
	};
	
	function wpliteInitGroups() {
		var groups = ['pages','comments','links','categories','themes'];
		for (var i = 0; i < groups.length; i++) {
			curGroup = eval('wpliteGroups.'+groups[i]);
			var allChecked = true;
			for (var j = 0; j < curGroup.length; j++) {
				$j("input[@value='"+curGroup[j]+"']").each(function(){
					if (!this.disabled && this.checked == false)
						allChecked = false;
				});
			}
			if (allChecked)
				$j('#wplite'+groups[i]).each(function(){
					this.checked = true;
				});
		}
	}
	wpliteInitGroups();

	function wpliteDoGroup(group) {
		if ($j('#wplite'+group).is(':checked'))
			wpliteEnableGroup(group);
		else
			wpliteDisableGroup(group);
	}
	
	function wpliteEnableGroup(group) {
		var items = eval('wpliteGroups.'+group);
		for (var i = 0; i < items.length; i++) 
			wpliteDisable(items[i]);
	}

	function wpliteDisableGroup(group) {
		var items = eval('wpliteGroups.'+group);
		for (var i = 0; i < items.length; i++) 
			wpliteEnable(items[i]);
	}
		
	function wpliteEnable(pageUrl) {
		$j("input[@value='"+pageUrl+"']").each(function(){ 
			if (!this.disabled) 
				this.checked = false; 
		});
	}

	function wpliteDisable(pageUrl) {
		$j("input[@value='"+pageUrl+"']").each(function(){ 
			if (!this.disabled) 
				this.checked = true;
		});
	}
	</script>
	
	<?php
}

function set_wplite_options() {
	add_option('wplite_default_menu');
	add_option('wplite_default_submenu');
	add_option('wplite_disabled_menu_items');
	add_option('wplite_disabled_submenu_items');
	add_option('wplite_disabled_metas');
	add_option('wplite_admins_see_everything');

	update_option('wplite_disabled_menu_items', array());
	update_option('wplite_disabled_submenu_items', array());
	update_option('wplite_disabled_metas', array());
	update_option('wplite_admins_see_everything', false);
}

function unset_wplite_options() {
	delete_option('wplite_default_menu');
	delete_option('wplite_default_submenu');
	delete_option('wplite_disabled_menu_items');
	delete_option('wplite_disabled_submenu_items');
	delete_option('wplite_disabled_metas');
	delete_option('wplite_admins_see_everything');
}

function remove_the_dashboard() {
	global $menu, $submenu, $user_ID;
	$the_user = new WP_User($user_ID);
	reset($menu); $page = key($menu);
	while ((__('Dashboard') != $menu[$page][0]) && next($menu))
		$page = key($menu);
	if (__('Dashboard') == $menu[$page][0]) unset($menu[$page]);
		reset($menu); $page = key($menu);
	while (!$the_user->has_cap($menu[$page][1]) && next($menu))
		$page = key($menu);
	if (preg_match('#wp-admin/?(index.php)?$#',$_SERVER['REQUEST_URI']) && ('index.php' != $menu[$page][2]))
		wp_redirect(get_option('siteurl') . '/wp-admin/' . $menu[$page][2]);
}

?>
