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
    this.root.addClass("sidebarModule");
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
    drawDest.innerHTML = "";
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
    this.root.addClass("sidebarModule");
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
    drawDest.innerHTML = "";
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
    HTML.makeText(td, itemData.form_blowout.competitor_count);

    // Details
    td = HTML.makeElement(item, "td");
    td.addClass("ringEventListItemDetails");
    HTML.makeText(td, CMAT.formatFormId(itemData.form_blowout.form_id));

    // Create handlers
    var handleHighlight = function () {
        item.toggleClass("highlightRow");
    }

    // Attach handlers
    item.addEvent("mouseover", handleHighlight);
    item.addEvent("mouseout", handleHighlight);

    return item;
};
