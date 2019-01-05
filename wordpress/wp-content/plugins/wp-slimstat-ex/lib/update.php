<?php
class SSUpdate {

	function SSUpdate() {
	}

	function request_filesystem_credentials($form_post, $type = '', $error = false) {
		$req_cred = apply_filters('request_filesystem_credentials', '', $form_post, $type, $error);
		if ( '' !== $req_cred )
			return $req_cred;

		if ( empty($type) )
			$type = get_filesystem_method();

		if ( 'direct' == $type )
			return true;
			
		if( ! $credentials = get_option('ftp_credentials') )
			$credentials = array();
		// If defined, set it to that, Else, If POST'd, set it to that, If not, Set it to whatever it previously was(saved details in option)
		$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : (!empty($_POST['hostname']) ? $_POST['hostname'] : $credentials['hostname']);
		$credentials['username'] = defined('FTP_USER') ? FTP_USER : (!empty($_POST['username']) ? $_POST['username'] : $credentials['username']);
		$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : (!empty($_POST['password']) ? $_POST['password'] : $credentials['password']);
		$credentials['ssl']      = defined('FTP_SSL')  ? FTP_SSL  : ( isset($_POST['ssl'])      ? $_POST['ssl']      : $credentials['ssl']);

		if ( ! $error && !empty($credentials['password']) && !empty($credentials['username']) && !empty($credentials['hostname']) ) {
			$stored_credentials = $credentials;
			unset($stored_credentials['password']);
			update_option('ftp_credentials', $stored_credentials);
			return $credentials;
		}
		$hostname = '';
		$username = '';
		$password = '';
		$ssl = '';
		if ( !empty($credentials) )
			extract($credentials, EXTR_OVERWRITE);
		if( $error )
			echo '<div id="message" class="error"><p>' . __('<strong>Error:</strong> There was an error connecting to the server, Please verify the settings are correct.') . '</p></div>';
	?>
	<form action="<?php echo $form_post ?>" method="post">
	<div class="wrap">
	<h2><?php _e('FTP Connection Information') ?></h2>
	<p><?php _e('To perform the requested update, FTP connection information is required.') ?></p>
	<table class="form-table">
	<tr valign="top">
	<th scope="row"><label for="hostname"><?php _e('Hostname:') ?></label></th>
	<td><input name="hostname" type="text" id="hostname" value="<?php echo attribute_escape($hostname) ?>"<?php if( defined('FTP_HOST') ) echo ' disabled="disabled"' ?> size="40" /></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="username"><?php _e('Username:') ?></label></th>
	<td><input name="username" type="text" id="username" value="<?php echo attribute_escape($username) ?>"<?php if( defined('FTP_USER') ) echo ' disabled="disabled"' ?> size="40" /></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="password"><?php _e('Password:') ?></label></th>
	<td><input name="password" type="password" id="password" value=""<?php if( defined('FTP_PASS') ) echo ' disabled="disabled"' ?> size="40" /><?php if( defined('FTP_PASS') && !empty($password) ) echo '<em>'.__('(Password not shown)').'</em>'; ?></td>
	</tr>
	<tr valign="top">
	<th scope="row"><label for="ssl"><?php _e('Use SSL:') ?></label></th>
	<td>
	<select name="ssl" id="ssl"<?php if( defined('FTP_SSL') ) echo ' disabled="disabled"' ?>>
	<?php
	foreach ( array(0 => __('No'), 1 => __('Yes')) as $key => $value ) :
		$selected = ($ssl == $value) ? 'selected="selected"' : '';
		echo "\n\t<option value='$key' $selected>" . $value . '</option>';
	endforeach;
	?>
	</select>
	</td>
	</tr>
	</table>
	<p class="submit">
	<input type="submit" name="submit" value="<?php _e('Proceed'); ?>" />
	</p>
	</div>
	</form>
	<?php
		return false;
	}

