$(document).ready(function(){

    /**
     * Trigger to open a lightbox
     * to tell the user that the satellite is closed
     * @author vpriem
     * @since 13.01.2011
     */
    $('#yd-trigger-satellite-closed').each(function(){
        openDialog(this.href, {
            width: 600,
            height: 380,
            modal: true,
            close: function(e, ui) {
                $(ui).dialog('destroy');
            },
            beforeClose: function (event, ui) {
                return false;
            }
        }, function(){
            $(':text:first', this)
            .emptytext()
            .plzAutocomplete(true)
            .focus()
            .caret(0);
        });
    });

    /**
     * Gallery
     * @author vpriem
     * @since 29.05.2011
     */
    if ($('#yd-satellite-gallery img').length) {
        $('#yd-satellite-gallery img').css({
            opacity: 0.0
        });
        var current = $('#yd-satellite-gallery img:first').css({
            opacity: 1.0
        });
        setInterval(function(){
            var next = current.next().length ? current.next() : $('#yd-satellite-gallery img:first');
            current
            .animate({
                opacity: 0.0
            }, 2000);
            current = next
            .animate({
                opacity: 1.0
            }, 2000);    
        }, 6000);
    }

    /**
     * A seo hack
     * @author vpriem
     * @since 18.01.2012
     */
    $("#satellite-go-away").click(function(){
        location.href = "http://" + $(this).html();
        this.blur();
        return false;
    });

    /**
     * Avanti Block Menu Hack
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 23.11.2011
     */
    
    if($('#yd-menu').length > 0){
        var avanti_idlist = ['37020','37315','98633','101711','101805','101827','101871','101904','101921','101953','101969','101987','101937','101887','101783','101767','101728','101679','101657','101627','101602','101581','101563','101546','91524'];
        for(var z = 0; z < avanti_idlist.length; z++){
            if($('#yd-category-'+avanti_idlist[z])){
                $('#yd-category-'+avanti_idlist[z]).addClass('avanti-block');
            }
        }
    }
    
    /**
     * Avanti Block Menu parse Size Class
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 25.11.2011
     */
    $.each($('.avanti-block .thead div'), function(b){
        if($('.avanti-block .thead div').eq(b).attr('id')){
            var nth_child = $('.avanti-block .thead div').eq(b).attr('class').split('th ')[1];
            $('<span class="avanti-mealsize">'+$('.avanti-block .thead div').eq(b).text()+'</span>').insertBefore($('.avanti-block .table .tr .'+nth_child+' .add-to-card'));
        }
    });
    
    //Premium Helper
    $('.yd-menu-modern .avanti-block .table .tr .td div:first-child').unbind();
    $('.premium-finish #yd-form-finish ul').eq(4).addClass('non-compulsory');
    $('.avanti .yd-deliver-time-input .yd-form-left img').attr('src', '/media/images/satellites/menu/AVANTI_clock.png');
    $('.avanti .yd-deliver-time-input .yd-form-right img').attr('src', '/media/images/satellites/menu/AVANTI_calendar.png');
    
    
    /**
     * Category Helper (if there are to many categrorys the menu position change)
     * 
     * @author Toni Meuschke <meuschke@lieferando.de>
     * @since 30.11.2011
     */
    if($('.yd-menu-nav1').height() > 170){
        $('#yd-menu').css('margin-top', $('.yd-menu-nav1').height()-160);
    }
    
});