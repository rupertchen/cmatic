
/**
 * Namespace for all setup-related code
 */
Ext.namespace('cmatic.setup');


/**
 * cmatic.setup.eventParameter
 * Namespace for all things related to event parameter types
 */
cmatic.setup.eventParameter = function () {
    var recordConstructor;

    return {
        /**
         * Expose record constructor so we can create them dynamically.
         */
        getRecordConstructor: function () {
            if (!recordConstructor) {
                recordConstructor = Ext.data.Record.create([
                    {name: 'id'},
                    {name: 'shortName'},
                    {name: 'longName'}
                ]);
            }
            return recordConstructor;
        }
    };
}();


/**
 * TODO: Comment this
 * subclass of Ext.grid.EditorGridPanel
 * config
 *  - id
 *  - title
 *  - cmaticType
 *  - maxShorthandLength
 */
cmatic.setup.eventParameter.EventParameterPanel = function (config) {
    var ds = cmatic.setup.app.getDataStore(config.cmaticType);

    // Save a reference to the grid (this)
    var _grid = this;
    Ext.apply(this, config, {
        closable: true,
        layout: 'anchor',
        enableColumnMove: false,
        autoExpandColumn: 2,
        autoScroll: true,
        colModel: new Ext.grid.ColumnModel(this.getColumnModelConfig(config)),
        store: ds,
        tbar: [{
            text: cmatic.labels.button.reload,
            handler: function () { ds.reload();}
        }, {
            text: cmatic.labels.button.add,
            handler: function () {
                var r = new (cmatic.setup.eventParameter.getRecordConstructor())({
                    id: '', // new records have no id
                    shortName: '-',
                    longName: '-'
                });
                _grid.stopEditing();
                ds.insert(0, r);
                _grid.startEditing(0, 1);

            }
        }, {
            text: cmatic.labels.button.save,
            handler: function () {
                var modifiedRecords = ds.getModifiedRecords();
                var addedRecs = new Array();
                var addedBody = new Array();
                var updatedRecs = new Array();
                var updateBody = new Array();
                for (var i = 0; i < modifiedRecords.length; i++) {
                    var rec = modifiedRecords[i];
                    if ('' == rec.get('id')) {
                        addedRecs.push(rec);
                        var r = {};
                        // We grab everything for the initial insert
                        Ext.apply(r, rec.data);
                        // But now we also have to delete the id
                        delete r.id;
                        addedBody.push(r);
                    } else {
                        updatedRecs.push(rec);
                        var r = {};
                        Ext.apply(r, rec.getChanges());
                        r['id'] = rec.get('id');
                        updateBody.push(r);
                    }
                }

                // Handle updates
                if (updatedRecs.length > 0) {
                    Ext.Ajax.request({
                        url: '../cms/api/set.php',
                        success: function (response) {
                            if ('' == response.responseText.trim()) {
                                Ext.each(updatedRecs, function (r) { r.commit(); });
                            } else {
                                Ext.Msg.alert(cmatic.labels.message.error + ':101', cmatic.labels.message.changesNotSaved);
                            }
                        },
                        failure: function () {
                            Ext.Msg.alert(cmatic.labels.message.error + ':102', cmatic.labels.message.changesNotSaved);
                        },
                        params: {
                            op: 'edit',
                            type: config.cmaticType,
                            records: Ext.util.JSON.encode(updateBody)
                        }
                    });
                }

                // Handle inserts
                if (addedRecs.length > 0) {
                    Ext.Ajax.request({
                        url: '../cms/api/set.php',
                        success: function (response) {
                            if ('' == response.responseText.trim()) {
                                Ext.each(addedRecs, function (r) { r.commit(); });
                                ds.reload();
                            } else {
                                Ext.Msg.alert(cmatic.labels.message.error + ':103', cmatic.labels.message.changesNotSaved);
                            }
                        },
                        failure: function () {
                            Ext.Msg.alert(cmatic.labels.message.error + ':104', cmatic.labels.message.changesNotSaved);
                        },
                        params: {
                            op: 'new',
                            type: config.cmaticType,
                            records: Ext.util.JSON.encode(addedBody)
                        }
                    });
                }
            }
        }, {
            text: cmatic.labels.button.cancel,
            handler: function () {
                ds.rejectChanges();
            }
        }]
    });

    cmatic.setup.eventParameter.EventParameterPanel.superclass.constructor.call(this);

    cmatic.setup.app.getMainPanel().on('beforeRemove', function (mainPanel, tab) {
        if (_grid == tab) {
            var unsaved = ds.getModifiedRecords();
            if (unsaved.length > 0) {
                Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseWithUnsavedChanges);
                return false;
            }
        }
    });
};
Ext.extend(cmatic.setup.eventParameter.EventParameterPanel, Ext.grid.EditorGridPanel);


