
/**
 * Namespace for all setup-related code
 */
Ext.namespace('cmatic.setup');


/**
 * cmatic.setup.eventParameter
 * Namespace for all things related to event parameter types
 */
cmatic.setup.eventParameter = function () {
    // TODO: Is there any problem with sharing the proxy?
    var eventParameterGetProxy;
    var eventParameterReader;
    var recordConstructor;

    return {
        /**
         * All of the event parameter types have the same fields at the moment,
         * so they can all have the same ColumnModel config.
         *
         * DON'T SHARE because they can't share the editors!
         */
        getColumnModelConfig: function () {
            return [{
                header: 'Record Id',
                sortable: true,
                dataIndex: 'id',
                width: 100
            }, {
                header: 'Shorthand',
                sortable: true,
                dataIndex: 'shortName',
                width: 100,
                editor: new Ext.form.TextField({
                    allowBlank: false,
                    maxLength: 1,
                    maxLengthText: 'This field can only be 1 character long'
                })
            }, {
                header: 'Description',
                sortable: true,
                dataIndex: 'longName',
                editor: new Ext.form.TextField({
                    allowBlank: false
                })
            }]
        },


        /**
         * Singleton Proxy object for the API get
         */
        getEventParameterGetProxy: function () {
            if (!eventParameterGetProxy) {
                eventParameterGetProxy = new Ext.data.HttpProxy({
                    url: '../cms/api/get.php',
                    method: 'POST'
                });
            }
            return eventParameterGetProxy;
        },


        /**
         * Singleton Reader object
         */
        getEventParameterReader: function () {
            if (!eventParameterReader) {
                eventParameterReader = new Ext.data.JsonReader({
                    root: 'records',
                    id: 'id'
                }, cmatic.setup.eventParameter.getRecordConstructor());
            }
            return eventParameterReader;
        },


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
 * subclass of Ext.grid.GridPanel
 * config
 *  - cmaticType
 *  - id
 *  - title
 */
cmatic.setup.eventParameter.EventParameterPanel = function (config) {

    var ds = new Ext.data.Store({
        proxy: cmatic.setup.eventParameter.getEventParameterGetProxy(),
        baseParams: {type: config.cmaticType},
        reader: cmatic.setup.eventParameter.getEventParameterReader(),
        // Sort by ID by default
        sortInfo: {
            field: 'id',
            direction: 'ASC'
        }
    });

    ds.load();
    var _grid = this;
    Ext.apply(this, config, {
        closable: true,
        layout: 'anchor',
        enableColumnMove: false,
        autoExpandColumn: 2,
        autoScroll: true,
        colModel: new Ext.grid.ColumnModel(cmatic.setup.eventParameter.getColumnModelConfig()),
        store: ds,
        tbar: [{
            text: 'Reload',
            handler: function () { ds.reload();}
        }, {
            text: 'Add',
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
            text: 'Save',
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
                                Ext.Msg.alert('Error', 'Problem during update. Changes were not saved.');
                            }
                        },
                        failure: function () {
                            Ext.Msg.alert('Error', 'Problem during update. Changes were not saved.');
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
                                Ext.Msg.alert('Error', 'Problem during insert. Changes were not saved.');
                            }
                        },
                        failure: function () {
                            Ext.Msg.alert('Error', 'Problem during insert. Changes were not saved.');
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
            text: 'Cancel',
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
                Ext.Msg.alert('Warning', 'There are unsaved changes. Cannot close. To continue, either save or cancel the changes.');
                return false;
            }
        }
    });
}

Ext.extend(cmatic.setup.eventParameter.EventParameterPanel, Ext.grid.EditorGridPanel);


cmatic.setup.app = function () {

    // ****************************************
    // private vars
    var headerPanel;
    var navTreePanel;
    var mainPanel;


    // ****************************************
    // private functions

    /**
     * Build the standard panels that will be used by the viewport
     */
    var buildPanels = function () {
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
                    text: 'Event Parameters',
                    children: [{
                        id: '1.0',
                        text: 'Division',
                        leaf: true,
                        doAction: function () {
                            // TODO: Can the event parameter stuff be cleaned up anymore?
                            var editor = cmatic.setup.app.getTab('divisionEditor');
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: 'divisionEditor',
                                    title: 'Divisions',
                                    cmaticType: 'division'
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '1.1',
                        text: 'Sex',
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab('sexEditor');
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: 'sexEditor',
                                    title: 'Sexes',
                                    cmaticType: 'sex'
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }, {
                        id: '1.2',
                        text: 'Age Groups',
                        leaf: true,
                        doAction: function () {
                            var editor = cmatic.setup.app.getTab('ageGroupEditor');
                            if (!editor) {
                                editor = new cmatic.setup.eventParameter.EventParameterPanel({
                                    id: 'ageGroupEditor',
                                    title: 'Age Groups',
                                    cmaticType: 'ageGroup'
                                });
                            }
                            cmatic.setup.app.addTab(editor);
                        }
                    }]
                },{
                    id: '2',
                    text: 'Event Management',
                    children: [{
                        id: '2.0',
                        text: 'Available Events',
                        leaf: true,
                        doAction: function () {
                            Ext.Msg.alert('To Do', 'This should open an event editor');
                        }
                    }, {
                        id: '2.1',
                        text: 'Event Schedule',
                        leaf: true,
                        doAction: function () {
                            Ext.Msg.alert('To Do', 'This should open the event schedule');
                        }
                    }]
                }]
            })
        });

        mainPanel = new Ext.TabPanel({
            region: 'center',
            id: 'mainPanel',
            title: 'Foo',
            defaults: {autoScroll: true},
            activeItem: 0,
            items: [{
                xtype: 'panel',
                title: 'FAQ',
                contentEl: 'faq'
            }]
        });

    };


    // ****************************************
    // public space
    return {
        init: function () {
            Ext.QuickTips.init();
            this.buildViewport();


            navTreePanel.on('click', function (node, e) {
                if (node.isLeaf()) {
                    e.stopEvent();
                    // doAction is a custom attribute
                    node.attributes.doAction();
                }
            });

            setTimeout(this.removeLoadingMask, 1000);
        },


        /**
         * Build the Setup viewport
         */
        buildViewport: function () {
            buildPanels();
            var viewport = new Ext.Viewport({
                layout: 'border',
                items: [headerPanel, navTreePanel, mainPanel]
            });

            return viewport;
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
        }
    };
}();

Ext.onReady(cmatic.setup.app.init, cmatic.setup.app);
