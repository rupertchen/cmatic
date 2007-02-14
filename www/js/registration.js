/**
 * Create a listing of competitors
 */
function CompetitorList (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.d = data;
    this.root = null;
};


CompetitorList.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
}


CompetitorList.prototype.makeDom = function () {
    var ret = HTML.makeElement(null, "div");

    // Header
    var header = HTML.makeElement(ret, "h1");
    HTML.makeText(header, "Registered Competitor List (" + this.d.length + " total)");

    // Table
    var table = HTML.makeTable(ret);
    table.addClass("competitorRegistrationList");

    // Headers
    var thead = document.createElement("thead");
    table.appendChild(thead);
    var headers = ["Name", "Id", "Birthdate", "Age Group", "Sex", "Divison", "#Events", "Events"];
    for (var i = 0; i < headers.length; i++) {
        var th = HTML.makeElement(table, "th", {"scope": "col"});
        HTML.makeText(th, headers[i]);
    }

    // Competitor Row
    var tbody = HTML.makeElement(table, "tbody");
    for (var i = 0; i < this.d.length; i++) {
        var tr = HTML.makeElement(tbody, "tr");
        tr.addClass((0 == i%2) ? "evenRow" : "oddRow");

        var c = this.d[i];
        var cells = new Array(8);
        cells[0] = HTML.makeElement(null, "span");
        cells[1] = HTML.makeElement(null, "span");
        cells[2] = HTML.makeText(null, c.birthdate);
        cells[3] = HTML.makeText(null, CMAT.formatAgeGroupId(c.age_group_id));
        cells[4] = HTML.makeText(null, CMAT.formatGenderId(c.gender_id));
        cells[5] = HTML.makeText(null, CMAT.formatLevelId(c.level_id));
        cells[6] = HTML.makeText(null, c.registration.length);
        cells[7] = HTML.makeElement(null, "span");

        // Name
        HTML.makeText(cells[0], c.last_name + ", " + c.first_name);
        cells[0].addClass("competitorName");

        // Id
        var editLink = HTML.makeElement(cells[1], "a", {"href":"competitor_registration.php?c=" + c.competitor_id});
        HTML.makeText(editLink, CMAT.formatCompetitorId(c.competitor_id));

        // Forms
        for (var j = 0; j < c.registration.length; j++) {
            var f = HTML.makeElement(cells[7], "span");
            var r = c.registration[j];
            HTML.makeText(f, CMAT.formatFormId(r.form_id));
            f.addClass("registeredForm");
            f.addClass(('t' == r.is_paid) ? "formIsPaid" : "formNotPaid");
        }

        // Assemble all
        for (var j = 0; j < cells.length; j++) {
            var td = HTML.makeElement(tr, "td");
            td.addClass("competitorRegistrationTableCell");
            td.appendChild(cells[j]);
        }
    }

    // nothing fancy for now
    this.root = ret;

    // Draw to page
    var drawDest = document.getElementById(this.drawLocation);
    drawDest.innerHtml = "";
    drawDest.appendChild(this.root);
};

CompetitorList.prototype.extractFormsForDisplay = function (r) {
    var events = new Array(r.length);
    for (var i = 0; i < r.length; i++) {
        events[i] = CMAT.formatFormId(r[i].form_id);
        if ("f" == r[i].is_paid) {
            events[i] = "*" + events[i];
        }
    }
    return events.sort();
};


/**
 * Registration UI for a single form.
 */
function FormRegistration (drawLocation, data) {
    this.drawLocation = drawLocation;
    this.root = null;
    this.form = null;
    this.isPaid = null;

    var self = this;
    this.handleRowHover = function () {
        self.root.toggleClass("formHoverOn");
    };
    this.handleRegister = function () {
        if (self.form.cb.checked) {
            self.isPaid.s.disabled = false;
	    self.handlePaidColor();
        } else {
            self.isPaid.s.disabled = true;
	    self.root.removeClass("formIsPaid");
	    self.root.removeClass("formNotPaid");
        }
    };
    this.handlePaidColor = function () {
        if (self.isPaid.s.value == "f") {
	    self.root.addClass("formNotPaid");
	    self.root.removeClass("formIsPaid");
        } else {
	    self.root.addClass("formIsPaid");
	    self.root.removeClass("formNotPaid");
        }
    };

    this.d = (data) ? this.setData(data) : null;
};

FormRegistration.prototype.repaint = function () {
    this.handleRegister();
};

FormRegistration.prototype.setData = function (data) {
    this.d = data;
    this.makeDom();
};

FormRegistration.prototype.makeDom = function () {
    this.root = HTML.makeElement(null, "tr");
    this.root.addEvent("mouseover", this.handleRowHover);
    this.root.addEvent("mouseout", this.handleRowHover);

    var td1 = HTML.makeElement(this.root, "td");
    td1.setAttribute("class", "formRegistrationName");
    var td2 = HTML.makeElement(this.root, "td");
    td2.setAttribute("class", "formRegistrationInput");

    var regCheckboxId = "reg_" + this.d.form_id;
    HTML.makeLabel(td1, regCheckboxId, CMAT.formatFormId(this.d.form_id));

    this.form = HTML.makeCheckbox(td2, regCheckboxId, "reg[]", this.d.form_id, "Register:");
    this.form.cb.addEvent("change", this.handleRegister);
    if (this.d.registration_id) {
        this.form.cb.checked = true;
        HTML.makeHidden(td2, "previousReg[]", this.d.form_id);
    }

    var defaultValue = (this.d.is_paid) ? this.d.is_paid : "f";
    var isPaidName = "isPaid_" + this.d.form_id;
    this.isPaid = HTML.makeSelect(td2, isPaidName, isPaidName, {"t":"Yes", "f":"No"}, defaultValue, "Paid:");
    this.isPaid.s.addEvent("change", this.handlePaidColor);

    // Extra
    this.repaint();


    // Draw to page
    var drawDest = $(this.drawLocation);
    drawDest.innerHtml = "";
    drawDest.appendChild(this.root);
};
