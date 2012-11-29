test('test budget', function() {  
    var hash;
    //add a budget
    ok(hash = ydBudget.add('matthias.laug@gmail.com', 100));
    equal(ydBudget.get_amount(),100);
    //add same budget, with different value
    ok(hash = ydBudget.add('matthias.laug@gmail.com', 200));
    equal(ydBudget.get_amount(),200);
    equal($('.yd-open-amount').html(),'38,00 €');
    
    //remove some budgets
    ydBudget.remove(hash);
    equal(ydBudget.get_amount(),0);
    equal($('.yd-open-amount').html(),'40,00 €');
    
    //payment should not be available
    ok($('#paymentcontent').is(':visible'));
    
    //payment should be not visible any more
    ok(hash = ydBudget.add('priem@lieferando.de',4000));
    ok(!$('#paymentcontent').is(':visible'));
    equal($('.yd-open-amount').html(),'0,00 €');
    
    ydBudget.remove(hash);
    ok($('#paymentcontent').is(':visible'));
    
});


test('initial budet object', function() {   
    equal(0,ydBudget.get_amount(),'initial costs are zero');
});

test('add a budget', function(){
    ydBudget.add('matthias.laug@gmail.com',1000);
    equal(1000,ydBudget.get_amount(),'must be 10 Euros');
    equal('10,00 €',ydBudget.get_amount(true),'must be 10 Euros');
});

test('add markup, add budget', function(){
    var hash = sha1('matthias.laug@gmail.com');
    ydBudget.add('matthias.laug@gmail.com',1000);
    equal(1000,ydBudget.get_amount(),'must be 10 Euros');
    
    ydBudget.update_view();
    
    //first button to remove budget
    $('#yd-budget-erase-'+hash+'-1').simulate('click',{});
    equal(0,ydBudget.get_amount(),'must be 0 Euros');
});