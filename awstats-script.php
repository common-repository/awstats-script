<?php
/*
 * Plugin Name: AWStats Script
 * Version: 0.3
 * Description: Adds the HTML script tag and JS code that <a href="http://awstats.sourceforge.net/">AWStats</a> requires to enable collection of  browser data like screen size and browser capabilities. 
 * Author: Jorge Garcia de Bustos
 * Author URI: http://www.linkedin.com/in/jgbustos
 */

/*  
	Copyright 2008 Jorge Garcia de Bustos (email: jgbustos@gmail.com)

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
	
	http://www.gnu.org/licenses/gpl-2.0.html
*/
 
define("aws_plugin_path", realpath(dirname(__FILE__)), true);	 
define("aws_i18n_domain", 'awstats-script', true);
 
// Constants for enabled/disabled state
define("aws_enabled", "enabled", true);
define("aws_disabled", "disabled", true);

// Defaults, etc.
define("key_aws_status", "aws_status", true);
define("key_aws_footer", "aws_footer", true);
define("key_aws_script_url", "aws_script_url", true);
define("key_aws_not_logged_user_level", "aws_not_logged_user_level", true);

define("aws_not_logged_user_level_default", "", true);
define("aws_status_default", aws_disabled, true);
define("aws_footer_default", aws_disabled, true);
define("aws_script_url_default", get_bloginfo('wpurl') . "/wp-content/plugins/awstats-script/js/awstats_misc_tracker.js", true);

// Create the default key and status
add_option(key_aws_status, aws_status_default);
add_option(key_aws_script_url, aws_script_url_default);
add_option(key_aws_footer, aws_footer_default);
add_option(key_aws_not_logged_user_level, aws_not_logged_user_level_default);

// Create a option page for settings
add_action('admin_menu', 'add_aws_option_page');

// Hook in the options page function
function add_aws_option_page() {
	global $wpdb;
	add_options_page(__('AWStats Script Options', aws_i18n_domain), 'AWStats Script', 8, basename(__FILE__), 'aws_options_page');
}

// wp_nonce
function aws_nonce_field() {
	echo "<input type='hidden' name='aws-nonce-key' value='" . wp_create_nonce('awstats-script') . "' />";
}

function aws_get_user_level() {
	for ($i = 10; $i >= 0; $i--) {
		if (current_user_can("level_$i")) {
			return $i;
		}
	}
}

function aws_enabled_message($enabled, $case) {
	switch($enabled) {
		case aws_enabled:
			$ret = __('enabled', aws_i18n_domain);
			break;
		case aws_disabled:
			$ret = __('disabled', aws_i18n_domain);
			break;
		default:
			return false;
	}
	switch($case) {
		case 'lower':
			return strtolower($ret);
		case 'upper':
			return strtoupper($ret);
		case 'caps':
			return ucwords($ret);
		default:
			return $ret;
	}
}

