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


/**
 * TODO: This should be saved in the database instead as configuration
 * on an event.
 * TODO: This is currently unused. DON'T USE IT.
 * @return array 2-item
 */
cmatic.scoring.getTimeWindow = function (eventId) {
    var divisionId = cmatic.util.getCachedFieldValue('event', 'divisionId', eventId);
    var ageGroupId = cmatic.util.getCachedFieldValue('event', 'ageGroupId', eventId);
    var formId = cmatic.util.getCachedFieldValue('event', 'formId', eventId);

    var division = cmatic.util.getCachedFieldValue('division', 'shortName', divisionId);
    var ageGroup = cmatic.util.getCachedFieldValue('ageGroup', 'shortName', ageGroupId);
    var form = cmatic.util.getCachedFieldValue('form', 'shortName', formId);

    if (10 <= form && form <= 29) {
        // Traditional
        if ('A' == division && ('T' == ageGroup || 'A' == ageGroup || 'S' == ageGroup)) {
            return [45, 120];
        } else {
            return [30, 120];
        }
    } else if (30 <= form && form <= 49) {
        // Contemporary
        if ('B' == division) {
            return [30, null];
        } else if ('I' == division) {
            return [60, null];
        } else if ('A' == division) {
            if (32 == form || 46 == form) {
                return [60, null];
            } else {
                return [80, null];
            }
        }
    } else if (50 <= form && form <= 69) {
        // Internal
        if (51 == form || 52 == form) {
            return [240, 300];
        } else if (60 == form) {
            return [180, 240];
        } else if (50 == form) {
            return [300, 360];
        } else if (56 == form) {
            return [60, 120];
        } else if (58 == form || 54 == form || 53 == form || 57 == form || 59 == form) {
            return [180, 210];
        } else if (61 == form) {
            return [120, 300];
        } else if (62 == form || 63 == form) {
            return [120, 180];
        } else if (55 == form) {
            return [60, 120];
        }
    } else {
        // Group
        if (71 == form) {
            return [45, 120];
        } else {
            return [45, 360];
        }
    }

    // If we got here, we we missed it.
    return [null, null];
}

