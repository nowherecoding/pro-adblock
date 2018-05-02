/*!
 * Pro-AdBlock
 * https://github.com/nowherecoding/pro-adblock/
 *
 * Adblockers routinely block javascript files that have *ads* in the name and hence,
 * this file will not be loaded if the adblocker is active.
 */
jQuery(document).ready(function($) {
	// mobile device detection
	var isMobile = false; //initiate as false
	// excluded b/c currently there are not many adblockers for mobile platforms
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
		isMobile = true;
	}

	// show the modal if adblocker is disabled
	if (!wpCookies.get('padb_accepted') && !isMobile) {
		$('#padb-modal').fadeIn('slow');
		// generate cookie if user closes modal
		$('#padb-modal-close').click(function() {
			$('#padb-modal').fadeOut('slow');
			var date = 7 * 24 * 60 * 60; // set cookie to expire after 7 days
			wpCookies.set('padb_accepted', true, date, '/');
		});
	} else {
		// hide the modal if adblocker is enabled
		$('#padb-modal').hide();
	}
});