/**
 * validated 10.02.2010
 */

$(document).ready(function(){
    Date.format = 'dd.mm.yyyy';
    var httpaddress = $("#httproot").val();
    $('.startTimeS, .endTimeS').datepicker({
		startDate: '01/01/2009',
		endDate: '',
        clickInput: true,
        createButton: false
    });
    $(".startTimeS").bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(".endTimeS").dpSetStartDate(d.addDays(0).asString());
            }
        }
    );
    $(".endTimeS").bind(
        'dpClosed',
        function(e, selectedDates)
        {
            var d = selectedDates[0];
            if (d) {
                d = new Date(d);
                $(".startTimeS").dpSetEndDate(d.addDays(0).asString());
            }
        }
    );

	// refresh charts
    $('.refreshChart').click(function() {

        var form = $(this).parent().parent();
        var container = form.parent().parent();
        var width = container.width();

        container.find('.img').html('').image(
            httpaddress +
            'request/getchart/class/' +
            form.find('select[name=class]').val() +
            '/width/' +
            width +
            '/start/' +
            form.find('input[name=startTimeD]').val() + '-' + form.find('input[name=startTimeT]').val() +
            '/end/' +
            form.find('input[name=endTimeD]').val() + '-' + form.find('input[name=endTimeT]').val(),

            function() {
                // lade animation
            }
        );
        return false;
    });

});

$.fn.image = function(src, f){
    return this.each(function(){
        var i = new Image();
        i.src = src;
        i.onload = f;
        this.appendChild(i);
    });
};