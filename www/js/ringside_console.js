// WARNING: This file expects certain global variables to be available.
// Search for "GLOBAL_"

EVENT_SCORING = {
    HEADER_TEXT_5 : [
        "ID", "Competitor",
        "#1", "#2", "#3", "#4", "#5",
        "Time (sec.)",
        "Merited Score", "Time Deduct.", "Other Deduct.",
        "Final Score",
        ""],
    HEADER_TEXT_6 : [
        "ID", "Competitor",
        "A1", "A2", "B1", "B2", "C1", "C2",
        "Time (sec.)",
        "Merited Score", "Time Deduct.", "Other Deduct.",
        "Final Score",
        ""]
};

RING_CONFIG = {
    // Includes head judge
    NUM_JUDGES: {
        1 : 1,
        2 : 6,
        3 : 6,
        4 : 6,
        5 : 6,
        6 : 6,
        7 : 7
    },

    MAX_JUDGES: 7,

    NAMES: {
        1 : "Push Hands",
        2 : "Internal",
        3 : "Traditional",
        4 : "Contemporary",
        5 : "External Group",
        6 : "Internal Group",
        7 : "Nandu"
    },

    SCORING_HEADER: {
        1 : EVENT_SCORING.HEADER_TEXT_5,
        2 : EVENT_SCORING.HEADER_TEXT_5,
        3 : EVENT_SCORING.HEADER_TEXT_5,
        4 : EVENT_SCORING.HEADER_TEXT_5,
        5 : EVENT_SCORING.HEADER_TEXT_5,
        6 : EVENT_SCORING.HEADER_TEXT_5,
        7 : EVENT_SCORING.HEADER_TEXT_6
    },

    BLANK_NAME : "--",

    NEEDS_NAME : "<Enter name>"
};

RING_EVENT_LIST = {
    HEADER_TEXT : ["Code", "#", "Details"],

    MIN_BODY_HEIGHT : 50,

    // This is the magic value that gets us the correct resizing of the list body
    MAGIC_VAL_1 : 400
};

/**
 * Store the configuration of this ring
 */
function RingConfiguration (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;

    // DOM
    this.root = null;
    this.ringType = null;
    this.judges = new Array(RING_CONFIG.MAX_JUDGES);
    this.ringLeader = null;

    // initialize data
    if (data) {
        this.setData(data);
    }
};

RingConfiguration.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

RingConfiguration.prototype.fillDomValues = function () {
    this.ringType.s.value = this.d.type;
    for (var i = 0; i < RING_CONFIG.MAX_JUDGES; i++) {
        this.judges[i].value = this.d.judges[i].name;
    }
    this.ringLeader.value = this.d.ring_leader;
};

RingConfiguration.prototype.disableExtraJudges = function () {
    for (var i = 0; i < RING_CONFIG.MAX_JUDGES; i++) {
        var extraJudge = RING_CONFIG.NUM_JUDGES[this.d.type] <= i;
        if (extraJudge) {
            this.judges[i].value = RING_CONFIG.BLANK_NAME;
        } else if (!extraJudge && this.judges[i].disabled) {
            this.judges[i].value = RING_CONFIG.NEEDS_NAME;
        }
        this.judges[i].disabled = extraJudge;
    }
};

RingConfiguration.prototype.repaint = function () {
    this.fillDomValues();
    this.disableExtraJudges();
};

