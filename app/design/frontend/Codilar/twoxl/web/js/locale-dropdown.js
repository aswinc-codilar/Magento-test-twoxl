/**
 * Clicking a disabled country (e.g. OMAN) does nothing: it doesn't navigate and
 * doesn't let the click reach the document handler that closes the dropdown.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        $(element).on('click', '.locale-country.disabled', function (event) {
            event.preventDefault();
            event.stopPropagation();
        });
    };
});
