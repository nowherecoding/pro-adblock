<?php
/**
  Plugin Name: Pro-AdBlock
  Plugin URI: https://github.com/nowherecoding/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: NowhereCoding
  Author URI: https://github.com/nowherecoding/
  Version: 2.0.0
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
define( 'PADB_VERSION', '2.0.0' );
define( 'PADB_URL', plugin_dir_url( __FILE__ ) );
define( 'PADB_MIN_WP_VERSION', '4.9.6' );

// load the plugin's translated strings
add_action( 'init', 'padb_load_textdomain' );

/**
 * Load plugin textdomain
 */
function padb_load_textdomain() {
	load_plugin_textdomain( 'pro-adblock', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/**
 * Enqueue plugin styles
 */
function padb_stylesheets() {
	wp_enqueue_style( 'pro-adblock', PADB_URL . 'padb-style.css', false, PADB_VERSION, 'all' );
}

/**
 * Enqueue plugin scripts
 */
function padb_javascripts() {
	$js = '<!-- Pro-AdBlock Javascript variables -->
		var padbDelay = %1$s; var padbExpiry = %2$s;';

	wp_enqueue_script( 'padb-detector', PADB_URL . 'gads.js', array( 'jquery', 'utils' ), PADB_VERSION, true );
	wp_add_inline_script( 'padb-detector', sprintf( $js, padb_get_option( 'modal_delay' ), padb_get_option( 'cookie_expiry' ) ), 'before' );
}

/**
 * Generate modal overlay
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
						<small>(*) <?php _e( 'By closing this notice you agree to our cookie policy. Pro-AdBlock uses a temporary cookie that is stored on your computer to disable the adblocker alert for a certain time. Further data are not collected.', 'pro-adblock' ); ?> <?php echo padb_privacy_policy_link(); ?></small>
						<button type="button" class="padb-modal-close" data-dismiss="padb-modal"><?php _e( 'Accept &amp; continue', 'pro-adblock' ); ?> *</button>
					</div>
				</div>

			</div>
		</div><!--/#padb-modal -->
		<?php
	}
}

/**
 * Display a link to the privacy policy
 * This is available only in WP 4.9.6+ and if you have generated a privacy police page in the settings
 *
 * @return type
 */
function padb_privacy_policy_link() {
	if ( function_exists( 'get_privacy_policy_url' ) and ! empty( get_option( 'wp_page_for_privacy_policy' ) ) ) {
		return sprintf( __( "For more information visit our %s.", 'pro-adblock' ), get_the_privacy_policy_link() );
	}
}

add_action( 'wp_enqueue_scripts', 'padb_stylesheets' );
add_action( 'wp_enqueue_scripts', 'padb_javascripts' );
add_action( 'wp_footer', 'padb_overlay' );

/* * *****************************************************************************
 * Admin section
 * **************************************************************************** */

add_action( 'admin_menu', 'padb_add_admin_menu' );
add_action( 'admin_init', 'padb_settings_init' );
add_action( 'admin_init', 'padb_add_privacy_policy_content' );
add_action( 'padb_notices', 'padb_wp_upgrade_notice' );

/**
 * Suggest an upgrade on WordPress versions lesser than 4.9.6
 */
function padb_wp_upgrade_notice() {
	if ( version_compare( get_bloginfo( 'version' ), PADB_MIN_WP_VERSION, '<' ) ) {
		$message = sprintf( __( 'Pro-AdBlock requires at least WP version %1$s to get fully functional. You are running version %2$s. Please upgrade WordPress.', 'pro-adblock' ), PADB_MIN_WP_VERSION, get_bloginfo( 'version' ) );
		printf( '<div class="notice notice-warning"><p>%s</p></div>', $message );
	}
}

/**
 * Add plugin link to admin menu
 */
function padb_add_admin_menu() {
	add_options_page( 'Pro-AdBlock Settings', 'Pro-AdBlock', 'manage_options', 'pro-adblock-options', 'padb_options_page' );
}

/**
 * Initiate plugin settings
 */
function padb_settings_init() {
	register_setting( 'pluginPage1', 'padb2_settings' );
	register_setting( 'pluginPage2', 'padb2_settings' );

	add_settings_section( 'padb_pluginPage_section_0', __( 'Message', 'pro-adblock' ), 'padb_settings_section_callback_1', 'pluginPage1' );
	add_settings_field( 'modal_message', __( 'Text', 'pro-adblock' ), 'padb_message_render', 'pluginPage1', 'padb_pluginPage_section_0' );

	add_settings_section( 'padb_pluginPage_section_1', __( 'Appearance', 'pro-adblock' ), 'padb_settings_section_callback_2', 'pluginPage2' );
	add_settings_field( 'modal_style', __( 'Modal background', 'pro-adblock' ), 'padb_select_modal_style_render', 'pluginPage2', 'padb_pluginPage_section_1' );
	add_settings_field( 'modal_delay', __( 'Modal delay', 'pro-adblock' ), 'padb_modal_delay_render', 'pluginPage2', 'padb_pluginPage_section_1' );
	add_settings_field( 'cookie_expiry', __( 'Cookie lifetime', 'pro-adblock' ), 'padb_cookie_expiry_render', 'pluginPage2', 'padb_pluginPage_section_1' );
}

/**
 * Add option for modal message
 */
function padb_message_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Text', 'pro-adblock' ); ?></span></legend>
		<textarea rows='15' cols='50' name='padb2_settings[modal_message]' class='large-text code'><?php echo padb_get_option( 'modal_message' ); ?></textarea>
	</fieldset>
	<?php
}

/**
 * Add option for modal background style
 */
function padb_select_modal_style_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php _e( 'Modal background', 'pro-adblock' ); ?></span></legend>
		<label><input type="radio" name="padb2_settings[modal_style]" value="1"<?php checked( 1, padb_get_option( 'modal_style' ), true ); ?> /> <span><?php _e( 'Dark transparent', 'pro-adblock' ); ?></span></label><br />
		<label><input type="radio" name="padb2_settings[modal_style]" value="2"<?php checked( 2, padb_get_option( 'modal_style' ), true ); ?> /> <span><?php _e( 'Light transparent', 'pro-adblock' ); ?></span></label>
	</fieldset>
	<?php
}

