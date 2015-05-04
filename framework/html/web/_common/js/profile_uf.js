var elx_flag_changed_profile = false;

/**
 * Procedimiento que inicializa todos los manejadores asociados a la 
 * administración del popup de perfil de usuario.
 */
$(document).ready(function() {
	// Guardar el cambio de lenguaje de usuario
	$(this).on('change', '#languageProfile', function() {
		$.post('index.php', {
			menu:			'_elastixutils',
			action:			'changeLanguageProfile',
			rawmode:		'yes',
			newLanguage:	$("select[name='languageProfile'] option:selected").val()
		}, function(response) {
			if (response.error != '') {
				alert(response.error);
				return;
			}
			elx_flag_changed_profile = true;
		});
	});

	// Eliminar la imagen asociada al perfil del usuario
	$(this).on('click', '#deleteImageProfile', function() {
		$.post('index.php', {
			menu:			'_elastixutils',
			action:			'deleteImageProfile',
			rawmode:		'yes'
		}, function(response) {
			if (response.error != '') {
				alert(response.error);
				return;
			}
			elx_flag_changed_profile = true;
			setImageURL(response.message);
		});
	});
	
	// Iniciar subida de nueva imagen asociada al perfil del usuario
	$(this).on('click', '#picture', function() {
		$('.picturePopupProfile').liteUploader({
			script:				'index.php',
			customParams:		{
				menu:		'_elastixutils',
				action:		'changeImageProfile',
				rawmode:	'yes'
			},
			allowedFileTypes:	null,
			maxSizeInBytes:		null,
			before:				function (files) {
				$('#previews').empty();
			},
			success:			function (response) {
				if (typeof response == 'string') {
					alert('response es string');
					response = $.parseJSON(response);
				}
				if (response.error != '') {
					alert(response.error);
					return;
				}
				elx_flag_changed_profile = true;
				setImageURL(response.message);
			}
		});
	});

	// Ocultar o mostrar las entradas para ingresar nueva contraseña
	$(this).on('click', '#elx_link_change_passwd', function() {
		$("#elx_data_change_passwd").toggle();
	});
	
	// Recargar la página si un cambio afecta la totalidad de la interfaz
	$(this).on('click', '.elx_close_popup_profile', function() {
		if (elx_flag_changed_profile) shutdownSIPClient(function() {
			location.reload();
		});
	})
	
	// Habilitar los campos de nueva contraseña si se ha escrito al menos un
	// caracter de la vieja contraseña.
	$(this).on('click', '#currentPasswordProfile', function() {
		$('#currentPasswordProfile').keyup(function() {
			var targets = $('#newPasswordProfile, #repeatPasswordProfile, #elx_save_change_passwd');
			
			if ($('#currentPasswordProfile').val() != ''){
				targets.removeAttr('disabled');
			} else {
				targets.attr('disabled','disabled');
			}
		});
	});
	
	// Mandar los cambios a la contraseña al servidor
	$(this).on('click', '#elx_save_change_passwd', function() {
		$.post('index.php', {
			menu:			'_elastixutils',
			action:			'changePasswordElastix',
			rawmode:		'yes',
			oldPassword:	$("input[name='currentPasswordProfile']").val(),
			newPassword:	$("input[name='newPasswordProfile']").val(),
			newRePassword:	$("input[name='newPasswordProfile']").val()
		}, function(response) {
			if (response.error != '') {
				alert(response.error);
				return;
			}
			$("#elx_data_change_passwd").hide();
		});
	});
});

function setImageURL(url)
{
    $('#previews').empty().append($('<img>', {
        'id':'preview',
        'class':'img-responsive',
        'src': url  + '#' + new Date().getTime(),
        'width': 159
    }));
}
