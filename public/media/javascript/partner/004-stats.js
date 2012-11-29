/**
 * @author dhahn
 */
$('document').ready(function() {
    
    function addMoneySignTolabel(label) {
        label = label + "";
            
        if (label.indexOf('KW') == -1) {
            label  += " " + CURRENCY;
        }
        return label;
    }
    
    
    var salesStats = null;   
    var orderStats = null; 
    function visualizeData(key){    
        salesStats = $("#vs-stats-table-sales-" + key).visualize({
            type: 'line', 
            bottomValue: 0, 
            barMargin: 10, 
            labelFilter: addMoneySignTolabel
        });

        orderStats = $("#vs-stats-table-orders-" + key).visualize({
            type: 'line', 
            bottomValue: 0, 
            barMargin: 10
        });
    }
    
    //init
    visualizeData(1);

    $('.visualize-key').html('<li class="partner-calendar-week">'+$.lang('partner-calendar-week')+'</li>');
    $('.yd-tab-stats ').live('click', function(){     
        $('.yd-tab-stats').removeClass('active');
        $('.yd-stats-data').hide();
        $('.stats-scrollbox').hide();
        $('.stats-staticbox').hide();
        
        var key = $(this).attr('data-tab');
        $('.yd-tab-stats-' + key).addClass('active');
        $('#yd-stats-sales-table-' + key).show();
        $('#yd-stats-sales-table-' + key + '-static').show();
        $('#yd-stats-orders-table-' + key).show();
        $('#yd-stats-orders-table-' + key + '-static').show();
        $('.stats-scrollbox-' + key).show();
        $('.stats-staticbox-' + key).show();
        
        salesStats.remove();
        orderStats.remove();
        visualizeData(key);
        
    });
    
});