/**
 * Add option for modal delay
 */
function padb_modal_delay_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php __( 'Modal delay', 'pro-adblock' ); ?></span></legend>
		<label><input type='number' name='padb2_settings[modal_delay]' value='<?php echo padb_get_option( 'modal_delay' ); ?>' min="0" class="small-text" /> <span><?php echo __( 'Waiting time in seconds before message appears', 'pro-adblock' ); ?></span></label>
	</fieldset>
	<?php
}

/**
 * Add option for cookie lifetime
 */
function padb_cookie_expiry_render() {
	?>
	<fieldset><legend class="screen-reader-text"><span><?php __( 'Cookie lifetime', 'pro-adblock' ); ?></span></legend>
		<label><input type='number' name='padb2_settings[cookie_expiry]' value='<?php echo padb_get_option( 'cookie_expiry' ); ?>' min="0" class="small-text" /> <span><?php echo __( 'Time in days after cookie gets auto deleted', 'pro-adblock' ); ?></span></label>
	</fieldset>
	<?php
}

/**
 * Return the default suggested privacy policy content
 *
 * @since WP 4.9.6
 *
 * @param type $descr
 * @return type
 */
function padb_get_default_privacy_content( $descr = false ) {

	$suggested_text	 = $descr ? '<strong class="privacy-policy-tutorial">' . __( 'Suggested text:', 'pro-adblock' ) . ' </strong>' : '';
	$content		 = '';

	// Start of the suggested privacy policy text.
	$descr && $content .= '<div class="wp-suggested-text">';
	$content .= '<h3>' . __( 'What personal data we collect and why we collect it', 'pro-adblock' ) . '</h3>';
	$content .= '<p>' . $suggested_text . __( "We use the plugin Pro-AdBlock based on our legitimate interests within the meaning of Art. 6 (1) lit. f GDPR, because with the help of Pro-AdBlock we alert users about the dangers of surfing without an adblocker browser plug-in and offer them the opportunity to install one.", 'pro-adblock' ) . '</p>';
	$content .= '<p>' . __( "Pro-AdBlock uses a temporary cookie that is stored on your computer to disable the adblocker alert for a certain time. Further data are not collected. If a user has installed an adblocker, generally no associated cookie will be stored on the user's device.", 'pro-adblock' ) . '</p>';
	$content .= '</div>';

	return apply_filters( 'wp_get_default_privacy_policy_content', $content );
}

/**
 * Add the suggested privacy policy text to the policy postbox
 *
 * @since 4.9.6
 */
function padb_add_privacy_policy_content() {

	$content = padb_get_default_privacy_content( true );

	if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
		wp_add_privacy_policy_content( __( 'Pro-AdBlock Plugin', 'pro-adblock' ), $content );
	}
}

/**
 * Get message settings callback
 */
function padb_settings_section_callback_1() {
	echo __( 'Display a custom text to users that have no adblocker enabled.', 'pro-adblock' );
}

/**
 * Get appearance settings callback
 */
function padb_settings_section_callback_2() {
	// currently empty
}

/**
 * Generate plugin options page
 */
function padb_options_page() {
	?>
	<div class="wrap">
		<h1><?php _e( 'Pro-AdBlock Settings', 'pro-adblock' ); ?></h1>

		<?php do_action( 'padb_notices' ); ?>

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
 * Get plugin settings
 *
 * @param type $value
 * @return type
 */
function padb_get_option( $value ) {
	// load default options if no entry in database
	$defaults = array(
		'modal_message'	 => __( "<h2>You are not using an Adblocker?!</h2>\n\nAdvertising displayed on webpages can be a security risk. Currently, the advertising mostly consists of embedded third party content. These contents are not under the website's owner editorial control and add a repeatedly criminally exploited attack vector to the website. An adblocker protects your surfing. This site explicitly supports the usage of advertisement blockers. Please consider to use one! A list of adblockers is available on the <strong><a href=\"https://nowherecoding.github.io/pro-adblock/#list\" target=\"_blank\">Pro-AdBlock Homepage</a></strong>.\n\nThank you for your attention.", 'pro-adblock' ),
		'modal_style'	 => 1,
		'modal_delay'	 => 10,
		'cookie_expiry'	 => 7,
	);

	$options = get_option( 'padb2_settings' );
	$output	 = array_key_exists( $value, $options ) ? $options[$value] : $defaults[$value];

	return $output;
}