	function do_update($_file, $form_post) {
		global $wp_filesystem;

		if ( false === ($credentials = SSUpdate::request_filesystem_credentials($form_post)) )
			return;

		if ( ! WP_Filesystem($credentials) ) {
			SSUpdate::request_filesystem_credentials($form_post, '', true); //Failed to connect, Error and request again
			return;
		}

		if ( $wp_filesystem->errors->get_error_code() ) {
			echo '<div class="error fade">';
			foreach ( $wp_filesystem->errors->get_error_messages() as $message )
				SSUpdate::show_message($message);
			echo '</div>';
			return;
		}


		$result = SSUpdate::update($_file, array('SSUpdate', 'show_message'));

		if ( is_wp_error($result) ) {
			echo '<div class="error fade">';
			SSUpdate::show_message($result);
			SSUpdate::show_message( __('Update Failed', 'wp-slimstat-ex') );
		} else {
			echo '<div class="updated fade">';
			//Result is the new plugin file relative to WP_PLUGIN_DIR
			SSUpdate::show_message( __('File upgraded successfully', 'wp-slimstat-ex') );	
		}
		echo '</div>';
	}

	function show_message($message) {
		if( is_wp_error($message) ){
			if( $message->get_error_data() )
				$message = $message->get_error_message() . ': ' . $message->get_error_data();
			else 
				$message = $message->get_error_message();
		}
		echo "<p>$message</p>\n";
	}

	function getExtension($file) {
		$pos = strrpos($file, '.');
		if(!$pos) {
			return false;
		}
		$str = substr($file, $pos, strlen($file));
		return $str;
	}

	function untar_file($file, $to) {
		global $wp_filesystem;

		if ( ! $wp_filesystem || !is_object($wp_filesystem) )
			return new WP_Error('fs_unavailable', __('Could not access filesystem.'));

		$fs =& $wp_filesystem;

		if ( ! $fs->is_dir($to) )
			$fs->mkdir($to, 0755);
		require_once(SLIMSTATPATH . 'lib/Tar.php');

		$archive = new Archive_Tar($file);

		// Is the archive valid?
		$to = trailingslashit($to);
		if ( false == ($archive_files = $archive->extract($to)) )
			return false;//new WP_Error('incompatible_archive', __('Incompatible archive'), $archive->errorInfo(true));

		if ( 0 == count($archive_files) )
			return new WP_Error('empty_archive', __('Empty archive'));

/*		$path = explode('/', $to);
		$tmppath = '';
		for ( $j = 0; $j < count($path) - 1; $j++ ) {
			$tmppath .= $path[$j] . '/';
			if ( ! $fs->is_dir($tmppath) )
				$fs->mkdir($tmppath, 0755);
		}

		foreach ($archive_files as $file) {
			$path = explode('/', $file['filename']);
			$tmppath = '';

			// Loop through each of the items and check that the folder exists.
			for ( $j = 0; $j < count($path) - 1; $j++ ) {
				$tmppath .= $path[$j] . '/';
				if ( ! $fs->is_dir($to . $tmppath) )
					if ( !$fs->mkdir($to . $tmppath, 0755) )
						return new WP_Error('mkdir_failed', __('Could not create directory'), $to . $tmppath);
			}

			// We've made sure the folders are there, so let's extract the file now:
			if ( ! $file['folder'] )
				if ( !$fs->put_contents( $to . $file['filename'], $file['content']) )
					return new WP_Error('copy_failed', __('Could not copy file'), $to . $file['filename']);
				$fs->chmod($to . $file['filename'], 0644);
		}*/

		return true;
	}

