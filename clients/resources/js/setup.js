Ext.namespace('cmatic.setup');

cmatic.setup.stuff = function () {
    var columnModel;
    var eventParameterGetProxy;
    var eventParameterReader;

    return {
        /**
         * All of the event parameter types have the same fields at the moment,
         * so they can all share this column model.
         */
        getEventParameterColumnModel: function () {
            if (!columnModel) {
                columnModel = new Ext.grid.ColumnModel([
                    {
                        header: 'Record Id',
                        sortable: true,
                        dataIndex: 'id',
                        width: 60
                    },
                    {
                        header: 'Short Name',
                        sortable: true,
                        dataIndex: 'shortName',
                        width: 100
                    },
                    {
                        header: 'Long Name',
                        sortable: true,
                        dataIndex: 'longName'
                    }
                ]);
            }
            return columnModel;
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
                }, Ext.data.Record.create([
                    {name: 'id'},
                    {name: 'shortName'},
                    {name: 'longName'}
                ]));
            }
            return eventParameterReader;
        }
    };
}();


/**
 * TODO: Comment this
 * subclass of Ext.grid.GridPanel
 * config
 *  - cmaticType
 */
cmatic.setup.EventParameterPanel = function (config) {
    cmatic.setup.EventParameterPanel.superclass.constructor.call(this);

    var ds = new Ext.data.Store({
        proxy: cmatic.setup.stuff.getEventParameterGetProxy(),
        baseParams: {type: config.cmaticType},
        reader: cmatic.setup.stuff.getEventParameterReader()
    });

    ds.load();

    Ext.apply(this, config, {
        closable: true,
        autoHeight: true,
        colModel: cmatic.setup.stuff.getEventParameterColumnModel(),
        store: ds
    });
}

Ext.extend(cmatic.setup.EventParameterPanel, Ext.grid.GridPanel);


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
                                editor = new cmatic.setup.EventParameterPanel({
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
                                editor = new cmatic.setup.EventParameterPanel({
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
                                editor = new cmatic.setup.EventParameterPanel({
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

            setTimeout(this.removeLoadingMask, 250);
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
        }
    };
}();

Ext.onReady(cmatic.setup.app.init, cmatic.setup.app);
