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
    id: 'Event Id',
    code: 'Event Code',
    divisionId: cmatic.labels.type_division._name,
    sexId: cmatic.labels.type_sex._name,
    ageGroupId: cmatic.labels.type_ageGroup._name,
    formId: cmatic.labels.type_form._name,
    ringId: 'Ring',
    order: 'Order',
    numCompetitors: '# Competitors',
    isFinished: 'Finished',
    short_code: 'Code',
    short_numCompetitors: '#'
};

// Registration
cmatic.labels.type_competitor = {
    _name: 'Competitor',
    subcategoryCompetition: 'Competition',
    subcategoryContact: 'Contact',
    subcategoryEmergency: 'Emergency Contact',
    subcategoryPayment: 'Payment',
    subcategoryMisc: 'Miscellaneous',
    id: 'Competitor Id',
    firstName: 'First Name',
    lastName: 'Last Name',
    sexId: cmatic.labels.type_sex._name,
    age: 'Age',
    divisionId: cmatic.labels.type_division._name,
    weight: 'Weight (lbs)',
    email: 'E-mail',
    phone1: 'Primary Phone',
    phone2: 'Seconary Phone',
    streetAddress: 'Street Address',
    city: 'City',
    state: 'State / Province',
    postalCode: 'Zip / Postal Code',
    country: 'Country',
    school: 'School',
    coach: 'Coach',
    emergencyContactName: 'Emergency Contact Name',
    emergencyContactRelation: 'Emergency Contact Relation',
    emergencyContactPhone: 'Emergency Contact Phone',
    isEarlyRegistration: 'Early Registration',
    isDiscountRegistration: 'Special Discount',
    amountPaid: 'Amount Paid ($)',
    isConfirmed: 'Confirmed',
    comments: 'Comments'
};

cmatic.labels.type_group = {
    _name: 'Group',
    id: 'Group Id',
    name: 'Name',
    eventId: cmatic.labels.type_event._name
};

cmatic.labels.type_groupMember = {
    _name: 'Group Member',
    id: 'Group Member Id',
    groupId: cmatic.labels.type_group._name,
    competitorId: cmatic.labels.type_competitor._name
};

// Scoring
cmatic.labels.type_scoring = {
    _name: 'Scoring',
    id: 'Scoring Id',
    eventId: cmatic.labels.type_event._name,
    competitorId: cmatic.labels.type_competitor._name,
    groupId: cmatic.labels.type_group._name,
    order: 'Order',
    judge0: 'Head Judge',
    judge1: 'Judge 2',
    judge2: 'Judge 3',
    judge3: 'Judge 4',
    judge4: 'Judge 5',
    judge5: 'Judge 6',
    score0: 'Score 1',
    score1: 'Score 2',
    score2: 'Score 3',
    score3: 'Score 4',
    score4: 'Score 5',
    score5: 'Score 6',
    time: 'Time',
    timeDeduction: 'Deduction (Time)',
    otherDeduction: 'Deduction (Other)',
    finalScore: 'Final Score',
    tieBreaker0: '1st Tie Breaker',
    tieBreaker1: '2nd Tie Breaker',
    tieBreaker2: '3rd Tie Breaker',
    placement: 'Placement',
    short_order: '#',
    short_score0: 'S:1',
    short_score1: 'S:2',
    short_score2: 'S:3',
    short_score3: 'S:4',
    short_score4: 'S:5',
    short_score5: 'S:6',
    short_timeDeduction: 'T.Deduct.',
    short_otherDeduction: 'Deduct',
    short_finalScore: 'Final',
    short_tieBreaker0: 'TB:1',
    short_tieBreaker1: 'TB:2',
    short_tieBreaker2: 'TB:3',
    short_placement: 'Place'
};


/**
 * Generic buttons
 */
cmatic.labels.button = {
    reload: 'Reload',
    add: 'Add',
    save: 'Save',
    cancel: 'Cancel',
    updateEventCodes: 'Update Event Codes',
    addCompetitor: 'Add Competitor',
    editCompetitorDetails: 'Edit Competitor Details',
    manageGroups: 'Manage Groups',
    editIndividualEvents: 'Edit Individual Events',
    editGroupEvents: 'Edit Group Events',
    editGroupDetails: 'Edit Group Details',
    remove: 'Remove',
    hideFinished: 'Hide Finished',
    updateNumCompetitors: 'Update Competitors Per Event',
    reloadAll: 'Reload All Data',
    randomize: 'Randomize Competitors',
    placement: 'Update Placements',
    startEvent: 'Start & Unlock Event',
    finishEvent: 'Finish Event',
    lockEvent: 'Lock Event',
    unlockEvent: 'Unlock Event'
};


/**
 * Message box
 */
cmatic.labels.message = {
    warning: 'Warning',
    error: 'Error',
    success: 'Success',
    input: 'Input Needed',
    confirmation: 'Confirmation',
    unlocking: 'Unlocking',
    locking: 'Locking',
    changesNotSaved: 'Changes were not saved.',
    changesSaved: 'Change saved.',
    cantCloseWithUnsavedChanges: 'There are unsaved changes. To continue, either save or cancel the changes first.',
    noRowSelected: 'No row was selected.',
    noNewEvents: 'No new events to add.',
    noNewGroups: 'No new group memberships to add.',
    noScoringUpdates: 'No scoring updates to save.',
    ringNumberPrompt: 'Enter your ring number (1-8):',
    ringNumberTryAgain: 'Oops. That\'s an unknown ring. Please try again.',
    areYouSure: 'Are you sure? Any unsaved changes may be lost.',
    onlyEditCurrentEvent: 'Only the currently unlocked event can be edited.',
    mustUnlockToEdit: 'The event must be unlocked before it can be edited.',
    eventLocked: 'Event locked from edits.',
    eventUnlocked: 'Event unlocked for edits.',
    eventAlreadyLocked: 'Event is already locked from edits.',
    eventAlreadyUnlocked: 'Event is already unlocked for edits.',
    eventStarted: 'Event started.',
    eventFinished: 'Event finished.',
    eventAlreadyStarted: 'Event is already in progress.',
    eventAlreadyFinished: 'Event is already finished.',
    otherEventAlreadyStarted: 'Another event is already in progress.',
    eventNotInProgress: 'This event is not in progress.',
    cantCloseInProgress: 'This event is still in progress. To continue, finish the event.'
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
 * Registration client labels
 */
cmatic.labels.registration = {
    competitorList: 'Competitor List',
    groupList: 'Group List',
    newCompetitor: 'New Competitor',
    newGroup: 'New Group',
    groupDetails: 'Group Details',
    addIndividualEvents: 'Add Individual Events',
    addGroupEvents: 'Add Group Events',
    // 0:id, 1:last name, 2:first name
    competitorDetails: '[{0}] {1}, {2} - Details',
    individualEvents: '[{0}] {1}, {2} - Individual Events',
    groupEvents: '[{0}] {1}, {2} - Group Events'
};


/**
 * Scoring client labels
 */
cmatic.labels.scoring = {
    eventList: 'Event List',
    judgesPanel: 'Judges'
}
