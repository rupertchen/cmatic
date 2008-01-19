/**
 * Namespace for all registration-related code
 */
Ext.namespace('cmatic.registration');

cmatic.registration.competitorList = function () {
    return {
        init: function () {
            setTimeout(cmatic.removeLoadingMask, 1000);
        }
    };
}();

Ext.onReady(cmatic.registration.competitorList.init, cmatic.registration.competitorList);
