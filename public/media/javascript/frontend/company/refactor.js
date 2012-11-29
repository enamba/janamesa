/**
 * validated 10.02.2010
 */

$(document).ready(function(){

    $('#yd-loading-page').hide();
	
    // http document root of website
    var httpaddress = $("#httproot").val();

    // filter billings
    $('#startTime-compAdmin, #endTime-compAdmin').live('change', function(){
        var from = $('#startTime-compAdmin').val();
        var until = $('#endTime-compAdmin').val();

    // anzeigen alle from oder until zwischen start und end
    // class-/id-bereich ansprechen möglich?????
    //$('.yd-billings').fadeOut();
        
    });

    // even and odd rows for tables
    function evenodd(){
        $("tr:even").addClass("even");
        $("tr:odd").addClass("odd");
    }
    evenodd();
	
    // ############### FORMS ########################
    $(".checkfield").each(function() {
        if($(this).val()==""){
            $(this).next().hide();
        }else{
            $(this).next().fadeIn();
        }
    });
    $(".checkfield").keyup(function() {
        if($(this).val()==""){
            $(this).next().hide();
        }else{
            $(this).next().fadeIn();
        }
    });    


    function checkValidBudgetTimes(elem){

        var from = elem.parent().find('.yd-budgettime-from').val();
        var until = elem.parent().find('.yd-budgettime-until').val();

        if( (from >= until) || !from.match(/^([0-1]?[0-9]|[2][0-3]):([0-5][0-9])$/) || !until.match(/^([0-1]?[0-9]|[2][0-3]):([0-5][0-9])$/) ){
            elem.
            parent().parent().
            find('.yd-budgettime').
            css('border','1px solid red');
            elem.addClass('invalid');
        }else{
            elem.
            parent().parent().
            find('.yd-budgettime').
            css('border','1px solid #D5D5D5');
            elem.removeClass('invalid');
        }
    }

    $('.yd-budgettime-amount').blur(function(){
        var val = $(this).val();
        if( val != '0,00' && !val.match(/((0)+(\,[1-9](\d)?))|((0)+(\,(\d)[1-9]+))|(([1-9]+(0)?)+(,\d+)?)|(([1-9]+(0)?)+(\,\d+)?)/) ){
            $(this).css('border','1px solid red').addClass('invalid');
        }else{
            $(this).css('border','1px solid #D5D5D5').removeClass('invalid');
        }
    });

    if( $('#yd-company-budget').length > 0 ){
        $('.yd-budgettime').each(function(){
            checkValidBudgetTimes($(this));
        });
    }
    
    $('.yd-budgettime').blur(function(){
        checkValidBudgetTimes($(this));
    });

    $('#yd-company-budget-submit').live('click',function(){
        $('.yd-budgettime').each(function(){
            checkValidBudgetTimes($(this));
        });
        if( $('.invalid'). length > 0 ){
            notification('error', 'Bitte überprüfen Sie die Budgetzeiten.');
            return false;
        }
        $('#yd-company-budget').submit();
    });


    $('.yd-removebudgettime').live('click', function(){
        var el = $(this).parent().parent();
        if( !$(this).hasClass('persistent') ){
            notification("success", $.lang("bugdet-notification"));
            el.fadeOut(function() {
                $(this).remove();
            });
            return;
        }
        
        var $obj = $(this);
        var budgetTimeId = $obj.attr('id').split('-')[2];
        var budgetId = $('#budgetId').val();
        
        $.ajax({
            type: "POST",
            url: "/request_company/removebudgettime/",
            data: ({
                'budgetId'     : budgetId,
                'budgetTimeId' : budgetTimeId
            }),
            success : function(data){
                
                if( data == 1 ){
                    notification("success", $.lang("bugdet-notification"));
                    el.fadeOut(function() {
                        $(this).remove();
                    });
                }else{
                    notification('error', 'Konnte Budgetzeit nicht entfernen');
                }
            }
        });
        return false;
    });
	
    $(".yd-addbudgettime").click(function() {
        var el = $(this).parent().parent();
        el.clone(true).insertAfter(el).fadeOut(0, function() {
            $(this).find('.persistent').removeClass('persistent');
            var day = el.find('.day').val();
            var budgettimekey = el.find('.budgettimekey').val();
            //alert(budgettimekey);
            var $objFrom = $(this).find('.yd-budgettime-from');
            $objFrom.attr('name','new['+day+']['+(parseInt(budgettimekey)+1)+'][from]');
            $objFrom.attr('id','yd-budgettime-from-'+day+'-'+(parseInt(budgettimekey)+1));
            var $objUntil = $(this).find('.yd-budgettime-until');
            $objUntil.attr('name','new['+day+']['+(parseInt(budgettimekey)+1)+'][until]');
            $objUntil.attr('id','yd-budgettime-until-'+day+'-'+(parseInt(budgettimekey)+1));
            $(this).find('.yd-budgettime-amount').attr('name','new['+day+']['+(parseInt(budgettimekey)+1)+'][amount]');
            $(this).find('.budgettimekey').val((parseInt(budgettimekey)+1));
        }).fadeIn();
        return false;
    });


    
    $("#moveright").click(function() {
        $("#leftlist li input:checked").attr("checked",false).parent().fadeOut("fast", function() {
            $(this).clone().hide().appendTo("#rightlist ul").fadeIn(function(el) {
                var idd = $(this).attr("id").split("-");              
                $(this).find("input[type=hidden]").val(idd[1]);
                
            });
			
        }).remove();
        return false;
    });
	
    $("#moveleft").click(function() {
        $("#rightlist li input:checked").attr("checked",false).parent().fadeOut("fast", function() {
            $(this).clone().hide().appendTo("#leftlist ul").fadeIn(function() {
                $(this).find("input[type=hidden]").val("");
            });
        }).remove();
        return false;
    });
	
    var toggleLeft = true;
    $("#selectAllLeft").click(function() {
        $("#leftlist li input").attr("checked",toggleLeft);
        toggleLeft = !toggleLeft;
        return false;
    });
    
    var toggleRight = true;
    $("#selectAllRight").click(function() {
        $("#rightlist li input").attr("checked",toggleRight);
        toggleRight = !toggleRight;
        return false;
    });




    $("#moveright2").click(function() {
        $("#leftlist2 li input:checked").attr("checked",false).parent().fadeOut("fast", function() {
            $(this).clone().hide().appendTo("#rightlist2 ul").fadeIn(function(el) {
                var idd = $(this).attr("id").split("-");
                $(this).find("input:hidden").val(idd[1]);
            });

        }).remove();
        return false;
    });

    $("#moveleft2").click(function() {
        $("#rightlist2 li input:checked").attr("checked",false).parent().fadeOut("fast", function() {
            $(this).clone().hide().appendTo("#leftlist2 ul").fadeIn(function() {
                $(this).find("input:hidden").val("");
            });
        }).remove();
        return false;
    });

    var toggle = true;
    $("#selectAll2").click(function() {
        $("#leftlist2 li input").attr("checked",toggle);
        toggle = !toggle;
        return false;
    });

    //add a new project number to form
    var count = 0;
    $('#newProjectNumber').live('click',function(){

        $('#newInput').append(
            "<tr id='newnumber-"+count+"'><td><input type='text' name='newnumber-"+count+"' value='' /><td><input type='submit' value='entfernen' class='removenumber' id='removenumber-"+count+"' /></td></tr>"
            );
        count++;
        return false;

    });

    //remove a number from form which has not been created yet
    $('.removenumber').live('click',function(){

        var id = $(this).attr('id').split('-')[1];
        $('#newnumber-'+id).remove();
        return false;

    });


    $(".yd-link-deleteProjectNr").live('click',
        function(){
            var id = $(this).attr('id').split('-')[2];
            $.ajax({
                url:'/request_company/removeproject/id/'+id
            });
            notification('success','Projektcode erfolgreich entfernt');
            $('#yd-pn-' + id).remove();
            redirect('/');
            return false;
        }
        );


    $(".yd-link-delete-costcenter").live('click',
        function(){
            var id = $(this).attr('id').split('-')[2];
            $.ajax({
                url:'/request_company/removecostcenter/id/'+id
            });
            notification('success','Kostenstelle erfolgreich entfernt');
            $('#yd-td-pn-' + id).remove();
            return false;
        }
        );
    
    $('#yd-add-caddress').live('click',function(){
        location.href = '/company/address';
    });
        

    /**
     * Reset password of one employee
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2011
     */
    $('#yd-resetEmployeePass').live('click',function(){
        var custId = $('#emplId').val();
        var realy = confirm('Soll das Passwort wirklich zurückgesetzt werden?');
        if(realy){
            notification('info', 'Bitte warten ...',true);
            $.ajax({
                url:'/request_company/newpassemployee/id/'+custId,
                dataType: "json",
                success: function(json){
                    $('.closeNotification').click();
                    if(json.result){
                        notification('success', json.msg);
                    }else{
                        notification('error', json.msg, true);
                    }
                }
            });
                
        }
        return false;
           
    }
    );



    /**
     * Reset all passwords of members of one budegt-group
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2011
     */
    $('#yd-resetBudgetPasses').live('click', function(){
        var budgetId = $('#yd-resetBudgetPasses-id').val();
        var realy = confirm(this.title);
        if(realy){
            notification('info', 'Bitte warten ...',true);
            $.ajax({
                url:'/request_company/newpassesbudget/id/'+budgetId,
                dataType: "json",
                success: function(json){
                    $('.closeNotification').click();
                    if(json.result){
                        notification('success', json.msg);
                    }else{
                        notification('error', json.msg, true);
                    }
                }
            });
        }
        return false;
    });

        
    /**
     * Reset all passwords of members of one company
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.07.2011
     */
    $('#yd-resetCompanyPasses').live('click', function(){
        var compId = $('#yd-resetCompanyPasses-id').val();
        var realy = confirm(this.title);
        
        if(realy){
            notification('info', 'Bitte warten ...',true);
            $.ajax({
                url:'/request_company/newpassescomp/id/'+compId,
                dataType: "json",
                success: function(json){
                    $('.closeNotification').click();
                    if(json.result){
                        notification('success', json.msg);
                    }else{
                        notification('error', json.msg, true);
                    }
                }
            });
                
        }
        return false;
    });

    if($('#yd-company-need-code-check').length > 0){
        var variant = $('#yd-company-code-variant').val();
        if(variant == 0){
            $('#yd-company-need-code-check').hide();
        }
    }

    $('#yd-company-code-variant').live('change',function(){
        var variant = $('#yd-company-code-variant').val();
        if(variant == 0){
            $('#yd-company-need-code-check').hide();
        }else{
            $('#yd-company-need-code-check').show();
        }
    });
        

    $('#yd-company-save-code').live('click',function(){
        var codeVariant = $('#yd-company-code-variant').val();
        var needCode = $('#yd-company-need-code').attr('checked');
        if(codeVariant == '0'){
            needCode = false;
        }
        $.ajax({
            type: "POST",
            url:'/request_company/code',
            data:({
                'codeVariant'   : codeVariant,
                'needCode'      : needCode
            })
        });
        notification('success','Projektcodeeinstellungen wurden erfolgreich geändert');
    });
    
    /**
     * Accordion Slide-Menü
     */
    if($('#main-nav').length > 0){
        $("#main-nav li ul").hide();
        $("#main-nav li a.current").parent().find("ul").slideToggle("slow");
        $("#main-nav li a.nav-top-item").click(function(){
            $(this).parent().siblings().find("ul").slideUp("normal");
            $(this).next().slideToggle("normal");
            return false;
        });
        $("#main-nav li a.no-submenu").click(function(){
            window.location.href=this.href;
            return false;
        });
        $("#main-nav li .nav-top-item").hover(function(){
            $(this).stop().animate({
                paddingRight:"25px"
            },200);
        },function(){
            $(this).stop().animate({
                paddingRight:"15px"
            });
        });
    }

}); // end of document ready