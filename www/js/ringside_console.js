EVENT_SCORING = {
    HEADER_TEXT_5 : [
        "ID", "Competitor",
        "#1", "#2", "#3", "#4", "#5",
        "Time (sec.)",
        "Merited Score", "Time Deduct.", "Other Deduct.",
        "Final Score", "Tie Breaker",
        ""],
    HEADER_TEXT_6 : [
        "ID", "Competitor",
        "#1", "#2", "#3", "#4", "#5", "#6",
        "Time (sec.)",
        "Merited Score", "Time Deduct.", "Other Deduct.",
        "Final Score", "Tie Breaker",
        ""]
};

RING_CONFIG = {
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
    this.ringLeader.value = this.d.ringLeader;
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
    this.ringLeader.addEvent("change", function() {self.d.ringLeader = this.value});

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Type:");
    this.ringType = HTML.makeSelect(HTML.makeElement(tr, "td"), null, "ringType", RING_CONFIG.NAMES, null, null);
    this.ringType.s.addEvent("change", function() {self.d.type = this.value;});
    this.ringType.s.addEvent("change", function() {self.disableExtraJudges();});

    tr = HTML.makeElement(tbody, "tr");
    HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Head Judge:");
    this.judges[0] = HTML.makeInput(HTML.makeElement(tr, "td"), null, "judge0", null);
    this.judges[0].addEvent("change", function() {self.d.judges[0].name = this.value});
    for (var i = 1; i < RING_CONFIG.MAX_JUDGES; i++) {
        tr = HTML.makeElement(tbody, "tr");
        HTML.makeText(HTML.makeElement(tr, "th", {"scope":"row"}), "Judge " + i + ":");
        this.judges[i] = HTML.makeInput(HTML.makeElement(tr, "td"), null, "judge" + i, null);
        var judges_i = i; // do this because otherwise the i we get is something else.
        this.judges[i].addEvent("change", function() {self.d.judges[judges_i].name = this.value});
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

EventScoring.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("module");
    this.root.addClass("eventScoring");

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
    for (var i = 0; i < EVENT_SCORING.HEADER_TEXT_5.length; i++) {
        var th = HTML.makeElement(thead, "th", {"scope":"col"});
        th.addClass("eventScoringHeaderCell");
        HTML.makeText(th, EVENT_SCORING.HEADER_TEXT_5[i]);
    }
    var tbody = HTML.makeElement(table, "tbody");
    for (var i = 0; i < this.d.scoring.length; i++) {
        var row = HTML.makeElement(tbody, "tr");
        new Scoring(row, this.d.scoring[i]);
    }

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
        var t = $(self.drawLocation);
        t.setHTML("");
        t.remove();
    };

    // Attach handlers
    controlMoveDownEvent.addEvent("click", handleMoveDownEvent);
    controlMoveUpEvent.addEvent("click", handleMoveUpEvent);
    controlToggleShade.addEvent("click", handleToggleShade);
    controlCloseEvent.addEvent("click", handleCloseEvent);

    // Extra
    this.repaint();

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    drawDest.appendChild(this.root);
};


/**
 * Represent a scoring row
 */
function Scoring(drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;

    // DOM
    this.cells = new Array();

    // initialize data
    if (data) {
        this.setData(data);
    }
};

Scoring.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

Scoring.prototype.repaint = function () {
};

