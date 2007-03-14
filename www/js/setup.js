SETUP = {
    TOTAL_NUM_RINGS : 8,
    SORT_EVENT_SUMMARY : function (eventA, eventB) {
        return eventA.event_order - eventB.event_order;
    }
};

/**
 * Renders the event order to the screen.
 */
function EventOrder (drawLocation) {
    this.drawLocation = drawLocation;
    this.d = null;
    this.ringsData = new Array(SETUP.TOTAL_NUM_RINGS);
    for (var i = 0; i < this.ringsData.length; i++) {
        this.ringsData[i] = new Array();
    }
    this.tempRingData = new Array();
    this.noRingData = new Array();

    // DOM
    this.root = null;
    this.detailsBox = null;
    this.rings = new Array(SETUP.TOTAL_NUM_RINGS);
    this.tempRing = null;
    this.noRing = null;
};

EventOrder.prototype.setData = function (data) {
    // Save all original data
    this.d = data;

    // Bucketize the events into the proper rings
    for (var i = 0; i < data.length; i++) {
        var event = data[i];
        if (-1 == event.ring_id) {
            this.noRingData.push(event);
        } else if (0 == event.ring_id) {
            this.tempRingData.push(event);
        } else {
            this.ringsData[event.ring_id - 1].push(event);
        }
    }

    // Sort each of the rings
    for (var i = 0; i < this.ringsData.length; i++) {
        this.ringsData[i].sort(SETUP.SORT_EVENT_SUMMARY);
    }

    this.makeDom();
};

EventOrder.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "div");
    this.root.addClass("eventOrder");

    // Detail bar
    this.detailsBox = HTML.makeElement(this.root);
    this.detailsBox.addClass("detailsBox");

    // Event order table    
    var table = HTML.makeTable(this.root);
    var tr = null;
    var thead = HTML.makeElement(table, "thead");
    tr = HTML.makeElement(thead, "tr");
    for (var i = 0; i < SETUP.TOTAL_NUM_RINGS; i++) {
        var th = HTML.makeElement(tr, "th", {"scope" : "col"});
        HTML.makeText(th, "Ring " + (i + 1));
    }
    var tbody = HTML.makeElement(table, "tbody");
    tr = HTML.makeElement(tbody, "tr");
    for (var i = 0; i < SETUP.TOTAL_NUM_RINGS; i++) {
        this.rings[i] = HTML.makeElement(tr, "td");
        for (var j = 0; j < this.ringsData[i].length; j++) {
            this.makeEventSummaryDom(this.rings[i], this.ringsData[i][j]);
        }
    }

    // Draw to page
    var drawDest = $(this.drawLocation);
    drawDest.innerHTML = "";
    drawDest.appendChild(this.root);
};

EventOrder.prototype.makeEventSummaryDom = function (parent, eventSummaryData) {
    var tmp = HTML.makeElement(parent, "div");
    tmp.addClass("eventSummary");
    tmp.style.height = eventSummaryData.form_blowout.competitor_count + "em";
    HTML.makeText(tmp, eventSummaryData.event_code + " (" + eventSummaryData.form_blowout.competitor_count + ")" );

    var self = this;
    tmp.addEvent("mouseover", function () {self.displayDetails(self.detailsBox, eventSummaryData)});
};

EventOrder.prototype.displayDetails = function (detailsBox, eventSummaryData) {
    var e = eventSummaryData;
    var fb = e.form_blowout;

    detailsBox.innerHTML = "";
    var title = HTML.makeElement(detailsBox, "span");
    title.addClass("detailsTitle");
    HTML.makeText(title, CMAT.formatLevelId(fb.level_id)
        + " " + CMAT.formatGenderId(fb.gender_id)
        + " " + CMAT.formatFormId(fb.form_id));

    var eventDetails = HTML.makeElement(detailsBox, "span");
    eventDetails.addClass("eventDetails");
    HTML.makeText(eventDetails, "Details:"
        + " [Ring, " + e.ring_id + "]"
        + " [Order, " + e.event_order + "]"
        + " [Done, " + e.is_done + "]");

    var dbDetails = HTML.makeElement(detailsBox, "span");
    dbDetails.addClass("databaseDetails");
    HTML.makeText(dbDetails, "Database Internals:"
        + " [event_id, " + e.event_id + "]"
        + " [form_blowout_id, " + fb.form_blowout_id + "]");

};
