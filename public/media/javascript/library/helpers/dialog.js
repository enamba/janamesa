/**
 * Open a jQuery dialog box
 * @author vpriem
 * @since 08.02.2011
 * @param string url
 * @param object options
 */
var openedUrl = {}; // client side cache
function openDialog (url, options, complete) {
    if (openedUrl[url] === undefined) {
        $('#dialog')
            .html('<div class="yd-lightbox-loading">&nbsp;</div>')
            .dialog(options)
            .load(url, function (resp) {
                openedUrl[url] = resp;
                $('.yd-lightbox-content-container', this).scrollTop(0);
                if (complete !== undefined) {
                    complete.call(this);
                }
               //beautyfication of be bubble boxes
               $('.be-dialogs .yd-grid-box').before('<br /><br />');                
            });
        return;
    }
    
    $('#dialog')
        .html(openedUrl[url])
        .dialog(options).each(function(){
                if (complete !== undefined) {
                    complete.call(this);                                        
                }                          
        });
}

/**
 * Close the jQuery dialog box
 * @author vpriem
 * @since 09.02.2011
 */
function closeDialog(destroy){
    
    var $d = $('.yd-dialog-parent')    
        .dialog('close');
        
    if (destroy === true) {
        $d.dialog('destroy');    
    }
}
