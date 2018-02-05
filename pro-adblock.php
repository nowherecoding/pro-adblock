<?php
/*
  Plugin Name: Pro-AdBlock
  Plugin URI: https://github.com/crxproject/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: Sergej Theiss
  Author URI: https://github.com/crxproject/
  Version: 2.0.0-beta
  Text Domain: pro-adblock
  License: http://www.gnu.org/licenses/gpl-2.0.html

  Pro-AdBlock is a WordPress plugin that shows a warning message to users that have no adblocker enabled.
  Copyright (C) 2018  Sergej Theiss

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
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
define( 'WP_PADB_VERSION', '2.0.0-beta' );
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
	wp_enqueue_style( 'pro-adblock', PADB_URL . 'padb-style.css', false, WP_PADB_VERSION, 'all' );

	// autogenerate colors from db
	$colors = padb_get_option( 'padb_settings' );

	// Custom css setup based on the users choice
	$css = "/* Pro-AdBlock Custom CSS */\n";

	if ( $colors[ 'modal_style' ] == 2 ) {
		$css .= '	#padb-modal {
			background-color: #%1$s;
		}' . "\n";
	}

	$css .= '	#padb-modal-box {
			background-color: #%1$s;
			color: #%2$s;
	}

	#padb-modal-box a {
		color: #%3$s;
	}

	#padb-modal-box a:hover {
		color: #%4$s;
	}' . "\n";

	wp_add_inline_style( 'pro-adblock', sprintf( $css, $colors[ 'modal_box_bg_color' ], $colors[ 'modal_font_color' ], $colors[ 'modal_link_color' ], $colors[ 'modal_link_color_hover' ] ) );
}

/**
 *  Overlay generation
 */
function padb_overlay() {
	$options = padb_get_option( 'padb_settings' );
	// the modal
	?>
	<div id="padb-modal" class="padb-style-<?php echo $options[ 'modal_style' ]; ?>">
		<div id="padb-modal-box">
			<div id="padb-modal-content"><?php echo wpautop( __( $options[ 'modal_message' ], 'pro-adblock' ) ); ?></div>
			<div id="padb-modal-footer"><span id="padb-modal-close">&#10008; <?php _e( 'Close modal to enter website', 'pro-adblock' ); ?></span></div>
		</div>
	</div>
	<?php
}

/**
 * Scripts enqueueing
 */
function padb_javascripts() {
	wp_enqueue_script('padb-detector', PADB_URL . 'gads.js', array('jquery', 'utils'), WP_PADB_VERSION, true);
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
	register_setting( 'pluginPage1', 'padb_settings' );
	register_setting( 'pluginPage2', 'padb_settings' );

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
			'modal_box_bg_color', __( 'Background color', 'pro-adblock' ), 'padb_box_bg_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_font_color', __( 'Font color', 'pro-adblock' ), 'padb_font_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_link_color', __( 'Link color', 'pro-adblock' ), 'padb_link_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_link_color_hover', __( 'Link hover color', 'pro-adblock' ), 'padb_link_color_hover_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_style', __( 'Modal style', 'pro-adblock' ), 'padb_select_modal_style_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);
}

function padb_message_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Text', 'pro-adblock' ); ?></span></legend>
		<textarea rows='15' name='padb_settings[modal_message]' class='large-text code'><?php echo $options[ 'modal_message' ]; ?></textarea>
	</fieldset>
	<?php
}

function padb_box_bg_color_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<input type='text' name='padb_settings[modal_box_bg_color]' value='<?php echo $options[ 'modal_box_bg_color' ]; ?>' />
	<?php
}

function padb_font_color_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<input type='text' name='padb_settings[modal_font_color]' value='<?php echo $options[ 'modal_font_color' ]; ?>' />
	<?php
}

function padb_link_color_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<input type='text' name='padb_settings[modal_link_color]' value='<?php echo $options[ 'modal_link_color' ]; ?>' />
	<?php
}

function padb_link_color_hover_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<input type='text' name='padb_settings[modal_link_color_hover]' value='<?php echo $options[ 'modal_link_color_hover' ]; ?>' />
	<?php
}

function padb_select_modal_style_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Modal style', 'pro-adblock' ); ?></span></legend>
		<label><input type="radio" name="padb_settings[modal_style]" value="1"<?php checked( 1, $options[ 'modal_style' ], true ); ?> /> <span><?php _e( 'Box w/ transparent background', 'pro-adblock' ); ?></span></label><br />
		<label><input type="radio" name="padb_settings[modal_style]" value="2"<?php checked( 2, $options[ 'modal_style' ], true ); ?> /> <span><?php _e( 'Fully locked screen', 'pro-adblock' ); ?></span></label>
	</fieldset>
	<?php
}

function padb_settings_section_callback_1() {
	echo __( 'Display a custom text to users that have no adblocker enabled.', 'pro-adblock' );
}

function padb_settings_section_callback_2() {
	echo __( 'Set custom colors for the modal box.', 'pro-adblock' );
}

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
function padb_get_option( $values ) {
	// load default options if no entry in database
	$defaults = array(
		'modal_message'			 => __( "<h1>You are not using an Adblocker?!</h1>\n\nAdvertising displayed on webpages can be a security risk. Currently, the advertising mostly consists of embedded third party content. These contents are not under the website's owner editorial control and add a repeatedly criminally exploited attack vector to the website. An adblocker protects your surfing. This site explicitly supports the usage of advertisement blockers. Please consider to use one!\n\nYou can find a listing of adblockers here:\n<strong><a href=\"http://crxproject.github.io/pro-adblock/lists.html\" target=\"_blank\">Pro-AdBlock (Adblocker Promotion)</a></strong>\n\nThank you for your attention.", 'pro-adblock' ),
		'modal_box_bg_color'	 => 'e89900',
		'modal_font_color'		 => 'fff',
		'modal_link_color'		 => 'fff',
		'modal_link_color_hover' => 'fff',
		'modal_style'			 => '1'
	);

	$output = get_option( $values, $defaults );

	return $output;
}
