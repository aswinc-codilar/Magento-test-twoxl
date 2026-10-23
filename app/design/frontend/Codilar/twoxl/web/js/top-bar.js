/**
 * Cycles the top bar items one at a time on mobile (they are all visible on desktop).
 */
define(['jquery', 'matchMedia'], function ($, mediaCheck) {
    'use strict';

    return function (config, element) {
        var $items = $(element).find('.top-bar-item'),
            interval = config.interval || 3000,
            timer = null,
            current = 0;

        function next() {
            $items.eq(current).removeClass('active');
            current = (current + 1) % $items.length;
            $items.eq(current).addClass('active');
        }

        if ($items.length < 2) {
            return;
        }

        mediaCheck({
            media: '(max-width: 767px)',
            entry: function () {
                timer = setInterval(next, interval);
            },
            exit: function () {
                clearInterval(timer);
            }
        });
    };
});
