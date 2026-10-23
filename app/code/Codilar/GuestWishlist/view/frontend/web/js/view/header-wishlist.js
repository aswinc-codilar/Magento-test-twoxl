/**
 * Header heart icon: item count and link from the "header-wishlist" private content section
 */
define(['uiComponent', 'Magento_Customer/js/customer-data'], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.wishlist = customerData.get('header-wishlist');
        }
    });
});
