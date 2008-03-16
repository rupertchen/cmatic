/**
 * Namespace for all scoring-related code
 */
Ext.namespace('cmatic.scoring');

cmatic.scoring.app = function () {

    // private member variables
    var headerPanel;
    var eventPanel;
    var judgesPanel;
    var mainPanel;


    function _primeDataStores () {
        // order is important here. we
        // want all the "basic" data loaded first so
        // it will be available when the "higher"
        // objects need to be rendered
        cmatic.util.getDataStore('division');
        cmatic.util.getDataStore('sex');
        cmatic.util.getDataStore('ageGroup');
        cmatic.util.getDataStore('form');
        cmatic.util.getDataStore('competitor');
        cmatic.util.getDataStore('group');

    }

    function _primeEventStore(ringNumber) {
        var eventStore = new Ext.data.Store({
            proxy: new Ext.data.HttpProxy({
                url: cmatic.url.get,
                method: 'POST'
            }),
            baseParams: {
                type: 'event',
                filterField: 'ringId',
                filterValue: ringNumber
            },
            reader: new Ext.data.JsonReader({
                root: 'records',
                id: 'id'
            }, cmatic.ddl._eventRecord),
            sortInfo: {
                field: 'order',
                direction: 'ASC'
            }
        });
        Ext.StoreMgr.add('event', eventStore);
        eventStore.load();
    }


    function _buildHeaderPanel() {
        return new Ext.Panel({
            contentEl: 'header',
            region: 'north'
        });
    }


    function _buildJudgesPanel() {
        return new Ext.form.FormPanel({
            region: 'east',
            collapsible: true,
            title: cmatic.labels.scoring.judgesPanel,
            width: 200,
            autoHeight: true,
            defaultType: 'textfield',
            labelWidth: 50,
            waitMsgTarget: true,
            items: [{
                fieldLabel: cmatic.labels.type_scoring.judge0,
                name: 'judge0'
            }, {
                fieldLabel: cmatic.labels.type_scoring.judge1,
                name: 'judge1'
            }, {
                fieldLabel: cmatic.labels.type_scoring.judge2,
                name: 'judge2'
            }, {
                fieldLabel: cmatic.labels.type_scoring.judge3,
                name: 'judge3'
            }, {
                fieldLabel: cmatic.labels.type_scoring.judge4,
                name: 'judge4'
            }, {
                fieldLabel: cmatic.labels.type_scoring.judge5,
                name: 'judge5'
            }]
        });
    }


    function _buildEventPanel() {
        return new Ext.grid.GridPanel({
            region: 'west',
            collapsible: true,
            split: true,
            minWidth: 100,
            maxWidth: 600,
            title: cmatic.labels.scoring.eventList,
            width: 300,
            autoExpandColumn: 1,
            store: cmatic.util.getDataStore('event'),
            columns: [{
                id: 'code',
                dataIndex: 'code',
                header: cmatic.labels.type_event.code,
                sortable: true,
                width: 30
            }, {
                id: 'id',
                dataIndex: 'id',
                header: cmatic.labels.type_event._name,
                sortable: true,
                renderer: cmatic.util.getShortEventNameRenderer
            }],
            viewConfig: {forceFit: true},
            autoScroll: true,
            stripeRows: true,
            tbar: [{
                text: cmatic.labels.button.reload,
                handler: function () {cmatic.util.getDataStore('event').reload(); }
            }, {
                text: cmatic.labels.button.showFinished,
                enableToggle: true,
                handler: function() {}
            }]
        });
    }


    function _buildMainPanel() {
        return new Ext.TabPanel({
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
    }


    function _buildViewport (ringNumber) {
        _primeEventStore(ringNumber);
        headerPanel = _buildHeaderPanel();
        eventPanel = _buildEventPanel();
        judgesPanel = _buildJudgesPanel();
        mainPanel = _buildMainPanel();

        return new Ext.Viewport({
            layout: 'border',
            items:[headerPanel, eventPanel, judgesPanel, mainPanel]
        });
    }


    function _ringNumberPrompt () {
        Ext.Msg.show({
            title: cmatic.labels.message.input,
            msg: cmatic.labels.message.ringNumberPrompt,
            buttons: Ext.Msg.OK,
            prompt: true,
            fn: function (btn, ringNumber) {
                ringNumber = parseInt(ringNumber);
                if (ringNumber && ringNumber > 0 && ringNumber < 9) {
                    document.title = document.title + ": Ring " + ringNumber;
                    _buildViewport(ringNumber);
                } else {
                    alert(cmatic.labels.message.ringNumberTryAgain);
                    setTimeout(function() {_ringNumberPrompt()}, 100);
                }
            }
        });
    }


    function _startTasks () {
        // Start a simple clock task that updates a div once per second
        var refreshEventList = {
            run: function(){
                cmatic.util.getDataStore('event').reload();
            },
            interval: 59000 // 59 seconds
        }

        Ext.TaskMgr.start(refreshEventList);
    }

    return {
        init: function () {
            _primeDataStores();

            Ext.QuickTips.init();
            _ringNumberPrompt();

            // Give some time for the browser to take care of everything
            // before unhiding the UI to allow user interaction.
            setTimeout(cmatic.util.removeLoadingMask, 2500);

            // Don't start performing reoccuring tasks until aft er the
            // user has been using the client (we guess) for some time.
            setTimeout(_startTasks, 60000);
        }
    };
}();

Ext.onReady(cmatic.scoring.app.init, cmatic.scoring.app);
