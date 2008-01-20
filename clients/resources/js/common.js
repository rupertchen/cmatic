Ext.namespace('cmatic');

/**
 * Base URL
 * These should be relative to the same domain. Both
 * the server and client must be shosted on the same
 * domain.
 */
cmatic.base = {
    clientUrl: '/~serka/cmc/',
    serverApiUrl: '/~serka/cms/api/'
};

/**
 * URLs
 */
cmatic.url = {
    get: cmatic.base.serverApiUrl + 'get.php',
    set: cmatic.base.serverApiUrl + 'set.php',
    massAddEvents: cmatic.base.serverApiUrl + 'massAddEvents.php',
    massUpdateEventCodes: cmatic.base.serverApiUrl + 'massUpdateEventCodes.php'
};


/**
 * Remove the loading mask
 */
cmatic.removeLoadingMask = function () {
    Ext.get('loading').remove();
    Ext.get('loading-mask').fadeOut({duration: .25, remove: true});
};


/**
 * Set the absolute path of the blank image for Ext.
 */
Ext.BLANK_IMAGE_URL = cmatic.base.clientUrl + 'resources/ext-2.0/resources/images/default/s.gif';
