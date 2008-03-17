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
    return last + ', ' + first + ' <span class="cmatic-scoringevent-cmatid">(' + cId + ')</span>';
};


cmatic.scoring.getGroupNameRenderer = function (groupId) {
    return groupId;
};


cmatic.scoring.EventList = Ext.extend(Ext.grid.GridPanel, {
    title: cmatic.labels.scoring.eventList,
    autoExpandColumn: 3,
    columns: [{
        id: 'id',
        dataIndex: 'id',
        header: cmatic.labels.type_event.id,
        sortable: true,
        hidden: true
    }, {
        id: 'code',
        dataIndex: 'code',
        header: cmatic.labels.type_event.short_code,
        sortable: true,
        width: 25
    }, {
        id: 'numCompetitors',
        dataIndex: 'numCompetitors',
        header: cmatic.labels.type_event.short_numCompetitors,
        sortable: true,
        width: 10
    }, {
        id: 'id-prettyprint',
        dataIndex: 'id',
        header: cmatic.labels.type_event._name,
        sortable: true,
        renderer: cmatic.util.getShortEventNameRenderer
    }],
    viewConfig: {forceFit: true},
    autoScroll: true,
    stripeRows: true,

    initComponent: function () {
        this.tbar = [{
                text: cmatic.labels.button.reload,
                scope: this,
                handler: function () { this.getStore().reload(); }
            }, {
                text: cmatic.labels.button.showFinished,
                enableToggle: true,
                handler: function() { Ext.Msg.alert('Todo')}
            }];
        cmatic.scoring.EventList.superclass.initComponent.call(this);
    },

    initEvents: function () {
        cmatic.scoring.EventList.superclass.initEvents.call(this);
        this.on('celldblclick', this.onCellDblClick, this);
    },

    onCellDblClick: function(g, row, col) {
        cmatic.scoring.app.openEventTab(g.getStore().getAt(row).get('id'));
    }
});


