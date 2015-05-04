$(document).ready(function() {
    $("#datepicker").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat:'dd M yy'
    });
    setInterval(function() {
    	var browser_date = new Date();
    	var server_date = new Date();
    	server_date.setTime(browser_date.getTime() - serv_msecdiff);
    	$('#SERVER_TIME').text(server_date.toLocaleString());
    }, 500);
});