var module_name = "calendar";
$(document).ready(function() {

    $('#calendar').fullCalendar({
	theme: true,
	header: {
	    left: 'prev,next today create',
	    center: 'title',
	    right: 'month,agendaWeek,agendaDay'
	},
	firstDay: 1,
	eventDrop: function(event, delta, minutes) {//linea 2613
	    //event.title   name's events
	    //delta         numbers of days to move
	    //minutes       numbers of minutes to move
	    //event.start   last date start
	    //event.end     last date end
	    var dateIni = new String(event.start);
	    var dateEnd = new String(event.end);
	    dateIni = dateIni.replace("+","|mas|");
	    dateIni = dateIni.replace("-","|menos|");
	    dateEnd = dateEnd.replace("+","|mas|");
	    dateEnd = dateEnd.replace("-","|menos|");
	    var order = "menu="+module_name+"&action=set_data&id="+event._id+"&days="+delta+"&minutes="+minutes+"&dateIni="+dateIni+"&dateEnd="+dateEnd+"&rawmode=yes";
	    $.post("index.php", order, function(theResponse){
		//location.reload();
		//alert(theResponse + " eventDrop");
	    });
	},
	eventResize: function(event, delta, minutes) { //linea 2627
	    //event.title   name's events
	    //delta         numbers of days to move
	    //minutes       numbers of minutes to move
	    //event.start   last date start
	    //event.end     last date end
	    var dateIni = new String(event.start);
	    var dateEnd = new String(event.end);
	    dateIni = dateIni.replace("+","|mas|");
	    dateIni = dateIni.replace("-","|menos|");
	    dateEnd = dateEnd.replace("+","|mas|");
	    dateEnd = dateEnd.replace("-","|menos|");
	    var order = "menu="+module_name+"&action=set_data&id="+event._id+"&days="+delta+"&minutes="+minutes+"&dateIni="+dateIni+"&dateEnd="+dateEnd+"&rawmode=yes";
	    $.post("index.php", order, function(theResponse){
		//location.reload();
		//alert(theResponse + " eventResize");
	    });
	},
	loading: function(bool) {
	    if (bool) $('#loading').show();
	    else $('#loading').hide();
	},
	editable: true,
	//currentTimezone: 'America/Chicago',
	timeFormat: 'H:mm{ - H:mm}',
	dateServer: $('#dateServer').val(),
	module: module_name,
	owner: true, // para cuando son usuarios elastix
	//uid: $('#id').val(),
	events: "index.php?menu="+module_name+"&action=get_data&rawmode=yes"
	/*events: [
		{
			title: 'All Day Event',
			start: new Date(y, m, 1)
		},
		{
			title: 'Long Event',
			start: new Date(y, m, d-5),
			end: new Date(y, m, d-2)
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: new Date(y, m, d-3, 16, 0),
			allDay: false
		},
		{
			id: 999,
			title: 'Repeating Event',
			start: new Date(y, m, d+4, 16, 0),
			allDay: false
		},
		{
			title: 'Meeting',
			start: new Date(y, m, d, 10, 30),
			allDay: false
		},
		{
			title: 'Lunch',
			start: new Date(y, m, d, 12, 0),
			end: new Date(y, m, d, 14, 0),
			allDay: false
		},
		{
			title: 'Birthday Party',
			start: new Date(y, m, d+1, 19, 0),
			end: new Date(y, m, d+1, 22, 30),
			allDay: false
		},
		{
			title: 'Click for Google',
			start: new Date(y, m, 28),
			end: new Date(y, m, 29),
			url: 'http://google.com/'
		}
	]*/
    });
});