RingConfiguration.prototype.makeDom = function () {
    var self = this;
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("module");
    this.root.addClass("ringConfiguration");

    var table = HTML.makeTable(this.root);
    var tbody = HTML.makeElement(table, "tbody");
    var tr = null;

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Ring:");
    HTML.makeText(HTML.makeElement(tr, "td"), this.d.ring_id);

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Ring Leader:");
    this.ringLeader = HTML.makeInput(HTML.makeElement(tr, "td"), null, "ringLeader", null);
    this.ringLeader.addEvent("change", function() {self.d.ring_leader = this.value});

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Type:");
    this.ringType = HTML.makeSelect(HTML.makeElement(tr, "td"), null, "ringType", RING_CONFIG.NAMES, null, null);
    this.ringType.s.addEvent("change", function() {self.d.type = this.value;});
    this.ringType.s.addEvent("change", function() {self.disableExtraJudges();});

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Head Judge:");
    this.judges[0] = HTML.makeInput(HTML.makeElement(tr, "td"), null, "judge0", null);
    this.judges[0].addEvent("change", function() {self.d.judges[0].name = this.value});
    var makeJudgeSyncFn = function(_i) {return function() {self.d.judges[_i].name = this.value};};
    for (var i = 1; i < RING_CONFIG.MAX_JUDGES; i++) {
        tr = HTML.makeElement(tbody, "tr");
        HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Judge " + i + ":");
        this.judges[i] = HTML.makeInput(HTML.makeElement(tr, "td"), null, "judge" + i, null);
        this.judges[i].addEvent("change", makeJudgeSyncFn(i));
    }

    // Extra
    this.repaint();

    // Draw to page
    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    drawDest.appendChild(this.root);
};


/**
 * Events to be run in this ring
 */
function RingEventList(drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;

    // DOM
    this.root = null;
    this.titleBar = null;
    this.items = null;

    // initialize data
    if (data) {
        this.setData(data);
    }
};

RingEventList.prototype.setData = function (data) {
    this.d = data;
    data.sort(CMAT.sortEventSummary);
    this.items = new Array(this.d.length);
    this.makeDom();
};

RingEventList.prototype.alternateRowStyle = function () {
    for (var i = 0; i < this.items.length; i++) {
        this.items[i].addClass((i%2) ? "evenRow" : "oddRow");
    }
};

RingEventList.prototype.repaint = function () {
    this.alternateRowStyle();
};

RingEventList.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("module");
    this.root.addClass("ringEventList");

    // Title bar
    this.titleBar = HTML.makeElement(this.root, "div");
    this.titleBar.addClass("ringEventListTitleBar");
    HTML.makeText(this.titleBar, "Wee!");

    // Listing
    var listTable = HTML.makeTable(this.root);
    listTable.addClass("ringEventListTable");
    var listHead = HTML.makeElement(listTable, "thead");
    listHead.addClass("ringEventListHead");
    for (var i = 0; i < RING_EVENT_LIST.HEADER_TEXT.length; i++) {
        var th = HTML.makeElement(listHead, "th", {"scope":"col"});
        HTML.makeText(th, RING_EVENT_LIST.HEADER_TEXT[i]);
    }
    var listBody = HTML.makeElement(listTable, "tbody");
    listBody.addClass("ringEventListBody");

    for (var i = 0; i < this.d.length; i++) {
        this.items[i] = this.makeItemRow(listBody, this.d[i]);
    }

    // Extra
    this.repaint();
    var resizeBody = function () {
        listBody.style.height = Math.max(RING_EVENT_LIST.MIN_BODY_HEIGHT, (window.getHeight() - RING_EVENT_LIST.MAGIC_VAL_1)) + 'px';
    };
    resizeBody();
    window.addEvent("resize", resizeBody);

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    drawDest.appendChild(this.root);
};

