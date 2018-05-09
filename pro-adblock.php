<?php
/*
  Plugin Name: Pro-AdBlock
  Plugin URI: https://github.com/nowherecoding/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: NowhereCoding
  Author URI: https://github.com/nowherecoding/
  Version: 2.0.0-beta
  Text Domain: pro-adblock
  License: http://www.gnu.org/licenses/gpl-2.0.html

  Pro-AdBlock is a WordPress plugin that shows a warning message to users that have no adblocker enabled.
  Copyright (C) 2018 NowhereCoding

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

// SECURITY: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed!' );
}

// Constants
define( 'PADB_VERSION', '2.0.0-beta' );
define( 'PADB_URL', plugin_dir_url( __FILE__ ) );

// load the plugin's translated strings
add_action( 'init', 'padb_load_textdomain' );

function padb_load_textdomain() {
	load_plugin_textdomain( 'pro-adblock', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/**
 * Styles enqueueing
 */
function padb_stylesheets() {
	wp_enqueue_style( 'pro-adblock', PADB_URL . 'padb-style.css', false, PADB_VERSION, 'all' );
}

/**
 * Overlay generation
 */
function padb_overlay() {
	// Don't display the modal on pages to access the privacy policy freely
	if ( !is_page() ) {
	?>
	<div id="padb-modal" class="padb-style-<?php echo padb_get_option( 'modal_style' ); ?>" tabindex="-1" role="dialog">
		<div class="padb-modal-wrapper" role="document">

			<div class="padb-modal-content">
				<div class="padb-modal-body">
					<?php echo wpautop( __( padb_get_option( 'modal_message' ), 'pro-adblock' ) ); ?>
				</div>
				<div class="padb-modal-footer">
					<small>(*) <?php _e( 'By closing this notice you agree to our cookie policy! Pro-AdBlock uses a temporary cookie that is stored on your computer to disable the adblocker alert for a certain time. Further data are not collected.', 'pro-adblock' ); ?> <?php echo padb_privacy_policy_link(); ?></small>
					<button type="button" class="padb-modal-close" data-dismiss="padb-modal"><?php _e( 'Accept &amp; continue', 'pro-adblock' ); ?> *</button>
				</div>
			</div>

		</div>
	</div>
	<?php
	}
}

/**
 * Display a link to the privacy policy
 * This is works only in WP 4.9.6+ and if you have generated a privacy police page in the settings
 *
 * @return type
 */
function padb_privacy_policy_link() {
	if ( function_exists('get_privacy_policy_url') and !empty( get_option( 'wp_page_for_privacy_policy' ) ) ) {
		return sprintf( __( "For more information visit our %s.", 'pro-adblock' ), get_the_privacy_policy_link() );
	}
}

/**
 * Scripts enqueueing
 */
function padb_javascripts() {
	wp_enqueue_script( 'padb-detector', PADB_URL . 'gads.js', array('jquery', 'utils'), PADB_VERSION, true );
}

add_action( 'wp_enqueue_scripts', 'padb_stylesheets' );
add_action( 'wp_footer', 'padb_overlay' );
add_action( 'wp_enqueue_scripts', 'padb_javascripts' );

/* * *****************************************************************************
 * Admin section
 * **************************************************************************** */

add_action( 'admin_menu', 'padb_add_admin_menu' );
add_action( 'admin_init', 'padb_settings_init' );

function padb_add_admin_menu() {
	add_options_page( 'Pro-AdBlock Settings', 'Pro-AdBlock', 'manage_options', 'pro-adblock-options', 'padb_options_page' );
}

