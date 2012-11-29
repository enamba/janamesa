/**
 * @author mlaug
 */
function prepareInput(params, form, name) {
    if (typeof(params) == 'string' || typeof(params) == 'number') {
        var hiddenField = document.createElement("input");        
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", name);
        hiddenField.setAttribute("value", params);       
        form.appendChild(hiddenField);
    }
    else if (typeof(params) == 'object') {
        for (var key in params) {
            if (name) {
                prepareInput(params[key], form, name  + "[" + key + "]");
            }
            else{
                prepareInput(params[key], form, key);
            }
        }
    }
}

/**
 * @author mlaug
 */
function post_to_url(path, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    prepareInput(params, form);
    document.body.appendChild(form);
    form.submit();
}
