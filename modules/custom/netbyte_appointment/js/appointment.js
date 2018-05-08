(function($,Drupal, drupalSettings, moment){
    $(document).ready(function(){
        var events = [];
        $('#netbyte_calendar').fullCalendar({
            dayClick: function() {

                var year = moment.utc().format('YYYY');
                var month = moment.utc().format('MM');
                console.log(year);
                console.log(month);
            },
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            events: function(start, end, timezone, callback){
                var url = "/appointment/fetchmonth/";

                var date = $("#netbyte_calendar").fullCalendar('getDate');
                var month = date.format('MM');
                var year = date.year();

                url += year + "/" + month;
                $.ajax({
                    url: url,

                    success: function(data) {

                        var list = data['data'];
                        events.splice(0,events.length);
                        $(list).each(function(index, item){
                            var date = new Date(item.start);
                            var start = moment(date).format('YYYY-MM-DDTHH:mm:ss');
                            console.log(item.start);
                            console.log(date);
                            events.push({
                                'title': item.title,
                                'start': start,
                                allDay: false,
                                selectable: true,
                                url:item.url
                            });
                        });
                        //console.log(events);
                        callback(events);
                    }
                });
            },
            eventClick: function(event) {
                if (event.url) {
                    window.open(event.url, "_blank");
                    return false;
                }
            },
            /*eventRender: function(event, element) {
                element.find('.fc-title').append("<br/>" + "hello");
            }*/
        })


    });
})(jQuery,Drupal, drupalSettings, moment);

/**
 * $('#calendar').fullCalendar({
    defaultView: 'timelineMonth',
    events: [
        // events go here
    ],
    resources: [
        // resources go here
    ]
    // other options go here...
});
 * **/