RingEventList.prototype.makeItemRow = function (parent, itemData) {
    var item = HTML.makeElement(parent, "tr");
    item.addClass("ringEventListItemRow");
    if (CMAT.convertDbBoolean(itemData.is_done)) {
        item.addClass("ringEventListItemRowDone");
    }
    var td = null;

    // Code
    td = HTML.makeElement(item, "td");
    td.addClass("ringEventListItemCode");
    HTML.makeText(td, itemData.event_code);

    // Competitor Count
    td = HTML.makeElement(item, "td");
    td.addClass("ringEventListItemCompetitorCount");
    HTML.makeText(td, itemData.form_blowout[0].competitor_count);

    // Details
    td = HTML.makeElement(item, "td");
    td.addClass("ringEventListItemDetails");
    HTML.makeText(td, CMAT.formatFormId(itemData.form_blowout[0].form_id));

    // Create handlers
    var handleHighlight = function () {
        item.toggleClass("highlightRow");
    };
    var handleOpenEventScoring = function () {
        var newId = "scoring_" + itemData.event_id;
        if ($(newId)) {
            // It's already opened, so don't make it
        } else {
            HTML.makeElement($("consoleContentArea"), "div", {"id" : newId});
            var ajax = new Json.Remote("../query/get_event_scoring.php?e=" + itemData.event_id,
                {"onComplete" : function (x) { new EventScoring(newId, x); }});
            ajax.send();
        }
    };

    // Attach handlers
    item.addEvent("mouseover", handleHighlight);
    item.addEvent("mouseout", handleHighlight);
    item.addEvent("click", handleOpenEventScoring);

    return item;
};


/**
 * Event Scoring
 */
function EventScoring (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;

    this.scoring = null;

    // DOM
    this.root = null;
    this.titleBar = null;
    this.contentBox = null;

    // initialize data
    if (data) {
        this.setData(data);
    }
};


EventScoring.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

EventScoring.prototype.repaint = function () {
};

EventScoring.prototype.areAllScoresSaved = function () {
    var areSaved = true;
    for (var i = 0; i < this.scoring.length; i++) {
        areSaved &= !this.scoring[i].needsSave;
    }
    return areSaved;
};

