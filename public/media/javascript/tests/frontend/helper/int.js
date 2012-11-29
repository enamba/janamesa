/**
 * test case for price2int
 */
test('price2int()', function() {
    var total = 1000;
    ok("10,00" == int2price(total), '1000 cents are 10,00 Anything');
    ok("10,00 €" == int2price(total, true), '1000 cents are 10,00 Euro');   
    ok("10,000" == int2price(total,false,3),'3 decemials');
    ok("10.00" == int2price(total,false,2,'.'),'dot delimiter');
});
