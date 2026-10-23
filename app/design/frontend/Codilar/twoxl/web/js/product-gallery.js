/**
 * Product gallery: mobile slider dots, lightbox, and the mobile wishlist/share buttons.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $gallery = $(element),
            $track = $gallery.find('[data-role="pdp-gallery-track"]'),
            $items = $gallery.find('[data-role="pdp-gallery-item"]'),
            $dots = $gallery.find('[data-role="pdp-gallery-dots"] button'),
            $lightbox = null,
            current = 0;

        function setActiveDot(index) {
            $dots.removeClass('active').eq(index).addClass('active');
        }

        // Mobile slider: keep the dots in sync with the scroll position
        $track.on('scroll', function () {
            var width = $track.innerWidth();

            if (width) {
                setActiveDot(Math.round($track.scrollLeft() / width));
            }
        });

        $dots.on('click', function () {
            var index = Number($(this).data('index'));

            $track.animate({scrollLeft: index * $track.innerWidth()}, 250);
            setActiveDot(index);
        });

        // Lightbox
        function show(index) {
            current = (index + $items.length) % $items.length;
            $lightbox.find('img').attr('src', $items.eq(current).attr('href'));
        }

        function close() {
            $lightbox.removeClass('active');
            $('body').removeClass('pdp-lightbox-open');
        }

        function open(index) {
            if (!$lightbox) {
                $lightbox = $(
                    '<div class="pdp-lightbox" role="dialog" aria-modal="true">' +
                    '<button type="button" class="pdp-lightbox-close" aria-label="Close">&times;</button>' +
                    '<button type="button" class="pdp-lightbox-prev" aria-label="Previous">&#8249;</button>' +
                    '<img alt=""/>' +
                    '<button type="button" class="pdp-lightbox-next" aria-label="Next">&#8250;</button>' +
                    '</div>'
                ).appendTo('body');

                $lightbox.on('click', function (event) {
                    if (event.target === this || $(event.target).is('.pdp-lightbox-close')) {
                        close();
                    }
                });
                $lightbox.on('click', '.pdp-lightbox-prev', function () {
                    show(current - 1);
                });
                $lightbox.on('click', '.pdp-lightbox-next', function () {
                    show(current + 1);
                });
                $(document).on('keydown', function (event) {
                    if (!$lightbox.hasClass('active')) {
                        return;
                    }

                    if (event.key === 'Escape') {
                        close();
                    } else if (event.key === 'ArrowLeft') {
                        show(current - 1);
                    } else if (event.key === 'ArrowRight') {
                        show(current + 1);
                    }
                });
            }

            $lightbox.toggleClass('single', $items.length < 2).addClass('active');
            $('body').addClass('pdp-lightbox-open');
            show(index);
        }

        $items.on('click', function (event) {
            event.preventDefault();
            open(Number($(this).data('index')));
        });

        // Mobile overlay buttons
        $gallery.on('click', '[data-role="pdp-wishlist"]', function () {
            $('.product-info-main .action.towishlist').first().trigger('click');
        });

        $gallery.on('click', '[data-role="pdp-share"]', function () {
            var data = {title: document.title, url: window.location.href};

            if (navigator.share) {
                navigator.share(data).catch(function () {});
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(data.url);
            }
        });
    };
});
