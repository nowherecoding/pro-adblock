<?php
/*
  Plugin Name: Pro-AdBlock
  Plugin URI: https://github.com/crxproject/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: Sergej Theiss
  Author URI: https://github.com/crxproject/
  Version: 1.0.1
  License: http://www.gnu.org/licenses/gpl-2.0.html
  
  Pro-AdBlock is a WordPress plugin that shows a warning message to users that have no adblocker enabled.
  Copyright (C) 2015  Sergej Theiss

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
define( 'WP_PADB_VERSION', '1.0.1' );
define( 'PADB_URL', plugin_dir_url( __FILE__ ) );

// load the plugin's translated strings
load_plugin_textdomain( 'proadblock', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

/**
 * Custom css setup based on the users choice
 */
function padb_css() {

	// autogenerate colors from db
	$colors = padb_get_option( 'padb_settings' );
	?>
	<!-- ProAdBlock Custom CSS -->
	<style type="text/css">
	<?php if ( $colors[ 'modal_style' ] == 2 ) { // needed for fully locked screen style ?>
			#padb-modal-overlay {
				background: #<?php echo $colors[ 'modal_box_bg_color' ]; ?>;
			}
	<?php } ?>

		#padb-modal-box {
			background: #<?php echo $colors[ 'modal_box_bg_color' ]; ?>;
			color: #<?php echo $colors[ 'modal_font_color' ]; ?>;
		}

		#padb-modal-box h1 {
			color: #<?php echo $colors[ 'modal_font_color' ]; ?>;
		}

		#padb-modal-box a {
			color: #<?php echo $colors[ 'modal_link_color' ]; ?>;
		}

		#padb-modal-box a:hover {
			color: #<?php echo $colors[ 'modal_link_color_hover' ]; ?>;
		}
	</style>
	<?php
}

/**
 *  Overlay generation
 */
function padb_overlay() {
	$options = padb_get_option( 'padb_settings' );
	// the modal
	?>
	<div id="padb-modal-overlay">
		<div id="padb-modal-box"><div id="padb-modal-box-header" class="padb-modal-close"></div>
			<div id="padb-modal-box-content"><?php echo wpautop( $options[ 'modal_message' ] ); ?></div>
			<div id="padb-modal-box-footer" class="padb-modal-close"><span>&#10008; <?php echo __( 'Click here to enter this site now', 'proadblock' ); ?></span></div>
		</div>
	</div>
	<?php
}

/**
 * Adblocker detection
 */
function padb_detector() {
	?>
	<script>var blockerDetected = true;</script>
	<script src="<?php echo PADB_URL; ?>gads.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			// mobile device detection
			var isMobile = false; //initiate as false
			// excluded b/c currently there are not many adblockers for mobile platforms
			if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
				isMobile = true;
			}
			// hide the modal if adblocker is enabled
			if (blockerDetected) {
				$('#padb-modal-overlay').hide();
			}
			// show the modal if adblocker is disabled
			else {
				if (!wpCookies.get('padb_accepted') && !isMobile) {
					$('#padb-modal-overlay').show();
					// generate cookie if user closes modal
					$('.padb-modal-close').click(function() {
						$('#padb-modal-overlay').fadeOut('slow');
						var date = 7 * 24 * 60 * 60; // set cookie to expire after 7 days
						wpCookies.set('padb_accepted', true, date, '/');
					});
				} else {
					$('#padb-modal-overlay').hide();
				}
			}
		});
	</script>
	<?php
}

/**
 * Scripts & styles enqueueing
 */
function padb_enqueue_scripts() {
	// set the style id
	$style = padb_get_option( 'padb_settings' );
	wp_enqueue_style( 'padb', PADB_URL . 'assets/css/padb-style-' . $style[ 'modal_style' ] . '.css', false, WP_PADB_VERSION, 'all' );
	wp_enqueue_script( 'utils' );
}

add_action( 'wp_head', 'padb_css' );
add_action( 'wp_footer', 'padb_overlay' );
add_action( 'wp_footer', 'padb_detector' );
add_action( 'wp_enqueue_scripts', 'padb_enqueue_scripts' );

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
			'padb_pluginPage_section_0', __( 'Message', 'proadblock' ), 'padb_settings_section_callback_1', 'pluginPage1'
	);

	add_settings_field(
			'modal_message', __( 'Text', 'proadblock' ), 'padb_message_render', 'pluginPage1', 'padb_pluginPage_section_0'
	);

	add_settings_section(
			'padb_pluginPage_section_1', __( 'Appearance', 'proadblock' ), 'padb_settings_section_callback_2', 'pluginPage2'
	);

	add_settings_field(
			'modal_box_bg_color', __( 'Background color', 'proadblock' ), 'padb_box_bg_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_font_color', __( 'Font color', 'proadblock' ), 'padb_font_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_link_color', __( 'Link color', 'proadblock' ), 'padb_link_color_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_link_color_hover', __( 'Link hover color', 'proadblock' ), 'padb_link_color_hover_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);

	add_settings_field(
			'modal_style', __( 'Modal style', 'proadblock' ), 'padb_select_modal_style_render', 'pluginPage2', 'padb_pluginPage_section_1'
	);
}

function padb_message_render() {
	$options = padb_get_option( 'padb_settings' );
	?>
	<textarea rows='15' name='padb_settings[modal_message]' class='large-text code'><?php echo $options[ 'modal_message' ]; ?></textarea>
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
	<select name='padb_settings[modal_style]'>
		<option value='1'<?php selected( $options[ 'modal_style' ], 1 ); ?>><?php echo __( 'Box w/ transparent background', 'proadblock' ); ?></option>
		<option value='2'<?php selected( $options[ 'modal_style' ], 2 ); ?>><?php echo __( 'Fully locked screen', 'proadblock' ); ?></option>
	</select>
	<?php
}

function padb_settings_section_callback_1() {
	echo __( 'Display a custom text to users that have no adblocker enabled.', 'proadblock' );
}

function padb_settings_section_callback_2() {
	echo __( 'Set custom colors for the modal box.', 'proadblock' );
}

function padb_options_page() {
	?>
	<div class="wrap">
		<h1><?php echo __( 'Pro-AdBlock Settings', 'proadblock' ); ?></h1>

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
	// loaded when no entry in database
	$defaults = array(
		'modal_message'			 => __( "<h1>You are not using an Adblocker!</h1>\n\nAdvertising displayed on webpages can be a security risk. Currently, the advertising consists of embedded third party content. These contents are not under the website's owner editorial control and add a repeatedly criminally exploited attack vector to the website. An adblocker protects a your surfing. This site explicitly supports the usage of advertisement blockers. Please consider to use one!\n\nYou can find a listing of adblockers here:\n<strong><a href=\"http://crxproject.github.io/pro-adblock/lists.html\" target=\"_blank\">Pro-AdBlock (Adblocker Promotion)</a></strong>\n\nThank you for your attention.", 'proadblock' ),
		'modal_box_bg_color'	 => 'E89900',
		'modal_font_color'		 => 'FFFFFF',
		'modal_link_color'		 => 'FFFFFF',
		'modal_link_color_hover' => 'FFFFFF',
		'modal_style'			 => '1'
	);

	$output = get_option( $values, $defaults );

	return $output;
}
