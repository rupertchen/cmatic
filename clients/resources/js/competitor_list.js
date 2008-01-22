/**
 * Namespace for all registration-related code
 */
Ext.namespace('cmatic.registration');

// TODO: Should this be put in common.js?
cmatic.registration.competitorIdRenderer = function (numberId) {
    return 'CMAT' + (16000 + numberId);
};

cmatic.registration.competitorList = function () {

    /**
     * TODO: Comment
     */
    function getEventCode (eventId) {
        return cmatic.util.getCachedFieldValue('event', 'code', eventId);
    }


    /**
     * TODO: Comment
     */
    function getFullEventNameRenderer (eventId) {
        return String.format('{0} {1} {2} {3}',
            cmatic.util.getParameterRenderer('division')(cmatic.util.getCachedFieldValue('event', 'divisionId', eventId)),
            cmatic.util.getParameterRenderer('sex')(cmatic.util.getCachedFieldValue('event', 'sexId', eventId)),
            cmatic.util.getParameterRenderer('ageGroup')(cmatic.util.getCachedFieldValue('event', 'ageGroupId', eventId)),
            cmatic.util.getParameterRenderer('form')(cmatic.util.getCachedFieldValue('event', 'formId', eventId))
        );
    }


    function primeDataStores () {
        cmatic.util.getDataStore('competitor');
        cmatic.util.getDataStore('event');
        cmatic.util.getDataStore('division');
        cmatic.util.getDataStore('sex');
        cmatic.util.getDataStore('ageGroup');
        cmatic.util.getDataStore('form');
    }

    return {
        init: function () {
            primeDataStores();

            var competitorStore = cmatic.util.getDataStore('competitor');
            var g = new Ext.grid.GridPanel({
                store: competitorStore,
                columns: [{
                    id: 'id',
                    dataIndex: 'id',
                    header: cmatic.labels.type_competitor.id,
                    sortable: true,
                    width: 50,
                    renderer: cmatic.registration.competitorIdRenderer
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
                    width: 50
                }, {
                    id: 'phone2',
                    dataIndex: 'phone2',
                    header: cmatic.labels.type_competitor.phone2,
                    sortable: true,
                    width: 50
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
                sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
                title: cmatic.labels.registration.competitorList,
                autoScroll: true,
                stripeRows: true,
                renderTo: 'competitorList',
                tbar: [{
                    text: cmatic.labels.button.reload,
                    handler: function () {
                        competitorStore.reload();
                        cmatic.util.getDataStore('division').reload();
                        cmatic.util.getDataStore('sex').reload();
                        cmatic.util.getDataStore('ageGroup').reload();
                        cmatic.util.getDataStore('form').reload();
                        cmatic.util.getDataStore('event').reload();
                        // TODO: Add group and group member
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
                                            competitorStore.reload();
                                            win.close();
                                        } else {
                                            Ext.Msg.alert(cmatic.labels.message.error, cmatic.labels.message.changesNotSaved);
                                        }
                                    },
                                    failure: function () { Ext.Msg.alert('failed', 'failed'); },
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
                        // TODO: add this here or create a separate page?
                        Ext.Msg.alert('Todo', 'Manage groups here.');
                    }
                }, {
                    xtype: 'tbseparator'
                }, {
                    text: cmatic.labels.button.editCompetitorDetails,
                    handler: function () {
                        var c = g.getSelectionModel().getSelected();
                        if (c) {
                            var details = new Ext.FormPanel({
                                labelWidth: 100,
                                url: cmatic.url.blank,
                                autoHeight: true,
                                items: [{
                                    xtype: 'fieldset',
                                    title: cmatic.labels.type_competitor.subcategoryPersonal,
                                    autoHeight: true,
                                    defaults: {width: 220},
                                    defaultType: 'textfield',
                                    items: [{
                                        xtype: 'hidden',
                                        name: 'id',
                                        value: c.get('id')
                                    }, {
                                        fieldLabel: cmatic.labels.type_competitor.lastName,
                                        name: 'lastName',
                                        value: c.get('lastName')
                                    }, {
                                        fieldLabel: cmatic.labels.type_competitor.firstName,
                                        name: 'firstName',
                                        value: c.get('firstName')
                                    }]
                                }, {
                                    xtype: 'fieldset',
                                    title: cmatic.labels.type_competitor.subcategoryContact,
                                    autoHeight: true,
                                    defaults: {width: 220},
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
                                    }]
                                }, {
                                    xtype: 'fieldset',
                                    title: cmatic.labels.type_competitor.subcategoryEmergency,
                                    autoHeight: true,
                                    defaults: {width: 220},
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
                                }]
                            });

                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.competitorDetails,
                                    cmatic.registration.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
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
                                            competitorStore.reload();
                                            win.close();
                                        } else {
                                            Ext.Msg.alert(cmatic.labels.message.error, cmatic.labels.message.changesNotSaved);
                                        }
                                    },
                                    failure: function () { Ext.Msg.alert('failed', 'failed')},
                                    params: {
                                        type: 'competitor',
                                        op: 'edit',
                                        records: '[' + Ext.util.JSON.encode(details.getForm().getValues(false)) + ']'
                                    }
                                });
                            });
                            details.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noCompetitorSelected);
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
                                    }, Ext.data.Record.create([
                                        {name: 'id'},
                                        {name: 'eventId'},
                                        {name: 'competitorId'}
                                    ])),
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
                                    header:cmatic.labels.type_scoring.id,
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
                                    renderer: getFullEventNameRenderer
                                }],
                                viewConfig: {forceFit: true },
                                autoHeight: true,
                                autoScroll: true,
                                stripeRows: true,
                                tbar: [{
                                    text: cmatic.labels.button.reload,
                                    handler: function () { iEventStore.reload(); }
                                }]
                            });
                            var individualEvents = new Ext.FormPanel({
                                url: cmatic.url.blank,
                                items: [eventsGrid]
                            });

                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.individualEvents,
                                    cmatic.registration.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
                                constrain: true,
                                resizable: false,
                                modal: true,
                                width: 500,
                                autoHeight: true,
                                items: [individualEvents]
                            });

                            individualEvents.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noCompetitorSelected);
                        }
                    }
                }, {
                    text: cmatic.labels.button.editGroupEvents,
                    handler: function () {
                        var c = g.getSelectionModel().getSelected();
                        if (c) {
                            var groupEvents = new Ext.FormPanel({
                                url: cmatic.url.blank,
                                items: [{
                                    fieldLabel: 'some group event',
                                    name: 'blegh',
                                    value: 'another event id',
                                    checked: true,
                                    xtype: 'checkbox'
                                }]
                            });


                            var win = new Ext.Window({
                                title: String.format(cmatic.labels.registration.groupEvents,
                                    cmatic.registration.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
                                ),
                                constrain: true,
                                resizable: false,
                                modal: true,
                                width: 400,
                                autoHeight: true,
                                items: [groupEvents]
                            });

                            groupEvents.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                            win.show();
                        } else {
                            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noCompetitorSelected);
                        }
                    }
                }]
            });

            setTimeout(cmatic.util.removeLoadingMask, 1000);
        }
    };
}();

Ext.onReady(cmatic.registration.competitorList.init, cmatic.registration.competitorList);
