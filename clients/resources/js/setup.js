Ext.namespace('Setup');

Setup.app = function () {

    // private vars

    // private functions

    // public space
    return {
        init: function () {

            var header =  new Ext.Panel({
                contentEl: 'header',
                region: 'north'
            });

            var tree = new Ext.tree.TreePanel({
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

            var main = new Ext.TabPanel({
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

            // Everything
            var viewport = new Ext.Viewport({
                layout: 'border',
                items: [header, tree, main]
            });

            // Removing "Loading"-related stuff when done
            setTimeout(function () {
                Ext.get('loading').remove();
                Ext.get('loading-mask').fadeOut({duration: .25, remove: true});
            }, 250);

        }
    };
}();