	function update($_file, $feedback = '') {
		global $wp_filesystem;

		if ( !empty($feedback) )
			add_filter('update_feedback', $feedback);

		// Is a filesystem accessor setup?
		if ( ! $wp_filesystem || ! is_object($wp_filesystem) )
			WP_Filesystem();

		if ( ! is_object($wp_filesystem) )
			return new WP_Error('fs_unavailable', __('Could not access filesystem.'));

		if ( $wp_filesystem->errors->get_error_code() )
			return new WP_Error('fs_error', __('Filesystem error'), $wp_filesystem->errors);

		//Get the base plugin folder
		$plugins_dir = $wp_filesystem->wp_plugins_dir();
		if ( empty($plugins_dir) )
			return new WP_Error('fs_no_plugins_dir', __('Unable to locate WordPress Plugin directory.'));

		//And the same for the Content directory.
		$content_dir = $wp_filesystem->wp_content_dir();
		if( empty($content_dir) )
			return new WP_Error('fs_no_content_dir', __('Unable to locate WordPress Content directory (wp-content).'));
		
		$plugins_dir = trailingslashit( $plugins_dir );
		$content_dir = trailingslashit( $content_dir );
		switch ($_file) {
			case 'geocity_dat':
				$url = 'http://www.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz';
				$this_file = SLIMSTATPATH . 'lib/geoip/GeoLiteCity.dat';
			break;
			case 'geocountry_dat' :
				$url = 'http://www.maxmind.com/download/geoip/database/GeoIP.dat.gz';
				$this_file = SLIMSTATPATH . 'lib/geoip/GeoIP.dat';
			break;
		}

		// Download the package
		apply_filters('update_feedback', sprintf(__('Downloading update from %s'), $url));
		$download_file = download_url($url);

		if ( is_wp_error($download_file) )
			return new WP_Error('download_failed', __('Download failed.'), $download_file->get_error_message());

		$working_dir = $content_dir . 'upgrade/' . $_file;

		// Clean up working directory
		if ( $wp_filesystem->is_dir($working_dir) )
			$wp_filesystem->delete($working_dir, true);

		apply_filters('update_feedback', __('Unpacking the update'));
		// Unzip package to working directory
		$ext = SSUpdate::getExtension($url);
		switch ($ext) {
			case '.zip':
				$result = unzip_file($download_file, $working_dir);
			break;
			case '.gz': case '.bz2': case '.bz':
				$result = SSUpdate::untar_file($download_file, $working_dir);
			break;
			default:
				return new WP_Error('slimstat_filetype', __('Unsupported file type.'));
			break;
		}

		// Once extracted, delete the package
		unlink($download_file);

		if ( is_wp_error($result) ) {
			$wp_filesystem->delete($working_dir, true);
			return $result;
		}

		// Remove the existing plugin.
		apply_filters('update_feedback', __('Removing the old version of the file', 'wp-slimstat-ex'));
		$deleted = $wp_filesystem->delete($this_file);

		if ( ! $deleted ) {
			$wp_filesystem->delete($working_dir, true);
			return new WP_Error('delete_failed', __('Could not remove the old file', 'wp-slimstat-ex'));
		}

		apply_filters('update_feedback', __('Installing the latest version'));
		// Copy new version of plugin into place.
		$result = copy_dir($working_dir, dirname($this_file));
		if ( is_wp_error($result) ) {
			//$wp_filesystem->delete($working_dir, true); //TODO: Uncomment? This DOES mean that the new files are available in the upgrade folder if it fails.
			return $result;
		}

		//Get a list of the directories in the working directory before we delete it, We need to know the new folder for the plugin
//		$filelist = array_keys( $wp_filesystem->dirlist($working_dir) );

		// Remove working directory
		$wp_filesystem->delete($working_dir, true);

		/*if( empty($filelist) )
			return false; //We couldnt find any files in the working dir, therefor no plugin installed? Failsafe backup.
		
		$folder = $filelist[0];
		$plugin = get_plugins('/' . $folder); //Ensure to pass with leading slash
		$pluginfiles = array_keys($plugin); //Assume the requested plugin is the first in the list

		return  $folder . '/' . $pluginfiles[0];*/
	}
}


?>