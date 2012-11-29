/**
 *  give date in form "dd.mm.yyyy" !!!
 */
function getDateFromString (dateString) {
    
    return new Date(dateString.split('.')[2], dateString.split('.')[1] - 1, dateString.split('.')[0]);
}

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 05.01.2010
 * @param string type
 * @param string idDatepickerElement
 * @param string start
 * @param string end
 */
function initDatepicker (type, idDatepickerElement, start, end) {

    switch (type) {
        case "default":
            $(idDatepickerElement).datepicker({
                minDate: new Date('01 January 2008'),
                maxDate: '+52w'
            });
            break;
        
        case "full":
            $("#" + idDatepickerElement ).datepicker({
                minDate: new Date('01 January 2008'),
                maxDate: '+52w'
            });
            break;
            
        case "before":
            $("#" + idDatepickerElement ).datepicker({
                minDate: new Date('01 January 2008'),
                maxDate: '-1d'
            });
            break;
            
        case "beforeAndToday":
            $("#" + idDatepickerElement ).datepicker({
                minDate: new Date('01 January 2008'),
                maxDate: 'now'
            });
            break;
            
        case "now":
            $("#" + idDatepickerElement ).datepicker({
                minDate: new Date(),
                maxDate: '+52w'
            });
            break;
            
        case "startEnd":
            // use start and end to define
            $("#" + idDatepickerElement ).datepicker({
                minDate: getDateFromString(start),
                maxDate: '+52w'
            });
            break;
            
        case 'raw':
            $("#" + idDatepickerElement ).datepicker({
                minDate: new Date('01 January 2008'),
                maxDate: new Date(),
                dateFormat : 'yy-mm-dd',
                defaultDate: '2010-01-01'
            });
            break;

        default:
            break;
    }
}

/**
 * check wether date of datepicker
 */
function checkDatepickerToday (idDatepickerElement) {

    var date = getDateFromString($('#' + idDatepickerElement).val());
    var today = new Date();

    if (today.getDate() == date.getDate() && today.getMonth() == date.getMonth() && today.getYear() == date.getYear()) {
        return true;
    }
    return false;
}