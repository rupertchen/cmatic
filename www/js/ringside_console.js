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
    }
};


/**
 * Store the configuration of this ring
 */
function RingConfiguration (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = null;
    if (data) {
        this.setData(data);
    }

    // DOM
    this.root = null;
    this.ringType = null;
    this.judges = new Array(RING_CONFIG.MAX_JUDGES);
    this.ringLeader = null;
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
        this.judges[i].disabled = i > (RING_CONFIG.NUM_JUDGES[this.d.type] - 1);
    }
};

RingConfiguration.prototype.repaint = function () {
    this.fillDomValues();
    this.disableExtraJudges();
};

RingConfiguration.prototype.makeDom = function () {
    var self = this;
    this.root = HTML.makeElement(null, "div");
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
    drawDest.innerHtml = "";
    drawDest.appendChild(this.root);
};
