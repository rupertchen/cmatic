/**
 * TODO: Comment, cmatic.* is root, cmatic.util has common functions
 */
Ext.namespace('cmatic.ddl');
Ext.namespace('cmatic.util');

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
 * Set the absolute path of the blank image for Ext.
 */
Ext.BLANK_IMAGE_URL = cmatic.base.clientUrl + 'resources/ext-2.0/resources/images/default/s.gif';

/**
 * URLs
 */
cmatic.url = {
    blank: cmatic.base.clientUrl + 'blank.html',
    get: cmatic.base.serverApiUrl + 'get.php',
    set: cmatic.base.serverApiUrl + 'set.php',
    massAddEvents: cmatic.base.serverApiUrl + 'massAddEvents.php',
    massUpdateEventCodes: cmatic.base.serverApiUrl + 'massUpdateEventCodes.php'
};


////////////////////////////////////////
// cmatic.ddl
////////////////////////////////////////

cmatic.ddl._eventParameterRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'shortName'},
    {name: 'longName'}
]);


cmatic.ddl._eventRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'code'},
    {name: 'divisionId'},
    {name: 'sexId'},
    {name: 'ageGroupId'},
    {name: 'formId'},
    {name: 'ringId'},
    {name: 'order'}
]);


cmatic.ddl._competitorRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'firstName'},
    {name: 'lastName'},
    {name: 'email'},
    {name: 'phone1'},
    {name: 'phone2'},
    {name: 'emergencyContactName'},
    {name: 'emergencyContactRelation'},
    {name: 'emergencyContactPhone'}
]);


cmatic.ddl._groupRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'name'}
]);


cmatic.ddl._groupMemberRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'groupId'},
    {name: 'competitorId'}
]);

cmatic.ddl._scoringRecord = Ext.data.Record.create([
    {name: 'id'},
    {name: 'eventId'},
    {name: 'competitorId'}
]);


////////////////////////////////////////
// cmatic.util
////////////////////////////////////////

/**
 * Remove the loading mask
 */
cmatic.util.removeLoadingMask = function () {
    Ext.get('loading').remove();
    Ext.get('loading-mask').fadeOut({duration: .25, remove: true});
};

/**
 * Getter for data stores
 * @param {String} cmaticType The api name of a type
 */
cmatic.util.getDataStore = function (cmaticType) {
    var s = Ext.StoreMgr.get(cmaticType);
    if (!s) {
        // Pick a record constructor
        // We're taking a shortcut because we happen to know
        // that only the "event" type is different
        var rc;
        var sortByField = 'id';
        // TODO: Ugly, but it's so simple I'll use it for now
        switch (cmaticType) {
            case 'competitor':
                rc = cmatic.ddl._competitorRecord;
                break;
            case 'event':
                rc = cmatic.ddl._eventRecord;
                sortByField = 'code';
                break;
            case 'group':
                rc = cmatic.ddl._groupRecord;
                sortByField = 'name';
                break;
            case 'groupMember':
                rc = cmatic.ddl._groupMemberRecord;
                break;
            default:
                rc = cmatic.ddl._eventParameterRecord;
        }

        // Make the new store
        s = new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({
                url: cmatic.url.get,
                method: 'POST'
            }),
            baseParams: { type: cmaticType },
            reader: new Ext.data.JsonReader({
                root: 'records',
                id: 'id'
            }, rc),
            sortInfo: {
                field: sortByField,
                direction: 'ASC'
            }
        });
        Ext.StoreMgr.add(cmaticType, s);
        s.load();
    }
    return s;
}


/**
 * TODO: Comment this. Falls back on id if all else fails
 */
cmatic.util.getCachedFieldValue = function (type, field, id) {
    var s = cmatic.util.getDataStore(type);
    if (s) {
        var r = s.getById(id);
        if (r) {
            return r.get(field);
        }
    }
    // If we got here, we didn't find a match. We wrap the
    // values in brackets simply to ensure that they aren't
    // mistaken for valid return ID. Silly yes.
    return '<' + id + '>';
};


/**
 * Build a renderer for event parameters. The renderer will use the
 * longName associated to the id of the type it is given. If a data
 * store or matching id is not found, the raw data is returned.
 *
 * @param {String} type The name of the cmatic type
 */
cmatic.util.getParameterRenderer = function () {
    var _cachedRenderers = new Array();

    return function (type) {
        var r = _cachedRenderers[type];
        if (r) return r;

        r = function (id) {
            return cmatic.util.getCachedFieldValue(type, 'longName', id);
        };
        _cachedRenderers[type] = r;
        return r;
    };
}();


/**
 * TODO: Comment this
 * Enhance it so we can pass an error code?
 */
cmatic.util.alertSaveFailed = function () {
    Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.changesNotSaved);
}
