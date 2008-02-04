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
                sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
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
                        // TODO: Consider moving to the data store manager
                        var iGroupStore = new Ext.data.Store({
                            proxy: new Ext.data.HttpProxy({
                                url: cmatic.url.get,
                                method: 'POST'
                            }),
                            baseParams: {
                                type: 'group'
                            },
                            reader: new Ext.data.JsonReader({
                                    root: 'records',
                                    id: 'id'
                                }, Ext.data.Record.create([
                                    {name: 'id'},
                                    {name: 'name'}
                                ])
                            ),
                            sortInfo: {
                                field: 'name',
                                direction: 'ASC'
                            }
                        });
                        iGroupStore.load();

                        var groupList = new Ext.grid.GridPanel({
                            store: iGroupStore,
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
                            }],
                            sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
                            viewConfig: {forceFit: true},
                            autoHeight: true,
                            autoScroll: true,
                            stripeRows: true,
                            tbar: [{
                                text: cmatic.labels.button.reload,
                                handler: function () { iGroupStore.reload(); }
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
                                            // TODO: this should be a drop down from the event list.
                                            xtype: 'combo',
                                            fieldLabel: 'Group Event',
                                            name: 'eventCode',
                                            store: cmatic.util.getDataStore('event'),
                                            displayField: 'code',
                                            valueField: 'id',
                                            typeAhead: true,
					    hiddenName: 'eventId',
                                            mode: 'local',
                                            triggerAction: 'all',
                                            emptyText: '__pick an event',
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
                                        Ext.Ajax.request({
                                            url: cmatic.url.set,
                                            params: {
                                                op: 'new',
                                                type: 'group',
                                                records: '[' + Ext.util.JSON.encode({name: values.name}) + ']'
                                            },
                                            success: function (response) {
                                                var r = Ext.util.JSON.decode(response.responseText);
                                                if (r.success) {
                                                    Ext.Ajax.request({
                                                        url: cmatic.url.set,
                                                        params: {
                                                            op: 'new',
                                                            type: 'scoring',
                                                            records: '[' + Ext.util.JSON.encode({eventId: values.eventId, groupId: r.newId}) + ']'
                                                        },
                                                        success: function (response) {
                                                            var r = Ext.util.JSON.decode(response.responseText);
                                                            if (r.success) {
                                                                iGroupStore.reload();
                                                                win.close();
                                                            } else {
                                                                cmatic.util.alertSaveFailed();
                                                            }
                                                        },
                                                        failure: cmatic.util.alertSaveFailed
                                                    });
                                                    iGroupStore.reload();
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
                                        title: '__some title',
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
                                                    iGroupStore.reload();
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
                                            cmatic.util.alertSaveFailed();
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
                                    renderer: getFullEventNameRenderer,
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
                                                sortable: true
                                            }, {
                                                id: 'divisionId',
                                                dataIndex: 'divisionId',
                                                header: cmatic.labels.type_event.divisionId,
                                                sortable: true,
                                                renderer: cmatic.util.getParameterRenderer('division')
                                            }, {
                                                id: 'sexId',
                                                dataIndex: 'sexId',
                                                header: cmatic.labels.type_event.sexId,
                                                sortable: true,
                                                renderer: cmatic.util.getParameterRenderer('sex')
                                            }, {
                                                id: 'ageGroupId',
                                                dataIndex: 'ageGroupId',
                                                header: cmatic.labels.type_event.ageGroupId,
                                                sortable: true,
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
                                            title: 'Some title',
                                            constrain: true,
                                            resizable: false,
                                            modal: true,
                                            width: 450,
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
                                    cmatic.registration.competitorIdRenderer(c.get('id')), c.get('lastName'), c.get('firstName')
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