function aws_options_page() {
	// If we are a postback, store the options
 	if (isset($_POST['info_update'])) {
		if ( wp_verify_nonce($_POST['aws-nonce-key'], 'awstats-script') ) {
			
			// Update the status
			$aws_status = $_POST[key_aws_status];
			if (($aws_status != aws_enabled) && ($aws_status != aws_disabled))
				$aws_status = aws_status_default;
			update_option(key_aws_status, $aws_status);

			// Update the script URL
			$aws_script_url = $_POST[key_aws_script_url];
			update_option(key_aws_script_url, $aws_script_url);

			// Update the compressed file to reflect the new URL
			$file_contents = file_get_contents(aws_plugin_path . '/js/awstats_misc_tracker.js');
			$file_contents = preg_replace('/var awstatsmisctrackerurl=\"[^"]*\";/', 'var awstatsmisctrackerurl="' . $aws_script_url . '";', $file_contents);
			file_put_contents(aws_plugin_path . '/js/awstats_misc_tracker.js', $file_contents);

			// Update the footer
			$aws_footer = $_POST[key_aws_footer];
			if (($aws_footer != aws_enabled) && ($aws_footer != aws_disabled))
				$aws_footer = aws_footer_default;
			update_option(key_aws_footer, $aws_footer);

			// Update the user level that won't be logged
			$aws_not_logged_user_level = $_POST[key_aws_not_logged_user_level];
			update_option(key_aws_not_logged_user_level, $aws_not_logged_user_level);

			// Give an updated message
			echo '<div class="updated fade"><p><strong>' . __('AWStats Script settings saved', aws_i18n_domain) . '</strong></p></div>';
		}
	}

	// Output the options page
	?>

		<div class="wrap">
		<form method="post" action="?page=awstats-script.php">
		<?php aws_nonce_field(); ?>
			<h2><?php _e('AWStats Script Options', aws_i18n_domain); ?></h2>
			<?php if (get_option(key_aws_status) == aws_disabled) { ?>
				<div style="margin:10px auto; border:3px #f00 solid; background-color:#fdd; color:#000; padding:10px; text-align:center;">
				<?php _e('AWStats Script integration is currently', aws_i18n_domain); ?> <strong><?php echo aws_enabled_message(aws_disabled, 'upper'); ?></strong>.
				</div>
			<?php } ?>
			<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_aws_status ?>"><?php _e('AWStats Script logging is:', aws_i18n_domain); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='".key_aws_status."' id='".key_aws_status."'>\n";
						
						echo "<option value='".aws_enabled."'";
						if(get_option(key_aws_status) == aws_enabled)
							echo " selected='selected'";
						echo ">" . aws_enabled_message(aws_enabled, 'caps') . "</option>\n";
						
						echo "<option value='".aws_disabled."'";
						if(get_option(key_aws_status) == aws_disabled)
							echo" selected='selected'";
						echo ">" . aws_enabled_message(aws_disabled, 'caps') . "</option>\n";
						
						echo "</select>\n";
						?>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_aws_script_url; ?>"><?php _e('AWStats misc tracker JS URL:', aws_i18n_domain); ?></label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='100'";
						echo "name='".key_aws_script_url."' ";
						echo "value='".stripslashes(get_option(key_aws_script_url))."' ";
						echo "id='".key_aws_script_url."' />\n";
						?>
						<p style="margin: 5px 10px;"><?php _e('URL (relative or absolute) for the AWStats miscellaneous tracker JavaScript file.', aws_i18n_domain); ?></p>
					</td>
				</tr>
				<tr>
					<th width="30%" valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_aws_footer ?>"><?php _e('Footer tracking code:', aws_i18n_domain); ?></label>
					</th>
					<td>
						<?php
						echo "<select name='".key_aws_footer."' id='".key_aws_footer."'>\n";
						
						echo "<option value='".aws_enabled."'";
						if(get_option(key_aws_footer) == aws_enabled)
							echo " selected='selected'";
						echo ">" . aws_enabled_message(aws_enabled, 'caps') . "</option>\n";
						
						echo "<option value='".aws_disabled."'";
						if(get_option(key_aws_footer) == aws_disabled)
							echo" selected='selected'";
						echo ">" . aws_enabled_message(aws_disabled, 'caps') . "</option>\n";
						
						echo "</select>\n";
						?>
						<p style="margin: 5px 10px;"><?php _e("Insert the AWStats Script code in your blog's footer instead of your header. This will speed up the page loading if turned on, but it might not work on certain themes.", aws_i18n_domain); ?></p>
					</td>
				</tr>
				<tr>
					<th valign="top" style="padding-top: 10px;">
						<label for="<?php echo key_aws_not_logged_user_level; ?>"><?php _e('User Level not to be logged:', aws_i18n_domain); ?></label>
					</th>
					<td>
						<?php
						echo "<input type='text' size='5'";
						echo "name='".key_aws_not_logged_user_level."' ";
						echo "value='".get_option(key_aws_not_logged_user_level)."' ";
						echo "id='".key_aws_not_logged_user_level."' />\n";
						?>
						<p style="margin: 5px 10px;"><?php _e("Any users whose Level is greater or equal than this value won't be logged using the AWStats script. Leave blank to disable and log all users.", aws_i18n_domain); ?><br /><?php _e('Your User Level is', aws_i18n_domain); ?> <b><?php echo aws_get_user_level(); ?></b></p>
						<ul>
							<li><?php _e('User Level', aws_i18n_domain); ?> 0 => <?php _e('Subscriber', aws_i18n_domain); ?></li>
							<li><?php _e('User Level', aws_i18n_domain); ?> 1 => <?php _e('Contributor', aws_i18n_domain); ?></li>
							<li><?php _e('User Levels', aws_i18n_domain); ?> 2-4 => <?php _e('Author', aws_i18n_domain); ?></li>
							<li><?php _e('User Levels', aws_i18n_domain); ?> 5-7 => <?php _e('Editor', aws_i18n_domain); ?></li>
							<li><?php _e('User Levels', aws_i18n_domain); ?> 8-10 => <?php _e('Administrator', aws_i18n_domain); ?></li>
						</ul>
					</td>
				</tr>
				</table>
			<p class="submit">
				<input type="submit" name="info_update" value="<?php _e('Save Changes', aws_i18n_domain); ?>" />
			</p>
		</div>
		</form>

<?php
}

// Add the script
if (get_option(key_aws_footer) == aws_enabled) {
	add_action('wp_footer', 'add_awstats_script');
} else {
	add_action('wp_head', 'add_awstats_script');
}

function add_awstats_script() {
	if (get_option(key_aws_status) == aws_disabled) {
		return;
	}
	$level = get_option(key_aws_not_logged_user_level);
	if (isset($level) && is_numeric($level) && (aws_get_user_level() >= $level)) {
		return;
	}
	$url = stripslashes(get_option(key_aws_script_url));
	echo "<script type=\"text/javascript\" src=\"$url\" ></script>\n";
	
	// <noscript><img /><noscript> is useless inside the page's <head>, obviously
	if (get_option(key_aws_footer) == aws_enabled) {
		echo "<noscript><img src=\"$url?nojs=y\" height=\"0\" width=\"0\" border=\"0\" style=\"display: none;\" alt=\"\" /></noscript>\n";
	}
}

?>