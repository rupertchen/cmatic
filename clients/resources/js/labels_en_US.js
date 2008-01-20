/**
 * English - US
 * Labels
 */
Ext.namespace('cmatic.labels');

/**
 * API types and fields
 * All sections corresponding to API fields have the form
 * type_X.Y where X is the api name of the type and Y is
 * the field's API name.
 *
 * Every type_X section has one special entry "_name" which
 * is the localized name of the type.
 */

// Event parameters
cmatic.labels.type_ageGroup = {
    _name: 'Age Group',
    shortName: 'Shorthand',
    longName: 'Description'
};

cmatic.labels.type_division = {
    _name: 'Division',
    shortName: cmatic.labels.type_ageGroup.shortName,
    longName: cmatic.labels.type_ageGroup.longName
};

cmatic.labels.type_form = {
    _name: 'Form',
    shortName: cmatic.labels.type_ageGroup.shortName,
    longName: cmatic.labels.type_ageGroup.longName
};

cmatic.labels.type_sex = {
    _name: 'Sex',
    shortName: cmatic.labels.type_ageGroup.shortName,
    longName: cmatic.labels.type_ageGroup.longName
};

// Event
// must come after the event parameter labels
cmatic.labels.type_event = {
    _name: 'Event',
    code: 'Event Code',
    divisionId: cmatic.labels.type_division._name,
    sexId: cmatic.labels.type_sex._name,
    ageGroupId: cmatic.labels.type_ageGroup._name,
    formId: cmatic.labels.type_form._name,
    ringId: 'Ring',
    order: 'Order'
};

// Registration
cmatic.labels.type_competitor = {
    _name: 'Competitor',
    id: 'Competitor Id',
    firstName: 'First Name',
    lastName: 'Last Name',
    email: 'E-mail',
    phone1: 'Primary Phone',
    phone2: 'Seconary Phone'
};

/**
 * Generic buttons
 */
cmatic.labels.button = {
    reload: 'Reload',
    add: 'Add',
    save: 'Save',
    cancel: 'Cancel',
    updateEventCodes: 'Update Event Codes'
};


/**
 * Message box
 */
cmatic.labels.message = {
    warning: 'Warning',
    error: 'Error',
    success: 'Success',
    changesNotSaved: 'Changes were not saved.',
    changesSaved: 'Change saved.',
    cantCloseWithUnsavedChanges: 'There are unsaved changes. To continue, either save or cancel the changes first.'
};


/**
 * Setup client labels
 */

// Misc
cmatic.labels.setup = {
    internalId: 'Record Id'
};

// Navigation tree
cmatic.labels.navTree = {
    // Categories
    eventParameters: 'Event Parameters',
    eventManagement: 'Event Management',
    // Leaf nodes (tabs)
    divisions: 'Divisions',
    sexes: 'Sexes',
    ageGroups: 'Age Groups',
    forms: 'Forms',
    events: 'Available Events',
    schedule: 'Event Schedule'
};

// Event management
cmatic.labels.eventManagement = {
    addingEvents: 'Adding events ...',
    massAddTitle: 'Mass Add Events'
};


/**
 * Registration clients labels
 */
cmatic.labels.registration = {
    competitorList: 'Competitor List'
};
