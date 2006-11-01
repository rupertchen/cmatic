/****************************************
 * Containing JS objects of this page
 */
document.elements = {};


/****************************************
 * Phone number field logic
 */
function PhoneNumberField (id) {
    // Fields
    this.id = id;
    this.field = document.getElementById(id);

    // Methods
    this.handleOnChange = function () {
        this.value = formatPhoneNumber(this.value);
    }

    this.init();
    document.elements["PhoneNumberField_" + id] = this;
}

PhoneNumberField.prototype.init = function() {
    addEvent(this.field, "change", this.handleOnChange, true);
};


/****************************************
 * Lookup field controls
 */
function LookupField(id, obj) {
    this.lookupId = id;
    this.obj = obj;
    this.idElem = document.getElementById(id + "_id");
    this.nameElem = document.getElementById(id + "_name");
    this.displayElem = document.getElementById(id + "_display");
    this.switchElem = document.getElementById(id + "_switch");
    this.clearElem = document.getElementById(id + "_clear");
    this.lookupElem = document.getElementById(id + "_lookup");

    this.isLookupOpen = this.lookupElem.style.display != "none";

    var self = this;
    this.clickSwitch = function() {
        if (self.isLookupOpen) {
            self.closeLookup();
        } else {
            self.openLookup();
        }
    }
    this.clickClear = function() {
        self.updateValues('00000000000000000000', '');
    }
    
    this.init();
    document.elements["LookupField_" + id] = this;
}

LookupField.prototype.openLookup = function() {
    this.lookupElem.src = "d.php?t=LookupPopup&orig=" + this.lookupId + "&obj=" + this.obj;
    this.lookupElem.style.display = "block";
    this.lookupElem.focus();
    this.isLookupOpen = true;
};

LookupField.prototype.closeLookup = function() {
    this.lookupElem.src = "blank.html";
    this.lookupElem.style.display = "none";
    this.isLookupOpen = false;
};

LookupField.prototype.updateValues = function(id, name) {
    this.idElem.value = id;
    this.nameElem.value = name;
    this.displayElem.innerHTML = ('' != name) ? name : '&hellip;';
};

LookupField.prototype.init = function () {
    addEvent(this.switchElem, "click", this.clickSwitch, false);
    addEvent(this.clearElem, "click", this.clickClear, false);
};
