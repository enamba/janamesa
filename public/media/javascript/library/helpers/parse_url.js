function getDomainName(url) {

    if ( url.length == 0 ){
        return null;
    }

    var url_parts = url.split('/');
    var domain_name_parts = url_parts[2].split(':');
    var domain_name = domain_name_parts[0];
    return domain_name;
}

function getPortNumber(url) {
    var url_parts = url.split('/');
    var domain_name_parts = url_parts[2].split(':');
    var port_number = domain_name_parts[1];
    return port_number;
}