Scoring.prototype.makeDom = function () {
    var td = null;

    td = HTML.makeElement(null, "td");
    td.addClass("scoringCompetitorId");
    this.cells.push(td);
    var competitorId = HTML.makeElement(td, "span");
    HTML.makeText(competitorId, this.d.competitor_id);

    // Competitior
    td = HTML.makeElement(null, "td");
    td.addClass("scoringCompetitorName");
    this.cells.push(td);
    var competitorName = HTML.makeElement(td, "span");
    HTML.makeText(competitorName, this.d.competitor_first_name + " " + this.d.competitor_last_name);

    // Given scores
    // TODO: Need to switch between configs
    var inputId = null;
    var idSuffix = "_" + this.d.scoring_id + "_" + i;
    var scoringInputs = new Array(5)
    for (var i = 0; i < scoringInputs.length; i++) {
        var inputId = "judgeScore" + idSuffix;
        td = HTML.makeElement(null, "td");
        td.addClass("scoringInput");
        td.addClass("judgeScore");
        this.cells.push(td);
        scoringInputs[i] = HTML.makeInput(td, inputId, inputId, "0.0");
        scoringInputs[i].setAttribute("maxlength", "4");
    }

    // Time
    inputId = "time" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("routineTime");
    this.cells.push(td);
    var timeInput = HTML.makeInput(td, inputId, inputId, "0");
    timeInput.setAttribute("maxlength", "8");

    // Computation
    inputId = "meritedScore" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("meritedScore");
    this.cells.push(td);
    var mScoreInput = HTML.makeInput(td, inputId, inputId, "0");
    mScoreInput.setAttribute("readonly", "readonly");

    inputId = "timeDeduction" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("timeDeduction");
    this.cells.push(td);
    HTML.makeText(td, "- ");
    var tDeductInput = HTML.makeInput(td, inputId, inputId, "0");
    tDeductInput.setAttribute("maxlength", "4");

    inputId = "otherDeduction" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("otherDeduction");
    this.cells.push(td);
    HTML.makeText(td, "- ");
    var oDeductInput = HTML.makeInput(td, inputId, inputId, "0");
    oDeductInput.setAttribute("maxlength", "4");

    inputId = "finalScore" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("finalScore");
    this.cells.push(td);
    HTML.makeText(td, "= ");
    var fScoreInput = HTML.makeInput(td, inputId, inputId, "0");
    fScoreInput.setAttribute("readonly", "readonly");

    inputId = "tieBreaker" + idSuffix;
    td = HTML.makeElement(null, "td");
    td.addClass("scoringInput");
    td.addClass("tieBreakerValue");
    this.cells.push(td);
    HTML.makeText(td, "( ");
    var tieBreakerInput = HTML.makeInput(td, inputId, inputId, "0");
    tieBreakerInput.setAttribute("readonly", "readonly");
    HTML.makeText(td, " )");

    // Submit score
    td = HTML.makeElement(null, "td");
    td.addClass("eventScoringControl");
    td.addClass("controlSubmitScore");
    this.cells.push(td);
    var controlSubmitScore = HTML.makeElement(td, "span");
    HTML.makeText(controlSubmitScore, ">");

    // Create handlers
    var self = this;
    var handleMeritedScore = function () {
        // Find max and min and sum
        var minScore = 10; // Start way higher
        var maxScore = 0; // Start way lower
        var sumScore = 0; // Start at nothing
        var numScores = scoringInputs.length;
        for (var i = 0; i < numScores; i++) {
            var thisScore = scoringInputs[i].value;
            minScore = Math.min(minScore, thisScore);
            maxScore = Math.max(maxScore, thisScore);
            sumScore = sumScore + parseFloat(thisScore);
        }

        // Set merited score
        var mScoreValue = (sumScore - minScore - maxScore) / (numScores - 2);
        mScoreInput.value = (Math.round(mScoreValue * 100) / 100);
        mScoreInput.fireEvent("change");
    };
    var handleTimeDeduction = function () {
        // Magicaly get max and min
        var limits = CMAT.getTimeLimits(3, 4, 17); // TODO Faked: need a way to really get this info
        var time = CMAT.parseSeconds(timeInput.value);
        var diff = 0;
        if (null != limits[0] && time < limits[0]) {
            diff = limits[0] - time;
        } else if (null != limits[1] && time > limits[1]) {
            diff = time - limits[1];
        }

        var deduction = 0;
        var penaltyInterval = 2; // TODO Faked.. figure out how to get the real interval
        if (diff > 0.090009) { // 0.09 is official, 0.090009 gets us past reasonable rounding errors
            deduction = Math.ceil(diff / penaltyInterval) * 0.1;
        }
        tDeductInput.value = deduction;
        tDeductInput.fireEvent("change");
    };
    var handleFinalScore = function () {
        var mScoreValue = parseFloat(mScoreInput.value);
        var tDeductValue = parseFloat(tDeductInput.value);
        var oDeductValue = parseFloat(oDeductInput.value);
        var fScoreValue = mScoreValue - tDeductValue - oDeductValue;
        fScoreInput.value = (Math.round(fScoreValue * 100) / 100);
        fScoreInput.fireEvent("change");
    };

    // Attach handlers
    for (var i = 0; i < scoringInputs.length; i++) {
        scoringInputs[i].addEvent("change", handleMeritedScore);
    }
    timeInput.addEvent("change", handleTimeDeduction);
    mScoreInput.addEvent("change", handleFinalScore);
    tDeductInput.addEvent("change", handleFinalScore);
    oDeductInput.addEvent("change", handleFinalScore);

    // Extra
    this.repaint();

    var drawDest = $(this.drawLocation);
    drawDest.setHTML("");
    for (var i = 0; i < this.cells.length; i++) {
        drawDest.appendChild(this.cells[i]);
    }
};

