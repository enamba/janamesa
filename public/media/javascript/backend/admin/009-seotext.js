$(document).ready(function(){
    
    /**
     * @author Jens Naie <naie@lieferando.de>
     * @since 20.07.2012
     */
    $('.seo-pupup-link').live('click', function(){
        var img = $(this).children().get(0);
        var id = parseInt($(this).parent().children().get(0).innerHTML);
        var controllerAction = location.href.replace(/.*administration_/, '')
        controllerAction = controllerAction.replace(/\/.*/, '')
        controllerAction += '-seo-text';
        var url= '/request_administration/' + controllerAction + '/id/' + id;
        
        openDialog(url, {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            }
        }, function() {
            $('textarea', this).tinymce({
                script_url : '/media/javascript/external/tiny_mce/tiny_mce.js',
                theme: 'advanced',
                mode : 'textarea',
                theme_advanced_buttons1 : "bold,italic,|,formath2,formath3,formath4,|,bullist,|,link,unlink,|,image,|,code",
                theme_advanced_buttons2 : null,
                theme_advanced_buttons3 : null,
                theme_advanced_toolbar_align : "left",
                theme_advanced_toolbar_location : "top",
                setup : function(ed){
                    ed.addButton('formath2', {
                        title : 'Make H2',
                        image : '/media/images/yd-backend/tinymce_h2.png',
                        onclick : function(){ed.execCommand('FormatBlock', false, 'h2');}
                    });
                    ed.addButton('formath3', {
                        title : 'Make H3',
                        image : '/media/images/yd-backend/tinymce_h3.png',
                        onclick : function(){ed.execCommand('FormatBlock', false, 'h3');}
                    });
                    ed.addButton('formath4', {
                        title : 'Make H4',
                        image : '/media/images/yd-backend/tinymce_h4.png',
                        onclick : function(){ed.execCommand('FormatBlock', false, 'h4');}
                    });
                }
            });
            $("form", this).ajaxForm({
                beforeSubmit : function(arr, $form, options){
                    $.each(arr, function() {
                        if(this.name == 'seoText') {
                            this.value = $('textarea').tinymce().getContent();
                        }
                    });
                    console.log(arr);
                    $(":submit", $form).hide();
                },
                success: function (data, status, xhr, $form) {
                    if(data.success) {
                        if(data.seoText) {
                            img.src = img.src.replace(/no/, 'yes');
                        } else {
                            img.src = img.src.replace(/yes/, 'no');
                        }
                        delete openedUrl[url];
                        $('#dialog').dialog('destroy');
                    } else if(data.message){
                        $('.be-dialogs-body .item-warning').show().html(data.message);
                    }
                    $(":submit", $form).show();
                },
                error: function(request, error, errorMessage) {
                    $(":submit").show();
                },
                dataType: 'json'
            });
             
        });
        
        return false;
    });
});