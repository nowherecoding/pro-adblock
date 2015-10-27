<?php
/*
  Plugin Name: Pro-AdBlock
  Plugin URI: https://github.com/crxproject/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: Sergej Theiss
  Author URI: https://github.com/crxproject/
  Version: 0.9.3
  License: http://www.gnu.org/licenses/gpl-2.0.html
 */

// SECURITY: Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    die( 'Direct acces not allowed!' );
}

// Constants
define( 'WP_PADB_VERSION', '0.9.3' );
define( 'PADB_URL', plugin_dir_url( __FILE__ ) );

/**
 * Custom css setup based on the users choice
 */
function padb_css() {
    // in future versions
}

/**
 *  Overlay generation
 */
function padb_overlay() {

    // the modal
    ?>
    <div id="padb-modal">
        <div id="padb-modal-inner"><div id="padb-modal-close"></div>
    	<h1>OOPS!</h1>
    	<p>
		<?php _e( 'Es sieht so aus, als hättest du keinen Werbeblocker installiert. Das ist schlecht für dein Gehirn und manchmal auch für deinen Computer.', 'padb' ); ?>
    	</p>

    	<p>
		<?php echo sprintf( _e( 'Bitte besuche eine der folgenden Seiten und installiere dir einen AdBlocker deiner Wahl, danach kannst du <em>%s</em> wieder ohne Einschränkungen genießen:', 'padb' ), get_bloginfo( 'name' ) ); ?>
    	</p>

    	<p><!--list of adblockers-->
    	    <strong><a href="https://www.ublock.org/">uBlock</a></strong><br />

    	    <strong><a href="https://adblockplus.org/" target="_blank">AdBlock Plus</a></strong><br />

    	    <strong><a href="https://addons.mozilla.org/de/firefox/addon/adblock-edge/" target="_blank">AdBlock Edge (Firefox)</a></strong><br />

    	    <strong><a href="https://github.com/gorhill/uMatrix" target="_blank">uMatrix</a></strong><br />

    	    <strong><a href="https://adguard.com/en/adguard-adblock-browser-extension/overview.html" target="_blank">Adguard</a></strong>
    	</p>
        </div>
    </div>
    <?php
}

/**
 * Adblocker detection
 */
function padb_detector() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
    	// mobile device detection
    	var isMobile = false; //initiate as false
    	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    	    isMobile = true;
    	}
    	// hide the modal if adblocker is enabled
    	function adBlockDetected() {
    	    $('#padb-modal').hide();
    	}
    	// show the modal if adblocker is disabled
    	function adBlockNotDetected() {
    	    if (!Cookies.set('padb_accepted') && !isMobile) {
    		$('#padb-modal').show();
    		// generate cookie if user closes modal
    		$('#padb-modal-close').click(function () {
    		    $('#padb-modal').slideUp('slow');
    		    var date = new Date();
    		    date.setTime(date.getTime() + 365 * 24 * 60 * 60 * 1000);
    		    Cookies.set('padb_accepted', true, {expires: date, path: '/'});
    		});
    	    } else {
    		$('#padb-modal').hide();
    	    }
    	}
    	if (typeof blockAdBlock === 'undefined') {
    	    adBlockDetected();
    	} else {
    	    blockAdBlock.setOption({debug: true});
    	    blockAdBlock.onDetected(adBlockDetected).onNotDetected(adBlockNotDetected);
    	}
        });
    </script>
    <?php
}

/**
 * Scripts & styles enqueueing
 */
function padb_enqueue_scripts() {
    wp_enqueue_style( 'padb', PADB_URL . 'assets/css/padb-style.min.css', false, WP_PADB_VERSION, 'all' );
    wp_enqueue_script( 'js-cookie', PADB_URL . 'assets/js/js.cookie.min.js', array( 'jquery' ), '2.0.4', true );
    wp_enqueue_script( 'blockadblock', PADB_URL . 'assets/js/blockadblock.js', array( 'jquery' ), '2.3.0', true );
}

add_action( 'wp_head', 'padb_css' );
add_action( 'wp_footer', 'padb_overlay' );
add_action( 'wp_footer', 'padb_detector' );
add_action( 'wp_enqueue_scripts', 'padb_enqueue_scripts' );
