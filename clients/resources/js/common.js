// TODO: This is probably going to be wrong. Change this
Ext.BLANK_IMAGE_URL = '/~serka/cmc/resources/ext-2.0/resources/images/default/s.gif';

Ext.namespace('cmatic');

/**
 * Remove the loading mask
 * TODO: Move this to some common area
 */
cmatic.removeLoadingMask = function () {
    Ext.get('loading').remove();
    Ext.get('loading-mask').fadeOut({duration: .25, remove: true});
};
