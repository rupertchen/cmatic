
function addEvent(node, event, fn, prop) {
    if (node.addEventListener) {
        node.addEventListener(event, fn, prop);
    } else if (node.attachEvent) {
        node.attachEvent("on" + event, fn);
    } else {
        node["on" + event] = fn;
    }
}
