function setTimepickerNow(idTimepickerElement){
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();

    while(minutes%15 > 0){
        minutes++;
    }

    if(minutes==60){
        minutes='00';
        hours++;
    }

    $("#"+idTimepickerElement).val(hours +':'+minutes);

}


function initTimepicker(type, idTimepickerElement, startTime, endTime) {

    var startHours = null;
    var startMinutes = null;
    var endHours = null;
    var endMinutes = null;
    var hours = null;
    var minutes = null;

    var now = new Date();

    switch (type){
        case 'full':
            // all times are shown
            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, 0, 0, 0),
                endTime:new Date(0, 0, 0, 23, 45, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });

            break;

        case "now":
            // starttime is now
            hours = now.getHours();
            minutes = now.getMinutes();

            while(minutes%15 > 0){
                minutes++;
            }

            if(minutes==60){
                minutes='00';
                hours++;
            }

            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, hours, minutes, 0),
                endTime:new Date(0, 0, 0, 23, 45, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });
            $('#'+idTimepickerElement).val('sofort');
            break;

        case "start":
            // starttime given
            if(startTime === null){
                break;
            }

            startHours = startTime.split(':')[0];
            startMinutes = startTime.split(':')[1];

            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, startHours, startMinutes, 0),
                endTime:new Date(0, 0, 0, 23, 45, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });

            break;

        case "end":
            // endtime given
            if(endTime === null){
                break;
            }

            endHours = endTime.split(':')[0];
            endMinutes = endTime.split(':')[1];

            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, 0, 0, 0),
                endTime:new Date(0, 0, 0, endHours, endMinutes, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });

            break;

        case "nowEnd":
            // endtime given at today
            if(endTime === null){
                break;
            }

            endHours = endTime.split(':')[0];
            endMinutes = endTime.split(':')[1];

            hours = now.getHours();
            minutes = now.getMinutes();

            while(minutes%15 > 0){
                minutes++;
            }

            if(minutes==60){
                minutes='00';
                hours++;
            }

            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, hours, minutes, 0),
                endTime:new Date(0, 0, 0, endHours, endMinutes, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });

            break;

        case "startEnd":
            // start- and endtime given
            if(startTime === null || endTime === null){
                break;
            }

            startHours = startTime.split(':')[0];
            startMinutes = startTime.split(':')[1];
            endHours = endTime.split(':')[0];
            endMinutes = endTime.split(':')[1];

            $("#"+idTimepickerElement).timePicker({
                startTime:new Date(0, 0, 0, startHours, startMinutes, 0),
                endTime:new Date(0, 0, 0, endHours, endMinutes, 0),
                show24Hours:true,
                separator:':',
                step: 15
            });
            break;

        default:
            // missing / wrong parameter start
            break;

    } // switch

} // initTimepicker