function padb_settings_init() {
	register_setting( 'pluginPage1', 'padb2_settings' );
	register_setting( 'pluginPage2', 'padb2_settings' );

	add_settings_section(
			'padb_pluginPage_section_0', __( 'Message', 'pro-adblock' ), 'padb_settings_section_callback_1', 'pluginPage1'
	);

	add_settings_field(
			'modal_message', __( 'Text', 'pro-adblock' ), 'padb_message_render', 'pluginPage1', 'padb_pluginPage_section_0'
	);

	add_settings_section(
			'padb_pluginPage_section_1', __( 'Appearance', 'pro-adblock' ), 'padb_settings_section_callback_2', 'pluginPage2'
	);

	add_settings_field(
			'modal_style', __( 'Modal background', 'pro-adblock' ), 'padb_select_modal_style_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);
}

function padb_message_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Text', 'pro-adblock' ); ?></span></legend>
		<textarea rows='10' cols='50' name='padb2_settings[modal_message]' class='large-text code'><?php echo padb_get_option( 'modal_message' ); ?></textarea>
	</fieldset>
	<?php
}

function padb_select_modal_style_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Modal background', 'pro-adblock' ); ?></span></legend>
		<label><input type="radio" name="padb2_settings[modal_style]" value="1"<?php checked( 1, padb_get_option( 'modal_style' ), true ); ?> /> <span><?php _e( 'Dark transparent', 'pro-adblock' ); ?></span></label><br />
		<label><input type="radio" name="padb2_settings[modal_style]" value="2"<?php checked( 2, padb_get_option( 'modal_style' ), true ); ?> /> <span><?php _e( 'Light transparent', 'pro-adblock' ); ?></span></label>
	</fieldset>
	<?php
}

function padb_privacy_notice() {
	?>
	<h2><?php _e( 'Privacy policy', 'pro-adblock' ); ?></h2>
	<?php _e( 'You should copy &amp; paste this text into your privacy policy page.', 'pro-adblock' ); ?>
	<table class="form-table">
		<tr>
			<td>
				<fieldset><legend class="screen-reader-text"><span><?php _e( 'Privacy policy', 'pro-adblock' ); ?></span></legend>
					<textarea rows='10' cols='50' class='large-text code' readonly='readonly'><?php _e( "<strong>Pro-AdBlock Plugin</strong>\n\nWithin our online presence we use the plugin Pro-AdBlock by the developer NowhereCoding.\n\nThe use of Pro-AdBlock is based on our legitimate interests within the meaning of Art. 6 (1) lit. f GDPR, because with the help of Pro-AdBlock we alert users about the dangers of surfing without an adblocker browser plug-in and offer them the opportunity to install one.\n\nPro-AdBlock uses a temporary cookie that is stored on your computer to disable the adblocker alert for a certain time. Further data are not collected. If a user has installed an adblocker, generally no associated cookie will be stored on the user's device.", 'pro-adblock' ); ?></textarea>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php
}

function padb_settings_section_callback_1() {
	echo __( 'Display a custom text to users that have no adblocker enabled.', 'pro-adblock' );
}

function padb_settings_section_callback_2() {}

function padb_options_page() {
	?>
	<div class="wrap">
		<h1><?php _e( 'Pro-AdBlock Settings', 'pro-adblock' ); ?></h1>

		<form action='options.php' method='post'>

			<?php
			settings_fields( 'pluginPage1' );
			do_settings_sections( 'pluginPage1' );
			settings_fields( 'pluginPage2' );
			do_settings_sections( 'pluginPage2' );
			submit_button();

			padb_privacy_notice();
			?>

		</form>
	</div>
	<?php
}

/**
 * Default plugin settings
 *
 * @param type $values
 * @return type
 */
function padb_get_option( $value ) {
	// load default options if no entry in database
	$defaults = array(
		'modal_message'	=> __( "<h2>You are not using an Adblocker?!</h2>\n\nAdvertising displayed on webpages can be a security risk. Currently, the advertising mostly consists of embedded third party content. These contents are not under the website's owner editorial control and add a repeatedly criminally exploited attack vector to the website. An adblocker protects your surfing. This site explicitly supports the usage of advertisement blockers. Please consider to use one! A list of adblockers is available on the <strong><a href=\"https://nowherecoding.github.io/pro-adblock/lists.html\" target=\"_blank\">Pro-AdBlock Homepage</a></strong>.\n\nThank you for your attention.", 'pro-adblock' ),
		'modal_style'	=> '1'
	);

	$options = get_option( 'padb2_settings' );
	$output = !empty($options[$value]) ? $options[$value] : $defaults[$value];

	return $output;
}
