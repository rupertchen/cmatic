Ext.namespace('Setup');

Setup.app = function () {

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
                        leaf: true
                    }, {
                        id: '1.1',
                        text: 'Sex',
                        leaf: true
                    }, {
                        id: '1.2',
                        text: 'Age Groups',
                        leaf: true
                    }]
                },{
                    id: '2',
                    text: 'Event Management',
                    children: [{
                        id: '2.0',
                        text: 'Available Events',
                        leaf: true
                    }, {
                        id: '2.1',
                        text: 'Event Schedule',
                        leaf: true
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
        }
    };
}();

Ext.onReady(Setup.app.init, Setup.app);
