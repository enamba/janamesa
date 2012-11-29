/**
 * @author vpriem
 * @since 25.07.2011
 */
test('ydCustomer setter / getter', function() {
    ok(ydCustomer);
    
    $.cookie('yd-customer', "bla", {
        path: '/'
    });
    ydCustomer.read();
    
    ydCustomer.setPrename("samson");
    equal(ydCustomer.getPrename(), "samson", 'set/get prename');

    ydCustomer.setName("tiffy");
    equal(ydCustomer.getName(), "tiffy", 'set/get name');
    equal(ydCustomer.getFullname(), "samson tiffy", 'set/get name');
    
    ydCustomer.setCompany("yd. yourdelivery GmbH");
    equal(ydCustomer.getCompany(), "yd. yourdelivery GmbH", 'set/get company');
    
    equal(ydCustomer.isAdmin(), 0, 'set/get admin');
    ydCustomer.setAdmin(true);
    equal(ydCustomer.isAdmin(), 1, 'set/get admin');
});

test('ydCustomer read', function() {
    ok(ydCustomer);
    
    ydCustomer.read();
    
    equal(ydCustomer.getPrename(), "samson", 'set/get city');
    equal(ydCustomer.getName(), "tiffy", 'set/get location');
    equal(ydCustomer.getCompany(), "yd. yourdelivery GmbH", 'set/get kind');
    equal(ydCustomer.isAdmin(), 1, 'set/get mode');
});