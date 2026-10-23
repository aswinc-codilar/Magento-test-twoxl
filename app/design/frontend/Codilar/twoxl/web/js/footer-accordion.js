/**
 * Turns the footer link columns into +/- accordions on mobile (always open on desktop).
 */
define(['jquery', 'matchMedia'], function ($, mediaCheck) {
    'use strict';

    return function (config, element) {
        var $footer = $(element),
            isMobile = false;

        function toggle($heading) {
            var $column = $heading.closest('.footer-column'),
                open = !$column.hasClass('active');

            $column.toggleClass('active', open);
            $heading.attr('aria-expanded', open);
        }

        $footer.on('click keydown', '[data-role="footer-toggle"]', function (event) {
            if (!isMobile || event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            toggle($(this));
        });

        mediaCheck({
            media: '(max-width: 767px)',
            entry: function () {
                isMobile = true;
            },
            exit: function () {
                isMobile = false;
                $footer.find('.footer-column').removeClass('active');
                $footer.find('[data-role="footer-toggle"]').attr('aria-expanded', true);
            }
        });
    };
});
