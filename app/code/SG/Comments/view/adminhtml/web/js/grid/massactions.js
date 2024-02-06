 /**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 * @author Serhiy Gavrylov
 */

define([
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate',
    'mageUtils'
], function (Massactions, uiAlert, _, $, $t, utils) {
    'use strict';

    return Massactions.extend({
        /**
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            let itemsType = 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            utils.submit({
                url: action.url,
                data: selections
            });
        }
    });
});
