/**
 * Go to the last visted page
 * but stay on the same domain
 * @author vpriem
 * @since 08.05.2012
 */
historyBack = function () {

    if (typeof useNoHistoryBack !== 'undefined' && useNoHistoryBack) {
        window.close(); //if this is a popup it will be closed
        return;
    }

    if (history.length > 1 && document.referrer.length && document.referrer.split('/')[2] == window.location.host) {
        history.back();
    }
    else {
        location.href = "/";
    }
}