cmatic.scoring.Event = Ext.extend(Ext.grid.EditorGridPanel, {
    closable: true,
    enableColumnMove: false,
    autoScroll: true,
    stripeRows: true,
    sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
    cls: 'cmatic-scoringevent',

    initComponent: function () {
        this.title = cmatic.util.getShortEventNameRenderer(this.eventId);
        this.tbar = [{
            text: cmatic.labels.button.reload,
            scope: this,
            handler: this.reloadStore
        }, {
            text: cmatic.labels.button.cancel,
            scope: this,
            handler: this.cancelChanges
        }, {
            xtype: 'tbseparator'
        }, {
            text: cmatic.labels.button.randomize,
            scope: this,
            handler: this.todo
        }, {
            text: cmatic.labels.button.placement,
            scope: this,
            handler: this.todo
        }, {
            xtype: 'tbseparator'
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
            width: 35,
            editor: new Ext.form.NumberField({
                allowBlank: false,
                allowNegative: false,
                allowDecimal: false
            })
        }, {
            header: this.isGroup ? cmatic.labels.type_scoring.groupId : cmatic.labels.type_scoring.competitorId,
            dataIndex: this.isGroup ? 'groupId' : 'competitorId',
            sortable: true,
            renderer: this.isGroup ? cmatic.scoring.getGroupNameRenderer : cmatic.scoring.getCompetitorNameRenderer
        }, {
            header: cmatic.labels.type_scoring.short_score0,
            dataIndex: 'score0',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_score1,
            dataIndex: 'score1',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_score2,
            dataIndex: 'score2',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_score3,
            dataIndex: 'score3',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_score4,
            dataIndex: 'score4',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_score5,
            dataIndex: 'score5',
            hidden: !this.isNandu,
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.time,
            dataIndex: 'time',
            sortable: true,
            width: 40,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
        }, {
            header: cmatic.labels.type_scoring.short_otherDeduction,
            dataIndex: 'otherDeduction',
            sortable: true,
            width: 50,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
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


    initEvents: function () {
        cmatic.scoring.Event.superclass.initEvents.call(this);

        // Prevent removing of an event with unsaved changes
        cmatic.scoring.app.getMainPanel().on('beforeRemove', function (mainPanel, tab) {
            if (this == tab) {
                if (!this.isSafeToClose()) {
                    Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseWithUnsavedChanges)
                    return false;
                }
            }
        }, this);

        this.on('beforeEdit', function (e) {
            var currentEvent = cmatic.scoring.app.getCurrentEvent();
            if (e.grid != currentEvent) {
                if (currentEvent && currentEvent.title) {
                    Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.onlyEditCurrentEvent);
                } else {
                    Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.mustUnlockToEdit);
                }
                return false;
            }
        }, this);
    },


    isSafeToClose: function () {
        return this.getStore().getModifiedRecords().length == 0;
    },


    // TODO: Delete this
    todo: function () { Ext.Msg.alert('TODO', 'not done.')},


    cancelChanges: function () {
        this.getStore().rejectChanges();
    },


    reloadStore: function () {
        this.askConfirmation(function (x) {
            if ('yes' == x) {
                this.store.reload();
            }
        }, this);
    },


    randomizeCompetitors: function () {
        this.askConfirmation(function (x) {
            if ('yes' == x) {
                // Do something.
            }
        });
    },


    updatePlacements: function () {
        // TODO: update the placement
    },


    convertNumeric: function (str) {
        var number = parseFloat(str);
        if (isNaN(number)) {
            return 0;
        } else {
            return number;
        }
    },


    saveScores: function () {
        var scoringUpdates = [];
        var scoringRecords = this.getStore().getModifiedRecords();
        console.debug('modified records: %o', scoringRecords);
        for (var i = 0; i < scoringRecords.length; i++) {
            var rec = scoringRecords[i];
            scoringUpdates.push({
                id: rec.get('id'),
                order: rec.get('order'),
                score0: this.convertNumeric(rec.get('score0')),
                score1: this.convertNumeric(rec.get('score1')),
                score2: this.convertNumeric(rec.get('score2')),
                score3: this.convertNumeric(rec.get('score3')),
                score4: this.convertNumeric(rec.get('score4')),
                score5: this.convertNumeric(rec.get('score5')),
                time: this.convertNumeric(rec.get('time')),
                timeDeduction: this.convertNumeric(rec.get('timeDeduction')),
                otherDeduction: this.convertNumeric(rec.get('otherDeduction')),
                finalScore: this.convertNumeric(rec.get('finalScore')),
                tieBreaker0: this.convertNumeric(rec.get('tieBreaker0')),
                tieBreaker1: this.convertNumeric(rec.get('tieBreaker1')),
                tieBreaker2: this.convertNumeric(rec.get('tieBreaker2'))
            });
        }

        if (scoringUpdates.length > 0) {
            var thizStore = this.getStore();
            Ext.Ajax.request({
                url: cmatic.url.set,
                success: function (response) {
                    var r = Ext.util.JSON.decode(response.responseText);
                    if (r.success) {
                        Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.changesSaved);
                        thizStore.commitChanges();
                        thizStore.reload();
                    } else {
                        cmatic.util.alertSaveFailed();
                    }
                },
                failure: cmatic.util.alertSaveFailed,
                params: {
                    op: 'edit',
                    type: 'scoring',
                    records: Ext.util.JSON.encode(scoringUpdates)
                }
            });
        } else {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.noScoringUpdates)
        }
    },


    askConfirmation: function (callback, scope) {
        Ext.Msg.confirm(cmatic.labels.message.confirmation, cmatic.labels.message.areYouSure, callback, scope);
    }
});
Ext.reg('scoringevent', cmatic.scoring.Event);

