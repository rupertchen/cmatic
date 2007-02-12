HTML = {
    makeText: function (p, str) {
        var e = document.createTextNode(str);
        if (p) {
            p.appendChild(e);
        }
        return e;
    },

    makeElement: function (p, tag, attribs) {
        var e = document.createElement(tag);
        if (p) {
            p.appendChild(e);
        }
        for (var i in attribs) {
            e.setAttribute(i, attribs[i]);
        }
        return e;
    },

    makeTable: function (p) {
        return HTML.makeElement(p, "table", {"border":"0", "cellpadding":"0", "cellspacing":"0"});
    },

    makeLabel: function (p, idFor, label) {
        var l = HTML.makeElement(p, "label", {"for":idFor});
        HTML.makeText(l, label);
        return l;
    },

    makeHidden: function(p, name, value) {
        return HTML.makeElement(p, "input", {"type":"hidden", "name":name, "value":value});
    },

    makeCheckbox: function (p, id, name, value, label) {
        var ret = HTML.makeElement(p, "span");
        ret.cb_label = HTML.makeLabel(ret, id, label);
        ret.cb = HTML.makeElement(ret, "input", {"type":"checkbox", "id":id, "name":name, "label":label, "value":value});
        return ret;
    },

    makeSelect: function (p, id, name, values, defaultValue, label) {
        var ret = HTML.makeElement(p, "span");

        ret.s_l = HTML.makeLabel(ret, id, label);

        ret.s = HTML.makeElement(ret, "select", {"id":id, "name":name});
        for (var i in values) {
            var attribs = {"value":i};
            var o = HTML.makeElement(ret.s, "option", attribs);
            if (defaultValue == i) {
                o.selected = true;
            }
            HTML.makeText(o, values[i]);
        }

        return ret;
    }
};
