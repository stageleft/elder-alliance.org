<?php
/*
Plugin Name:  WP Dash Note
Plugin URI: http://www.maxpower.ca/wordpress-plugins/
Description:  Write a note to yourself and view your drafts, all from within the dashboard.
Version: RC1
Author: Kirk Montgomery
Author URI: http://www.maxpower.ca
*/

/*  Copyright 2006  Kirk Montgomery  (email : webmaster@maxpower.ca)

      This wordpress plugin is released under the Creative Commons Deed 
                    Attribution-ShareAlike 2.5 Canada
              http://creativecommons.org/licenses/by-sa/2.5/ca/
      
      You are free to:
        - copy, distribute, display, and perform the work
        - make derivative works
        - make commercial use of the work
      
      Under the following conditions:
        - You may not exchange or sell this code (or any derivitive of it)  for 
              goods or services
        - If you alter, transform, or build upon this work, you may distribute 
              the resulting work only under a licence identical to this one
        - Derivative works must keep intact notice of original source (www.maxpower.ca)
*/

/*---- add appropriate css to admin section only for use with admin_head ----- */

function custom_dash_css() { ?>
<link rel="stylesheet" type="text/css" media="screen" href="<? echo get_bloginfo('wpurl'); ?>/wp-content/plugins/dash-note/dash-note-admin.css" />
<?php
}

/*---- the function that does all the work ----- */
function custom_dash_options() { 
global $wpdb;
if (isset($_POST["custom_update_pushed"])) {
			//update values in db
  			$dash_note_textarea = $_POST["dash-note"];
      	update_option("dash-note", $dash_note_textarea);
        } 

//global $dash_note_textarea, $dash_note_datecheck;

$dash_note_textarea = get_option("dash-note");
$dash_note_datecheck = get_option("dash_note_datecheck");


// Don't mess with the version #
$DN_installedVersion = "RC1";
// Don't mess with the version #

// Now check to see if should check if there is an update

$WP_DN_date_today = mktime(0,0,0,date('m'),date('d'),date('Y'));
$WP_DN_total_days = Round((($WP_DN_date_today-$dash_note_datecheck)/86400), 0);

	?>
  	<h3>Dash Note<?php 
    // 2. Check if difference is greater or equal to 7, if so update the stored date and perform the check [a blank date results ina big diff]

    if ($WP_DN_total_days >= 3) { //Change back to 7
      update_option("dash_note_datecheck", $WP_DN_date_today); ?>
      <script src="http://www.maxpower.ca/wp-content/upgrade/dash_note_version_check.php?version=<?php echo $DN_installedVersion; ?>" type="text/javascript"></script> 
  <?php } ?>
    </h3>
  	<form method="post">
  	<fieldset class="dash-note">
    <textarea name="dash-note" cols="40" rows="4" class="dash-note" /><? echo $dash_note_textarea ?></textarea>
    </fieldset>
   	<input type="hidden" name="custom_update_pushed" value="1"/>
  	<p class="submit"><input type="submit" name="Submit" value="<?php _e('Post-it') ?> &raquo;" /></p>
  	</form>

<?php 
// List drafts here
$drafts = @$wpdb->get_results("SELECT post_title, ID FROM $wpdb->posts WHERE post_status = 'draft' ORDER BY ID ASC"); 
if ($drafts != '') {
$i = 0;
      foreach ($drafts as $draft) {
      if ($i == 0) { echo "<p><strong>Your Drafts:</strong> "; }
      if (0 != $i)
      			echo ', ';
          $draft->post_title = stripslashes($draft->post_title);
      		if ($draft->post_title == '')
      			$draft->post_title = sprintf(__('Post #%s'), $draft->ID);
      		echo "<a href='post.php?action=edit&amp;post=$draft->ID' title='" . __('Edit this draft') . "'>$draft->post_title</a>";
      		++$i;
      }
echo '.</p>';
}

}	
/*---- the hook to add it to the dashboard ---- */
add_action('admin_head', 'custom_dash_css');
add_action('activity_box_end','custom_dash_options'); 
?>
