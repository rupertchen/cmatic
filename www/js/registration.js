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

    // Table
    var table = HTML.makeTable(ret);

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

        var c = this.d[i];
        var cells = [
            c.last_name + ", " + c.first_name,
            CMAT.formatCompetitorId(c.competitor_id),
            c.birthdate,
            CMAT.formatAgeGroupId(c.age_group_id),
            CMAT.formatGenderId(c.gender_id),
            CMAT.formatLevelId(c.level_id),
            c.registration.length,
            this.extractFormsForDisplay(c.registration).join(", ")];
        for (var j = 0; j < cells.length; j++) {
            var td = HTML.makeElement(tr, "td");
            HTML.makeText(td, cells[j]);
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
    this.handleRegister = function () {
        if (self.form.cb.checked) {
            self.isPaid.s.disabled = false;
        } else {
            self.isPaid.s.disabled = true;
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

    var td1 = HTML.makeElement(this.root, "td");
    td1.setAttribute("class", "formRegistrationName");
    var td2 = HTML.makeElement(this.root, "td");
    td2.setAttribute("class", "formRegistrationInput");

    HTML.makeText(td1, CMAT.formatFormId(this.d.form_id));

    this.form = HTML.makeCheckbox(td2, "reg_"+this.d.form_id, "reg[]", this.d.form_id, "Register:");
    this.form.cb.addEvent("change", this.handleRegister);
    if (this.d.registration_id) {
        this.form.cb.checked = true;
        HTML.makeHidden(td2, "previousReg[]", this.d.form_id);
    }

    var defaultValue = (this.d.is_paid) ? this.d.is_paid : "f";
    var isPaidName = "isPaid_" + this.d.form_id;
    this.isPaid = HTML.makeSelect(td2, isPaidName, isPaidName, {"t":"Yes", "f":"No"}, defaultValue, "Is Paid:");

    // Extra
    this.repaint();


    // Draw to page
    var drawDest = $(this.drawLocation);
    drawDest.innerHtml = "";
    drawDest.appendChild(this.root);
};
