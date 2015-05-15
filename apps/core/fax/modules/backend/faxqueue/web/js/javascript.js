$(document).ready(function(){
    checkQueueStatus();
});

function checkQueueStatus()
{
	var params = {
		menu:		'faxqueue',
		action:		'checkqueue',
		rawmode:	'yes',
		outputhash:	$('#outputhash').val(),
	};

	$.post('index.php', params,
		function (respuesta) {
			$('#outputhash').val(respuesta.message.outputhash);
	        if (respuesta.statusResponse == 'CHANGED'){
	        	$('#faxqueuelist').html(respuesta.message.html);
	        }
			
			// Lanzar el método de inmediato
			setTimeout(checkQueueStatus, 1);
		});
}