/**
 * Tabby / Tamara installment boxes:
 * - keeps every installment amount in sync with the product price box
 * - opens the matching plans popup when a box is clicked (or Enter / Space)
 */
define(['jquery', 'Magento_Catalog/js/price-utils'], function ($, priceUtils) {
    'use strict';

    return function (config, element) {
        var $root = $(element),
            $priceBox = $('[data-role=priceBox][data-price-box=product-id-' + config.productId + ']'),
            // Popups live at the end of <body> so they sit above the sticky header
            $modals = $root.find('[data-installment-modal]').appendTo('body'),
            $amounts = $root.find('[data-installment-amount]').add($modals.find('[data-installment-amount]')),
            $lastTrigger = null;

        function updateAmounts() {
            var widget = $priceBox.data('magePriceBox'),
                prices = widget && widget.cache && widget.cache.displayPrices,
                price = prices && prices.finalPrice ? prices.finalPrice.amount : null;

            if (!price || !config.priceFormat) {
                return;
            }

            $amounts.each(function () {
                var $amount = $(this),
                    count = Number($amount.data('count')),
                    fee = Number($amount.data('fee')) / 100,
                    value = $amount.data('type') === 'fee' ? price * fee / count : price * (1 + fee) / count;

                $amount.text(priceUtils.formatPrice(value, config.priceFormat) + ($amount.data('suffix') || ''));
            });
        }

        function close() {
            $modals.filter(':not([hidden])').attr('hidden', true);
            $('body').removeClass('installment-modal-open');

            if ($lastTrigger) {
                $lastTrigger.trigger('focus');
                $lastTrigger = null;
            }
        }

        function open(name, $trigger) {
            var $modal = $modals.filter('[data-installment-modal="' + name + '"]');

            close();
            $lastTrigger = $trigger;
            $modal.removeAttr('hidden').scrollTop(0);
            // Focus for keyboard users without letting the browser scroll the panel to the very top edge
            $modal.find('.installment-modal-panel').scrollTop(0).get(0).focus({preventScroll: true});
            $('body').addClass('installment-modal-open');
        }

        $root.on('click', '[data-installment-open]', function (event) {
            event.preventDefault();
            open($(this).data('installmentOpen'), $(this));
        });

        $root.on('keydown', '[data-installment-open]', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open($(this).data('installmentOpen'), $(this));
            }
        });

        // Close on the X button or a click on the dimmed backdrop
        $modals.on('click', function (event) {
            if (event.target === this || $(event.target).closest('[data-installment-close]').length) {
                close();
            }
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && $('body').hasClass('installment-modal-open')) {
                close();
            }
        });

        $priceBox.on('reloadPrice', updateAmounts);
    };
});
