<?php
/*
Plugin Name: To Do List
Plugin URI: http://www.presscoders.com/to-do-list/
Description: Easily keep track of daily tasks and activities. Every registered user can maintain an individual to-do list using the rich text editor built-in to WordPress.
Version: 2.0
Author: David Gwyer
Author URI: http://www.presscoders.com/
*/

/*
    Copyright 2009 David Gwyer (email : d.v.gwyer(at)presscoders.com)

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

/*

@todo

- Add i18n support.
- If a Plugin options page is added then also add a Settings link on the main Plugin list page.
- Add to-do list meta info on revisions, last edit, by who, time etc. to the users profile page.
- Implement a to-do list revisions (next version). Save this as user meta data when the to do list is updated. You could even store changes made which would be cool.
- Bug: In Chrome the user profile editor in HTML mode won't stretch across the screen.
- Add CSS to separate file and enqueue on dashboard page only.
- Need to show output as it appears on the to-do list editor. At the moment if you format some text with one of the headings in TinyMCE it uses a WordPress style when viewed on the dashboard which looks a little weird.

*/

/* Plugin Prefix. */
/* To Do List prefix pctdl_ made up from p[ress] c[oders] t[o] d[o] l[ist] */

/* WordPress Hooks. */
register_activation_hook( __FILE__, 'pctdl_legacy_todo_lists' );
add_action( 'admin_init', 'pctdl_init' );
add_action( 'wp_dashboard_setup', 'pctdl_add_dashboard_widgets' );

function pctdl_legacy_todo_lists() {

	/* Update old style to-do lists. */
	$users = get_users();
	$all_options = wp_load_alloptions();

	foreach ($users as $user) {
		/* Check if there is a legacy to-do list for this user. */
		if( array_key_exists( 'todo-'.$user->ID, $all_options ) ) {
			$current_user_content = get_the_author_meta( 'pctdl_user_todolist_content', $user->ID ); /* Get current (new) to-do list. */
			update_user_meta( $user->ID, 'pctdl_user_todolist_content', $current_user_content.'<br />'.$all_options['todo-'.$user->ID] ); /* Amend (new) with legacy to-do list. */
			delete_option( 'todo-'.$user->ID ); /* Delete legacy to-do list. */
		}
	}
} 

function pctdl_init(){

	add_action( 'show_user_profile', 'pctdl_show_user_fields' );
	add_action( 'edit_user_profile', 'pctdl_show_user_fields' );
	add_action( 'personal_options_update', 'pctdl_save_user_fields' );
	add_action( 'edit_user_profile_update', 'pctdl_save_user_fields' );
}

function pctdl_add_dashboard_widgets() {
	wp_add_dashboard_widget( 'pctdl_to_do_list', 'To Do List', 'pctdl_to_do_list_dashboard_widget_cb' );	
} 

function pctdl_to_do_list_dashboard_widget_cb() {

	/* Logged in user information. */
	$current_user = wp_get_current_user();
	$caps = $current_user->roles; /* Get all the users capabilities into a single array. */
	$user_profile_link = get_admin_url().'profile.php';
	echo '<h4>Welcome back '.$current_user->display_name.'!</h4>';
	$content = get_the_author_meta( 'pctdl_user_todolist_content', $current_user->ID );

	/* Build select drop down HTML. */
	$users = get_users();
	$select_box = '<select onchange="if(this.value) window.location.href=this.value">';
	$select_box .= '<option selected="selected">- Edit To Do Lists -</option>';

	foreach ($users as $user) {
		$user_profile_url = get_admin_url().'user-edit.php?user_id='.$user->ID.'#to-do-list-profile';
		$select_box .= '<option value="'.$user_profile_url.'">'.$user->display_name.'</option>';
	}
	$select_box .= '</select>';

	?>

	<div>
		<?php if( !empty($content) ) : ?>
		<p style="margin:6px 0;padding-top:5px;float:left;">Your current to-do list is shown below. Click <strong><a href="<?php echo $user_profile_link.'#to-do-list-profile'; ?>">here</a></strong> to update it.</p>
		<?php else : ?>
		<p style="background-color:lightYellow;border:1px #E6DB55 solid;margin:10px 0;padding:4px 0 4px 6px;">Your to-do list is currently empty. Click <strong><a href="<?php echo $user_profile_link.'#to-do-list-profile'; ?>">here</a></strong> to start adding some tasks!</p>
		<?php endif; ?>

		<?php if (in_array("administrator", $caps)) : ?>
		<p style="margin:6px 0;float:right;">Admin: <?php echo $select_box; ?></p>
		<?php endif; ?>
	</div>

	<?php if( !empty($content) ) : ?>

	<div id="user-todolist-container" style="clear:both;height:200px;overflow:auto;background-color:#fff;border:1px #dfdfdf solid;padding:0 10px;"><?php echo wpautop($content); ?></div>
	<?php endif; ?>

	<div style="clear:both;">
		<p>If you like this free Plugin please consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5YY6QK2LA377J" target="_blank">donation</a> to keep it alive!</p>
		<p style="margin-top:10px;"><a href="http://www.facebook.com/PressCoders" title="Our Facebook page" target="_blank"><img src="<?php echo plugins_url(); ?>/to-do-list/images/facebook.png" /></a><a href="http://www.twitter.com/dgwyer" title="Follow on Twitter" target="_blank"><img src="<?php echo plugins_url(); ?>/to-do-list/images/twitter.png" /></a>&nbsp;<input class="button" style="vertical-align:12px;" type="button" value="Visit Our Site" onClick="window.open('http://www.presscoders.com')">&nbsp;<input class="button" style="vertical-align:12px;" type="button" value="Free Responsive Theme!" onClick="window.open('http://www.presscoders.com/designfolio')"></p>
	</div>

	<?php

} 

function pctdl_show_user_fields($user) {
	?>

	<h3 id="to-do-list-profile">To Do List</h3>
	
	<span class="description">Edit your to-do list below using the built-in WordPress editor. <a href="<?php echo get_admin_url(); ?>">Back to dashboard</a></span>

	<table class="form-table">
		<tr>
			<td>
				<?php
					$cont = get_the_author_meta( 'pctdl_user_todolist_content', $user->ID);
					$args = array('textarea_name' => 'pctdl_user_todolist_content');
					wp_editor( $cont, 'pctdl_user_todolist_content', $args );
				?>

				<div style="margin-top:15px;">
					<p style="margin-bottom:10px;">If you use the To Do List Plugin regularly then please consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5YY6QK2LA377J" target="_blank">donation</a> to support continued development.</p>
				</div>
			</td>
		</tr>
	</table>

	<?php
}

function pctdl_save_user_fields($user_id) {
	update_user_meta($user_id, 'pctdl_user_todolist_content', ( isset($_POST[ 'pctdl_user_todolist_content']) ? $_POST[ 'pctdl_user_todolist_content'] : '' ) );
}

?>