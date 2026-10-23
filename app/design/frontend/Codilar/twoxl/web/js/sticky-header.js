/**
 * Sticky header helper: publishes the real header height as a CSS variable
 * (used to pin the menu bar right under the header in _extend.less) and flags when the page is scrolled.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $header = $(element),
            ticking = false;

        function measure() {
            document.documentElement.style.setProperty('--twoxl-header-height', $header.outerHeight() + 'px');
        }

        function onScroll() {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(function () {
                    $('body').toggleClass('header-stuck', window.pageYOffset > 0);
                    ticking = false;
                });
            }
        }

        measure();
        onScroll();
        $(window).on('resize', measure).on('scroll', onScroll);
    };
});
