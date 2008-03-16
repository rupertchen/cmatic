/**
 * Namespace for all registration-related code
 */
Ext.namespace('cmatic.registration');

cmatic.registration.competitorList = function () {

    /**
     * Get the event code of the specified event.
     */
    function getEventCode (eventId) {
        return cmatic.util.getCachedFieldValue('event', 'code', eventId);
    }


    /**
     * Get a name of the specified group.
     */
    function getGroupName (groupId) {
        return cmatic.util.getCachedFieldValue('group', 'name', groupId);
    }


    /**
     * Retrieve the event id of an event that is an instance of the specified
     * form. This is only useful for forms that should only have one event such
     * as group forms.
     */
    function getEventIdFromFormId (formId) {
        var eventStore = cmatic.util.getDataStore('event');
        return eventStore.getAt(eventStore.find('formId', formId)).get('id');
    }


    /**
     * Get the name of the event a group is registered for
     */
    function getFullEventNameFromGroup (groupId) {
        return cmatic.util.getFullEventNameRenderer(cmatic.util.getCachedFieldValue('group', 'eventId', groupId));
    }


    function primeDataStores () {
        cmatic.util.getDataStore('division');
        cmatic.util.getDataStore('sex');
        cmatic.util.getDataStore('ageGroup');
        cmatic.util.getDataStore('form');
        cmatic.util.getDataStore('event');
        cmatic.util.getDataStore('competitor');
        cmatic.util.getDataStore('group');
    }

    return {
        init: function () {
            primeDataStores();

            var g = new Ext.grid.GridPanel({
                store: cmatic.util.getDataStore('competitor'),
                columns: [{
                    id: 'id',
                    dataIndex: 'id',
                    header: cmatic.labels.type_competitor.id,
                    sortable: true,
                    width: 50,
                    renderer: cmatic.util.competitorIdRenderer
                }, {
                    id: 'lastName',
                    dataIndex: 'lastName',
                    header: cmatic.labels.type_competitor.lastName,
                    sortable: true,
                    width: 50
                }, {
                    id: 'firstName',
                    dataIndex: 'firstName',
                    header: cmatic.labels.type_competitor.firstName,
                    sortable: true,
                    width: 50
                }, {
                    id: 'amountPaid',
                    dataIndex: 'amountPaid',
                    header: cmatic.labels.type_competitor.amountPaid,
                    sortable: true,
                    width: 50
                }, {
                    id: 'isConfirmed',
                    dataIndex: 'isConfirmed',
                    header: cmatic.labels.type_competitor.isConfirmed,
                    sortable: true,
                    width: 50,
                    renderer: function (x) { if (x) { return x; } else { return '<span style="color: red; font-weight: bold;">FALSE</span>'; } }
                }, {
                    id: 'email',
                    dataIndex: 'email',
                    header: cmatic.labels.type_competitor.email,
                    sortable: true,
                    width: 75
                }, {
                    id: 'phone1',
                    dataIndex: 'phone1',
                    header: cmatic.labels.type_competitor.phone1,
                    sortable: true,
                    width: 50,
                    hidden: true
                }, {
                    id: 'phone2',
                    dataIndex: 'phone2',
                    header: cmatic.labels.type_competitor.phone2,
                    sortable: true,
                    width: 50,
                    hidden: true
                }, {
                    id: 'emergencyContactName',
                    dataIndex: 'emergencyContactName',
                    header: cmatic.labels.type_competitor.emergencyContactName,
                    sortable: true,
                    width: 50,
                    hidden: true
                }, {
                    id: 'emergencyContactRelation',
                    dataIndex: 'emergencyContactRelation',
                    header: cmatic.labels.type_competitor.emergencyContactRelation,
                    sortable: true,
                    width: 50,
                    hidden: true
                }, {
                    id: 'emergencyContactPhone',
                    dataIndex: 'emergencyContactPhone',
                    header: cmatic.labels.type_competitor.emergencyContactPhone,
                    sortable: true,
                    width: 50,
                    hidden: true,
                }],
                viewConfig: { forceFit: true },
                autoHeight: true,
                sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
                title: cmatic.labels.registration.competitorList,
                autoScroll: true,
                stripeRows: true,
                renderTo: 'competitorList',
                tbar: [{
                    text: cmatic.labels.button.reload,
                    handler: function () {
                        // Reload all data stores that were primed
                        cmatic.util.getDataStore('division').reload();
                        cmatic.util.getDataStore('sex').reload();
                        cmatic.util.getDataStore('ageGroup').reload();
                        cmatic.util.getDataStore('form').reload();
                        cmatic.util.getDataStore('event').reload();
                        cmatic.util.getDataStore('competitor').reload();
                        cmatic.util.getDataStore('group').reload();
                    }
                }, {
                    xtype: 'tbseparator'
                }, {
                    text: cmatic.labels.button.addCompetitor,
                    handler: function () {
                        var formPanel = new Ext.form.FormPanel({
                            autoHeight: true,
                            defaultType: 'textfield',
                            waitMsgTarget: true,
                            items: [{
                                fieldLabel: cmatic.labels.type_competitor.firstName,
                                name: 'firstName',
                                allowBlank: false
                            }, {
                                fieldLabel: cmatic.labels.type_competitor.lastName,
                                name: 'lastName',
                                allowBlank: false
                            }]
                        });

                        var win = new Ext.Window({
                            title: cmatic.labels.registration.newCompetitor,
                            constrain: true,
                            resizable: false,
                            width: 300,
                            modal: true,
                            items: [formPanel]
                        });

                        formPanel.addButton(cmatic.labels.button.save,
                            function () {
                                Ext.Ajax.request({
                                    url: cmatic.url.set,
                                    success: function (response) {
                                        var r = Ext.util.JSON.decode(response.responseText);
                                        if (r.success) {
                                            cmatic.util.getDataStore('competitor').reload();
                                            win.close();
                                        } else {
                                            cmatic.util.alertSaveFailed();
                                        }
                                    },
                                    failure: cmatic.util.alertSaveFailed,
                                    params: {
                                        op: 'new',
                                        type: 'competitor',
                                        records: '[' + Ext.util.JSON.encode(formPanel.getForm().getValues(false)) + ']'
                                    }
                                });
                            }
                        );
                        formPanel.addButton(cmatic.labels.button.cancel, function () { win.close(); })

                        win.show();
                    }
                }, {
                    text: cmatic.labels.button.manageGroups,
                    handler: function () {

                        var groupList = new Ext.grid.GridPanel({
                            store: cmatic.util.getDataStore('group'),
                            columns: [{
                                id: 'id',
                                dataIndex: 'id',
                                header: cmatic.labels.type_group.id,
                                sortable: true,
                                hidden: true
                            }, {
                                id: 'name',
                                dataIndex: 'name',
                                header: cmatic.labels.type_group.name,
                                sortable: true
                            }, {
                                id: 'eventId',
                                dataIndex: 'eventId',
                                header: cmatic.labels.type_group.eventId,
                                sortable: true,
                                renderer: cmatic.util.getFullEventNameRenderer
                            }],
                            sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
                            viewConfig: {forceFit: true},
                            autoHeight: true,
                            autoScroll: true,
                            stripeRows: true,
                            tbar: [{
                                text: cmatic.labels.button.reload,
                                handler: function () { cmatic.util.getDataStore('group').reload(); }
                            }, {
                                text: cmatic.labels.button.add,
                                handler: function () {
                                    var details = new Ext.FormPanel({
                                        labelWidth: 100,
                                        url: cmatic.url.blank,
                                        autoHeight: true,
                                        defaultType: 'textfield',
                                        items: [{
                                            fieldLabel: cmatic.labels.type_group.name,
                                            name: 'name'
                                        }, {
                                            xtype: 'combo',
                                            fieldLabel: 'Group Event',
                                            name: 'formName',
                                            store: cmatic.util.getDataStore('form'),
                                            displayField: 'longName',
                                            valueField: 'id',
                                            typeAhead: true,
                                            hiddenName: 'formId',
                                            mode: 'local',
                                            triggerAction: 'all',
                                            selectOnFocus: true
                                        }]
                                    });

                                    var win = new Ext.Window({
                                        title: cmatic.labels.registration.newGroup,
                                        constrain: true,
                                        resizable: false,
                                        modal: true,
                                        width: 400,
                                        autoHeight: true,
                                        items: [details]
                                    });

                                    details.addButton(cmatic.labels.button.save, function () {
                                        var values = details.getForm().getValues(false);
                                        var eventId = getEventIdFromFormId(values.formId);
                                        Ext.Ajax.request({
                                            url: cmatic.url.set,
                                            params: {
                                                op: 'new',
                                                type: 'group',
                                                records: '[' + Ext.util.JSON.encode({name: values.name, eventId: eventId}) + ']'
                                            },
                                            success: function (response) {
                                                var r = Ext.util.JSON.decode(response.responseText);
                                                if (r.success) {
                                                    Ext.Ajax.request({
                                                        url: cmatic.url.set,
                                                        params: {
                                                            op: 'new',
                                                            type: 'scoring',
                                                            records: '[' + Ext.util.JSON.encode({eventId: eventId, groupId: r.newId}) + ']'
                                                        },
                                                        success: function (response) {
                                                            var r = Ext.util.JSON.decode(response.responseText);
                                                            if (r.success) {
                                                                cmatic.util.getDataStore('group').reload();
                                                                win.close();
                                                            } else {
                                                                cmatic.util.alertSaveFailed();
                                                            }
                                                        },
                                                        failure: cmatic.util.alertSaveFailed
                                                    });
                                                    cmatic.util.getDataStore('group').reload();
                                                    win.close();
                                                } else {
                                                    cmatic.util.alertSaveFailed();
                                                }
                                            },
                                            failure: cmatic.util.alertSaveFailed
                                        });
                                    });

                                    details.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                                    win.show();
                                }
                            }, {
                                text: cmatic.labels.button.editGroupDetails,
                                handler: function () {
                                    var c = groupList.getSelectionModel().getSelected();
                                    if (!c) {
                                        Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected)
                                        return;
                                    }

                                    var details = new Ext.FormPanel({
                                        labelWidth: 100,
                                        url: cmatic.url.blank,
                                        autoHeight: true,
                                        defaultType: 'textfield',
                                        items: [{
                                            xtype: 'hidden',
                                            fieldLabel: cmatic.labels.type_group.id,
                                            name: 'id',
                                            value: c.get('id')
                                        }, {
                                            fieldLabel: cmatic.labels.type_group.name,
                                            name: 'name',
                                            value: c.get('name')
                                        }]
                                    });

                                    var win2 = new Ext.Window({
                                        title: cmatic.labels.registration.groupDetails,
                                        constrain: true,
                                        resizable: false,
                                        modal: true,
                                        width: 400,
                                        autoHeight: true,
                                        items: [details]
                                    });

                                    details.addButton(cmatic.labels.button.save, function () {
                                        Ext.Ajax.request({
                                            url: cmatic.url.set,
                                            success: function (response) {
                                                var r = Ext.util.JSON.decode(response.responseText);
                                                if (r.success) {
                                                    cmatic.util.getDataStore('group').reload();
                                                    win2.close();
                                                } else {
                                                    cmatic.util.alertSaveFailed();
                                                }
                                            },
                                            failure: cmatic.util.alertSaveFailed,
                                            params: {
                                                type: 'group',
                                                op: 'edit',
                                                records: '[' + Ext.util.JSON.encode(details.getForm().getValues(false)) + ']'
                                            }
                                        });
                                    });
                                    details.addButton(cmatic.labels.button.cancel, function () { win2.close(); });

                                    win2.show();
                               }
                           }]
                        });

                        var win = new Ext.Window({
                            title: cmatic.labels.registration.groupList,
                            constrain: true,
                            resizable: false,
                            width: 300,
                            modal: true,
                            items: [groupList]
                        });

                        win.show();
                    }
                }, {
                    xtype: 'tbseparator'
                }, {
                    text: cmatic.labels.button.editCompetitorDetails,
                    handler: function () {
                        var c = g.getSelectionModel().getSelected();
                        if (c) {
                            var competitionFieldSet = {
                                xtype: 'fieldset',
                                title: cmatic.labels.type_competitor.subcategoryCompetition,
                                autoHeight: true,
                                defaults: {width: 150},
                                defaultType: 'textfield',
                                items: [{
                                    xtype: 'hidden',
                                    name: 'id',
                                    value: c.get('id'),
                                    allowBlank: false
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.lastName,
                                    name: 'lastName',
                                    value: c.get('lastName'),
                                    allowBlank: false
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.firstName,
                                    name: 'firstName',
                                    value: c.get('firstName'),
                                    allowBlank: false
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: cmatic.labels.type_competitor.sexId,
                                    name: 'sexName',
                                    store: cmatic.util.getDataStore('sex'),
                                    displayField: 'longName',
                                    valueField: 'id',
                                    typeAhead: true,
                                    hiddenName: 'sexId',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    forceSelection: true,
                                    value: c.get('sexId'),
                                    allowBlank: false
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.age,
                                    name: 'age',
                                    value: c.get('age'),
                                    allowBlank: false
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: cmatic.labels.type_competitor.divisionId,
                                    name: 'divisionName',
                                    store: cmatic.util.getDataStore('division'),
                                    displayField: 'longName',
                                    valueField: 'id',
                                    typeAhead: true,
                                    hiddenName: 'divisionId',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    forceSelection: true,
                                    value: c.get('divisionId'),
                                    allowBlank: false,
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.weight,
                                    name: 'weight',
                                    value: c.get('weight')
                                }]
                            };
                            var contactFieldSet = {
                                xtype: 'fieldset',
                                title: cmatic.labels.type_competitor.subcategoryContact,
                                autoHeight: true,
                                defaults: {width: 150},
                                defaultType: 'textfield',
                                items: [{
                                    fieldLabel: cmatic.labels.type_competitor.email,
                                    name: 'email',
                                    value: c.get('email')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.phone1,
                                    name: 'phone1',
                                    value: c.get('phone1')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.phone2,
                                    name: 'phone2',
                                    value: c.get('phone2')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.streetAddress,
                                    name: 'streetAddress',
                                    value: c.get('streetAddress')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.city,
                                    name: 'city',
                                    value: c.get('city')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.state,
                                    name: 'state',
                                    value: c.get('state')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.postalCode,
                                    name: 'postalCode',
                                    value: c.get('postalCode')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.country,
                                    name: 'country',
                                    value: c.get('country')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.school,
                                    name: 'school',
                                    value: c.get('school')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.coach,
                                    name: 'coach',
                                    value: c.get('coach')
                                }]
                            };
                            var emergencyFieldSet = {
                                xtype: 'fieldset',
                                title: cmatic.labels.type_competitor.subcategoryEmergency,
                                autoHeight: true,
                                defaults: {width: 150},
                                defaultType: 'textfield',
                                items: [{
                                    fieldLabel: cmatic.labels.type_competitor.emergencyContactName,
                                    name: 'emergencyContactName',
                                    value: c.get('emergencyContactName')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.emergencyContactRelation,
                                    name: 'emergencyContactRelation',
                                    value: c.get('emergencyContactRelation')
                                }, {
                                    fieldLabel: cmatic.labels.type_competitor.emergencyContactPhone,
                                    name: 'emergencyContactPhone',
                                    value: c.get('emergencyContactPhone')
                                }]
                            };
                            var paymentFieldSet = {
                                xtype: 'fieldset',
                                title: cmatic.labels.type_competitor.subcategoryPayment,
                                autoHeight: true,
                                defaults: {width: 150},
                                items: [{
                                    xtype: 'combo',
                                    fieldLabel: cmatic.labels.type_competitor.isEarlyRegistration,
                                    name: 'isEarlyRegistrationLabel',
                                    store: cmatic.constant.booleanStore,
                                    displayField: 'label',
                                    valueField: 'value',
                                    typeAhead: true,
                                    hiddenName: 'isEarlyRegistration',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    forceSelection: true,
                                    value: cmatic.util.getCmaticBoolean(c.get('isEarlyRegistration')),
                                    allowBlank: false,

                                }, {
                                    xtype: 'combo',
                                    fieldLabel: cmatic.labels.type_competitor.isDiscountRegistration,
                                    name: 'isDiscountRegistrationLabel',
                                    store: cmatic.constant.booleanStore,
                                    displayField: 'label',
                                    valueField: 'value',
                                    typeAhead: true,
                                    hiddenName: 'isDiscountRegistration',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    forceSelection: true,
                                    value: cmatic.util.getCmaticBoolean(c.get('isDiscountRegistration')),
                                    allowBlank: false
                                }, {
                                    xtype: 'textfield',
                                    fieldLabel: cmatic.labels.type_competitor.amountPaid,
                                    name: 'amountPaid',
                                    value: c.get('amountPaid')
                                }, {
                                    xtype: 'combo',
                                    fieldLabel: cmatic.labels.type_competitor.isConfirmed,
                                    name: 'isConfirmedLabel',
                                    store: cmatic.constant.booleanStore,
                                    displayField: 'label',
                                    valueField: 'value',
                                    typeAhead: true,
                                    hiddenName: 'isConfirmed',
                                    mode: 'local',
                                    triggerAction: 'all',
                                    selectOnFocus: true,
                                    forceSelection: true,
                                    value: cmatic.util.getCmaticBoolean(c.get('isConfirmed')),
                                    allowBlank: false
                                }]
                            };
                            var miscFieldSet = {
                                xtype: 'fieldset',
                                title: cmatic.labels.type_competitor.subcategoryMisc,
                                autoHeight: true,
                                defaults: {width: 150},
                                items: [{
                                    xtype: 'textarea',
                                    fieldLabel: cmatic.labels.type_competitor.comments,
                                    name: 'comments',
                                    value: c.get('comments'),
                                    height: 130
                                }]
                            };

                            var details = new Ext.FormPanel({
                                labelWidth: 100,
                                url: cmatic.url.blank,
                                autoHeight: true,
                                items: [{
                                    xtype: 'panel',
                                    layout: 'column',
                                    defaults: {
                                        columnWidth: .5
                                    },
                                    items: [{
                                        xtype: 'panel',
                                        items: [competitionFieldSet, contactFieldSet]
                                    }, {
                                        xtype: 'panel',
                                        items: [emergencyFieldSet, paymentFieldSet, miscFieldSet]
                                    }]
                                }],
                            });

                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.competitorDetails,
                                    cmatic.util.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
                                constrain: true,
                                resizable: false,
                                modal: true,
                                width: 700,
                                autoHeight: true,
                                items: [details]
                            });
                            details.addButton(cmatic.labels.button.save, function () {
                                var vals = details.getForm().getValues(false);
                                // weight is an optional field, but it is an integer use null instead of the emptry string
                                if (!vals['weight']) {
                                    vals['weight'] = null;
                                }
                                Ext.Ajax.request({
                                    url: cmatic.url.set,
                                    success: function (response) {
                                        var r = Ext.util.JSON.decode(response.responseText);
                                        if (r.success) {
                                            cmatic.util.getDataStore('competitor').reload();
                                            win.close();
                                        } else {
                                            cmatic.util.alertSaveFailed();
                                        }
                                    },
                                    failure: cmatic.util.alertSaveFailed,
                                    params: {
                                        type: 'competitor',
                                        op: 'edit',
                                        records: '[' + Ext.util.JSON.encode(vals) + ']'
                                    }
                                });
                            });
                            details.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected);
                        }
                    }
                }, {
                    text: cmatic.labels.button.editIndividualEvents,
                    handler: function () {
                        var c = g.getSelectionModel().getSelected();
                        if (c) {
                            var iEventStore = new Ext.data.Store({
                                proxy: new Ext.data.HttpProxy({
                                    url: cmatic.url.get,
                                    method: 'POST'
                                }),
                                baseParams: {
                                    type: 'scoring',
                                    filterField: 'competitorId',
                                    filterValue: c.get('id')
                                },
                                reader: new Ext.data.JsonReader({
                                        root: 'records',
                                        id: 'id'
                                    }, cmatic.ddl._scoringRecord),
                                sortInfo: {
                                    field: 'eventId',
                                    direction: 'ASC'
                                }
                            });
                            iEventStore.load();
                            var eventsGrid = new Ext.grid.GridPanel({
                                store: iEventStore,
                                columns: [{
                                    id: 'id',
                                    dataIndex: 'id',
                                    header: cmatic.labels.type_scoring.id,
                                    sortable: true,
                                    hidden: true
                                }, {
                                    id: 'eventCode',
                                    dataIndex: 'eventId',
                                    header: cmatic.labels.type_event.code,
                                    sortable: true,
                                    renderer: getEventCode,
                                    width: 50
                                }, {
                                    id: 'eventName',
                                    dataIndex: 'eventId',
                                    header: cmatic.labels.type_event._name,
                                    renderer: cmatic.util.getFullEventNameRenderer,
                                    width: 150
                                }],
                                viewConfig: {forceFit: true },
                                autoHeight: true,
                                autoScroll: true,
                                stripeRows: true,
                                tbar: [{
                                    text: cmatic.labels.button.reload,
                                    handler: function () { iEventStore.reload(); }
                                }, {
                                    text: cmatic.labels.button.add,
                                    handler: function () {
                                        // TODO: Add the ability to filter the list. The current
                                        // list is ridiculously huge and annoying to use.

                                        var eventList = new Ext.grid.GridPanel({
                                            store: cmatic.util.getDataStore('event'),
                                            columns: [{
                                                id: 'id',
                                                dataIndex: 'id',
                                                header: cmatic.labels.setup.internalId,
                                                sortable: true,
                                                hidden: true
                                            }, {
                                                id: 'code',
                                                dataIndex: 'code',
                                                header: cmatic.labels.type_event.code,
                                                sortable: true,
                                                width: 50
                                            }, {
                                                id: 'divisionId',
                                                dataIndex: 'divisionId',
                                                header: cmatic.labels.type_event.divisionId,
                                                sortable: true,
                                                width: 50,
                                                renderer: cmatic.util.getParameterRenderer('division')
                                            }, {
                                                id: 'sexId',
                                                dataIndex: 'sexId',
                                                header: cmatic.labels.type_event.sexId,
                                                sortable: true,
                                                width: 40,
                                                renderer: cmatic.util.getParameterRenderer('sex')
                                            }, {
                                                id: 'ageGroupId',
                                                dataIndex: 'ageGroupId',
                                                header: cmatic.labels.type_event.ageGroupId,
                                                sortable: true,
                                                width: 50,
                                                renderer: cmatic.util.getParameterRenderer('ageGroup')
                                            }, {
                                                id: 'formId',
                                                dataIndex: 'formId',
                                                header: cmatic.labels.type_event.formId,
                                                sortable: true,
                                                renderer: cmatic.util.getParameterRenderer('form')
                                            }],
                                            viewConfig: {forceFit: true},
                                            height: 400,
                                            autoScroll: true,
                                            stripeRows: true
                                        });

                                        var formPanel = new Ext.form.FormPanel({
                                            autoHeight: true,
                                            items: [eventList]
                                        });

                                        var win2 = new Ext.Window({
                                            title: cmatic.labels.registration.addIndividualEvents,
                                            constrain: true,
                                            resizable: false,
                                            modal: true,
                                            width: 650,
                                            autoHeight: true,
                                            items: [formPanel]
                                        });

                                        formPanel.addButton(cmatic.labels.button.add, function () {
                                            var scoringToAdd = new Array();

                                            // Add all events that aren't already associated to the competitor
                                            Ext.each(eventList.getSelectionModel().getSelections(), function (eventRecord) {
                                                var eventId = eventRecord.get('id');
                                                if (-1 == iEventStore.find('eventId', eventId)) {
                                                    scoringToAdd.push({
                                                        eventId: eventId,
                                                        competitorId: c.get('id')
                                                    });
                                                }
                                            });

                                            if (0 == scoringToAdd.length) {
                                                Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noNewEvents);
                                            } else {
                                                Ext.Ajax.request({
                                                    url: cmatic.url.set,
                                                    success: function (response) {
                                                        var r = Ext.util.JSON.decode(response.responseText);
                                                        if (r.success) {
                                                            iEventStore.reload();
                                                            Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.changesSaved);
                                                        } else {
                                                            cmatic.util.alertSaveFailed();
                                                        }
                                                    },
                                                    failure: cmatic.util.alertSaveFailed,
                                                    params: {
                                                        op: 'new',
                                                        type: 'scoring',
                                                        records: Ext.util.JSON.encode(scoringToAdd)
                                                    }
                                                });
                                            }
                                        });

                                        formPanel.addButton(cmatic.labels.button.cancel, function () {
                                            win2.close();
                                        });

                                        win2.show();
                                    }
                                }, {
                                    text: cmatic.labels.button.remove,
                                    handler: function () {
                                        var recordsToRemove = eventsGrid.getSelectionModel().getSelections();

                                        if (0 == recordsToRemove.length) {
                                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected);
                                            return;
                                        }

                                        var eventIdsToRemove = new Array();
                                        Ext.each(recordsToRemove, function (x) {
                                            eventIdsToRemove.push({id: x.get('id')});
                                        });
                                        Ext.Ajax.request({
                                            url: cmatic.url.set,
                                            success: function (response) {
                                                var r = Ext.util.JSON.decode(response.responseText);
                                                if (r.success) {
                                                    iEventStore.reload();
                                                } else {
                                                    cmatic.util.alertSaveFailed();
                                                }
                                            },
                                            failure: cmatic.util.alertSaveFailed,
                                            params: {
                                                op: 'delete',
                                                type: 'scoring',
                                                records: Ext.util.JSON.encode(eventIdsToRemove)
                                            }
                                        });
                                    }
                                }]
                            });

                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.individualEvents,
                                    cmatic.util.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
                                constrain: true,
                                resizable: false,
                                modal: true,
                                width: 500,
                                autoHeight: true,
                                items: [eventsGrid]
                            });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected);
                        }
                    }
                }, {
                    text: cmatic.labels.button.editGroupEvents,
                    handler: function () {
                        var c = g.getSelectionModel().getSelected();
                        if (c) {
                            var iGroupMemberStore = new Ext.data.Store({
                                proxy: new Ext.data.HttpProxy({
                                    url: cmatic.url.get,
                                    method: 'POST'
                                }),
                                baseParams: {
                                    type: 'groupMember',
                                    filterField: 'competitorId',
                                    filterValue: c.get('id')
                                },
                                reader: new Ext.data.JsonReader({
                                    root: 'records',
                                    id: 'id'
                                }, cmatic.ddl._groupMemberRecord),
                                sortInfo: {
                                    field: 'groupId',
                                    direction: 'ASC'
                                }
                            });
                            iGroupMemberStore.load();

                            var groupEventGrid = new Ext.grid.GridPanel({
                                store: iGroupMemberStore,
                                columns: [{
                                    id: 'id',
                                    dataIndex: 'id',
                                    header: cmatic.labels.type_groupMember.id,
                                    sortable: true,
                                    hidden: true
                                }, {
                                    id: 'groupName',
                                    dataIndex: 'groupId',
                                    header: cmatic.labels.type_groupMember.groupId,
                                    renderer: getGroupName
                                }, {
                                    id: 'eventName',
                                    dataIndex: 'groupId',
                                    header: cmatic.labels.type_event._name,
                                    renderer: getFullEventNameFromGroup
                                }],
                                viewConfig: {forceFit: true},
                                autoHeight: true,
                                autoScroll: true,
                                stripeRows: true,
                                tbar: [{
                                    text: cmatic.labels.button.reload,
                                    handler: function () { iGroupMemberStore.reload(); }
                                }, {
                                    text: cmatic.labels.button.add,
                                    handler: function () {
                                        var groupsList = new Ext.grid.GridPanel({
                                            store: cmatic.util.getDataStore('group'),
                                            columns: [{
                                                id: 'id',
                                                dataIndex: 'id',
                                                header: cmatic.labels.type_group.id,
                                                sortable: true,
                                                hidden: true
                                            }, {
                                                id: 'name',
                                                dataIndex: 'name',
                                                header: cmatic.labels.type_group.name,
                                                sortable: true
                                            }, {
                                                id: 'event',
                                                dataIndex: 'eventId',
                                                header: cmatic.labels.type_group.eventId,
                                                sortable: true,
                                                renderer: cmatic.util.getFullEventNameRenderer
                                            }],
                                            viewConfig: {forceFit: true},
                                            autoHeight: true,
                                            autoScroll: true,
                                            stripeRows: true
                                        });

                                        var formPanel = new Ext.FormPanel({
                                            autoHeight: true,
                                            items: [groupsList]
                                        });

                                        var win = new Ext.Window({
                                            title: cmatic.labels.registration.addGroupEvents,
                                            constrain: true,
                                            resizable: false,
                                            modal: true,
                                            width: 400,
                                            autoHeight: true,
                                            items: [formPanel]
                                        });

                                        formPanel.addButton(cmatic.labels.button.add, function () {
                                            groupsToAdd = new Array();

                                            // Add all groups that the competitor isn't already a member of
                                            Ext.each(groupsList.getSelectionModel().getSelections(), function (groupRecord) {
                                                var groupId = groupRecord.get('id');
                                                if (-1 == cmatic.util.getDataStore('group').find('groupId', groupId)) {
                                                    groupsToAdd.push({
                                                        groupId: groupId,
                                                        competitorId: c.get('id')
                                                    });
                                                }
                                            });

                                            if (0 == groupsToAdd.length) {
                                                Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noNewGroups);
                                            } else {
                                                Ext.Ajax.request({
                                                    url: cmatic.url.set,
                                                    params: {
                                                        op: 'new',
                                                        type: 'groupMember',
                                                        records: Ext.util.JSON.encode(groupsToAdd)
                                                    },
                                                    success: function (response) {
                                                        var r = Ext.util.JSON.decode(response.responseText);
                                                        if (r.success) {
                                                            Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.changesSaved);
                                                            iGroupMemberStore.load();
                                                            win.close();
                                                        } else {
                                                            cmatic.util.alertSaveFailed();
                                                        }
                                                    },
                                                    failure: cmatic.util.alertSaveFailed
                                                });
                                            }
                                        });

                                        formPanel.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                                        win.show();
                                    }
                                }, {
                                    text: cmatic.labels.button.remove,
                                    handler: function () {
                                        var groupMembershipsToRemove = groupEventGrid.getSelectionModel().getSelections();
                                        if (0 == groupMembershipsToRemove.length) {
                                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected);
                                            return;
                                        }

                                        var groupMembershipIdsToRemove = new Array();
                                        Ext.each(groupMembershipsToRemove, function (x) {
                                            groupMembershipIdsToRemove.push({id: x.get('id')});
                                        });

                                        Ext.Ajax.request({
                                            url: cmatic.url.set,
                                            params: {
                                                op: 'delete',
                                                type: 'groupMember',
                                                records: Ext.util.JSON.encode(groupMembershipIdsToRemove)
                                            },
                                            success: function (response) {
                                                var r = Ext.util.JSON.decode(response.responseText);
                                                if (r.success) {
                                                    iGroupMemberStore.reload();
                                                } else {
                                                    cmatic.util.alertSaveFailed();
                                                }
                                            },
                                            failure: cmatic.util.alertSaveFailed
                                        });
                                    }
                                }]
                            });

                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.groupEvents,
                                    cmatic.util.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
                                constrain: true,
                                resizable: false,
                                modal: true,
                                width: 400,
                                autoHeight: true,
                                items: [groupEventGrid]
                            });

                            groupEventGrid.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noRowSelected);
                        }
                    }
                }]
            });

            setTimeout(cmatic.util.removeLoadingMask, 1000);
        }
    };
}();

Ext.onReady(cmatic.registration.competitorList.init, cmatic.registration.competitorList);
