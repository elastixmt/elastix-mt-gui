$(document).ready(function() {
	var gauges = ['cpugauge', 'memgauge', 'swapgauge'];
	
	// Si el navegador soporta <canvas>, se reemplaza el <img> por un <canvas>
	var useCanvas = false;
	var testcanvas = document.createElement('canvas');
	if (typeof testcanvas.getContext != 'undefined') useCanvas = true;
	testcanvas = null;
	
	if (useCanvas) {
		for (var g = 0; g < gauges.length; g++) {
			var gauge = gauges[g];
			var acanvas = $('<canvas/>')
				.attr('width', 124)
				.attr('height', 124)
				.attr('id', gauge + '_canvas');
			$('#'+gauge).children('.neo-applet-sys-gauge-percent').prev().replaceWith(acanvas);
		}
		
		// Cargar las imágenes base para dibujar en el canvas
		systemresources_gauge_base = new Image();
		systemresources_gauge_base.src = '../web/_common/images/gauge_base.png';
		systemresources_gauge_center = new Image();
		systemresources_gauge_center.src = '../web/_common/images/gauge_center.png';
		systemresources_gauge_base.onload = function() {
			systemresources_gauge_center.onload = function() {
				for (var g = 0; g < gauges.length; g++) {
					var gauge = gauges[g];
					SystemResources_setGaugeFraction(gauge, $('#'+gauge+'_value').val());
				}
			}
		}
	} else {
		// Si no se tiene canvas, se setean los valores directamente
		for (var g = 0; g < gauges.length; g++) {
			var gauge = gauges[g];
			SystemResources_setGaugeFraction(gauge, $('#'+gauge+'_value').val());
		}
	}

	if (typeof systemresources_status_timer == 'undefined')
		systemresources_status_timer = null;
	if (systemresources_status_timer != null) clearInterval(systemresources_status_timer);
	systemresources_status_timer = setInterval(function() {
		$.post('index.php', {
			menu:		getCurrentElastixModule(), 
			rawmode:	'yes',
			applet:		'SystemResources',
			action:		'updateStatus'
		}, function(respuesta) {
			if (respuesta.status != null) {
				for(var gauge in respuesta.status) {
					SystemResources_setGaugeFraction(gauge, respuesta.status[gauge]);
				}
			}
		});
	}, 5000);
});

function SystemResources_setGaugeFraction(gauge, fraction)
{
	var container = $('#'+gauge);
	var height_used = (100.0 * fraction).toFixed(0);
	var height_free = 100 - height_used;
	var percent = (100.0 * fraction).toFixed(1);
	
    var rgb = null;
	if (fraction < 0.25)
        rgb = [0, fraction * 4, 1.0];
    else if (fraction < 0.5)
        rgb = [0, 1.0, (1.0 - (fraction - 0.25) * 4)];
    else if (fraction < 0.75)
        rgb = [(fraction - 0.5) * 4, 1.0, 0];
    else
        rgb = [1.0, (1.0 - (fraction - 0.75) * 4), 0];
	for (var i = 0; i < 3; i++) rgb[i] = (rgb[i] * 255).toFixed(0);
	var htmlcolor = 'rgb('+rgb[0]+','+rgb[1]+','+rgb[2]+')';
	
	container.children('.neo-applet-sys-gauge-percent').text(percent + '%');
	container.children('img').attr('src', '?menu='+ getCurrentElastixModule() +'&rawmode=yes&applet=SystemResources&action=graphic&percent='+fraction);
	container.find('div>div>div')
		.css('background', htmlcolor)
		.css('top', height_free+'%')
		.css('height', height_used+'%');

	if (typeof systemresources_gauge_base != 'undefined') {
		// Se ha inicializado un canvas
		var centerw = 44;	// Dimensiones de imagen de centro
		var basew = 285;	// Dimensiones de imagen de base
		var canvasw = 124;	// Dimensiones de canvas
		var escala = canvasw / basew;

		var ctx = $('#'+gauge+'_canvas').get(0).getContext('2d');
		
		// Dibujar el fondo del dial
		ctx.drawImage(systemresources_gauge_base, 0, 0, canvasw, canvasw);
				
		// Dibujar la aguja del dial
		var centrox = (basew / 2) * escala;
		var angulorad = (-135.0 + 270 * fraction) * Math.PI / 180;
		ctx.translate(centrox, centrox);
		ctx.rotate(angulorad);
		ctx.beginPath();
		ctx.moveTo(0, 0);
		ctx.lineTo(0, - (100 * escala));
		ctx.lineTo(- (12 * escala), 0);
		ctx.fillStyle = 'rgb(100, 0, 0)';
		ctx.fill();
		ctx.beginPath();
		ctx.moveTo(0, 0);
		ctx.lineTo(0, - (100 * escala));
		ctx.lineTo((12 * escala), 0);
		ctx.fillStyle = 'rgb(180, 0, 0)';
		ctx.fill();
		ctx.rotate(-angulorad);
		
		// Dibujar el eje del centro
		var dst_center = ((-centerw)/2) * escala;
		ctx.drawImage(systemresources_gauge_center,
				0, 0, centerw, centerw,
				dst_center, dst_center, centerw * escala, centerw * escala);
		ctx.translate(-centrox, -centrox);
	}
	
	$('#'+gauge+'_value').val(fraction);
}