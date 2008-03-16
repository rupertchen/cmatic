/**
 * Namespace for all scoring-related code
 */
Ext.namespace('cmatic.scoring');


/**
 * Retrieve the name of a competitor or group for display in the
 * scoring window
 */
cmatic.scoring.getCompetitorNameRenderer = function (competitorId) {
    var last = cmatic.util.getCachedFieldValue('competitor', 'lastName', competitorId);
    var first = cmatic.util.getCachedFieldValue('competitor', 'firstName', competitorId);
    var cId = cmatic.util.competitorIdRenderer(competitorId);
    return last + ', ' + first + ' <span>(' + cId + ')</span>';
};


cmatic.scoring.getGroupNameRenderer = function (groupId) {
    return groupId;
}

cmatic.scoring.Event = Ext.extend(Ext.grid.GridPanel, {
    closable: true,
    enableColumnMove: false,
    autoScroll: true,
    stripeRows: true,

    initComponent: function () {
        this.title = cmatic.util.getShortEventNameRenderer(this.eventId);
        this.tbar = [{
            text: cmatic.labels.button.reload,
            scope: this,
            handler: this.reloadStore
        }, {
            text: cmatic.labels.button.save,
            scope: this,
            handler: this.saveScores
        }];

        // TODO: FIXME: should not have this hard-coded
        this.isGroup = /^NNN:/.test(this.title);
        // TODO: FIXME: should not have this hard-coded
        this.isNandu = /\(Nandu\)/.test(this.title);

        this.autoExpandColumn = 2;
        this.colModel = new Ext.grid.ColumnModel([{
            header: cmatic.labels.type_scoring.id,
            dataIndex: 'id',
            sortable: true,
            hidden: true
        }, {
            header: cmatic.labels.type_scoring.short_order,
            dataIndex: 'order',
            sortable: true,
            width: 35
        }, {
            header: this.isGroup ? cmatic.labels.type_scoring.groupId : cmatic.labels.type_scoring.competitorId,
            dataIndex: this.isGroup ? 'groupId' : 'competitorId',
            sortable: true,
            renderer: this.isGroup ? cmatic.scoring.getGroupNameRenderer : cmatic.scoring.getCompetitorNameRenderer
        }, {
            header: cmatic.labels.type_scoring.short_score0,
            dataIndex: 'score0',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_score1,
            dataIndex: 'score1',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_score2,
            dataIndex: 'score2',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_score3,
            dataIndex: 'score3',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_score4,
            dataIndex: 'score4',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_score5,
            dataIndex: 'score5',
            sortable: true,
            width: 40,
            hidden: !this.isNandu
        }, {
            header: cmatic.labels.type_scoring.time,
            dataIndex: 'time',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_otherDeduction,
            dataIndex: 'otherDeduction',
            sortable: true,
            width: 50
        }, {
            header: cmatic.labels.type_scoring.short_timeDeduction,
            dataIndex: 'timeDeduction',
            sortable: true,
            width: 60
        }, {
            header: cmatic.labels.type_scoring.short_tieBreaker0,
            dataIndex: 'tieBreaker0',
            sortable: true,
            width: 30,
            hidden: true
        }, {
            header: cmatic.labels.type_scoring.short_tieBreaker1,
            dataIndex: 'tieBreaker1',
            sortable: true,
            width: 30,
            hidden: true
        }, {
            header: cmatic.labels.type_scoring.short_tieBreaker2,
            dataIndex: 'tieBreaker2',
            sortable: true,
            width: 30,
            hidden: true
        }, {
            header: cmatic.labels.type_scoring.short_finalScore,
            dataIndex: 'finalScore',
            sortable: true,
            width: 40
        }, {
            header: cmatic.labels.type_scoring.short_placement,
            dataIndex: 'placement',
            sortable: true,
            width: 40
        }]);
        cmatic.scoring.Event.superclass.initComponent.call(this);
    },


    reloadStore: function () {
        this.askConfirmation(function (x) {
            if ('yes' == x) {
                this.store.reload();
            }
        }, this);
    },


    saveScores: function () {
        // TODO: Write this
        Ext.Msg.alert('TODO', 'save scores here');
    },


    askConfirmation: function (callback, scope) {
        Ext.Msg.confirm('__ Confirmation', '-- are you sure?', callback, scope);
    }
});
Ext.reg('scoringevent', cmatic.scoring.Event);

cmatic.scoring.app = function () {

    // private "constants"
    var EVENT_TAB_ID_PREFIX = 'eventTab_';
    var SCORING_STORE_ID_PREFIX = 'scoringStore_';

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


    function _reloadDataStores () {
        // This should include everything from _primeDataStores
        cmatic.util.getDataStore('division').reload();
        cmatic.util.getDataStore('sex').reload();
        cmatic.util.getDataStore('ageGroup').reload();
        cmatic.util.getDataStore('form').reload();
        cmatic.util.getDataStore('competitor').reload();
        cmatic.util.getDataStore('group').reload();

        // Also reload the events for this ring.
        cmatic.util.getDataStore('event').reload();
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
                id: 'id-prettyprint',
                dataIndex: 'id',
                header: cmatic.labels.type_event._name,
                sortable: true,
                renderer: cmatic.util.getShortEventNameRenderer
            }, {
                id: 'id',
                dataIndex: 'id',
                header: cmatic.labels.type_event.id,
                sortable: true,
                hidden: true
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
            }],
            bbar: [{
                text: cmatic.labels.button.reloadAll,
                handler: _reloadDataStores
            }, {
                // TODO: RPC: remove this.
                // Fake way to open an event for now.
                text: '__Open IMC30 (194)',
                handler: function () { cmatic.scoring.app.openEventTab(194); }
            }, {
                // TODO: RPC: remove this.
                // Fake way to open an event for now.
                text: '__Open NNN71 (686)',
                handler: function () { cmatic.scoring.app.openEventTab(686); }
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


    function _getEventTabId(eventId) {
        return EVENT_TAB_ID_PREFIX + eventId;
    }


    function _getScoringStoreId(eventId) {
        return SCORING_STORE_ID_PREFIX + eventId;
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
        },


        /**
        * WARN: does not load data store
        */
        getEventScoringDataStore: function (eventId) {
            var storeId = _getScoringStoreId(eventId);
            var s = Ext.StoreMgr.get(storeId);
            if (!s) {
                var s = new Ext.data.Store({
                    proxy: new Ext.data.HttpProxy({
                        url: cmatic.url.get,
                        method: 'POST'
                    }),
                    baseParams: {
                        type: 'scoring',
                        filterField: 'eventId',
                        filterValue: eventId
                    },
                    reader: new Ext.data.JsonReader({
                        root: 'records',
                        id: 'id'
                    }, cmatic.ddl._scoringRecord),
                    sortInfo: {
                        field: 'order',
                        direction: 'ASC'
                    }
                });
                Ext.StoreMgr.add(_getScoringStoreId(eventId), s);
            }
            return s;
        },


        openEventTab: function (eventId) {
            // TODO: in progress
            // create or find panel
            var eventTabId = _getEventTabId(eventId);
            var panel = mainPanel.getComponent(eventTabId);
            if (!panel) {
                var store = cmatic.scoring.app.getEventScoringDataStore(eventId);
                store.load();
                panel = new cmatic.scoring.Event({
                    id: eventTabId,
                    eventId: eventId,
                    store: store
                });
            }
            mainPanel.add(panel);
            mainPanel.setActiveTab(panel);
        }
    };
}();

Ext.onReady(cmatic.scoring.app.init, cmatic.scoring.app);
