/**
 * @author vpriem
 * @since 20.07.2011
 */
test('ydState default values', function() {
    ok(ydState);
    
    $.cookie('yd-state', "bla", {
        path: '/'
    });
    ydState.read();
    
    equal(ydState.getCity(), null, 'city');
    equal(ydState.getLocation(), null, 'location');
    equal(ydState.getKind(), "priv", 'kind');
    equal(ydState.getMode(), "rest", 'mode');
});

test('ydState setter / getter', function() {
    ok(ydState);
    
    ydState.setCity(5);
    equal(ydState.getCity(), 5, 'set/get city');

    ydState.setLocation(34);
    equal(ydState.getLocation(), 34, 'set/get location');
    
    ydState.setKind("comp");
    equal(ydState.getKind(), "comp", 'set/get kind');
});

test('ydState read', function() {
    ok(ydState);
    
    ydState.read();
    
    equal(ydState.getCity(), 5, 'set/get city');
    equal(ydState.getLocation(), 34, 'set/get location');
    equal(ydState.getKind(), "comp", 'set/get kind');
});