EventScoring.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("module");
    if (CMAT.convertDbBoolean(this.d.is_done)) {
        this.root.addClass("eventScoringDone");
    } else {
        this.root.addClass("eventScoring");
    }

    // Title bar
    this.titleBar = HTML.makeElement(this.root, "div");
    this.titleBar.addClass("eventScoringTitleBar");
    var controls = HTML.makeElement(this.titleBar, "div");
    controls.addClass("eventScoringControlBox");

    var controlMoveDownEvent = HTML.makeElement(controls, "span");
    controlMoveDownEvent.addClass("eventScoringControl");
    controlMoveDownEvent.addClass("controlMoveDownEvent");
    HTML.makeText(controlMoveDownEvent, "v");

    var controlMoveUpEvent = HTML.makeElement(controls, "span");
    controlMoveUpEvent.addClass("eventScoringControl");
    controlMoveUpEvent.addClass("controlMoveUpEvent");
    HTML.makeText(controlMoveUpEvent, "^");

    var controlToggleShade = HTML.makeElement(controls, "span");
    controlToggleShade.addClass("eventScoringControl");
    controlToggleShade.addClass("controlToggleShade");
    HTML.makeText(controlToggleShade, "-");

    var controlCloseEvent = HTML.makeElement(controls, "span");
    controlCloseEvent.addClass("eventScoringControl");
    controlCloseEvent.addClass("controlCloseEvent");
    HTML.makeText(controlCloseEvent, "X");

    var fb = this.d.form_blowout[0];
    HTML.makeText(this.titleBar, this.d.event_code + ":"
        + " " + CMAT.formatLevelId(fb.level_id)
        + " " + CMAT.formatGenderId(fb.gender_id)
        + " " + CMAT.formatAgeGroupId(fb.age_group_id)
        + " " + CMAT.formatFormId(fb.form_id));

    // Content
    this.contentBox = HTML.makeElement(this.root, "div");
    this.contentBox.addClass("eventScoringContent");

    var table = HTML.makeTable(this.contentBox);
    table.addClass("eventScoringTable");
    var thead = HTML.makeElement(table, "thead");
    var scoringHeader = RING_CONFIG.SCORING_HEADER[fb.ring_configuration_id];
    for (var i = 0; i < scoringHeader.length; i++) {
        var th = HTML.makeElement(thead, "th", {"scope":"col"});
        th.addClass("eventScoringHeaderCell");
        HTML.makeText(th, scoringHeader[i]);
    }
    var tbody = HTML.makeElement(table, "tbody");
    var timeLimits = CMAT.getTimeLimits(fb.level_id, fb.age_group_id, fb.form_id);
    var penaltyTimeInterval = CMAT.getPenaltyTimeInterval(fb.form_id);
    this.scoring = new Array(this.d.scoring.length);
    for (var i = 0; i < this.d.scoring.length; i++) {
        var row = HTML.makeElement(tbody, "tr");
        this.scoring[i] = new Scoring(row, this.d.scoring[i], timeLimits[0], timeLimits[1], penaltyTimeInterval, fb.ring_configuration_id);
    }

    var buttons = HTML.makeElement(this.contentBox, "div");
    buttons.addClass("eventScoringButtons");
    var startButton = HTML.makeElement(buttons, "span");
    startButton.addClass("eventScoringControl");
    startButton.addClass("controlEventStart");
    HTML.makeText(startButton, "Start Event");
    var editButton = HTML.makeElement(buttons, "span");
    editButton.addClass("eventScoringControl");
    editButton.addClass("controlEventEdit");
    HTML.makeText(editButton, "Edit");
    var doneButton = HTML.makeElement(buttons, "span");
    doneButton.addClass("eventScoringControl");
    doneButton.addClass("controlEventDone");
    HTML.makeText(doneButton, "Finalize Event & Place Competitors");

    var infoBox = HTML.makeElement(this.contentBox, "div");
    infoBox.addClass("eventScoringInfoBox");
    HTML.makeText(infoBox, "Min Time: " + CMAT.formatSeconds(timeLimits[0])
        + ",  Max Time: " + CMAT.formatSeconds(timeLimits[1])
        + ",  Interval: " + CMAT.formatSeconds(penaltyTimeInterval));

    // Create handlers
    var self = this;
    var handleMoveDownEvent = function () {
        var current = $(self.drawLocation);
        var next = current.getNext();
        if (next) {
            $(current).injectAfter($(next));
        }
    };
    var handleMoveUpEvent = function () {
        var current = $(self.drawLocation);
        var previous = current.getPrevious();
        if (previous) {
            $(current).injectBefore($(previous));
        }
    };
    var handleToggleShade = function () {
        self.root.toggleClass("shadedEventScoring");
    };
    var handleCloseEvent = function () {
        if (self.areAllScoresSaved()
            && (GLOBAL_CURRENT_EVENT != self
                || (GLOBAL_CURRENT_EVENT == self
                    && confirm("Either this event has not been finalized or there are unsaved scores. Are you sure you want to close it instead of saving the scores and/or finalizing the event? Unsaved changes will be lost.\n\nClick \"Cancel\" to go back or \"OK\" to close anyway.")))) {
            var t = $(self.drawLocation);
            GLOBAL_CURRENT_EVENT = null;
            t.setHTML("");
            t.remove();
        }
    };
    var handleStartEvent = function () {
        if (GLOBAL_CURRENT_EVENT) {
            alert("Can't start event. Event " + GLOBAL_CURRENT_EVENT.d.event_code + " is still in progress.");
        } else {
            GLOBAL_CURRENT_EVENT = self;
            self.root.addClass("_inProgress");
            for (var i = 0; i < self.scoring.length; i++) {
                self.scoring[i].disableEditableInputs(false);
            }
        }
    };
    var handleEditEvent = function () {
        self.root.addClass("_underEdit");
        for (var i = 0; i < self.scoring.length; i++) {
            self.scoring[i].disableEditableInputs(false);
        }
    };
    var handleDoneEvent = function () {
        //if (GLOBAL_CURRENT_EVENT == self || CMAT.convertDbBoolean(self.d.is_done)) {
        if (self.areAllScoresSaved()) {
            var url = "../query/save_event_done.php";
            var body = {};
            body["event_id"] = self.d.event_id;
            body["ring_configuration_id"] = self.d.form_blowout[0].ring_configuration_id;
            var myAjax = new Ajax(url, {postBody: body});
            myAjax.request();
            GLOBAL_CURRENT_EVENT = null;
            controlCloseEvent.fireEvent("click");
        } else {
            alert("Can't finalize this event. There are unsaved scores (marked with red).");
        }
    };

    // Attach handlers
    controlMoveDownEvent.addEvent("click", handleMoveDownEvent);
    controlMoveUpEvent.addEvent("click", handleMoveUpEvent);
    controlToggleShade.addEvent("click", handleToggleShade);
    controlCloseEvent.addEvent("click", handleCloseEvent);
    startButton.addEvent("click", handleStartEvent);
    editButton.addEvent("click", handleEditEvent);
    doneButton.addEvent("click", handleDoneEvent);

    // Extra
    this.repaint();

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    drawDest.appendChild(this.root);

    // Extra warning
    if (this.d.form_blowout[0].ring_configuration_id != GLOBAL_RING_CONFIG.d.type) {
        alert("Be careful! The current ring configuration type does not match this event's type.");
    }
};