/**
 * Event parameters all have very similar column configurations.
 * The only difference at the moment is the maximum length of the short name field.
 */
cmatic.setup.eventParameter.EventParameterPanel.prototype.getColumnModelConfig = function (panelConfig) {
    return [{
        header: cmatic.labels.setup.internalId,
        sortable: true,
        dataIndex: 'id',
        width: 100
    }, {
        header: cmatic.labels['type_'+panelConfig.cmaticType].shortName,
        sortable: true,
        dataIndex: 'shortName',
        width: 100,
        editor: new Ext.form.TextField({
            allowBlank: false,
            maxLength: panelConfig.maxShorthandLength
        })
    }, {
        header: cmatic.labels['type_'+panelConfig.cmaticType].longName,
        sortable: true,
        dataIndex: 'longName',
        editor: new Ext.form.TextField({
            allowBlank: false
        })
    }]
};


/**
 * Event Panel
 */
cmatic.setup.event = function () {
    return {
        // TODO: Fill this in, or is nothing needed?
    };
}();


/**
 * TODO: Comment this
 * subclass of Ext.grid.GridPanel
 */
cmatic.setup.event.EventPanel = function (config) {

    var eventDs = new Ext.data.Store({
        proxy: new Ext.data.HttpProxy({
            url: '../cms/api/get.php',
            method: 'POST'
        }),
        baseParams: { type: 'event' },
        reader: new Ext.data.JsonReader({
            root: 'records',
            id: 'id'
        }, Ext.data.Record.create([
            {name: 'id'},
            {name: 'code'},
            {name: 'divisionId'},
            {name: 'sexId'},
            {name: 'ageGroupId'},
            {name: 'formId'}
        ])),
        sortInfo: {
            field: 'code',
            directions: 'ASC'
        }
    });
    Ext.StoreMgr.add('event', eventDs);
    eventDs.load();


    /**
     * Fill a field set for the Mass Add Event form.
     * @param {String} type API name of the type
     * @param {Ext.form.FieldSet} fieldSet The FieldSet to add to
     *
     * TODO: Refactor this as a method of a subclass of FieldSet, then
     * we can get rid of some things.
     */
    function fillMassAddFormFieldSet (type, fieldSet) {
        var records = cmatic.setup.app.getDataStore(type).getRange();
        for (var i = 0; i < records.length; i++) {
            fieldSet.add(new Ext.form.Checkbox({
                boxLabel: records[i].get('longName'),
                inputValue: records[i].get('id')
            }));
        }
    }

    Ext.apply(this, config, {
        closable: true,
        layout: 'anchor',
        enableColumnMove: false,
        autoScroll: true,
        autoExpandColumn: 5,
        store: eventDs,
        colModel: new Ext.grid.ColumnModel([{
            header: cmatic.labels.setup.internalId,
            sortable: true,
            dataIndex: 'id',
            width: 100
        }, {
            header: cmatic.labels.type_event.code,
            sortable: true,
            dataIndex: 'code',
            width: 100
        }, {
            header: cmatic.labels.type_event.divisionId,
            sortable: true,
            dataIndex: 'divisionId',
            renderer: this.getParameterRenderer('division')
        }, {
            header: cmatic.labels.type_event.sexId,
            sortable: true,
            dataIndex: 'sexId',
            renderer: this.getParameterRenderer('sex')
        }, {
            header: cmatic.labels.type_event.ageGroupId,
            sortable: true,
            dataIndex: 'ageGroupId',
            renderer: this.getParameterRenderer('ageGroup')
        }, {
            header: cmatic.labels.type_event.formId,
            sortable: true,
            dataIndex: 'formId',
            renderer: this.getParameterRenderer('form')
        }]),
        tbar: [{
            text: cmatic.labels.button.reload,
            handler: function () { eventDs.reload(); }
        }, {
            text: cmatic.labels.button.add,
            handler: function () {
                // Build the window and form

                var divisionSet = new Ext.form.FieldSet({
                    title: cmatic.labels.navTree.divisions,
                    defaults: { name: 'divisions[]', hideLabel: true }
                });
                fillMassAddFormFieldSet('division', divisionSet);

                var sexSet = new Ext.form.FieldSet({
                    title: cmatic.labels.navTree.sexes,
                    defaults: { name: 'sexes[]', hideLabel: true }
                });
                fillMassAddFormFieldSet('sex', sexSet);

                var ageGroupSet = new Ext.form.FieldSet({
                    title: cmatic.labels.navTree.ageGroups,
                    defaults: { name: 'ageGroups[]', hideLabel: true }
                });
                fillMassAddFormFieldSet('ageGroup', ageGroupSet);

                var formSet = new Ext.form.FieldSet({
                    title: cmatic.labels.navTree.forms,
                    defaults: { name: 'forms[]', hideLabel: true }
                });
                fillMassAddFormFieldSet('form', formSet);

                var formPanel = new Ext.form.FormPanel({
                    autoHeight: true,
                    defaultType: 'textfield',
                    waitMsgTarget: true,
                    items: [
                        new Ext.Panel({
                            layout: 'column',
                            defaults: { columnWidth: .25, autoScroll: true, height: 200 },
                            items: [divisionSet, sexSet, ageGroupSet, formSet]
                        })
                    ]
                });

                var win = new Ext.Window({
                    title: cmatic.labels.eventManagement.massAddTitle,
                    constrain: true,
                    resizable: false,
                    width: 650,
                    items: [formPanel]
                });

                // Defining these here rather than in the config because it's
                // convenient to reference the form and window components
                formPanel.addButton(cmatic.labels.button.save,
                    function () {
                        formPanel.getForm().submit({
                            url: '../cms/api/massAddEvents.php',
                            waitMsg: cmatic.labels.eventManagement.addingEvents,
                            success: function () { win.close(); },
                            failure: function (form, action) { Ext.Msg.alert(cmatic.labels.message.error + ':105', cmatic.labels.message.changesNotSaved); }
                    })});
                formPanel.addButton(cmatic.labels.button.cancel, function () { win.close(); });

                win.show();
            }
        }]
    });

    cmatic.setup.event.EventPanel.superclass.constructor.call(this);
};
Ext.extend(cmatic.setup.event.EventPanel, Ext.grid.EditorGridPanel);


