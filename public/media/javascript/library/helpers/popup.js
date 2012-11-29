/**
 * Show a centered popup
 * @author vpriem
 */
function popup (url, name, w, h, r, s) {
    if (r === undefined) {
        r = "no";
    }
    if (s === undefined) {
        s = "yes";
    }
    var x = (screen.availWidth - w) / 2;
    var y = (screen.availHeight - h) / 2;
    var pop = window.open(url, name, "left=" + x + ",top=" + y + ",width=" + w + ",height=" + h + ",resizable=" + r + ",scrollbars=" + s + ",status=no,directories=no,toolbar=no,location=no,menubar=no");
    pop.moveTo(x, y);
    pop.resizeTo(w, h);
    pop.focus();
    return false;
}