/**
 * Represent a scoring row
 */
function Scoring(drawLocation, data, minTime, maxTime, penaltyInterval, ringConfigurationId) {
    this.drawLocation = drawLocation;
    this.d = null;
    this.minTime = minTime;
    this.maxTime = maxTime;
    this.penaltyInterval = penaltyInterval;
    this.ringConfigurationId = ringConfigurationId;
    this.needsSave = false;

    // DOM
    this.cells = new Array();
    this.scoringInputs = null;
    this.timeInput = null;
    this.mScoreInput = null;
    this.tDeductInput = null;
    this.oDeductInput = null;
    this.fScoreInput = null;

    // initialize data
    if (data) {
        this.setData(data);
    }
};

Scoring.prototype.setNeedsSave = function (flag) {
    this.needsSave = flag;
    var css = "scoringRowNeedsSave";
    if (flag) {
        this.drawLocation.addClass(css);
    } else {
        this.drawLocation.removeClass(css);
    }
};

Scoring.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

Scoring.prototype.disableEditableInputs = function (flag) {
    for (var i = 0; i < this.scoringInputs.length; i++) {
        this.scoringInputs[i].disabled = flag;
    }
    this.timeInput.disabled = flag;
    this.oDeductInput.disabled = flag;
};

Scoring.prototype.repaint = function () {
};