/**
 * Build a renderer for event parameters. The renderer will use the
 * longName associated to the id of the type it is given. If a data
 * store or matching id is not found, the raw data is returned.
 *
 * @param {String} type The name of the cmatic type
 */
cmatic.setup.event.EventPanel.prototype.getParameterRenderer = function (type) {
    return function (data) {
        var s = cmatic.setup.app.getDataStore(type);
        if (s) {
            var r = s.getById(data);
            if (r) {
                return r.get('longName');
            }
        }
        return data;
    };
};


/**
 * Setup Client
 */
cmatic.setup.app = function () {

    // ****************************************
    // private vars
    // "constants"
    var DIVISION_TAB_ID = 'divisionTab';
    var SEX_TAB_ID = 'sexTab';
    var AGE_GROUP_TAB_ID  ='ageGroupTab';
    var FORM_TAB_ID = 'formTab';
    var EVENT_TAB_ID = 'eventTab';
    var SCHEDULE_TAB_ID = 'scheduleTab';

    // member variables
    var headerPanel;
    var navTreePanel;
    var mainPanel;


    // ****************************************
    // private functions

    /**
     * Build the standard panels that will be used by the viewport
     */
    function buildPanels () {
        headerPanel =  new Ext.Panel({
            contentEl: 'header',
            region: 'north'
        });

        navTreePanel = new Ext.tree.TreePanel({
            region: 'west',
            id: 'treePanel',
            loader: new Ext.tree.TreeLoader({
                preloadChildren: true,
                clearOnLoad: false
            }),
            width: 150,
            rootVisible: false,
            root: new Ext.tree.AsyncTreeNode({
                text: 'root',
                children: [{
                    id: '1',
                    text: cmatic.labels.navTree.eventParameters,
                    children: [{
                        id: '1.0',
                        text: cmatic.labels.navTree.divisions,
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab(DIVISION_TAB_ID);
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: DIVISION_TAB_ID,
                                    title: cmatic.labels.navTree.divisions,
                                    cmaticType: 'division',
                                    maxShorthandLength: 1
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '1.1',
                        text: cmatic.labels.navTree.sexes,
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab(SEX_TAB_ID);
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: SEX_TAB_ID,
                                    title: cmatic.labels.navTree.sexes,
                                    cmaticType: 'sex',
                                    maxShorthandLength: 1
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '1.2',
                        text: cmatic.labels.navTree.ageGroups,
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab(AGE_GROUP_TAB_ID);
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: AGE_GROUP_TAB_ID,
                                    title: cmatic.labels.navTree.ageGroups,
                                    cmaticType: 'ageGroup',
                                    maxShorthandLength: 1
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '1.3',
                        text: cmatic.labels.navTree.forms,
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab(FORM_TAB_ID);
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: FORM_TAB_ID,
                                    title: cmatic.labels.navTree.forms,
                                    cmaticType: 'form',
                                    maxShorthandLength: 3
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }]
                },{
                    id: '2',
                    text: cmatic.labels.navTree.eventManagement,
                    children: [{
                        id: '2.0',
                        text: cmatic.labels.navTree.events,
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab(EVENT_TAB_ID);
                            if (!editor) {
                                editor = new cmatic.setup.event.EventPanel({
                                    id: EVENT_TAB_ID,
                                    title: cmatic.labels.navTree.events
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '2.1',
                        text: cmatic.labels.navTree.schedule,
                        leaf: true,
                        doAction: function () {
                            Ext.Msg.alert('To Do', 'This should open the event schedule');
                            var editor = cmatic.setup.app.getTab(SCHEDULE_TAB_ID);
                            if (editor) cmatic.setup.app.addTab(editor);
                        }
                    }]
                }]
            })
        });

        // Wire up the navigation nodes
        navTreePanel.on('click', function (node, e) {
            if (node.isLeaf()) {
                e.stopEvent();
                // doAction is a custom attribute that was passed into
                // every leaf node as it was created
                node.attributes.doAction();
            }
        });

        mainPanel = new Ext.TabPanel({
            region: 'center',
            id: 'mainPanel',
            title: 'Main',
            defaults: {autoScroll: true},
            activeItem: 0,
            items: [{
                xtype: 'panel',
                title: 'FAQ',
                contentEl: 'faq'
            }]
        });
    };


    /**
     * Prime the event parameter data stores.
     * This should be done early as they're needed (for nice labels)
     * on the Event Management pages. Don't bother priming the event
     * store as it may be big.
     */
    function primeDataStores () {
        cmatic.setup.app.getDataStore('division');
        cmatic.setup.app.getDataStore('sex');
        cmatic.setup.app.getDataStore('ageGroup');
        cmatic.setup.app.getDataStore('form');
    }



    /**
     * Build the Setup viewport
     */
    function buildViewport () {
        buildPanels();

        return new Ext.Viewport({
            layout: 'border',
            items: [headerPanel, navTreePanel, mainPanel]
        });
    }


    // ****************************************
    // public space
    return {
        init: function () {
            // Prime the data stores early
            primeDataStores();

            Ext.QuickTips.init();
            buildViewport();

            setTimeout(this.removeLoadingMask, 1000);
        },


        /**
         * Remove the loading mask
         * TODO: Move this to some common area
         */
        removeLoadingMask: function () {
            Ext.get('loading').remove();
            Ext.get('loading-mask').fadeOut({duration: .25, remove: true});
        },


        /**
         * Get
         */
        getTab: function (idOrIndex) {
            return mainPanel.getComponent(idOrIndex);
        },


        /**
         * Create a new tab
         */
        addTab: function (panel) {
            mainPanel.add(panel);
            mainPanel.setActiveTab(panel);
        },


        /**
         * Getter for the main panel
         */
        getMainPanel: function () {
            return mainPanel;
        },


        /**
         * Getter for data stores
         * @param {String} cmaticType The api name of a type
         *
         * TODO: Need to figure out a good way to switch on all the types
         * But it shouldn't be so bad, because there's really only events
         * and everything else.
         */
        getDataStore: function (cmaticType) {
            var s = Ext.StoreMgr.get(cmaticType);
            if (!s) {
                // Pick a record constructor
                // We're taking a shortcut because we happen to know
                // that only the "event" type is different
                var rc;
                var sortByField;
                if ('event' == cmaticType) {
                    rc = Ext.data.Record.create([
                        {name: 'id'},
                        {name: 'code'},
                        {name: 'divisionId'},
                        {name: 'sexId'},
                        {name: 'ageGroupId'},
                        {name: 'formId'}
                    ]);
                    sortByField = 'code';
                } else {
                    rc = cmatic.setup.eventParameter.getRecordConstructor();
                    sortbyField = 'id';
                }


                // Make the new store
                s = new Ext.data.Store({
                    proxy: new Ext.data.HttpProxy({
                        url: '../cms/api/get.php',
                        method: 'POST'
                    }),
                    baseParams: { type: cmaticType },
                    reader: new Ext.data.JsonReader({
                        root: 'records',
                        id: 'id'
                    }, rc),
                    sortInfo: {
                        field: sortbyField,
                        direction: 'ASC'
                    }
                });
                Ext.StoreMgr.add(cmaticType, s);
                s.load();
            }
            return s;
        }
    };
}();

Ext.onReady(cmatic.setup.app.init, cmatic.setup.app);
