$(document).ready(function() {
	if (typeof communicationactivity_status_timer == 'undefined')
		communicationactivity_status_timer = null;
	if (communicationactivity_status_timer != null) clearInterval(communicationactivity_status_timer);
	communicationactivity_status_timer = setInterval(function() {
		$.post('index.php', {
			menu:		getCurrentElastixModule(), 
			rawmode:	'yes',
			applet:		'CommunicationActivity',
			action:		'updateStatus'
		}, function(respuesta) {
			$('#communication_activity_rx_bytes').text(respuesta.rx_bytes);
			$('#communication_activity_tx_bytes').text(respuesta.tx_bytes);
		});
	}, 5000);
});

