<?php

class wp_slimstat_ex_options {
	var $page, $wpssop, $url, $pinpage, $adminpage, $exclusionpage, $admin_url;

	function wp_slimstat_ex_options() {
		global $SlimCfg;
		$this->page = $_GET['page'];
		$this->wpssop = isset($_GET['wpssop']) ? $_GET['wpssop'] : false;
		$this->url = $SlimCfg->option_page;
		$this->admin_url = get_option('siteurl') . '/wp-content/plugins/' . $SlimCfg->basedir . '/lib/ss-admin';
		$this->pinpage = $this->wpssop == 'pin';
		$this->adminpage = $this->wpssop == 'admin';
		$this->exclusionpage = $this->wpssop == 'exclusion';
		$this->permissionpage = $this->wpssop == 'permission';
	}

	function update_options() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('manage_slimstat_options')) {
			return;
		}
		if (!$this->wpssop) {
			if ( !isset($_POST['ssex_op']) || !is_array($_POST['ssex_op']) )// Main Options
				return;
			$intvals = array( 'tracking', 'usepins', 'guesstitle', 'cachelimit', 'limitrows', 'dbmaxage', 'iptohost', 'whois', 'meta', 'time_offset', 'use_ajax', 'nice_titles' );
			foreach($_POST['ssex_op'] as $key=>$value) {
				$SlimCfg->option[$key] = (in_array($key, $intvals)) ? (int)$value : stripslashes(trim($value));
			}
			update_option('wp_slimstat_ex', $SlimCfg->option);
			echo '<div id="message" class="updated fade"><p>'.__('Wp-SlimStat-Ex options updated', 'wp-slimstat-ex').'</p></div>';
		} elseif ($this->pinpage)
			$this->manage_pins();
		elseif ($this->exclusionpage)
			$this->manage_exclusions();
		elseif ($this->adminpage)
			$this->manage_admin_options();
		elseif ($this->permissionpage)
			$this->manage_permissions();
	}

	function manage_admin_options() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('manage_options'))
			return;
		if ( isset($_POST['do_reimport']) ) {
			require(SLIMSTATPATH . 'lib/setup.php');
			$rebuild = SSSetup::rebuild_iptc_db();
			if ($rebuild)
				echo '<div id="message" class="updated fade"><p>'.__('ip-to-country data imported successfully.', 'wp-slimstat-ex').'</p></div>';
			else 
				echo '<div id="message" class="updated fade"><p>'.__('Failed to import ip-to-country data.', 'wp-slimstat-ex').'</p></div>';
		} elseif ( isset($_POST['slimstat_update_file']) ) {
			require(SLIMSTATPATH . 'lib/update.php');
			if (isset($_POST['update_geodata_country'])) {
				SSUpdate::do_update('geocountry_dat', $this->url . '&amp;wpssop=admin');
			} elseif (isset($_POST['update_geodata_city'])) {
				SSUpdate::do_update('geocity_dat', $this->url . '&amp;wpssop=admin');
			}
		}
	}

	function manage_exclusions() {
		global $SlimCfg;
		if ( isset($_POST['ig_op']) && is_array($_POST['ig_op']) ) {
			$checkboxes = array('ig_bots', 'ig_feeds', 'ig_validators', 'ig_tools');
			foreach($checkboxes as $box)
				$SlimCfg->exclude[$box] = isset($_POST['ig_op'][$box]);
			$intvals = array('ignore_bots');
			foreach($_POST['ig_op'] as $key=>$value) {
				if (!in_array($key, $checkboxes))
					$SlimCfg->exclude[$key] = (in_array($key, $intvals))?(int)$value:stripslashes(trim($value));
			}
			update_option('wp_slimstat_ex_exclude', $SlimCfg->exclude);
			echo '<div id="message" class="updated fade"><p>'.__('Wp-SlimStat-Ex options updated', 'wp-slimstat-ex').'</p></div>';
		}
	}

	function manage_permissions() {
		global $SlimCfg, $wp_roles;
		if (!$SlimCfg->has_cap('manage_options'))
			return;
		if ( isset($_POST['ssex_perm']) && is_array($_POST['ssex_perm']) ) {
			$new = $_POST['ssex_perm'];
			$roles = array_keys($wp_roles->role_names);
			if (empty($roles)) {
				echo '<div id="message" class="error fade"><p>'.__('No Roles.', 'wp-slimstat-ex').'</p></div>';
				return;
			}
			foreach ($roles as $role) {
				if ( !isset($new[$role]) )
					$new[$role] = array();
			}
			if ($SlimCfg->caps != $new) {// if option has changed
				$SlimCfg->caps = array_merge($SlimCfg->caps, $new);
				update_option('wp_slimstat_ex_caps', $SlimCfg->caps);
				$SlimCfg->check_caps(true);// force update role caps
			}
			echo '<div id="message" class="updated fade"><p>'.__('Permissions updated.', 'wp-slimstat-ex').'</p></div>';
		}
	}

	function manage_pins() {
		global $SlimCfg, $wpdb;
		if ( isset($_GET["pinact"]) && $_GET["pinact"] != '' ) {
			$pin_id = (int)$_GET['pinid'];
			$pin_active = (int)$_GET['pinact'];

//			require_once(SLIMSTATPATH . 'lib/pins.php');
			$pin = SSPins::getPin($pin_id);
			if (!$pin) {
				echo '<div id="message" class="updated fade"><p>'.__('Invalid Pin ID.', 'wp-slimstat-ex').'</p></div>';
				return;
			}

			$pin_file = SLIMSTATPATH . 'pins/'.$pin->name.'/pin.php';
			if (!file_exists($pin_file)) {
				SSPins::delete_pin("`id` = $pin->id");
				echo '<div id="message" class="updated fade"><p>'.__('Pin file does not exists.', 'wp-slimstat-ex').'</p></div>';
				return;
			}

			if ($pin_active) {
				if ($pin->active) {
					echo '<div id="message" class="updated fade"><p>'.__('Pin already activated.', 'wp-slimstat-ex').'</p></div>';
					return;
				}

				$default_compatible = SSPins::pin_compatible();

				ob_start();
				@include_once($pin_file);
				if (!class_exists($pin->name)) {
					ob_end_clean();
					echo '<div id="message" class="updated fade"><p>'.$default_compatible['message'].'</p></div>';
					return;
				}

				eval('$temp_pin = new $pin->name();');
				if ('sspins' != strtolower(get_parent_class($temp_pin))) {
					ob_end_clean();
					echo '<div id="message" class="updated fade"><p>'.$default_compatible['message'].'</p></div>';
					return;
				}

				$compatible = $temp_pin->pin_compatible();
				if (!$compatible['compatible']) {
					ob_end_clean();
					if (isset($compatible['message']) && !empty($compatible['message']))
						echo '<div id="message" class="updated fade"><p>'.$compatible['message'].'</p></div>';
					else 
						echo '<div id="message" class="updated fade"><p>'.$default_compatible['message'].'</p></div>';
					return;
				}

				$actions = $temp_pin->pin_actions();
				if ( $actions['extra_table'] && isset($temp_pin->extra_table) && 
						is_array($temp_pin->extra_table) && !empty($temp_pin->extra_table)) {// dobule check if Pin really needs extra table
					$create_table = SSPins::maybe_create_extra_table($temp_pin->extra_table);
					if (!$create_table) {
						ob_end_clean();
						echo '<div id="message" class="updated fade"><p>'.__('Failed to activate Pin', 'wp-slimstat-ex').'</p></div>';
						return;
					}
				}
				$temp_pin->activate_action();
				ob_end_clean();
			} else {
				if (!$pin->active) {
					echo '<div id="message" class="updated fade"><p>'.__('Pin already deactivated.', 'wp-slimstat-ex').'</p></div>';
					return;
				}
				ob_start();
				@include_once($pin_file);
				if (class_exists($pin->name)) {
					eval('$temp_pin = new $pin->name();');
					if ('sspins' == strtolower(get_parent_class($temp_pin)))
						$temp_pin->deactivate_action();
				}
				ob_end_clean();
			}
			$query = "UPDATE $SlimCfg->table_pins SET active = $pin_active WHERE id = $pin_id LIMIT 1";
			if ($wpdb->query($query) !== false) {
				if ($pin_active)
					$message = __('Pin activated', 'wp-slimstat-ex');
				else
					$message = __('Pin deactivated', 'wp-slimstat-ex');
			} else {
				if ($pin_active)
					$message = __('Failed to activate Pin', 'wp-slimstat-ex');
				else
					$message = __('Failed to deactivate Pin', 'wp-slimstat-ex');
			}
			echo '<div id="message" class="updated fade"><p>'.$message.'</p></div>';
			return;
		}
	}

	function nav_bar() {
		global $SlimCfg;
?>
		<div id="wpssoptionselector" style="font-size:18px;">
<?php 
if ($SlimCfg->has_cap('manage_slimstat_options')) {
	echo '
	<span>'.($this->wpssop ? '<a href="'.$this->url.'" title="'.__('General Options').'">':'').__('General Options', 'wp-slimstat-ex').($this->wpssop ? '</a>':'').'</span> | ';
	if ($SlimCfg->option['usepins']) {
		echo '
	<span>'.(!$this->pinpage ?'<a href="'.$this->url.'&amp;wpssop=pin" title="'.__('Pins', 'wp-slimstat-ex').'">':'').__('Pins', 'wp-slimstat-ex').(!$this->pinpage ? '</a>':'').'</span> | ';
	}
	echo '
	<span>'.(!$this->exclusionpage ?'<a href="'.$this->url.'&amp;wpssop=exclusion" title="'.__('Exclusions', 'wp-slimstat-ex').'">':'').__('Exclusions', 'wp-slimstat-ex').(!$this->exclusionpage ? '</a>':'').'</span> | ';
}
if ($SlimCfg->has_cap('manage_options')) {
	echo '
	<span>'.(!$this->permissionpage ?'<a href="'.$this->url.'&amp;wpssop=permission" title="'.__('Permissions', 'wp-slimstat-ex').'">':'').__('Permissions', 'wp-slimstat-ex').(!$this->permissionpage ? '</a>':'').'</span> | ';
	echo '
	<span>'.(!$this->adminpage ?'<a href="'.$this->url.'&amp;wpssop=admin" title="'.__('SlimStat-Admin', 'wp-slimstat-ex').'">':'').__('SlimStat-Admin', 'wp-slimstat-ex').(!$this->adminpage ? '</a>':'').'</span>';
}
?>
		</div>
		<br />
<?php
	}

	function options_page() {
		global $SlimCfg;
		if (!$SlimCfg->has_cap('manage_slimstat_options')) {
			echo '<div id="message" class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
		$this->update_options();
?>
<div class="wrap">
  <h2><?php _e('Wp-SlimStat-Ex', 'wp-slimstat-ex') ?></h2>

	<?php $this->nav_bar(); ?>

<?php /********** Main Option Page ***********/
	if (!$this->wpssop) { ?>
	<form name="slimstat_option" method="post" action="<?php echo $this->url; ?>"> 
    <!-- <p class="submit" style="float:right;"><input type="submit" name="update_options" value="<?php _e('Update Options', 'wp-slimstat-ex') ?> &raquo;" /></p> -->

		<div class="options">
<!-- General Options Start -->
		<h3><?php _e('General Options', 'wp-slimstat-ex'); ?></h3>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table" id="optiontable1"> 
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Enable Tracking?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[tracking]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['tracking']); ?>>enable</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['tracking']); ?>>disable</option>
          </select><br />
<?php _e('&mdash; If you want to track blog visitors select &quot;enable&quot;', 'wp-slimstat-ex') ?></td> 
      </tr>
<?php if ($SlimCfg->upOption) { //dev?>
			<tr valign="top">
        <th width="25%" scope="row"><?php _e('Track Mode:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[track_mode]">
          <option value="full"<?php selected('full', $SlimCfg->option['track_mode']); ?>><?php _e('Entire blog', 'wp-slimstat-ex'); ?></option>
          <option value="footer"<?php selected('footer', $SlimCfg->option['track_mode']); ?>><?php _e('Blog pages only', 'wp-slimstat-ex'); ?></option>
          <option value="footer_feed"<?php selected('footer_feed', $SlimCfg->option['track_mode']); ?>><?php _e('Blog pages and feed', 'wp-slimstat-ex'); ?></option>
          </select><br />
<?php _e('&mdash; If you need stats for REAL visitors, \'Blog pages only\' would be the one.', 'wp-slimstat-ex') ?></td> 
      </tr>
<?php } ?>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Use Pins?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[usepins]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['usepins']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['usepins']); ?>>false</option>
          </select><br />
<?php _e('&mdash; If you want to use Pins select &quot;true&quot;', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Use AJAX?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[use_ajax]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['use_ajax']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['use_ajax']); ?>>false</option>
          </select><br />
<?php _e('&mdash; Use &quot;AJAX&quot; or not?. Setting it to false will disable some modules.', 'wp-slimstat-ex') ?></td> 
      </tr>
<?php if ($SlimCfg->option['use_ajax']) { ?>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php echo __('Ajax cache limit:', 'wp-slimstat-ex') ?></th> 
        <td><input name="ssex_op[cachelimit]" type="text" value="<?php echo $SlimCfg->option['cachelimit']; ?>" size="3" /> 
		  <?php echo __('minutes', 'wp-slimstat-ex'); ?><br />
<?php echo __('&mdash; Cache time of Ajax result page by minutes. (disable cache = 0)', 'wp-slimstat-ex') ?></td> 
      </tr>
<?php } ?>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('SQL limit rows:', 'wp-slimstat-ex') ?></th> 
        <td><input name="ssex_op[limitrows]" type="text" value="<?php echo $SlimCfg->option['limitrows']; ?>" size="3" /> 
		  <?php _e('rows', 'wp-slimstat-ex'); ?><br />
<?php _e('&mdash; Limit rows of each modules', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('DB max-age:', 'wp-slimstat-ex') ?></th> 
        <td><input name="ssex_op[dbmaxage]" type="text" value="<?php echo $SlimCfg->option['dbmaxage']; ?>" size="3" /> 
		  <?php _e('days', 'wp-slimstat-ex'); ?><br />
<?php _e('&mdash; Set database max-age by days (disable reduce DB : 0)', 'wp-slimstat-ex') ?><br />
<?php _e('&mdash; You can reduce DB from ', 'wp-slimstat-ex') ?>"<a href="<?php echo $this->admin_url; ?>/admin.php">ss-admin</a>"<?php _e(' page', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('IPTC method:', 'wp-slimstat-ex') ?></th> 
<?php if ($SlimCfg->geoip == 'mysql') { ?>
        <td><ul>
<?php if ($SlimCfg->external_iptc == 'external') { ?>
				<li><?php _e('You are using external IPTC remote DB', 'wp-slimstat-ex'); ?></li>
<?php } else { ?>
				<li><?php _e('You are using Mysql IPTC DB', 'wp-slimstat-ex'); ?></li>
<?php } ?>
				<li><?php _e('You can use GeoIP database(<a href="http://www.maxmind.com/app/geoip_country">GeoIP.dat</a> or <a href="http://www.maxmind.com/app/geolitecity">GeoLiteCity.dat</a>) from <a href="http://www.maxmind.com">MaxMind</a>.', 'wp-slimstat-ex'); ?></li>
				<li><?php _e('As for the GeoSlimStat Pin, it\'s about 100 times faster than remote query (with <a href="http://www.maxmind.com/app/geolitecity">GeoLiteCity.dat</a>)', 'wp-slimstat-ex'); ?></li>
				<li><?php _e('Upload GeoIP database(GeoIP.dat or GeoLiteCity.dat) file to <u>lib/geoip</u> folder, that\'s all', 'wp-slimstat-ex'); ?></li></ul>
<?php } else { ?>
        <td><ul>
<?php 
			$geo_file = $SlimCfg->geoip == 'city' ? 'GeoLiteCity.dat' : 'GeoIP.dat';
			$geo_url = 'http://www.maxmind.com/app/' . ($SlimCfg->geoip == 'city' ? 'geolitecity' : 'geoip_country');
?>
				<li><?php printf(__('You are using GeoIP databse(%s)', 'wp-slimstat-ex'), $geo_file); ?></li>
				<li><?php printf(__('You can update your database file every start of month from <a href="http://www.maxmind.com">MaxMind</a>\'s free <a href="%s">GeoIP Source</a> page.', 'wp-slimstat-ex'), $geo_url); ?></li></ul>
<?php } ?>
<?php _e('&mdash; IP to Country Resource', 'wp-slimstat-ex') ?></td> 
      </tr>
			</table>
<!-- General Options End -->
		</div>
		<div class="options">
<!-- Display Options Start -->
		<h3><?php _e('Display Options', 'wp-slimstat-ex'); ?></h3>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table" id="optiontable2"> 
			<tr valign="top">
        <th width="25%" scope="row"><?php _e('Visit type:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[visit_type]">
          <option value="uniques"<?php selected('uniques', $SlimCfg->option['visit_type']); ?>>uniques</option>
          <option value="visits"<?php selected('visits', $SlimCfg->option['visit_type']); ?>>visits</option>
          </select><br />
<?php _e('&mdash; Select visit type. uniques: count unique ip, visits: 30-minute intervals', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Guess post title?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[guesstitle]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['guesstitle']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['guesstitle']); ?>>false</option>
          </select><br />
<?php _e('&mdash; Get post title from resource(page address)', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Get host name?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[iptohost]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['iptohost']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['iptohost']); ?>>false</option>
          </select><br />
<?php _e('&mdash; Get host name from remote address', 'wp-slimstat-ex') ?>(IP)</td> 
      </tr>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Use Whois link?:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[whois]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['whois']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['whois']); ?>>false</option>
          </select> &mdash; 
					<select name="ssex_op[whois_db]">
          <option value="dnsstuff"<?php selected('dnsstuff', $SlimCfg->option['whois_db']); ?>>dnsstuff.com</option>
          <option value="iplookup"<?php selected('iplookup', $SlimCfg->option['whois_db']); ?>>ip-lookup.net</option>
          </select><br />
<?php _e('&mdash; Use &quot;Whois&quot; link on &quot;Visitors&quot; modules', 'wp-slimstat-ex') ?></td> 
      </tr>
			</table>
<!-- Display Options End -->
		</div>
		<div class="options">
<!-- Extra Options Start -->
		<h3><?php _e('Extra Options', 'wp-slimstat-ex'); ?></h3>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table" id="optiontable3"> 
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Nice Titles:', 'wp-slimstat-ex') ?></th> 
        <td><select name="ssex_op[nice_titles]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['nice_titles']); ?>>true</option>
          <option value="0"<?php selected(0, (int)$SlimCfg->option['nice_titles']); ?>>false</option>
          </select><br />
<?php _e('&mdash; Enable or disable &quot;Nice Titles&quot;. Powered by <a href="http://www.dustindiaz.com/sweet-titles-finalized">SweetTitles</a>', 'wp-slimstat-ex') ?></td> 
      </tr>
		<tr valign="top"> 
        <th width="25%" scope="row"><?php _e('Your server time is:', 'wp-slimstat-ex') ?></th> 
        <td><?php echo date('Y-m-d g:i:s a', time()); ?></td> 
      </tr>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('Time Offset:', 'wp-slimstat-ex') ?></th> 
        <td><input name="ssex_op[time_offset]" type="text" value="<?php echo $SlimCfg->option['time_offset']; ?>" size="3" /> 
		  <?php _e('hours', 'wp-slimstat-ex'); ?>
<?php _e('&mdash; Time offset from server time by hours.(NOT gmt offset)', 'wp-slimstat-ex') ?></td> 
      </tr>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('Your blog time is:', 'wp-slimstat-ex') ?></th> 
        <td><?php echo date('Y-m-d g:i:s a', (time() + ($SlimCfg->option['time_offset'] * 60 * 60))); ?></td> 
      </tr>
	</table>
<!-- Extra Options End -->
		</div>
    <p class="submit">
    <input type="submit" name="update_options" value="<?php _e('Update Options', 'wp-slimstat-ex') ?> &raquo;" />
    </p>
  </form>
<?php 
	} /********** Pin Option Page ***********/
	elseif ( $SlimCfg->option['usepins'] && $this->pinpage ) {
		$_optin_page = $this->option_pins();
	} /********** Exclusion Option Page ***********/
	elseif ($this->exclusionpage) { 
		$_optin_page = $this->option_exclusions();
	} /********** Admin Option Page ***********/
	elseif ($this->adminpage) {
		$_optin_page = $this->option_admin();
	} /********** Admin Option Page ***********/
	elseif ($this->permissionpage) {
		$_optin_page = $this->option_permission();
	}
?>
</div><!-- wrap -->
<?php 
	}

	function option_pins() {
		global $SlimCfg;
//		require_once(SLIMSTATPATH . 'lib/pins.php');
		SSPins::findPins();
		$pins = SSPins::_getPins(0,5,true);// get all pins
?>
	<div class="options">
  <h3><?php _e('Pins', 'wp-slimstat-ex'); ?></h3>
<?php 
		if ( empty($pins) ) {
			echo '<div class="updated"><p>There is no Pins available</p></div>'; 
		} else { 
?>
	<table width="100%" cellpadding="3" cellspacing="3" class="widefat">
	<thead>
	<tr>
	  <th class="vers"><?php _e('ID', 'wp-slimstat-ex'); ?></th>
	  <th class="name"><?php _e('Name (version)', 'wp-slimstat-ex'); ?></th>
	  <th class="vers"><?php _e('Author', 'wp-slimstat-ex'); ?></th>
	  <th class="desc"><?php _e('Description', 'wp-slimstat-ex'); ?></th>
	  <th class="togl"><?php _e('Acitve', 'wp-slimstat-ex'); ?></th>
	</tr>
	</thead>
	<tbody id="plugins">
<?php 
			$alt = '';
			$get_page = $_GET['page'];
			foreach($pins as $pin) {
				$alt = ($alt == ' alternate')?'':' alternate';
				$class_tr = ($pin->active == 1)?'active':'waitingpin';
				$class_act = ($pin->active == 1)?'delete':'edit';
				$act_text = ($pin->active == 1)?__('Deactivate', 'wp-slimstat-ex'):__('Activate', 'wp-slimstat-ex');
?>
	<tr class="<?php echo $class_tr . $alt; ?>">
	  <td class="vers"><?php echo $pin->id; ?></td>
	  <td class="name"><?php echo $pin->title . " ( " . $pin->version . " )"; ?></td>
	  <td class="vers"><a href="<?php echo $pin->url; ?>" title="Author URL"><?php echo $pin->author; ?></a></td>
	  <td class="desc" width="50%"><?php echo $pin->text; ?></td>
	  <td class="togl action-links"><a class="<?php echo $class_act; ?>" href="<?php echo $this->url; ?>&amp;wpssop=pin&amp;pinact=<?php echo (($pin->active == 1)?'0':'1'); ?>&amp;pinid=<?php echo $pin->id; ?>"><?php echo $act_text; ?></a></td>
	 </tr>
<?php 
			} /* foreach */
?>
		</tbody>
	 </table>
	</div>
<?php 
			$active_pins = SSPins::_getPins(1,5);// get all active pins
			if (empty($active_pins))
				return;
			$include_pins = SSPins::_incPins(0);// include all active pins - funtionable pins are already included.
			$i = 0;
			$_opt_pins = array();
			foreach($active_pins as $pin) {
				if (!class_exists($pin->name))
					continue;
				eval('$_pin{$i} = new $pin->name();');
				if ('sspins' != strtolower(get_parent_class($_pin{$i})))
					continue;
				$_pin_opt = $_pin{$i}->pin_actions();
				if (!$_pin_opt['options'])
					continue;
				$_opt_pins[] = array($_pin{$i}, $pin);
				$i++;
			}
			if (empty($_opt_pins))
				return;
?>
	<hr />
	<?php SSPins::update_pin_options($_opt_pins); ?>
	<?php SSPins::pin_option_menu_bar($_opt_pins); ?>

<form name="slimstat_pin_options" id="slimstat_pin_options" method="post" action="<?php echo $this->url; ?>&amp;wpssop=pin#slimstat_pin_options">
<?php
			$first_option = true;
			foreach($_opt_pins as $pin) {
				$display = $first_option ? '' : ' style="display:none;"';
				$first_option = false;
?>
	<div class="options" id="pin_option_<?php echo $pin[1]->id; ?>"<?php echo $display; ?>>
	<h3><?php printf(__('%s Options', 'wp-slimstat-ex'), wp_specialchars($pin[1]->title, 1)); ?></h3>
	<?php $pin[0]->pin_options(); ?>
	</div>
<?php
			}
?>
	<p class="submit">
		<input type="submit" name="slimstat_pin_options_submit" value="<?php _e('Update Options  &raquo;') ?>" />
	</p>
</form>
<?php
		} /* is there any pins? */
	}

	function option_exclusions() {
		global $SlimCfg;
?>
	<form name="slimstat_option_exclusion" method="post" action="<?php echo $this->url; ?>&amp;wpssop=exclusion"> 
		<div class="options">
<!-- Exclusion Options Start -->
		<h3><?php _e('Exclusion Options', 'wp-slimstat-ex'); ?></h3>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table" id="optiontable4"> 
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('Ignore IP-List:', 'wp-slimstat-ex') ?></th> 
        <td><?php _e('This setting define which remote ip will <em>always</em> not to be tracked.', 'wp-slimstat-ex'); ?><br />
		  <?php _e('Seperate multiple ip with semi-colon( ; )', 'wp-slimstat-ex'); ?><br /><br />
		  <textarea name="ig_op[ignore_ip]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['ignore_ip'],true); ?></textarea></td> 
      </tr>
			<tr valign="top">
       <th width="25%" scope="row"><?php _e('Ignore Bots?:', 'wp-slimstat-ex') ?></th> 
       <td><select name="ig_op[ignore_bots]">
          <option value="1"<?php selected(1, (int)$SlimCfg->option['nice_titles']); ?>>true</option>
         <option value="0"<?php selected(0, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('No', 'wp-slimstat-ex') ?></option>
         <option value="1"<?php selected(1, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Disable track', 'wp-slimstat-ex') ?></option>
				 <?php /* ?>
         <!-- <option value="2"<?php selected(2, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Disable display', 'wp-slimstat-ex') ?></option>
         <option value="3"<?php selected(3, (int)$SlimCfg->exclude['ignore_bots']); ?>><?php _e('Track and display', 'wp-slimstat-ex') ?></option> -->
				 <?php */ ?>
         </select> <br />
				<?php _e('&mdash; Ignore miscellaneous bots, crawlers and empty user-agent visitors.', 'wp-slimstat-ex') ?><br />
				<!-- <?php //_e('&mdash; Selecting option related to "display" may slow down your stats page.', 'wp-slimstat-ex') ?><br /> -->
				<?php	if ($SlimCfg->exclude['ignore_bots']) _e('&mdash; See more settings below.', 'wp-slimstat-ex'); 
					else _e('&mdash; More options will shown while you enable this option.', 'wp-slimstat-ex'); ?>
				</td>
			</tr>
<?php if ($SlimCfg->exclude['ignore_bots']) { ?>
			<tr valign="top">
       <th width="25%" scope="row"><?php _e('More Exclusions:', 'wp-slimstat-ex') ?></th> 
       <td>
			 <label for="ig_bots"><?php _e('Ignore famous Bots(google,yahoo,msn...)', 'wp-slimstat-ex') ?> : </label>
			 <input id="ig_bots" type="checkbox" name="ig_op[ig_bots]" value="1"<?php checked(1, $SlimCfg->exclude['ig_bots']); ?> />
			 <br /><br />
			 <label for="ig_feeds"><?php _e('Ignore RPC Service(technorati,feedburner...) and RSS readers', 'wp-slimstat-ex') ?> :</label>
			 <input id="ig_feeds" type="checkbox" name="ig_op[ig_feeds]" value="1"<?php checked(1, $SlimCfg->exclude['ig_feeds']); ?> />
			 <br /><br />
			 <label for="ig_validators"><?php _e('Ignore validators(w3c,feedvalidator...)', 'wp-slimstat-ex') ?> : </label>
			 <input id="ig_validators" type="checkbox" name="ig_op[ig_validators]" value="1"<?php checked(1, $SlimCfg->exclude['ig_validators']); ?> />
			 <br /><br />
			 <label for="ig_tools"><?php _e('Ignore fetching tools (curl,snoopy...)', 'wp-slimstat-ex') ?> : </label>
			 <input id="ig_tools" type="checkbox" name="ig_op[ig_tools]" value="1"<?php checked(1, $SlimCfg->exclude['ig_tools']); ?> />
			 </td>
			</tr>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('Black User-Agent List:', 'wp-slimstat-ex') ?></th> 
        <td><?php _e('SlimStat will <em>always ignore</em> User-Agent below.', 'wp-slimstat-ex'); ?><br />
		  <?php _e('Seperate multiple pattern with new line.', 'wp-slimstat-ex'); ?><br />
			<p><?php _e('Some <a href="http://php.net/manual/reference.pcre.pattern.syntax.php" title="syntax help">syntaxes</a>(^, $, *) are available.', 'wp-slimstat-ex') ?> &mdash; <?php _e('"*" means non-whitespace characters (\\S*?)', 'wp-slimstat-ex'); ?></p>
		  <textarea name="ig_op[black_ua]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['black_ua'],true); ?></textarea></td> 
      </tr>
      <tr valign="top"> 
        <th width="25%" scope="row"><?php _e('White User-Agent List:', 'wp-slimstat-ex') ?></th> 
        <td><?php _e('SlimStat will <em>always track</em> User-Agent below. (will be applied before ignore list)', 'wp-slimstat-ex'); ?><br />
		  <?php _e('Seperate multiple pattern with new line.', 'wp-slimstat-ex'); ?><br /><br />
		  <textarea name="ig_op[white_ua]" cols="60" rows="4" style="width: 98%; font-size: 12px;"><?php echo wp_specialchars($SlimCfg->exclude['white_ua'],true); ?></textarea></td> 
      </tr>
<?php } ?>
	</table>
<!-- Exclusion Options End -->
		</div>
    <p class="submit">
    <input type="submit" name="update_options" value="<?php _e('Update Options', 'wp-slimstat-ex') ?> &raquo;" />
    </p>
  </form>
<?php
	}

	function option_permission() {
		global $SlimCfg, $wp_roles;
		if (!$SlimCfg->has_cap('manage_options')) {
			echo '<div id="message" class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
?>
	<form name="slimstat_option_permission" method="post" action="<?php echo $this->url; ?>&amp;wpssop=permission"> 

		<div class="options">
		<h3><?php _e('Permissions', 'wp-slimstat-ex'); ?></h3>
		<?php $hidden_text = __('Ignore Track') . __('View Stats') . __('Manage Options'); ?>
			<table width="100%" cellspacing="2" cellpadding="5" class="editform form-table" id="optiontable5"> 
			<thead>
      <tr valign="top">
        <th width="25%" scope="row"><?php _e('Role Name:', 'wp-slimstat-ex'); ?></th> 
        <td><?php _e('Role Permissions:', 'wp-slimstat-ex'); ?></td>
			</tr>
			</thead>
			<tbody>
<?php
		foreach ($wp_roles->role_names as $rolekey => $role_name) {
?>
      <tr valign="top">
        <th width="25%" scope="row"><?php echo $role_name; ?></th> 
        <td>
				<?php foreach (array_values($SlimCfg->caps['administrator']) as $cap) { ?>
				<?php if ( $rolekey == 'administrator' ) { ?>
				<p class="input_checkbox input_checkbox_admin"><label for="<?php echo $cap.'_'.$rolekey; ?>"><?php echo ucwords(str_replace(array('_slimstat', '_'), array('', ' '), $cap)); ?></label>
				<input id="<?php echo $cap.'_'.$rolekey; ?>" type="hidden" name="ssex_perm[<?php echo $rolekey; ?>][]" value="<?php echo $cap; ?>" /></p>
				<?php } else { ?>
				<p class="input_checkbox"><label for="<?php echo $cap.'_'.$rolekey; ?>"><?php _e( ucwords(str_replace(array('_slimstat', '_'), array('', ' '), $cap)), 'wp-slimstat-ex'); ?></label>
				<input id="<?php echo $cap.'_'.$rolekey; ?>" type="checkbox" name="ssex_perm[<?php echo $rolekey; ?>][]" value="<?php echo $cap; ?>"<?php checked(true, in_array($cap, (array)$SlimCfg->caps[$rolekey])); echo $disabled; ?> /></p>
				<?php } ?>
				<?php } ?>
				</td> 
      </tr>
<?php } ?>
			</tbody>
			</table>
		</div>
    <p class="submit">
    <input type="submit" name="update_options" value="<?php _e('Update Options', 'wp-slimstat-ex') ?> &raquo;" />
    </p>
  </form>
<?php
	}

	function option_admin() {
		global $SlimCfg, $table_prefix;
		if (!$SlimCfg->has_cap('manage_options')) {
			echo '<div id="message" class="error fade"><p>'.__('You do not have sufficient permissions to access this page.').'</p></div>';
			return;
		}
?>
	<div class="options">
  <h3><?php _e('SlimStat-Ex Admin Tools', 'wp-slimstat-ex'); ?></h3>
  <ul>
  <li><?php echo __('Delete SlimStat data more than &quot;DB max-age(', 'wp-slimstat-ex') . (($SlimCfg->option['dbmaxage']==0)?'disabled':$SlimCfg->option['dbmaxage']) .__(')&quot; days old', 'wp-slimstat-ex') ?></li>
  <li><?php _e('SlimStat-Ex performance tool', 'wp-slimstat-ex') ?></li>
<?php if ($SlimCfg->geoip == 'mysql') { ?>
  <li><?php _e('Update ip-to-country database', 'wp-slimstat-ex') ?></li>
<?php } ?><?php /* does not supports upgrade from shortstat or slimstat anymore ?>
	<li><?php _e('Upgrade from Wp-SlimStat(0.92)', 'wp-slimstat-ex') ?></li>
  <li><?php _e('Upgrade from Wp-ShortStat', 'wp-slimstat-ex') ?></li><?php */ ?>
  <li><?php _e('Display available modules', 'wp-slimstat-ex') ?></li>
  </ul>
  <h4><a href="<?php echo $this->admin_url; ?>/index.php"><?php _e('Go to SlimStat Admin Page', 'wp-slimstat-ex') ?> &raquo;</a></h4>
 	</div>
 <hr />
<?php if ($SlimCfg->geoip == 'mysql') { ?>
	<div class="options">
  <h3><?php _e('Re-build ip-to-country DB', 'wp-slimstat-ex'); ?></h3>
	<form name="reimport_iptc" method="post" action="<?php echo $this->url; ?>&amp;wpssop=admin">
	<p style="padding:2px 20px;"><?php _e('Press "Re-build ip-to-country" button below to re-build IPTC DB.', 'wp-slimstat-ex'); ?></p>
	<p style="padding:2px 20px;"><?php _e('<strong>NOTE</strong>: It may takes time to importing. Please be patient until the full page is loaded and "success" message appears.', 'wp-slimstat-ex'); ?></p>
	<p class="submit">
	<input type="submit" name="do_reimport" value="<?php _e('Re-build ip-to-country DB', 'wp-slimstat-ex'); ?> &raquo;" />
	</p>
	</form>
	</div>
	<hr />
<?php } else { /*?>
	<div class="options">
  <h3><?php _e('Update GeoIP Data', 'wp-slimstat-ex'); ?></h3>
	<ul>
<?php if (strpos($SlimCfg->geoip, 'country') !== false) {
	$country_db_time = filemtime(SLIMSTATPATH . 'lib/geoip/GeoIP.dat');
//	$next_db_time = mktime( 0, 0, 0, date("n", $country_db_time)+1, 2, date("Y", $country_db_time) ); // second of the month
	$next_db_time = $country_db_time;
	$li_class = '';//($next_db_time < time()) ? ' class="updated fade"' : '';
?>
	<li<?php echo $li_class; ?>><?php _e('GeoIP Country Data', 'wp-slimstat-ex'); ?> : 
<?php
	if ($next_db_time < time()) {
?>
	<form style="display:inline;" name="slimstat_update_file_form" method="post" action="<?php echo $this->url; ?>&amp;wpssop=admin">
	<input name="slimstat_update_file" type="hidden" value="1" />
	<input class="button" type="submit" name="update_geodata_country" value="<?php _e('Update coutry data', 'wp-slimstat-ex'); ?> &raquo;" />
	</form>
<?php } else { ?>
	<?php _e('Your GeoIP data is up to date', 'wp-slimstat-ex'); ?>
<?php } ?>
	</li>
<?php }
if (strpos($SlimCfg->geoip, 'city') !== false) {
	$country_db_time = filemtime(SLIMSTATPATH . 'lib/geoip/GeoLiteCity.dat');
//	$next_db_time = mktime( 0, 0, 0, date("n", $country_db_time)+1, 2, date("Y", $country_db_time) ); // second of the month
	$next_db_time = $country_db_time;
	$li_class = '';//($next_db_time < time()) ? ' class="updated fade"' : '';
?>
	<li<?php echo $li_class; ?>><?php _e('GeoIP City Data', 'wp-slimstat-ex'); ?> : 
<?php
	if ($next_db_time < time()) {
?>
	<form style="display:inline;" name="slimstat_update_file_form" method="post" action="<?php echo $this->url; ?>&amp;wpssop=admin">
	<input name="slimstat_update_file" type="hidden" value="1" />
	<input class="button" type="submit" name="update_geodata_city" value="<?php _e('Update city data', 'wp-slimstat-ex'); ?> &raquo;" />
	</form>
<?php } else { ?>
	<?php _e('Your GeoIP data is up to date', 'wp-slimstat-ex'); ?>
<?php } ?>
	</li>
<?php } ?>
	</ul>
	</div>
	<hr />
<?php */} ?>
	<div class="options">
 <h3><?php _e('SlimStat-Ex external tracking', 'wp-slimstat-ex'); ?></h3>
  <ul>
<?php if ($SlimCfg->is_wpmu) { ?>
	<li><?php _e('External tracking does not support Wordpress-MU by now.', 'wp-slimstat-ex') ?></li>
<?php } else { ?>
	<li><?php _e('If you want to track external PHP web tools on your server, rename <strong>external-sample.php</strong> to <strong>external.php</strong> and change some values refer to code below', 'wp-slimstat-ex'); ?><br />
  <div style="padding:8px;margin:6px 0px;border:1px solid gray;background-color:#f8f8f8;color:#14568A;">
	// ** MySQL settings ** //<br />
	<code>$slimtrack_ext['DB_NAME']</code> = '<?php echo DB_NAME; ?>';&nbsp;&nbsp;&nbsp;&nbsp;// The name of the database<br />
	<code>$slimtrack_ext['DB_USER']</code> = '<?php echo DB_USER; ?>';&nbsp;&nbsp;&nbsp;&nbsp;// Your MySQL username<br />
	<code>$slimtrack_ext['DB_PASSWORD']</code> = '<?php echo DB_PASSWORD; ?>';&nbsp;&nbsp;// ...and password<br />
	<code>$slimtrack_ext['DB_HOST']</code> = '<?php echo DB_HOST; ?>';&nbsp;&nbsp;&nbsp;// 99% chance you won't need to change this value<br />
	<code>if(!defined('DB_CHARSET')) define('DB_CHARSET', '<?php echo DB_CHARSET; ?>');</code><br />
	<code>if(!defined('DB_COLLATE')) define('DB_COLLATE', '<?php echo DB_COLLATE; ?>');</code><br />
	<br />
	// Change SECRET_KEY to a unique phrase.  You won't have to remember it later,<br />
	// so make it long and complicated.  You can visit http://api.wordpress.org/secret-key/1.0/<br />
	// to get a secret key generated for you, or just make something up.<br />
	<code>if(!defined('SECRET_KEY')) define('SECRET_KEY', '<?php echo SECRET_KEY; ?>');</code> // Change this to a unique phrase.<br />
	<br />
	// You can have multiple installations in one database if you give each a unique prefix<br />
	<code>$slimtrack_ext['table_prefix']</code>  = '<?php echo $table_prefix; ?>';&nbsp;&nbsp;// Only numbers, letters, and underscores please!<br />
	</div></li>
  <li><?php _e('Put the line of code below on any page you want to track.', 'wp-slimstat-ex'); _e('(recommended)', 'wp-slimatat-ex'); ?><br />
  <div style="padding:8px;margin:6px 0px;border:1px solid gray;background-color:#f8f8f8;color:#14568A;">&lt;?php include_once("<?php echo ABSPATH; ?>wp-content/plugins/<?php echo $SlimCfg->basedir; ?>/lib/external.php"); ?&gt;</div></li>
	<li><?php _e('You can track pages with javascript also. Insert lines below to bottom of page(just before &lt;/body&gt;) you want to track.', 'wp-slimstat-ex'); ?><br />
	<div style="padding:8px;margin:6px 0px;border:1px solid gray;background-color:#f8f8f8;color:#14568A;">
	&lt;script type='text/javascript' src='<?php echo $SlimCfg->pluginURL; ?>/lib/external.js.php'>&lt;/script&gt;<br />
	&lt;script type='text/javascript'&gt;<br />
	var _SlimStatExTrack = SlimStatExTrack();<br />
	&lt;/script&gt;</div></li>
<?php } ?>
  </ul>
 	</div>
<?php
	}

	function &get_instance() {
		static $instance = array();
		if ( empty( $instance ) ) {
			$instance[] =& new wp_slimstat_ex_options();
		}
		return $instance[0];
	}

}

?>