cmatic.scoring.app = function () {

    // private "constants"
    var EVENT_TAB_ID_PREFIX = 'eventTab_';
    var SCORING_STORE_ID_PREFIX = 'scoringStore_';

    // private member variables
    var basePageTitle = '';
    var headerPanel;
    var eventPanel;
    var judgesPanel;
    var mainPanel;
    var startEventButton;
    var finishEventButton;
    // This refers to the event that is being scored. Only one event can be unlocked
    // for scoring at a time.
    var currentEvent;


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


    function _getEventStore(ringNumber) {
        var eventStore = Ext.StoreMgr.get('event');
        if (!eventStore) {
            eventStore = new Ext.data.Store({
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
        }
        return eventStore;
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


    function _buildEventPanel(ringNumber) {
        return new cmatic.scoring.EventList({
            region: 'west',
            collapsible: true,
            split: true,
            minWidth: 100,
            maxWidth: 600,
            width: 300,
            store: _getEventStore(ringNumber)
        });
    }


    function _buildMainPanel() {
        startEventButton = new Ext.Toolbar.Button({
                text: cmatic.labels.button.startEvent,
                handler: _startEvent
            });
        finishEventButton = new Ext.Toolbar.Button({
            text: cmatic.labels.button.finishEvent,
            scope: this,
            handler: _finishEvent,
            disabled: true
        });
        return new Ext.TabPanel({
            region: 'center',
            id: 'mainPanel',
            title: 'Main',
            defaults: {autoScroll: true},
            activeItem: 0,
            enableTabScroll: true,
            items: [{
                xtype: 'panel',
                title: 'FAQ',
                contentEl: 'faq'
            }],
            bbar: [{
                text: cmatic.labels.button.reloadAll,
                handler: _reloadDataStores
            }, startEventButton, finishEventButton]
        });
    }


    function _buildViewport (ringNumber) {
        _getEventStore(ringNumber).load();

        headerPanel = _buildHeaderPanel();
        eventPanel = _buildEventPanel(ringNumber);
        //judgesPanel = _buildJudgesPanel();
        mainPanel = _buildMainPanel();

        return new Ext.Viewport({
            layout: 'border',
            items:[headerPanel, eventPanel, /*judgesPanel, */mainPanel]
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
                var s = cmatic.util.getDataStore('event');
                if (s) {
                    s.reload();
                }
            },
            interval: 59000 // 59 seconds
        }

        Ext.TaskMgr.start(refreshEventList);
    }


    function _startEvent() {
        // TODO: this is currently "dead code" because we protect
        // it via disabling and enabling the buttons.
        if (currentEvent) {
            Ext.Msg.alert(currentEvent.title, 'Another event is still running')
            // another event is currently running
            return false;
        }

        var tmp = cmatic.scoring.app.getMainPanel().getActiveTab();
        // Special case, we know the only other tab is the FAQ, but it's
        // not an event.
        if ('FAQ' == tmp.title) {
            currentEvent = null;
            return false;
        }

        currentEvent = tmp;
        startEventButton.disable();
        finishEventButton.enable();
        // Save the base page title the first time.
        if ('' == basePageTitle) {
            basePageTitle = document.title;
        }
        document.title = basePageTitle + ": " + currentEvent.title;


        Ext.Msg.alert(cmatic.labels.message.unlocking, currentEvent.title);
    }


    function _finishEvent() {
        // TODO: this is currently "dead code" because we protect
        // it via disabling and enabling the buttons.
        if (!currentEvent) {
            Ext.Msg.alert(cmatic.labels.message.warning, 'No event currently running.');
            return false;
        }

        // If there are unsaved changes, don't let them finish
        // force user to save or cancel. This condition is a bit hacky,
        // but we assume we can always close anything that doesn't have
        // an "isSafeToClose" member.
        if (!currentEvent.isSafeToClose || !currentEvent.isSafeToClose()) {
            Ext.Msg.alert(currentEvent.title, cmatic.labels.message.cantCloseWithUnsavedChanges);
            return false;
        }

        var eventName = currentEvent.title;
        currentEvent = null;
        finishEventButton.disable();
        startEventButton.enable();
        document.title = basePageTitle;

        Ext.Msg.alert(cmatic.labels.message.locking, eventName);
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
        },


        getMainPanel: function () {
            return mainPanel;
        },


        getCurrentEvent: function () {
            return currentEvent;
        }
    };
}();

Ext.onReady(cmatic.scoring.app.init, cmatic.scoring.app);
