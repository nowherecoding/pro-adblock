<?php

/*
  Plugin Name: Pro-Adblock
  Plugin URI: https://github.com/crxproject/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: Sergej Theiss
  Author URI: https://github.com/crxproject/
  Version: 0.9
  License: http://www.gnu.org/licenses/gpl-2.0.html
 */

function adforce_css()
{
    echo'<style>
    #ad-space {
      opacity:    0.95;
      background: #000;
      width:      100%;
      height:     100%;
      z-index:    10;
      top:        0;
      left:       0;
      position:   fixed;
    }
    #ad-space-inner {
      background: #300;
      text-align: center;
      margin:     10%;
      padding:    50px 20px;
      color:      #fff;
    }
    #ad-space-inner h1 {
      color: #fff;
      font-weight:  bold;
    }
</style>';
}

function adforce_overlay()
{
    echo '<div id="ad-space">
        <div id="ad-space-inner">
            <h1>Werbung ist schlecht. Bitte benutzen Sie einen AdBlocker...</h1>
            <p>
                Diese Seite unterstützt ausdrücklich die Verwendung von Adblockern,
                auch wenn sie selbst keine Werbung verwendet.<br />
                Vielen Dank für Ihre Aufmerksamkeit.
            </p>
        </div>
    </div>';
}

add_action('wp_head', 'adforce_css');
add_action('wp_footer', 'adforce_overlay');
