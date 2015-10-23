<?php

/*
  Plugin Name: Pro-Adblock
  Plugin URI: https://github.com/crxproject/pro-adblock/
  Description: Displays an overlay to users when no adblocker is enabled.
  Author: Sergej Theiss
  Author URI: https://github.com/crxproject/
  Version: 0.9.1
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
      background:   #500;
      text-align:   center;
      margin:       10%;
      padding:      20px 20px;
      color:        #fff;
      font-weight:  normal;
      font-size:    14px;
    }
    #ad-space-inner h1 {
      color:        #fff;
      font-weight:  bold;
      font-size:    48px;
    }
    #ad-space-inner a {
      color:            #fff;
      text-decoration:  underline;
    }
</style>';
}

function adforce_overlay()
{
    echo '<div id="ad-space">
        <div id="ad-space-inner">
            <h1>OOPS!</h1>
            <p>
                Es sieht so aus, als hättest du keinen Werbeblocker installiert. Das ist schlecht für dein Gehirn und manchmal auch für deinen Computer.
            </p>

            <p>
                ' . sprintf('Bitte besuche eine der folgenden Seiten und installiere dir einen AdBlocker deiner Wahl, danach kannst du <em>%s</em> wieder ohne Einschränkungen genießen:', get_bloginfo('name')) . '
            </p>

            <p>
                <strong>uBlock</strong>: <a href="https://www.ublock.org/">https://www.ublock.org/</a><br />

                <strong>AdBlock Plus</strong>: <a href="https://adblockplus.org/" target="_blank">https://adblockplus.org/</a><br />

                <strong>AdBlock Edge</strong>: für <a href="https://addons.mozilla.org/de/firefox/addon/adblock-edge/" target="_blank">Firefox</a><br />

                <strong>uMatrix</strong>: für <a href="https://addons.mozilla.org/firefox/addon/umatrix/" target="_blank">Firefox</a> &#124; <a href="https://chrome.google.com/webstore/detail/%C2%B5matrix/ogfcmafjalglgifnmanfmnieipoejdcf" target="_blank">Chrome</a> &#124; <a href="https://addons.opera.com/en-gb/extensions/details/umatrix/" target="_blank">Opera</a><br />

                <strong>Adguard AdBlocker</strong>: <a href="https://adguard.com/en/adguard-adblock-browser-extension/overview.html" target="_blank">https://adguard.com/en/adguard-adblock-browser-extension/overview.html</a>
            </p>
        </div>
    </div>';
}

add_action('wp_head', 'adforce_css');
add_action('wp_footer', 'adforce_overlay');
