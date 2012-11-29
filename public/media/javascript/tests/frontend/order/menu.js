/**
 * test case for the menu site
 */
test('initial order object', function() {   
    equal(0, ydOrder.calculate_amount(), 'initial costs are zero');
    equal(0, ydOrder.get_discount(), 'initial discount are zero');
    equal('0,00 €', ydOrder.calculate_amount(true), 'inital formated costs are 0,00 €');
    equal('0,00 €', ydOrder.get_discount(true), 'inital formated discount are 0,00 €');  
});

test('check minamount', function(){
    var ydMeal = getMeal();
    ydMeal.exMinCost = true;
    var hash = ydOrder.add_meal(ydMeal);
    equal(0,ydOrder.calculate_min_amount(false));
    ydMeal.exMinCost = false;
    notEqual(0,ydOrder.calculate_min_amount(false));
    ydOrder.meals = {};
});

test('add meal to order', function() {
    var ydMeal = getMeal();
    var hash = ydOrder.add_meal(ydMeal); 
    //adding, adding and removing
    equal(3400, ydOrder.calculate_amount(), 'one meal with the cost of 20');
    equal(hash, ydOrder.add_meal(ydMeal));
    equal(6800, ydOrder.calculate_amount(), 'two meals with the cost of 20');
    ydOrder.decrease_meal(hash);
    equal(5100, ydOrder.calculate_amount(), 'one meal with the cost of 20');
    ydOrder.increase_meal(hash);
    equal(6800, ydOrder.calculate_amount(), 'two meals with the cost of 20');
    ydOrder.remove_meal(hash);
    equal(0, ydOrder.calculate_amount(), 'no more meals');
});

test('call meal and to to card via ajax', function() {
   stop();
   $('.add-to-card').simulate('click',{});
   //give the ajax some time to wait
   setTimeout(function(){
       start();
       $('.yd-add-to-card').simulate('click', {});
       equal(ydOrder.calculate_amount(), 250);
   }, 2000);
});

test('check for yd-city cookie', function() {
    ok($.cookie('yd-ctiy') == null);
});

test('no deliver cost above certian amount',function(){   
    ydOrder.no_deliver_cost_above = ydOrder.calculate_amount() + 10000;
    ydMeal = getMeal();
    equal(ydOrder.deliver_cost,ydOrder.get_deliver_cost());
    ydOrder.add_meal(ydMeal);
    ydOrder.add_meal(ydMeal);
    ydOrder.add_meal(ydMeal);
    ydOrder.add_meal(ydMeal);
    ydOrder.add_meal(ydMeal);
    ydOrder.add_meal(ydMeal);
    equal(0,ydOrder.get_deliver_cost());
});