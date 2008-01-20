/**
 * Namespace for all registration-related code
 */
Ext.namespace('cmatic.registration');

cmatic.registration.competitorList = function () {
    return {
        init: function () {
            var s = Ext.StoreMgr.get('competitors');
            var rc;
            if (!s) {
                rc = Ext.data.Record.create([
                    {name: 'id'},
                    {name: 'firstName'},
                    {name: 'lastName'},
                    {name: 'email'},
                    {name: 'phone1'},
                    {name: 'phone2'}
                ]);

                s = new Ext.data.Store({
                    proxy: new Ext.data.HttpProxy({
                        url: '../cms/api/get.php',
                        method: 'POST'
                    }),
                    baseParams: {
                        type: 'competitor'
                    },
                    reader: new Ext.data.JsonReader({
                        root: 'records',
                        id: 'id'
                    }, rc),
                    sortInfo: {
                        field: 'id',
                        direction: 'ASC'
                    }
                });
                Ext.StoreMgr.add('competitors', s);
                s.load();
            }

            var g = new Ext.grid.GridPanel({
                store: s,
                columns: [{
                    id: 'id',
                    dataIndex: 'id',
                    header: cmatic.labels.type_competitor.id,
                    sortable: true,
                    width: 50,
                    // TODO: factor this renderer out to a common area
                    renderer: function (x) { return 'CMAT' + (16000 + x); }
                }, {
                    id: 'lastName',
                    dataIndex: 'lastName',
                    header: cmatic.labels.type_competitor.lastName,
                    sortable: true,
                    width: 75,
                }, {
                    id: 'firstName',
                    dataIndex: 'firstName',
                    header: cmatic.labels.type_competitor.firstName,
                    sortable: true,
                    width: 75
                }, {
                    id: 'email',
                    dataIndex: 'email',
                    header: cmatic.labels.type_competitor.email,
                    sortable: true,
                }, {
                    id: 'phone1',
                    dataIndex: 'phone1',
                    header: cmatic.labels.type_competitor.phone1,
                    sortable: true,
                    width: 75
                }, {
                    id: 'phone2',
                    dataIndex: 'phone2',
                    header: cmatic.labels.type_competitor.phone2,
                    sortable: true,
                    width: 75
                }],
                viewConfig: { forceFit: true },
                autoHeight: true,
                title: cmatic.labels.registration.competitorList,
                stripeRows: true,
                enableColumnMove: false,
                autoExpandColumn: 3,
                renderTo: 'competitorList',
                tbar: [{
                    text: cmatic.labels.button.reload,
                    handler: function () { s.reload(); }
                }, {
                    text: cmatic.labels.button.add,
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
                            title: 'add a competitor',
                            constrain: true,
                            resizabel: false,
                            width: 300,
                            items: [formPanel]
                        });

                        formPanel.addButton(cmatic.labels.button.save,
                            function () {
                                var form = formPanel.getForm();
                                form.submit({
                                    url: '../cms/api/set.php',
                                    waitMsg: '__',
                                    success: function () {
                                        s.reload();
                                        win.close();
                                    },
                                    params: {
                                        op: 'new',
                                        type: 'competitor',
                                        records: '[' + Ext.util.JSON.encode(form.getValues(false)) + ']'
                                    }
                                });
                            }
                        );
                        formPanel.addButton(cmatic.labels.button.cancel, function () { win.close(); })

                        win.show();
                    }
                }]
            });

            setTimeout(cmatic.removeLoadingMask, 1000);
        }
    };
}();

Ext.onReady(cmatic.registration.competitorList.init, cmatic.registration.competitorList);
