/**
 * -/+ buttons around the product quantity input.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $input = $(element).find('input.qty');

        $(element).on('click', '.qty-step', function () {
            var current = parseFloat($input.val()) || 0,
                next = Math.max(1, current + Number($(this).data('step')));

            $input.val(next).trigger('change');
        });
    };
});
