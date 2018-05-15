(function (document) {
    // scale fix
    var metas = document.getElementsByTagName('meta'),
            changeViewportContent = function (content) {
                for (var i = 0; i < metas.length; i++) {
                    if (metas[i].name == "viewport") {
                        metas[i].content = content;
                    }
                }
            },
            initialize = function () {
                changeViewportContent("width=device-width, minimum-scale=1.0, maximum-scale=1.0");
            },
            gestureStart = function () {
                changeViewportContent("width=device-width, minimum-scale=0.25, maximum-scale=1.6");
            },
            gestureEnd = function () {
                initialize();
            };

    if (navigator.userAgent.match(/iPhone/i)) {
        initialize();

        document.addEventListener("touchstart", gestureStart, false);
        document.addEventListener("touchend", gestureEnd, false);
    }

    // scroll to top
    var backToTop = document.getElementById("goto-top");
    var scrollExt = document.body.scrollTop;

    window.onscroll = function () {
        ((document.body.scrollTop > 750 || document.documentElement.scrollTop > 750) ?
                backToTop.style.display = "block" : backToTop.style.display = "none");
    };

    backToTop.onclick = function () {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
})(document);
