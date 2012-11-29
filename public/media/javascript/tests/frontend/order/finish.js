/**
 * test case for the menu site
 */
test('test minamount without discount', function() {
    var ydMeal = getMeal(); 
    ydOrder.add_meal(ydMeal);
    ydOrder.set_min_amount(ydOrder.calculate_min_amount() - 100);  
    ok(ydOrder.is_minamount_reached());
    
    var discount = new YdDiscount();
    discount.min_amount = ydOrder.calculate_min_amount() + 100;
    discount.kind = 1;
    discount.value = 500;
    
    ydOrder.add_discount(discount);
    ok(!ydOrder.is_minamount_reached());
    ydOrder.remove_discount();
    ok(ydOrder.is_minamount_reached());
    discount.min_amount = ydOrder.calculate_min_amount() - 200;
    ydOrder.add_discount(discount);
    ok(ydOrder.is_minamount_reached());
    
});