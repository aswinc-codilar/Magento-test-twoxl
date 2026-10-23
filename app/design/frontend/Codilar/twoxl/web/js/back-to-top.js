/**
 * Shows the "Top" button once the page is scrolled past `offset` px; clicking it scrolls back to the top.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $button = $(element),
            offset = config.offset || 300,
            ticking = false;

        function update() {
            $button.prop('hidden', window.pageYOffset < offset);
            ticking = false;
        }

        $(window).on('scroll', function () {
            if (!ticking) {
                ticking = true;
                window.requestAnimationFrame(update);
            }
        });

        $button.on('click', function () {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });

        update();
    };
});