Scoring.prototype.makeDom = function () {
    var td = null;

    td = HTML.makeElement(null, "td");
    td.addClass("scoringCompetitorId");
    this.cells.push(td);
    var competitorId = HTML.makeElement(td, "span");
    HTML.makeText(competitorId, (this.d.competitor_id) ? this.d.competitor_id : this.d.group_id);

    // Competitior
    td = HTML.makeElement(null, "td");
    this.cells.push(td);
    var competitorName = HTML.makeElement(td, "span");
    if (this.d.competitor_first_name) {
        HTML.makeText(competitorName, this.d.competitor_first_name + " " + this.d.competitor_last_name);
        td.addClass("scoringCompetitorName");
    } else {
        HTML.makeText(competitorName, this.d.group_name);
        td.addClass("scoringGroupName");
    }

    // Given scores
    var inputId = null;
    var initialValue = null;
    var idSuffix = "_" + this.d.scoring_id;
    this.scoringInputs = new Array(RING_CONFIG.NUM_JUDGES[this.ringConfigurationId] - 1)
    for (var i = 0; i < this.scoringInputs.length; i++) {
        var inputId = "judgeScore_" + i + idSuffix;
        td = HTML.makeElement(null, "td");
        td.addClass("scoringInput");
        td.addClass("judgeScore");
        this.cells.push(td);
        initialValue = this.d["score_"+i];
        this.scoringInputs[i] = HTML.makeInput(td, inputId, inputId,
            initialValue ? CMAT.formatFloat(initialValue, 100) : "0");
        this.scoringInputs[i].setAttribute("maxlength", "4");
    }

    // Time
    inputId = "time" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("routineTime");
    this.cells.push(td);
    initialValue = this.d.time;
    this.timeInput = HTML.makeInput(td, inputId, inputId, initialValue ? CMAT.formatSeconds(CMAT.formatFloat(initialValue, 100)) : "0:00");
    this.timeInput.setAttribute("maxlength", "8");

    // Computation
    inputId = "meritedScore" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("meritedScore");
    this.cells.push(td);
    initialValue = this.d.merited_score;
    this.mScoreInput = HTML.makeInput(td, inputId, inputId, initialValue ? CMAT.formatFloat(initialValue, 100) : "0");
    this.mScoreInput.setAttribute("disabled", "disabled");

    inputId = "timeDeduction" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("timeDeduction");
    this.cells.push(td);
    HTML.makeText(td, "- ");
    initialValue = this.d.time_deduction;
    this.tDeductInput = HTML.makeInput(td, inputId, inputId, initialValue ? CMAT.formatFloat(initialValue, 100) : "0");
    this.tDeductInput.setAttribute("disabled", "disabled");

    inputId = "otherDeduction" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("otherDeduction");
    this.cells.push(td);
    HTML.makeText(td, "- ");
    initialValue = this.d.other_deduction;
    this.oDeductInput = HTML.makeInput(td, inputId, inputId, initialValue ? CMAT.formatFloat(initialValue, 100) : "0");
    this.oDeductInput.setAttribute("maxlength", "4");

    inputId = "finalScore" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("finalScore");
    this.cells.push(td);
    HTML.makeText(td, "= ");
    initialValue = this.d.final_score;
    this.fScoreInput = HTML.makeInput(td, inputId, inputId, initialValue ? CMAT.formatFloat(initialValue, 100) : "0");
    this.fScoreInput.setAttribute("disabled", "disabled");

    // Submit score
    td = HTML.makeElement(null, "td");
    td.addClass("submitScore");
    this.cells.push(td);
    var controlSubmitScore = HTML.makeElement(td, "span");
    controlSubmitScore.addClass("controlSubmitScore");
    controlSubmitScore.addClass("eventScoringControl");
    HTML.makeText(controlSubmitScore, "\u00BB");

    var self = this;
    // Keep data in sync
    var makeScoreSyncFn = function(_i) { return function () { self.d["score_" + _i] = this.value;}; };
    for (var i = 0; i < this.scoringInputs.length; i++) {
        this.scoringInputs[i].addEvent("change", makeScoreSyncFn(i));
    }
    this.timeInput.addEvent("change", function () {self.d.time = CMAT.parseSeconds(this.value);});
    this.mScoreInput.addEvent("change", function () {self.d.merited_score = this.value;});
    this.tDeductInput.addEvent("change", function () {self.d.time_deduction = this.value;});
    this.oDeductInput.addEvent("change", function () {self.d.other_deduction = this.value;});
    this.fScoreInput.addEvent("change", function () {self.d.final_score = this.value;});
    

    // Create handlers
    var handleAnyChange = function () {
        self.setNeedsSave(true);
    };
    var handleMeritedScore = function () {
        // Find max and min and sum
        var minScore = 10; // Start way higher
        var maxScore = 0; // Start way lower
        var sumScore = 0; // Start at nothing
        var numScores = self.scoringInputs.length;
        for (var i = 0; i < numScores; i++) {
            var thisScore = self.scoringInputs[i].value;
            minScore = Math.min(minScore, thisScore);
            maxScore = Math.max(maxScore, thisScore);
            sumScore += parseFloat(thisScore);
        }

        // Set merited score
        var mScoreValue = (sumScore - minScore - maxScore) / (numScores - 2);
        self.mScoreInput.value = (Math.round(mScoreValue * 100) / 100);
        self.mScoreInput.fireEvent("change");
    };
    var handleMeritedScoreNandu = function () {
        var sumScore = 0;
        for (var i = 0; i < self.scoringInputs.length; i++) {
            sumScore += parseFloat(self.scoringInputs[i].value);
        }
        self.mScoreInput.value = sumScore / 2;
        self.mScoreInput.fireEvent("change");
    }
    var handleTimeDeduction = function () {
        var time = CMAT.parseSeconds(self.timeInput.value);
        var diff = 0;
        if (null != self.minTime && time < self.minTime) {
            diff = self.minTime - time;
        } else if (null != self.maxTime && time > self.maxTime) {
            diff = time - self.maxTime;
        }

        var deduction = 0;
        if (diff > 0.090009) { // 0.09 is official, 0.090009 gets us past reasonable rounding errors
            deduction = CMAT.formatFloat(Math.ceil(diff / self.penaltyInterval) * 0.1, 100);
        }
        self.tDeductInput.value = deduction;
        self.tDeductInput.fireEvent("change");
    };
    var handleFinalScore = function () {
        var mScoreValue = parseFloat(self.mScoreInput.value);
        var tDeductValue = parseFloat(self.tDeductInput.value);
        var oDeductValue = parseFloat(self.oDeductInput.value);
        var fScoreValue = mScoreValue - tDeductValue - oDeductValue;
        self.fScoreInput.value = (Math.round(fScoreValue * 100) / 100);
        self.fScoreInput.fireEvent("change");
    };
    var handleSubmitScore = function () {
        // Spot check
        var isValid = ("NaN" != self.d.final_score)
            && (CMAT.isValidTimeString(self.d.time));

        if (isValid) {
            var url = "../query/save_scoring_row.php";
            // HACK: We silently kill off all percentages (%) in names
            var body = {};
            body["scoring_id"] = self.d.scoring_id;
            body["time"] = self.d.time;
            body["merited_score"] = self.d.merited_score;
            body["time_deduction"] = self.d.time_deduction;
            body["other_deduction"] = self.d.other_deduction;
            body["final_score"] = self.d.final_score;
            body["num_judges"] = self.scoringInputs.length;
            body["ring_leader"] = encodeURIComponent(GLOBAL_RING_CONFIG.d.ring_leader.replace("%", ""));
            body["head_judge"] = encodeURIComponent(GLOBAL_RING_CONFIG.d.judges[0].name.replace("%", ""));
            for (var i = 0; i < self.scoringInputs.length; i++) {
                // i+1 accounts for the head judge (doesn't score) in RingConfiguration
                body["judge_" + i] = encodeURIComponent(GLOBAL_RING_CONFIG.d.judges[i+1].name.replace("%", ""));
                body["score_" + i] = self.d["score_" + i];
            }
            var markSaved = function () { self.setNeedsSave(false); };
            var myAjax = new Ajax(url, {postBody: body, onComplete: markSaved});
            myAjax.request();
        } else {
            alert("Bad values found. Check that score and deduction inputs are valid numbers and that the time is properly formatted.");
        }
    };

    // Attach handlers
    for (var i = 0; i < this.scoringInputs.length; i++) {
        this.scoringInputs[i].addEvent("change",
            (7 == this.ringConfigurationId) ? handleMeritedScoreNandu : handleMeritedScore);
        this.scoringInputs[i].addEvent("change", handleAnyChange);
    }

    this.timeInput.addEvent("change", handleTimeDeduction);
    this.mScoreInput.addEvent("change", handleFinalScore);
    this.tDeductInput.addEvent("change", handleFinalScore);
    this.oDeductInput.addEvent("change", handleFinalScore);
    controlSubmitScore.addEvent("click", handleSubmitScore);

    this.timeInput.addEvent("change", handleAnyChange);
    this.mScoreInput.addEvent("change", handleAnyChange);
    this.tDeductInput.addEvent("change", handleAnyChange);
    this.oDeductInput.addEvent("change", handleAnyChange);
    controlSubmitScore.addEvent("click", handleAnyChange);

    // Extra
    this.disableEditableInputs(true);
    this.repaint();

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    for (var i = 0; i < this.cells.length; i++) {
        drawDest.appendChild(this.cells[i]);
    }
};