cmatic.scoring.EventList = Ext.extend(Ext.grid.GridPanel, {
    title: cmatic.labels.scoring.eventList,
    autoExpandColumn: 3,
    disableSelection: true,
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
                text: cmatic.labels.button.hideFinished,
                enableToggle: true,
                scope: this,
                handler: function() {
                    var s = this.getStore();
                    if (s.isFiltered()) {
                        s.clearFilter();
                    } else {
                        s.filter('isFinished', 'false');
                    }
                }
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
    clicksToEdit: 1,
    autoScroll: true,
    stripeRows: true,
    sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
    cls: 'cmatic-scoringevent',
    lockedFromEdit: true,

    initComponent: function () {
        var isFinished = this.isFinished();

        this.title = cmatic.util.getShortEventNameRenderer(this.eventId);
        if (isFinished) {
            this.title += ' [FINISHED]';
        }

        this.buttonUnlock = new Ext.Toolbar.Button({
            text: cmatic.labels.button.unlockEvent,
            scope: this,
            handler: this.unlock,
            disabled: !isFinished
        });
        this.buttonLock = new Ext.Toolbar.Button({
            text: cmatic.labels.button.lockEvent,
            scope: this,
            handler: this.lock,
            disabled: !isFinished
        });
        this.buttonStart = new Ext.Toolbar.Button({
            text: cmatic.labels.button.startEvent,
            scope: this,
            handler: this.start,
            disabled: isFinished
        });
        this.buttonFinish = new Ext.Toolbar.Button({
            text: cmatic.labels.button.finishEvent,
            scope: this,
            handler: this.finish,
            disabled: isFinished
        });

        this.tbar = [{
            text: cmatic.labels.button.save,
            scope: this,
            handler: this.saveScores
        }, {
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
            handler: this.randomizeCompetitors
        }, {
            text: cmatic.labels.button.placement,
            scope: this,
            handler: this.computePlacement
        }, {
            xtype: 'tbseparator'
        }, this.buttonUnlock, this.buttonLock, {
            xtype: 'tbseparator'
        }, this.buttonStart, this.buttonFinish];

        // TODO: FIXME: should not have this hard-coded
        this.isGroup = /^NNN:/.test(this.title);
        // TODO: FIXME: should not have this hard-coded
        this.isNandu = /Nandu/.test(this.title);

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
            editor: new Ext.form.TextField()
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
            width: 60,
            editor: new Ext.form.NumberField({
                allowNegative: false
            })
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
                if (this.getScoringUpdates().length > 0) {
                    Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseWithUnsavedChanges)
                    return false;
                }
            }
        }, this);

        // Prevent editing of a locked event
        this.on('beforeEdit', function (e) {
            if (this.lockedFromEdit) {
                Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.mustUnlockToEdit);
                return false;
            }
        }, this);

        // Fix-up scores
        this.on('afterEdit', function (e) {
            this.computeFinalScore(e.record);
        }, this);
    },


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
        if (this.lockedFromEdit) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.onlyEditCurrentEvent);
            return false;
        }

        var s = this.getStore();
        s.each(function (r) {
            r.set('order', Math.round(Math.random() * 1000));
        });
        s.sort('order', 'ASC');
    },


    computePlacement: function () {
        if (this.lockedFromEdit) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.onlyEditCurrentEvent);
            return false;
        }

        var s = this.getStore().getRange();
        // Sort in order from highest placement to lowest
        var sorter = function (a, b) {
            var ret = b.get('finalScore') - a.get('finalScore');
            if (0 == ret) {
                ret = b.get('tieBreaker0') - a.get('tieBreaker0');
            }
            if (0 == ret) {
                ret = b.get('tieBreaker1') - a.get('tieBreaker1');
            }
            if (0 == ret) {
                ret = b.get('tieBreaker2') - a.get('tieBreaker2');
            }
            return ret;
        }
        s.sort(sorter);

        lastSeenScore = '';
        lastGivenPlace = 0;
        for (var i = 0; i < s.length; i++) {
            var thisScore = s[i].get('finalScore') + '_' + s[i].get('tieBreaker0') + '_' + s[i].get('tieBreaker1') + '_' + s[i].get('tieBreaker2');
            if (lastSeenScore == thisScore) {
                s[i].set('placement', lastGivenPlace);
            } else {
                lastGivenPlace = i+1;
                s[i].set('placement', lastGivenPlace);
                lastSeenScore = thisScore;
            }
        }
    },


    convertNumeric: function (str) {
        var number = parseFloat(str);
        if (isNaN(number)) {
            return 0;
        } else {
            return number;
        }
    },


    getScoringUpdates: function () {
        var scoringUpdates = [];
        var scoringRecords = this.getStore().getModifiedRecords();
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
                tieBreaker2: this.convertNumeric(rec.get('tieBreaker2')),
                placement: this.convertNumeric(rec.get('placement'))
            });
        }
        return scoringUpdates;
    },


    saveScores: function () {
        this.computePlacement();
        var scoringUpdates = this.getScoringUpdates();
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


    computeFinalScore: function (scoringRecord) {
        var finalScore = 0;
        var tieBreaker0 = 0;
        var tieBreaker1 = 0;
        var tieBreaker2 = 0;
        if (this.isNandu) {
            // In nandu, add up all scores, subtract all deductions.
            finalScore = this.convertNumeric(scoringRecord.get('score0'))
                + this.convertNumeric(scoringRecord.get('score1'))
                + this.convertNumeric(scoringRecord.get('score2'))
                + this.convertNumeric(scoringRecord.get('score3'))
                + this.convertNumeric(scoringRecord.get('score4'))
                + this.convertNumeric(scoringRecord.get('score5'))
                - this.convertNumeric(scoringRecord.get('timeDeduction'))
                - this.convertNumeric(scoringRecord.get('otherDeduction'));
        } else {
            // Get all given scores
            // Ignore 0-valued scores
            var givenScores = [];
            // provide impossible scores for the initial min and max.
            var maxScore = -1;
            var minScore = 11;
            var sumScores = 0;
            var scoreFields = ['score0', 'score1', 'score2', 'score3', 'score4'];
            for (var i = 0; i < scoreFields.length; i++) {
                var score = this.convertNumeric(scoringRecord.get(scoreFields[i]));
                if (score > 0) {
                    maxScore = Math.max(maxScore, score);
                    minScore = Math.min(minScore, score);
                    sumScores += score;
                    givenScores.push(score);
                }
            }

            if (0 == givenScores.length || 3 > givenScores.length) {
                // Short-circuit the empty case.
                // If this doesn't meet the minimum judges, quit.
            } else {
                // average of scores not including the highest and lowest
                var meritedScore = (sumScores - minScore - maxScore) / (givenScores.length - 2);
                // final score is merited score minus any deductions
                finalScore = meritedScore - this.convertNumeric(scoringRecord.get('timeDeduction')) - this.convertNumeric(scoringRecord.get('otherDeduction'));
            }

            // Compute and set tiebreakers
            // negate so "bigger is better"
            tieBreaker0 = -1 * (Math.abs((maxScore + minScore) / 2 - finalScore));
            tieBreaker1 = (maxScore + minScore) / 2;
            tieBreaker2 = minScore;
        }

        scoringRecord.set('finalScore', finalScore);
        scoringRecord.set('tieBreaker0', tieBreaker0);
        scoringRecord.set('tieBreaker1', tieBreaker1);
        scoringRecord.set('tieBreaker2', tieBreaker2);

        this.computePlacement();

        return finalScore;
    },


    isFinished: function () {
        return cmatic.util.getDataStore('event').getById(this.eventId).get('isFinished');
    },


    unlock: function () {
        if (!this.lockedFromEdit) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.eventAlreadyUnlocked);
            return;
        }

       this.lockedFromEdit = false;
        Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.eventUnlocked);
    },


    lock: function () {
        if (this.lockedFromEdit) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.eventAlreadyLocked);
            return;
        }

        if (this.getScoringUpdates().length > 0) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseWithUnsavedChanges);
            return;
        }

        this.lockedFromEdit = true;
        Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.eventLocked);
    },


    /**
     * Will also unlock
     */
    start: function () {
        // Can't start this event if another is already started
        var currentEvent = cmatic.scoring.app.getCurrentEvent();
        if (currentEvent) {
            Ext.Msg.alert(cmatic.labels.message.warning, (this == currentEvent) ? cmatic.labels.message.eventAlreadyStarted : cmatic.labels.message.otherEventAlreadyStarted)
            return;
        }

        this.lockedFromEdit = false;
        cmatic.scoring.app.setCurrentEvent(this);
        Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.eventStarted);
    },


    finish: function () {
        if (this != cmatic.scoring.app.getCurrentEvent()) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.eventNotInProgress);
            return;
        }

        if (this.getScoringUpdates().length > 0) {
            Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseWithoutUnsavedChanges);
            return;
        }

        var thiz = this;
        Ext.Ajax.request({
            url: cmatic.url.set,
            params: {
                op: 'edit',
                type: 'event',
                records: '[{"id": ' + this.eventId + ', "isFinished": true}]'
            },
            success: function (response) {
                var r = Ext.util.JSON.decode(response.responseText);
                if (r.success) {
                    cmatic.scoring.app.unsetCurrentEvent();
                    Ext.Msg.alert(cmatic.labels.message.success, cmatic.labels.message.eventFinished);

                    // change up buttons
                    thiz.buttonUnlock.setDisabled(false);
                    thiz.buttonLock.setDisabled(false);
                    thiz.buttonStart.setDisabled(true);
                    thiz.buttonFinish.setDisabled(true);
                } else {
                    cmatic.util.alertSaveFailed();
                }
            },
            failure: cmatic.util.alertSaveFailed
        });

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
            }]
        });
    }


    function _buildViewport (ringNumber) {
        _getEventStore(ringNumber).load();

        headerPanel = _buildHeaderPanel();
        eventPanel = _buildEventPanel(ringNumber);
        //judgesPanel = _buildJudgesPanel();
        mainPanel = _buildMainPanel();

        mainPanel.on('beforeRemove', function (mainPanel, tab) {
            if (tab == cmatic.scoring.app.getCurrentEvent()) {
                Ext.Msg.alert(cmatic.labels.message.warning, cmatic.labels.message.cantCloseInProgress);
                return false;
            }
        });

        return new Ext.Viewport({
            layout: 'border',
            items:[/*headerPanel, */eventPanel, /*judgesPanel, */mainPanel]
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
                    _startTasks();
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
        },


        setCurrentEvent: function (eventTab) {
            if (!basePageTitle) {
                basePageTitle = document.title;
            }
            currentEvent = eventTab;
            document.title = basePageTitle + ": " + cmatic.util.getFullEventNameRenderer(eventTab.eventId);
        },


        unsetCurrentEvent: function () {
            currentEvent = null;
            document.title = basePageTitle;
        }
    };
}();

Ext.onReady(cmatic.scoring.app.init, cmatic.scoring.app);
