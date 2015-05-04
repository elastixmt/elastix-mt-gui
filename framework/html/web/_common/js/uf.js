$(document).ready(function(){
    $(this).on('click','.elx-msg-area-close',function(e){
        $("#elx_msg_area").slideUp();
    });
                            
    $(window).resize(function(){
    	var main_content_div = $('#main_content_elastix'); //div que contiene lo que cada modulo tiene
    	var rightdiv = $('#rightdiv'); //panel lateral en donde aparece el chat
        var w = $(window).width();
        var tmpSize=0;
        if(w>=500){
            if(rightdiv.is(':hidden') == false){ //esta abierto
                $("#elx_chat_space").css("right","200px");
                tmpSize= tmpSize + 180;
            }
            tmpSize = w - tmpSize;
            main_content_div.css("width",tmpSize+"px");
            $("#elx_chat_space").show(1);
        }else{
            if(rightdiv.is(':hidden') == false){ //esta abierto
                $("#elx_chat_space").hide(1,function(){
                    $("#elx_chat_space").css("right","15px");
                });
            }else
                $("#elx_chat_space").css("right","15px");
            main_content_div.css("width",w+"px"); 
        }
        
        adjustTabChatToWindow(w);
        
        //calulamos la altura maxima del div del chat donde estan los contactos
        if(rightdiv.is(':hidden') == false){
            adjustHeightElxListUser();
        }
        
        //se calcula el alto del contenido del modulo y se resta del alto del navegador cada
        //que se haga un resize, para que aparezca el scroll cuando sea necesario
        scrollContentModule()
    });

    //se calcula el alto del contenido del modulo y se resta del alto del navegador cada
    //que se haga un resize, para que aparezca el scroll cuando sea necesario
    scrollContentModule();
    
    /* evento que modifica el estilo de todos los paneles, al pulsar el icono para desplegar u ocultar 
    el panel lateral derecho (rightpanel)*/         
    $(this).on('click','#icn_disp2',function(e){
    	var main_content_div = $('#main_content_elastix'); //div que contiene lo que cada modulo tiene
    	var rightdiv = $('#rightdiv'); //panel lateral en donde aparece el chat
        var w = $(window).width();
        if( rightdiv.is(':hidden') ){ //estaba oculto y lo abrimos
            //es necesario modificar la el margin right del espacio del chat
            if(w>=500){
                $("#elx_chat_space").css("right",200+"px");
                //modificamos el tamaño del div principal
                var tmpSize = w - 180;
                main_content_div.css("width",tmpSize+"px");
            }else{
                //escondemos las pestañas del chat activas
                $("#elx_chat_space").hide(10);
            }
            rightdiv.show(10,function(){
                adjustHeightElxListUser();
            });
            adjustTabChatToWindow(w);
        }else{ //estaba abierto y lo cerramos
            if(w>=500){
                //es necesario modificar la el margin right del espacio del chat
                $("#elx_chat_space").css("right",15+"px");
                //si esta abierto lo coultamos y modificamos el tamaño de la pantalla
                tmpSize = w;
                main_content_div.css("width",tmpSize+"px");
            }else{
                $("#elx_chat_space").show(10);
            }
            rightdiv.hide(10);
            $("#elx_im_list_contacts").css('height','');
            adjustTabChatToWindow(w);
        }
    });

    setupChatWindowHandlers(this);
    
    setupPresenceHandlers(this);
    
    setupSendFaxHandlers(this);

    // Al hacer click en opción Profile, se inicia carga de formulario
    $(this).on('click', '.elx-display-dialog-show-profile', function() {
    	$.get('index.php', {
    		menu:	'_elastixutils',
    		action:	'getUserProfile',
    		rawmode:'yes'
    	}, function(response) {
    		if (response.error != '') {
    			alert(response.error);
    			return;
    		}
    		$("#elx_popup_content").html(response.message);
    		$('#elx_general_popup').modal({show: true});
    	});
    });
    
    //despliega en efecto slider el menu oculto, en tamño < 480px;
    $(this).on('click','#elx-navbar-min',function(){
        if ($("#elx-slide-menu-mini").is(":hidden") ) {
            $("#elx-slide-menu-mini").slideDown("slow");
            setTimeout(function() { $("#elx-slide-menu-mini").css('overflow','visible'); }, 600);
            $("#rightdiv").animate({
                top: "88px"
            }, 600 );
        } else {
            $("#elx-slide-menu-mini").slideUp("slow");
            $("#rightdiv").animate({
                top: "55px"
            }, 600 );
        }
        
    });
    
    
    //captura ingresado por el teclado y manda a consultar a la base los contactos del chat
    $(this).on('keyup', '#im_search_filter', updateContactVisibility);
    $('#elx-chk-show-offline-contacts').click(updateContactVisibility);

    setupSIPClient();
	
	// Hacer aparecer la ventana de marcado de llamadas
    $('#icn_call').on('click', function(e) {
    	showCallWindow(null, null, null);
    });

    /* Instalar manejador que intenta apagar el cliente SIP antes de cambiar de 
     * página, para mitigar las sesiones inválidas. */
	$('a').on('click', function(e) {
		if ($(this).attr('href').match(/^(index.php)?\?/)) {
			e.preventDefault();
			shutdownSIPClient(function() {
				document.location = $(this).attr('href');				
			}.bind(this));
		}
	});
});

/**
 * Procedimiento que inicializa todos los manejadores asociados al soporte de
 * chat, excepto la carga remota y registro en sí.
 * 
 * @returns void
 */
function setupChatWindowHandlers(doc)
{
    // Click en contacto para abrir la ventana de chat correspondiente
	$(doc).on('click', '.elx_li_contact', function() {
        // Ahorrar espacio para caso de ventana estrecha
        if ($(window).width() < 500) $('#rightdiv').hide(10);
        
        startChatUser($(this).data('uri'), 'sent')
        	.find('.elx_text_area_chat > textarea').focus();
        $("#elx_chat_space").show(10);
    });

	// Acciones para controlar las ventanas de chat
    $(doc).on('click', '.elx_close_chat', function() {
        // Cerrar la ventana del chat (realmente la oculta, pero el div sigue presente)

        $(this).parents(".elx_tab_chat").removeClass('elx_chat_active').addClass('elx_chat_close');
        //debemos comprobar si ahi pestañas minimizadas por falta de espacio
        //si existen entonces abrimos la ultima pestaña
        var chatMIn=$("#elx_chat_space_tabs > .elx_chat_min").last();
        if(chatMIn!=='undefined'){
            chatMIn.removeClass('elx_chat_min').addClass('elx_chat_active');
            removeElxUserNotifyChatMini(chatMIn);
        }
    });
    $(doc).on('click', '.elx_min_chat', function() {
        // Minimizar la ventana de chat    	
        $(this).removeClass("glyphicon-minus elx_min_chat").addClass("glyphicon-resize-vertical elx_max_chat");
        $(this).parents(".elx_header_tab_chat").next('.elx_body_tab_chat').css('display','none');
    });
    $(doc).on('click', '.elx_max_chat', function() {
        // Restaurar la ventana de chat    	
        $(this).removeClass("glyphicon-resize-vertical elx_max_chat").addClass("glyphicon-minus elx_min_chat");
        $(this).parents(".elx_header_tab_chat").next('.elx_body_tab_chat').css('display','block');
	});
	$(doc).on('click', 'div.elx_header2_tab_chat > span.glyphicon-envelope', function() {
		// Envío de correo al usuario del chat
		elx_newEmail($(this).parents('.elx_tab_chat').data('alias'));
	});
	$(doc).on('click', 'div.elx_header2_tab_chat > span.glyphicon-print', function() {
		// Envío de fax al usuario del chat
		showSendFax($(this).parents('.elx_tab_chat').data('alias'));
	});
	$(doc).on('click', 'div.elx_header2_tab_chat > span.glyphicon-earphone', function() {
		/* Llamada de voz al usuario del chat. Se timbra a extensión en lugar de
		 * a cuenta directa para timbrar simultáneamente al teléfono tradicional
		 * y a la cuenta IM, y que conteste la primera disponible. */
		var contactInfo = lookupSIPRoster($(this).parents('.elx_tab_chat').data('uri'));
		showCallWindow(
			contactInfo.extension,
			contactInfo.name,
			null);
	});
	
	//accion que controla cuando damos enter en el text-area de una de la pestañas del chat
	$(doc).on("keydown",".elx_chat_input", function( event ) {
        // Ignore TAB and ESC.
        if (event.which == 9 || event.which == 27) {
            return false;
            // Enter pressed? so send chat.
        } else if ( event.which == 13 && $(this).val() !='') {
            event.preventDefault();
            //debemos mandar el mensaje y 
            //hacer que el texto del text area desaparezca y sea enviado la divdel chat al que corresponde
            var elx_txt_chat=$(this).val();
            var elx_tab_chat=$(this).parents('.elx_tab_chat:first');
            
            $(this).val('');
            sendMessage(elx_txt_chat, elx_tab_chat.data('uri'));
            // Ignore Enter when empty input.
        } else if (event.which == 13 && $(this).val() == "") {
            event.preventDefault();
            return false;
        }
    });
	$(doc).on("click",".elx_tab_chat", function( event ) {
	    $(this).children('.elx_header_tab_chat').removeClass('elx_blink_chat');
	    $(this).find('.elx_text_area_chat > textarea').focus();
	});
	
	//motificaciones en pestañas de chat minimizadas por falta de espacio
	$('#elx_notify_min_chat_box').on("click",function(event){
	    var hidMinList=$('#elx_hide_min_list').val();
	    if(hidMinList=='yes'){
	        //se deben ocultar la lista de las conversaciones minimizadas por falta de espacio
	        $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
	        $('#elx_hide_min_list').val('no');
	        $("#elx_list_min_chat").css('visibility','hidden');
	    }else{
	        //antes de mostrar la lista debemos calcular si el espacio que queda es suficiente para
	        //mostrar los elementos de la lista
	        //si no queda mucho espacio cambiamos la direccion de la lista al otro lado
	        //se deben mostrar la lista de las conversaciones minimizadas por falta de espacio
	        $("#elx_notify_min_chat_box").addClass('elx_notify_min_chat_box_act');
	        $('#elx_hide_min_list').val('yes');
	        var offElement=$("#elx_notify_min_chat_box").offset();
	        var widthList = $("#elx_list_min_chat > div > .elx_list_min_chat_ul").width();
	        if((offElement.left-40)>(widthList)){
	            //existe suficiente espacio para mostrar la lista 
	            $("#elx_list_min_chat").css('right','0px');
	            $("#elx_list_min_chat").css('left','');
	        }else{
	            //no existe suficiente espacio para mostrar la lista 
	            $("#elx_list_min_chat").css('left','0px');
	            $("#elx_list_min_chat").css('right','');
	        }
	        $("#elx_list_min_chat").css('visibility','visible');
	    }
	});
	$(doc).on('click', '.elx_min_name', function(event) {
	    $(this).children(".elx_min_chat_num").css('visibility','hidden');
	    //se deben ocultar la lista de las conversaciones minimizadas por falta de espacio
	    $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
	    $('#elx_hide_min_list').val('no');
	    $("#elx_list_min_chat").css('visibility','hidden');
	    var uri = $(this).parents('.elx_list_min_chat_li:first').data('uri');
	    var elx_tab_chat = startChatUser(uri, 'sent');
	    elx_tab_chat.find('.elx_text_area_chat > textarea').focus();
	    elx_tab_chat.find('.elx_tab_tittle_icon > span:first').removeClass("glyphicon-resize-vertical elx_max_chat").addClass("glyphicon-minus elx_min_chat");
	    elx_tab_chat.find('.elx_body_tab_chat').css('display','block');
	});
	$(doc).on('click','.elx_min_remove',function(event){
	    var liIcon = $(this).parents('.elx_list_min_chat_li:first');
	    var uri = liIcon.data('uri');
	    liIcon.remove();
	    var tabChat = getTabElxChat(uri);
	    tabChat.removeClass('elx_chat_min').addClass('elx_chat_close');
	    //disminuir la cuenta de las conversaciones minimizadas y en caso de no quedar niguna ocultar tab notificaciones
	    removeElxUserNotifyChatMini(tabChat);
	});
}

/**
 * Procedimiento que inicializa los manejadores que hacen cambios de la presencia
 * del propio usuario.
 * 
 * @param doc Referencia al documento
 */
function setupPresenceHandlers(doc)
{
	$(doc).on('click', '#elx_presence_online', function() {
		if (sp != null) sp.setPresenceStatus('Online', []);
	});
	$(doc).on('click', '#elx_presence_away', function() {
		if (sp != null) sp.setPresenceStatus('Away', ['away']);
	});
	$(doc).on('click', '#elx_presence_meeting', function() {
		if (sp != null) sp.setPresenceStatus('On a meeting', ['meeting']);
	});
	$(doc).on('click', '#elx_presence_busy', function() {
		if (sp != null) sp.setPresenceStatus('Busy (DND)', ['busy']);
	});
	$(doc).on('click', '#elx_presence_offline', function() {
		if (sp != null) sp.withdrawPresence();
	});
}

/**
 * Procedimiento que inicializa los manejadores asociados al diálogo de envío
 * de fax.
 * 
 * @param doc Referencia al documento
 */
function setupSendFaxHandlers(doc)
{
    $(doc).on('click', '.elx-display-dialog-show-sendfax', function() {
    	showSendFax(false);
    })    
    
    //oculta o muestra la opcion de subir archivo para la opción "sendfax"
    $(doc).on('click','#elx-chk-attachment-file',function(){
        if($(this).is(':checked')) {
            $("#elx-body-fax-label").removeClass("visible").addClass("oculto");
            $("#elx-body-fax-content").removeClass("visible").addClass("oculto");
            $("#elx-attached-fax-file").removeClass("oculto").addClass("visible");
            $("#elx-notice-fax-file").removeClass("oculto").addClass("visible");
            $("textarea[name='faxContent']").val("")
        }else{
            $("#elx-body-fax-label").removeClass("oculto").addClass("visible");
            $("#elx-body-fax-content").removeClass("oculto").addClass("visible");
            $("#elx-attached-fax-file").removeClass("visible").addClass("oculto");
            $("#elx-notice-fax-file").removeClass("visible").addClass("oculto");
        }
    });
}

//se calcula el alto del contenido del modulo y se resta del alto del navegador cada
//que se haga un resize, para que aparezca el scroll cuando sea necesario
function scrollContentModule(){
	if( $('.elx-modules-content').length )
    {
        var height_browser = $(window).height();
        var offElement=$(".elx-modules-content").offset();
        $(".elx-modules-content").css("height",height_browser-offElement.top +"px");
    }
}
function elxTitleAlert(message){
       
     $.titleAlert(message, {
        requireBlur:true,
        stopOnFocus:true,
        interval:600
    });
}
function adjustHeightElxListUser(){
    var h = $("#b3_1").height();
    var max_h=h-$("#head_rightdiv").height()-15;
    $("#elx_im_list_contacts").css('height',max_h+"px");
}
/*
function changeModuleUF(moduleName){
    if(typeof(moduleName) == 'undefined' || moduleName === null) //nada que hacer no paso el modulo
        return false;
    
    var regexp_user = /^[\w-_]+$/;
    if (moduleName.match(regexp_user) == null) return false;
    
    showElastixUFStatusBar('Loading Module...');
    
    var arrAction = new Array();
    arrAction["changeModule"]  = "yes";
    arrAction["rawmode"] = "yes";
    arrAction["menu"] = moduleName;
    request("index.php",arrAction,false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if(error!=''){
                alert(error);
            }else{
                //var cssFiles=arrData['CSS_HEAD'];
                
                //var jsFiles=arrData['JS_HEAD'];
                //cargamos los scripts del modulo
                var content="<input type='hidden' id='elastix_framework_module_id' value='"+moduleName+"' />";
                content +=arrData['JS_CSS_HEAD'];
                content +=arrData['data'];
                $("#module_content_framework").html(content);
                //se debe setear el contenido de la barra #main_opc en cada modulo
                //aun no se quien deberia hacer esto
                //$("#main_opc").html(arrData['CONTENT_OPT_MENU']);
            }
        }
    );
}
*/
function showElastixUFStatusBar(msgbar){
    $("#notify_change_elastix").css('display','block');
    if(msgbar){
        $(".progress-bar-elastix").html(msgbar);
    }else{
        $(".progress-bar-elastix").html('Loading..');
    }
}
function hideElastixUFStatusBar(){
    $("#notify_change_elastix").css('display','none');
}
function showElxUFMsgBar(status,msgbar){
    if(status=='error'){
        $("#elx_msg_area_text").removeClass("alert-success").addClass("alert-danger");
    }else{
        $("#elx_msg_area_text").removeClass("alert-danger").addClass("alert-success");
    }
    $("#elx_msg_area_text").html(msgbar);
    $("#elx_msg_area").slideDown(); 
}

var ua;	// Objeto de SIP.UA
var sp; // Objeto de SIPPresence

/**
 * Procedimiento que inicia la configuración de las configuraciones SIP. Las
 * dependencias que hay que resolver hasta ahora son las siguientes:
 * 
 * Carga credenciales SIP,
 * 	requiere: (nada)
 * Inicio cliente SIP
 * 	requiere: Carga credenciales SIP
 * Carga lista de contactos
 * 	requiere: (nada)
 * Creación de lista de contactos con data
 * 	requiere: Carga lista de contactos
 * Publicación de presencia propia
 * 	requiere: Inicio cliente SIP
 * Subscripción a lista de contactos
 * 	requiere: Inicio cliente SIP, Creación de lista de contactos con data
 * 
 * @return void
 */
function setupSIPClient()
{
    // Centrar spinner en el recuadro de chat
    var alturaDiv = $('#rightdiv').height();
    $('#startingSession').css({'top': alturaDiv/2 - 10, 'display':'block'});

    // Marcar color de presencia como desconectado hasta actualizar
    $(".elx-content-photo").css('border-color', 'gray');
    
    var deferred_registerUA = new $.Deferred();
    var deferred_rosterLoad = new $.Deferred();

    // Iniciar la carga de las credenciales SIP
    $.get('index.php', {
        menu:	'_elastixutils',
        action:	'getSIPParameters'
    }, function (response) {
        if (response.error != '') {
            // Rechazar la promesa con el mensaje de error
            deferred_registerUA.reject(response.error);
        } else {
            // Con las credenciales, se puede iniciar el SIP.UA
            ua = new SIP.UA({
                uri:                response.message.elxuser_username,
                wsServers:          response.message.ws_servers,
                displayName:        response.message.display_name,
                password:           response.message.password,
                hackIpInContact:    response.message.hack_ip_in_contact,
                autostart:          true,
                register:           response.message.register,
                registerExpires:    response.message.register_expires,
                usePreloadedRoute:  response.message.use_preloaded_route,
                noAnswerTimeout:    response.message.no_answer_timeout,
                traceSip:           response.message.trace_sip,
                stunServers:        ["stun:null"]
            });
            ua.on('message', function (e) {
                var remoteUri = e.remoteIdentity.uri.toString();
                var remoteUser = remoteUri.split('sip:');     
                var uri2 = remoteUser[1];
                var elx_txt_chat = e.body;

                receiveMessage(uri2, e.contentType, elx_txt_chat);
            }).on('registered', function () {
                // El registro exitoso es uno de los requisitos para iniciar suscripción
            	deferred_registerUA.resolve();
            }).on('registrationFailed', function(cause) {
                deferred_registerUA.reject('Failed to register: ' + cause);
            }).on('invite', function(in_sess) {
            	console.log(in_sess.remoteIdentity.uri.user);
            	showCallWindow(in_sess.remoteIdentity.uri.user, in_sess.remoteIdentity.displayName, in_sess);
            });
        }
    }).fail(function() {
        deferred_registerUA.reject('Failed to load SIP parameters!');
    });
    
    // Iniciar la carga de la lista de contactos
    $.get('index.php', {
        menu:	'_elastixutils',
        action:	'getSIPRoster'
    }, function (response) {
        if (response.error != '') {
            // Rechazar la promesa con el mensaje de error
        	deferred_rosterLoad.reject(response.error);
        } else {
            for (var i = 0; i < response.message.length; i++) {
                $("#elx_ul_list_contacts").append(
                	createDivContact(
                        response.message[i]['idUser'],
                        response.message[i]['display_name'],
                        response.message[i]['uri'],
                        response.message[i]['username'],
                        response.message[i]['extension']));
            }
            
            // La carga exitosa del roster es uno de los requisitos para subscripción
            deferred_rosterLoad.resolve();
        }
    }).fail(function() {
    	deferred_rosterLoad.reject('Failed to load SIP roster!');
    });
    
    // Iniciar la subscripción cuando ambas promesas puedan resolverse
    $.when(deferred_registerUA, deferred_rosterLoad)
    .done(function() {
    	$('#startingSession').css('display','none');
        $('#b3_1').css('display','block');
        
        sp = new SIPPresence(ua);
        sp.publishPresence();
    }).fail(function(msg) {
    	errorRegisterChatBar(msg);
    });
}

/**
 * Procedimiento que apaga de forma ordenada el cliente SIP antes de navegar
 * usando GET al URL indicado por el parámetro.
 * 
 * @param callback Funcion a la cual llamar luego de finalizar cliente SIP
 */
function shutdownSIPClient(callback)
{
	if (ua != null && (ua.isConnected() || ua.isRegistered())) {
		$('body').css('cursor', 'progress');
		if (sp != null) sp.withdrawPresence();
		if (callback != null)
			ua.on('disconnected', callback);
		ua.unregister();
		ua.stop();
	} else {
		if (callback != null) callback();
	}
}

/**
 * Esta función actualiza el estado de visibilidad de todos los contactos. Un
 * contacto debe mostrarse si su nombre coincide con el patrón de búsqueda (una
 * cadena vacía coincide con todo), y si deben mostrarse los contactos offline,
 * o si el contacto en sí está online.
 */
function updateContactVisibility()
{
    var pattern = $("input[name='im_search_filter']").val();
    var mostrarTodos = $('#elx-chk-show-offline-contacts').is(':checked');

    // Ocultar todos los contactos, y volver a mostrar sólo coincidencias 
    $("#elx_ul_list_contacts > li.elx_li_contact").hide().each(function() {
    	if ($(this).data('name').match(pattern) &&
    		(mostrarTodos || $(this).data('status') != 'offline')) {
    		$(this).show();
    	}
    });
}

/**
 * Esta función construye una nueva instancia de una plantilla donde se muestra
 * la información del contacto SIP para chat, con los datos ya rellenados. La
 * plantilla base está definida en index_uf.tpl .
 * 
 * @param idUser			ID del usuario representado
 * @param display_name		Nombre completo del usuario representado
 * @param uri				URI del contacto SIP IM para el usuario
 * @param alias				URI del contacto SIP telefónico para el usuario
 * @param extension			Número de extensión asignado al usuario
 * 
 * @returns Objeto jQuery que representa el tag <li> sin insertar
 */
function createDivContact(idUser, display_name, uri, alias, extension)
{
	var liContact = $('#elx_template_contact_status > li').clone()
		.addClass('elx_li_contact')
		.attr('data-status', 'offline')
		.attr('data-uri', uri)
		.attr('data-alias', alias)
		.attr('data-name', display_name)
		.attr('data-idUser', idUser)
		.attr('data-extension', extension);
	liContact.find('.box_status_contact').css('background-color', 'grey');
	liContact.find('.elx_im_name_user').text(display_name);
	liContact.find('.extension_status').text('(unknown)');
	return liContact;
}

/**
 * Esta función busca en la lista de contactos de roster, un contacto que tenga
 * una propiedad con el valor indicado en keyval. Se devuelve toda la información
 * almacenada sobre ese contacto, o información por omisión si no se encuentra.
 * 
 * @param keyval	Cadena que identifica de alguna manera el contacto SIP
 * 
 * @returns object
 */
function lookupSIPRoster(keyval)
{
	var contactInfo = {
		status:		'offline',
		uri:		null,
		alias:		null,
		name:		'(unknown)',
		idUser:		null,
		extension:	null
	};
	var dataKeys = ['uri', 'alias', 'extension', 'name'];
	var liContact = null;
	
	for (var i = 0; i < dataKeys.length; i++) {
		liContact = $('ul#elx_ul_list_contacts > li[data-' + dataKeys[i] + '="' + keyval + '"]');
		if (liContact.length > 0) {
			contactInfo.status = liContact.data('status');
			contactInfo.uri = liContact.data('uri');
			contactInfo.alias = liContact.data('alias');
			contactInfo.name = liContact.data('name');
			contactInfo.idUser = liContact.data('iduser');
			contactInfo.extension = liContact.data('extension');
			break;
		}
	}
	
	return contactInfo;
}

/******************************************************************
 * Funciones usadas para el crear el dispositivo sip del usuario
*******************************************************************/
function receiveMessage(urioralias, content_type, msg_txt)
{
    //verificamos si existe una conversacion abierta con el dispositivo
    //si no existe la creamos
    var elx_tab_chat = startChatUser(urioralias, 'receive');
    if (!elx_tab_chat.hasClass('elx_chat_min')){
        if (!elx_tab_chat.find('.elx_text_area_chat > textarea').is(':focus')){
            //añadimos clase que torna header anaranjado para indicar que llego nuevo mensaje
            elx_tab_chat.children('.elx_header_tab_chat').addClass('elx_blink_chat');
        }
    }
 
    if (content_type == 'application/im-iscomposing+xml') {
        // El mensaje es una indicación de que el usuario remoto está escribiendo
    	var xml = $.parseXML(msg_txt);
    	var state = $(xml).find('isComposing > state').text();
    	if (state != 'active') state = 'idle';
    	setComposingStateElxChatTab(elx_tab_chat, state);
    } else {
        addMessageElxChatTab(elx_tab_chat, 'in', msg_txt);
    }
}

/**
 * Función para implementar el envío de un mensaje escrito en el textarea de un
 * chat
 * 
 * @param msg_txt	Cadena de texto a enviar
 * @param uri		Contacto SIP al cual se envía mensaje
 */
function sendMessage(msg_txt, uri)
{    
    var elx_tab_chat = getTabElxChat(uri);    
    addMessageElxChatTab(elx_tab_chat, 'out', msg_txt);

    ua.message(uri, msg_txt).on('failed', function (response, cause) {
        var error_msg = (response) 
            ? response.status_code.toString() + " " + response.reason_phrase 
            : cause;

        if (elx_tab_chat != false) {
            elx_tab_chat.find(".elx_content_chat").find("div").last().css("color","red");
            addMessageElxChatTab(elx_tab_chat, 'in', $('<span style="color: red;"></span>').text(error_msg));
        } else {
            alert(error_msg);
        }
    });
}

/**
 * Función para buscar el div de la conversación del usuario, dado su uri.
 * Se devuelve el div del chat, si se encuentra, o false si no existe.
 */
function getTabElxChat(uri)
{
	var chatTab = $("#elx_chat_space_tabs > .elx_tab_chat[data-uri='" + uri + "'] :first");
	return chatTab.length > 0 ? chatTab : false;
}

/**
 * Función que crea una nueva ventana de chat para el contacto de uri o alias 
 * indicado. Si no existía previamente una ventana de chat para el usuario, se
 * la crea. Si la ventana encontrada está minimizada, se intenta activarla, a
 * menos que no haya suficiente espacio. Si se está obteniendo un chat debido
 * a la recepción de un mensaje, y no se puede abrir la ventana, se agrega la
 * indicación de mensaje nuevo.
 * 
 * @param urioralias	URI SIP (IM u ordinario) del usuario de chat
 * @param action 'send' o 'receive'
 * @returns jQuery
 */
function startChatUser(urioralias, action)
{
    var can_add_chat = true;
    var contactInfo;
    
    /* Se busca en el roster la información del contacto. Si no se encuentra,
     * se asume que es un nuevo cliente SIP, y se usará el uri directamente. */
    contactInfo = lookupSIPRoster(urioralias);
    if (contactInfo.uri == null) {
    	// El contacto no existe, se usa información temporal
    	contactInfo.uri = urioralias;
    	contactInfo.alias = urioralias;
    	contactInfo.name = 'Unknown <sip:' + urioralias + '>';
    }
    
    // Se intenta reutilizar la ventana activa de un chat previo
    var elx_tab_chat = getTabElxChat(contactInfo.uri);

    if (!elx_tab_chat) {
    	// Ventana para este usuario no existe, se debe de crear una nueva
        
        var content = $('#elx_template_tab_chat > .elx_tab_chat').clone()
        	.attr('data-uri', contactInfo.uri);
        content.find('.elx_tab_chat_name_span').text(contactInfo.name);
    
        // Se agrega el nuevo chat a la lista de ventanas de chat
        $("#elx_chat_space_tabs").prepend(content);
        elx_tab_chat = $("#elx_chat_space_tabs > .elx_tab_chat:first");
        
        /* Llegado a este punto, la situación es igual que si se hubiese 
         * encontrado anteriormente la ventana, en estado minimizado. */
    }

    if (!elx_tab_chat.hasClass('elx_chat_active')) {
        /* La ventana solicitada esta minimizada o fue cerrada anteriormente. Se
         * procede a abrirla si se dispone de suficiente espacio. */
        can_add_chat = resizeElxChatTab($(window).width(), action);
        if (can_add_chat) {
            //funcion que maneja el hecho de que aparezca una venta del chat que estaba aculta
            if(elx_tab_chat.hasClass('elx_chat_close')){
                //si existia el tab pero tenia esta clase significa que se chateo en un momento pero
                //de ahi se cerro la ventana del chat por lo que la volvemos a abrir
                elx_tab_chat.removeClass('elx_chat_close').addClass('elx_chat_active');
                removeElxUserNotifyChatMini(elx_tab_chat);
            } else if(elx_tab_chat.hasClass('elx_chat_min')){
                elx_tab_chat.removeClass('elx_chat_min').addClass('elx_chat_active');
                removeElxUserNotifyChatMini(elx_tab_chat);
            }
        }
    }
    
    /* Se ha recibido un nuevo mensaje, y la pestaña de chat correspondiente no
     * puede ser abierta por falta de espacio. Se muestra una notificación de
     * nuevo mensaje. */
    if (action == 'receive' && !can_add_chat) {
        addElxUserNotifyChatMini(elx_tab_chat);
        elx_tab_chat.removeClass('elx_chat_close').addClass('elx_chat_min');
        $("#elx_notify_min_chat_box").addClass('elx_notify_min_chat_box_act');
        
        // Hacer visible el asterisco al lado del item de chat minimizado que recibe el mensaje
        $('#elx_list_min_chat > div > .elx_list_min_chat_ul > .elx_list_min_chat_li[data-uri="'
        		+ contactInfo.uri + '"] .elx_min_name > .elx_min_chat_num')
        	.css('visibility', 'inherit');
    }

    return elx_tab_chat;
}

/**
 * Esta función verifica si se puede acomodar una nueva pestaña de chat en el 
 * ancho de pantalla indicado por elx_w. Si se alcanzó el máximo, en el caso de
 * sent, es posible minimizar otra pestaña para acomodar la nueva.
 * 
 * @param	elx_w	Ancho en pixeles de la pantalla
 * @param	action	Razón por la cual se requiere acomondar nueva pestaña.
 * 					Puede ser 'sent' o 'receive'.
 * 
 * @returns	TRUE si hay (o se ha hecho) espacio para una pestaña más, o FALSE.
 */
function resizeElxChatTab(elx_w, action)
{
	var max_tab = getMaxNumTabChat(elx_w);
    var num_act_chat = $("#elx_chat_space_tabs > .elx_chat_active").size();
    
    if (num_act_chat < max_tab) return true;	// No se ha alcanzado máximo
    if (action != 'sent') return false;			// Recepción es async y no autoriza minimizado
    
    // Para envío de mensaje, se puede minimizar un tab activo
    var elxTabChatToMin = $("#elx_chat_space_tabs > .elx_chat_active").first();
    elxTabChatToMin.removeClass('elx_chat_active').addClass('elx_chat_min');
    addElxUserNotifyChatMini(elxTabChatToMin);
    return true;
}

/**
 * Función para determinar, a partir del ancho de pantalla disponible, cuántas
 * ventanas de chat se pueden abrir simultáneamente.
 * 
 * @param elx_w		Ancho de pantalla para usar en cálculo
 * @returns {Number}
 */
function getMaxNumTabChat(elx_w)
{
	// Restar del ancho disponible el ancho de la lista de chat
	if (!$("#rightdiv").is(':hidden')) elx_w -= 180;

	if (elx_w < 320) return 1;
	if (elx_w >= 1240) return 4; // Más allá sólo se permitirán 4 pestañas abiertas
	return Math.floor((elx_w - 90) / 230);	// Cada pestaña reserva 230px
}
function errorRegisterChatBar(error){
    alert(error);
    $('#b3_1').css('display','none');
    $('#startingSession').html(error);
    $('#startingSession').css({'display':'block',margin:'5px'});
}

/**
 * Procedimiento para agregar un nuevo mensaje de chat (enviado o recibido) al
 * historial que se muestra encima del textarea del siguiente mensaje.
 * TODO: i18n
 * 
 * @param chatTab	Objeto jQuery de la ventana de chat a actualizar
 * @param direction	'in' para mensajes entrantes, 'out' para mensajes salientes
 * @param message	Texto del mensaje, o un objeto jQuery con formato
 */
function addMessageElxChatTab(chatTab, direction, message)
{
    var send_name = (direction == 'out') 
        ? 'me'
        : chatTab.find('.elx_tab_chat_name > .elx_tab_chat_name_span').text();
    
    // Para ahorrar espacio, se toma el primer elemento sin espacios
    var tokens = send_name.trim().split(' ');
    send_name = tokens[0];    
    if (send_name == 'undefined' || send_name == '') send_name = 'receive';
    if (direction != 'out') elxTitleAlert('New Message ' + send_name);
    
    var messagediv = $('<div></div>');
    if (typeof message == 'string') 
    	messagediv.text(message);
    else messagediv.append(message);
    chatTab.find('.elx_body_tab_chat:first .elx_content_chat:first')
    	.append(messagediv.prepend($('<b></b>').text(send_name + ': ')))
    	.scrollTop(1e4);
    if (direction != 'out') {
    	chatTab.find('.elx_body_tab_chat:first .elx_content_chat:first div.elx_chat_composing').remove();
    }
}

/**
 * Procedimiento para actualizar el estado de composición remota en el chat.
 * 
 * @param chatTab	Objeto jQuery de la ventana de chat a actualizar
 * @param state		'active' o 'idle'
 */
function setComposingStateElxChatTab(chatTab, state)
{
	console.log(state);
	
	var target = chatTab.find('.elx_body_tab_chat:first .elx_content_chat:first');
	target.find('div.elx_chat_composing').remove();
	if (state == 'active') {
		var messagediv = $('<div class="elx_chat_composing"></div>');
		messagediv.text('composing...');
		target.append(messagediv).scrollTop(1e4);
	}
}

function addElxUserNotifyChatMini(elxTabChatToMin)
{
	var uri = elxTabChatToMin.data('uri');
	var list_min_chat = $('#elx_list_min_chat > div > ul.elx_list_min_chat_ul');
	if (list_min_chat.find('li.elx_list_min_chat_li[data-uri="' + uri + '"]').size() > 0)
		return;
	
	var contactInfo = lookupSIPRoster(uri);
	if (contactInfo.uri == null) {
		contactInfo.name = elxTabChatToMin.find('.elx_tab_chat_name > .elx_tab_chat_name_span').html();
	}
	
	// Clonar plantilla y asignar data-uri y nombre
	var elx_chat_user = $('#elx_template_min_chat_ul > li.elx_list_min_chat_li')
		.clone().attr('data-uri', uri);
	elx_chat_user.find('span.elx_min_chat_name').text(contactInfo.name);
	list_min_chat.prepend(elx_chat_user);
	
	// Actualizar número de chats minimizados
	$("#elx_num_mim_chat").text(list_min_chat.find('li.elx_list_min_chat_li').size());
	
    // Si no se estaba mostrando el chat con las notificaciones lo mostramos
    $("#elx_notify_min_chat").removeClass('elx_nodisplay');
}

function removeElxUserNotifyChatMini(elxTabChatToMin)
{
    var uri = elxTabChatToMin.data('uri');
    var list_min_chat = $('#elx_list_min_chat > div > ul.elx_list_min_chat_ul');
    list_min_chat.find('li.elx_list_min_chat_li[data-uri="' + uri + '"]').remove();
    var numMinchat = list_min_chat.find('li.elx_list_min_chat_li').size();

    if (numMinchat == 0) {
        //ocultamos el div con las notificaciones
        $("#elx_notify_min_chat").addClass('elx_nodisplay');
        $("#elx_notify_min_chat_box").removeClass('elx_notify_min_chat_box_act');
        $('#elx_hide_min_list').val('no');
        $("#elx_list_min_chat").css('visibility','hidden');
    } else {
        //actualizazamos la informacion del numero de chat abiertos minimizados
        $("#elx_num_mim_chat").text(numMinchat);
    }
}

function adjustTabChatToWindow(elxw){ 
    //contralamos el número de pestañas activas abiertas de acuerdo al tamamaño de la pantalla
    var max_tab=getMaxNumTabChat(elxw); 
    //revisar el número de pestañas activas
    var num_act_chat=$("#elx_chat_space_tabs > .elx_chat_active").size();
    //si el número de pestañas activas es mayor que el máximo
    //entones procedemos a minimizar pestañas
    if(num_act_chat>max_tab){
        for(var i=0;i<(num_act_chat-max_tab);i++){
            var elxTabChatToMin=$("#elx_chat_space_tabs > .elx_chat_active").first();
            elxTabChatToMin.removeClass('elx_chat_active').addClass('elx_chat_min');
            addElxUserNotifyChatMini(elxTabChatToMin);
        }
    }else{
        //si existen pestañas minimizadas y el número de activas es menor que el máximo procedemos
        //a minizar pestañas
        if(num_act_chat < max_tab){
            for(var i=0;i<(max_tab-num_act_chat);i++){
                //si existen entonces abrimos la ultima pestaña
                var chatMIn=$("#elx_chat_space_tabs > .elx_chat_min").last();
                if(chatMIn!=='undefined'){
                    chatMIn.removeClass('elx_chat_min').addClass('elx_chat_active');
                    removeElxUserNotifyChatMini(chatMIn);
                }else{
                    break;
                }
            }
        }
    }
}


function elxGridData(moduleName, action, arrFilter, page){
    var currentNumPage=$("#elxGridNumPage").val();
    
    //validar si page es un numero
    if(isNaN(page)){
        page=1;
    }else{
        if (page % 1 != 0) {
            page=1;
        } 
    }

    
    if(page>currentNumPage){
        page=currentNumPage;
    }
    
    var arrAction = new Array();
    arrAction["menu"]=moduleName;
    arrAction["action"]=action;
    arrAction["page"]=page;
    arrAction["nav"]='bypage';
    
    for( var x in arrFilter){   
        arrAction[x]=arrFilter[x];
    }
    
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if (error != '' ){
                $("#message_area").slideDown();
                $("#msg-text").removeClass("alert-success").addClass("alert-danger");
                $("#msg-text").html(error['stringError']);
                // se recorre todos los elementos erroneos y se agrega la clase error (color rojo)
            }else{
                var content='';
                var grid=arrData['content'];
                for(var i=0;i<grid.length;i++){
                    content+='<tr>';
                    for(var j=0;j<grid[i].length;j++){
                        content +="<td>"+grid[i][j]+"</td>";
                    }
                    content+='</tr>';
                }
                $("#elx_data_grid > table > tbody").html(content);
                
                var url=arrData['url'];
                
                var newUrl=url+"&exportcsv=yes&rawmode=yes"; 
                
                $("#exportcsv > a").attr('href', newUrl);
                $("#exportspreadsheet > a").attr('href', newUrl);
                $("#exportpdf > a").attr('href', newUrl);
                
                if(arrData['numPage']==0){
                    arrData['numPage']=1;
                    page=1;
                }
                
                $("#elxGridNumPage").val(arrData['numPage']);
                $("#elxGridCurrent").val(page);
                
                var options = {
                    currentPage: page,
                    totalPages: $("#elxGridNumPage").val(),
                    }
                
                $('#elx_pager').bootstrapPaginator(options); 

            }
    });
}

//llama a la función "showSendFax" que muestra la ventana del popup para enviar fax
//la función se encuentra dentro del módulo "my_fax"
function showSendFax(alias){
    var arrAction = new Array();
    arrAction["menu"]="my_fax";
    arrAction["action"]="showSendFax";
    if(alias){
        arrAction["alias"]=alias;
    }
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                $("#elx_popup_content").html(arrData);
                var options = {
                    show: true
                    }
                $('#elx_general_popup').modal(options);
                formSendFax();
            }
        }
    );       
}

/*llama a la función "sendFax dentro del módulo "my_fax""*/

function sendNewFax(){
    var arrAction = new Array();
    arrAction["menu"]="my_fax";
    arrAction["action"]="sendNewFax";
    arrAction["to"]=$("input[name='destinationFaxNumber']").val();
    if($('#elx-chk-attachment-file').is(':checked')) {
        arrAction["checked"]="true";
    }else{
        arrAction["body"]=$("textarea[name='faxContent']").val();
        arrAction["checked"]="false";
    }
    
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if (error != '' ){
                alert(error);
            }else{
                alert(arrData);
                $('#elx_general_popup').modal('hide');
            }
    });      
}

/*función para subir el archivo en el popup de "sendFax"*/
function formSendFax(){

    $('#faxFile').liteUploader(
    {
        script: '?menu=my_fax&action=faxAttachmentUpload&rawmode=yes',
        allowedFileTypes: null,
        maxSizeInBytes: null,
        customParams: {
            'custom': 'tester'
        },
        each: function (file, errors)
        {
            if (errors.length > 0)
            {
                alert('Error uploading your file');
            }

        },
        success: function (response)
        {
            var response = $.parseJSON(response);
            if(response.error !== ''){
                alert(response.error);
            }else{
                //alert(response.message);
            }
        }
    });
}

function elx_newEmail(alias){
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="get_templateEmail";
    if(alias){
        arrAction["destination"]=alias;
    }
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error != ''){
                alert(error);
            }else{
                //esto es importante hacer para asegurarmos que no haya 
                //oculto otro elemente con el mismo id
                $("#elx-compose-email").remove();
                $("#elx_popup_content").html(arrData['modulo']);
                $("#elx-compose-email").addClass("modal-content");
                $("#elx-compose-email").prepend("<div class='modal-header'><button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button><h3 id='myModalLabel'>Send Mail/Enviar Mail</h3></div>");
                $("#elx-compose-email").append("<div class='modal-footer'><button type='button' class='btn btn-primary' id='elx_attachButton'>Attach<input type='file' name='attachFileButton' id='attachFileButton'></button><button type='button' class='btn btn-primary' onclick='composeEmail(\"popup\")'>Send</button></div>");    
                emailAttachFile();
                var options = {
                    show: true
                    }
                $('#elx_general_popup').modal(options);
                richTextInit();
                
                //autocomplete mail list
                mailList(arrData['contacts']); 
            }
        }
    );
}

//funcion autocomplete para listar los contactos en la ventana popup de enviar email
function mailList(contacts){
    
    $('textarea[name=compose_to]')
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
            $( this ).data( "ui-autocomplete" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        minLength: 0,
        source: function( request, response ) {
          // delegate back to autocomplete, but extract the last term
          response( $.ui.autocomplete.filter(
            contacts, extractLast( request.term ) ) );
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        },
        appendTo: '#compose-to',
      });
      
    setTimeout(function(){ 
        var ancho = $('textarea[name=compose_to]').width();
        ancho = ancho +"px";
        $('.ui-autocomplete').width(ancho);    
    }, 3000);
    
}

function split( val ) {
    return val.split( /,\s*/ );
}
function extractLast( term ) {
    return split( term ).pop();
}

$(window).resize(function(){
        var ancho = $('textarea[name=compose_to]').width();
        ancho = ancho +"px"; 
        $('.ui-autocomplete').width(ancho);
});

var sess = null;

/**
 * Procedimiento que muestra la ventana dedicada al GUI del videoteléfono. Hasta
 * ahora hay 3 escenarios que hay que acomodar:
 * 1) Clic en botón de llamar independiente, ventana aparece con recuadro de 
 *    dial vacío, que hay que llenar antes de llamar.
 * 2) Clic en botón de llamar de chat, ventana aparece con recuadro de dial
 *    asignado a extensión de usuario chat, o cuenta IM si no hay extensión, y
 *    marcado inicia inmediatamente.
 * 3) Viene llamada desde el exterior, y no hay otro cuadro modal activo, 
 *    ventana aparece con recuadro de dial asignado a cuenta/número entrante,
 *    y con opciones de aceptar o rechazar la llamada. Por ahora, si el cuadro
 *    modal está activo, la llamada se rechaza de inmediato.
 * 
 * @param dialstring		Cadena de marcado del chat, o null para ventana libre
 * @param displayname		Texto de caller-id para llamadas entrantes
 * @param incoming_sess		Sesión entrante para llamada entrante, null para saliente
 */
function showCallWindow(dialstring, displayname, incoming_sess)
{
	var divCall = $('#elx_template_videocall > div.modal-content').clone();	
	$("#elx_popup_content").html(divCall);
	divCall = $("#elx_popup_content > div.modal-content");

	// TODO: manejar caso de que llamada entra habiendo una segunda llamada activa
	
	// Operaciones asignadas a cada uno de los los botones
	divCall.find('#elx_videocall_dial').on('click', startOutgoingCall);
	divCall.find('#elx_videocall_reject').on('click', function() {
		if (sess != null) sess.reject();
	});
	divCall.find('#elx_videocall_cancel').on('click', function() {
		if (sess != null) sess.cancel();
	});
	divCall.find('#elx_videocall_accept').on('click', function() {
		if (sess != null) sess.accept(getVideoCallMedia(divCall));
	});
	divCall.find('#elx_videocall_hangup').on('click', function() {
		if (sess != null) sess.bye();
	});
	
	// Esconder todos los botones, y luego mostrar los requeridos
	// TODO: manejar antes destrucción de sesión en caso de llamada entrante
	transitionCall_DialReady(divCall);

	$('#elx_general_popup').modal({show: true});
	if (incoming_sess != null) {
		divCall.find('div.elx_video_call_buttons > button').hide();
		divCall.find('#elx_videocall_accept').show();
		divCall.find('#elx_videocall_reject').show();
		divCall.find('input[name="elx_videocall_dialstring"]').attr('disabled', true);
		
		divCall.find('input[name="elx_videocall_dialstring"]').val(dialstring);
		sess = incoming_sess;
		setupSessionHandlers(sess, divCall);
		
		showVideoCallStatus(divCall, 'alert-info', 'Incoming call from ' + 
			displayname + ' at ' + dialstring);
	} else if (dialstring != null) {
		divCall.find('input[name="elx_videocall_dialstring"]').val(dialstring);
		startOutgoingCall();
	} else {
		// Ventana vacía lista para marcar
	}
}

function showVideoCallStatus(divCall, extraclass, msgtext, hide)
{
	divCall.find('div.elx_video_callstatus')
		.attr('role', 'alert')
		.removeClass('alert-success alert-info alert-warning alert-danger')
		.addClass('alert')
		.addClass(extraclass)
		.text(msgtext).show();
	if (hide) divCall.find('div.elx_video_callstatus').delay(5000).fadeOut(500);
}

/**
 * Procedimiento que inicia la llamada saliente usando el dialstring del 
 * recuadro de texto.
 */
function startOutgoingCall()
{
	var divCall = $("#elx_popup_content > div.modal-content");
	var videoRow = divCall.find('.elx_video_row');
	var dialInput = divCall.find('input[name="elx_videocall_dialstring"]');
	var dialstring = dialInput.val();
	
	if (ua == null || !ua.isConnected()) {
		alert('SIP Client not initialized!');
		return;
	}
	
	// Asignar poster de relleno para caso sin video
	var videoPosterParams = {
		menu: '_elastixutils',
		action: 'getVideoPoster',
		rawmode: 'yes',
		dialstring: dialstring
	};
	divCall.find('#elx_video_remote').attr('poster', 'index.php?' + $.param(videoPosterParams));
	videoPosterParams.dialstring = ua.configuration.authorizationUser;
	divCall.find('#elx_video_local').attr('poster', 'index.php?' + $.param(videoPosterParams));
	
	// Desactivar el ingreso de dialstring mientras haya llamada en progreso
	divCall.find('div.elx_video_call_buttons > button').hide();
	divCall.find('#elx_videocall_cancel').show();
	dialInput.attr('disabled', true);
	
	// Construir cadena de marcado completa con dominio
	dialstring = dialstring.trim();	
	if (dialstring.indexOf('@') == -1) {
		dialstring += '@' + ua.configuration.hostportParams;
	}
	
	sess = ua.invite(dialstring, getVideoCallMedia(divCall));
	setupSessionHandlers(sess, divCall);

	showVideoCallStatus(divCall, 'alert-info', 'Dialing to ' + dialstring);
}

function getVideoCallMedia(divCall)
{
	return {
		media: {
			constraints: {
				audio: true,
				video: true
			},
			render: {
				remote: {
					video: divCall.find('#elx_video_remote').get(0),
					audio: divCall.find('#elx_audio_remote').get(0)
				},
				local: {
					video: divCall.find('#elx_video_local').get(0)
				}
			}
		}
	};
}

function setupSessionHandlers(sess, divCall)
{
	sess.on('progress', function(response) {
		//g_progress_response = response;
		showVideoCallStatus(divCall, 'alert-info', response.status_code + ' ' + response.reason_phrase);
	}).on('cancel', function() {
		showVideoCallStatus(divCall, 'alert-success', 'Call cancelled', true);
		transitionCall_DialReady(divCall);
	}).on('accepted', function(data) {
		//g_accepted_data = data;
		showVideoCallStatus(divCall, 'alert-success', 'Call accepted', true);
		transitionCall_Accepted(divCall);
	}).on('failed', function(response, cause) {
		//g_failed_response = response;
		//g_failed_cause = cause;
		showVideoCallStatus(divCall, 'alert-danger', cause + ((response != null && (typeof response != 'string')) ? (': ' + response.status_code + ' ' + response.reason_phrase) : ''), true);
		transitionCall_DialReady(divCall);
	}).on('bye', function(request) {
		//g_bye_request = request;
		showVideoCallStatus(divCall, 'alert-info', 'Call terminated', true);
		transitionCall_DialReady(divCall);
	});
}

function transitionCall_DialReady(divCall)
{
	var videoRow = divCall.find('.elx_video_row');
	var dialInput = divCall.find('input[name="elx_videocall_dialstring"]');

	videoRow.hide();
	divCall.find('div.elx_video_call_buttons > button').hide();
	divCall.find('#elx_videocall_dial').show();
	dialInput.attr('disabled', false);
	sess = null;
}

function transitionCall_Accepted(divCall)
{
	var videoRow = divCall.find('.elx_video_row');
	var dialInput = divCall.find('input[name="elx_videocall_dialstring"]');

	videoRow.show();
	divCall.find('div.elx_video_call_buttons > button').hide();
	divCall.find('#elx_videocall_hangup').show();
	dialInput.attr('disabled', true);
}

/**
 * La clase Presentity es una clase que modela el documento XML que contiene la
 * información de presencia RPID. La presencia rica puede estar activa (propiedad
 * open en TRUE) o inactiva. Se puede agregar una nota para describir con más
 * detalle el tipo de actividad en que se encuentra el usuario. Además se soporta
 * una lista de actividades (propiedad activities) cuyo contenido determina si
 * el usuario está disponible, ocupado, ausente, u otra cosa. 
 * 
 * La clase soporta generar el XML a partir de las propiedades, y además parsear
 * un documento XML y separar sus propiedades. La implementación se ha probado
 * con el documento XML publicado por Jitsi.
 */
function Presentity()
{
	// urn:ietf:params:xml:ns:pidf      http://tools.ietf.org/html/rfc3863
	// urn:ietf:params:xml:ns:pidf:rpid http://tools.ietf.org/html/rfc4480
	this.user = "user";
	this.domain = "example.com";
	this.activities = [];
	this.status_icon = null;
	this.open = true;
	this.note = "Online";
	
	/** Generación del documento XML a partir de las propiedades */
	this.toXML = function() {
		var xml_presence = $.parseXML(
			'<?xml version="1.0" encoding="UTF-8" standalone="no"?>' +
			'<presence xmlns="urn:ietf:params:xml:ns:pidf" xmlns:dm="urn:ietf:params:xml:ns:pidf:data-model" xmlns:rpid="urn:ietf:params:xml:ns:pidf:rpid"/>');
		var sipuri = 'sip:' + this.user + '@' + this.domain;

		/* A partir de aquí se usan funciones nativas de XMLDocument porque la
		 * abstracción de jQuery no permite agregar elementos con namespace
		 */
		var xmlpr = xml_presence.getElementsByTagName('presence')[0];
		xmlpr.setAttribute('entity', sipuri);
				
		var xmlpers = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:data-model", "person");
		xmlpers.setAttribute('id', 'p1401');
		var xmlactivities = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", "activities");

		// http://tools.ietf.org/html/rfc4480
		var knownactivities = ['appointment', 'away', 'breakfast', 'busy', 'dinner',
			'holiday', 'in-transit', 'looking-for-work', 'lunch', 'meal', 'meeting',
			'on-the-phone', 'performance', 'permanent-absence', 'playing', 'presentation',
			'shopping', 'sleeping', 'spectator', 'steering', 'travel', 'tv', 'unknown',
			'vacation', 'working', 'worship'];
		for (var i = 0; i < this.activities.length; i++) {
			var xmlactv
			if (-1 != knownactivities.indexOf(this.activities[i])) {
				xmlactv = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", this.activities[i]);
			} else {
				xmlactv = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", 'other');
				xmlactv.appendChild(xml_presence.createTextNode(this.activities[i]));
			}
			xmlactivities.appendChild(xmlactv);
		}
		xmlpers.appendChild(xmlactivities);
		if (this.status_icon != null) {
			var xmlicon = xml_presence.createElementNS("urn:ietf:params:xml:ns:pidf:rpid", "status-icon");
			xmlicon.appendChild(xml_presence.createTextNode(this.status_icon));
			xmlpers.appendChild(xmlicon);
		}
		xmlpr.appendChild(xmlpers);
		

		var xmltuple = xml_presence.createElement('tuple');
		xmltuple.setAttribute('id', 't1072');

		var xmlstatus = xml_presence.createElement('status');
		var xmlbasic = xml_presence.createElement('basic');
		xmlbasic.appendChild(xml_presence.createTextNode(this.open ? 'open' : 'closed'));
		xmlstatus.appendChild(xmlbasic);
		xmltuple.appendChild(xmlstatus);
		var xmlcontact = xml_presence.createElement('contact');
		xmlcontact.appendChild(xml_presence.createTextNode(sipuri));
		xmltuple.appendChild(xmlcontact);
		var xmlnote = xml_presence.createElement('note');
		xmlnote.appendChild(xml_presence.createTextNode(this.note));
		xmltuple.appendChild(xmlnote);
		xmlpr.appendChild(xmltuple);
		
		return xml_presence;
	}
	
	/** Parseo de un XML y extracción de las propiedades */
	this.fromXML = function(xml_presence) {
		var xmlpr = xml_presence.getElementsByTagName('presence')[0];
		
		// Blink manda el atributo entity codificado
		var sipuri = decodeURIComponent(xmlpr.getAttribute('entity'));
		var m = /^(sip:)?(\S+)@(\S+)$/.exec(sipuri);
		this.user = m[2];
		this.domain = m[3];
		
		this.activities = [];
		var xmlactivities = xml_presence.getElementsByTagNameNS("urn:ietf:params:xml:ns:pidf:rpid", "activities");
		if (xmlactivities.length > 0) for (var i = 0; i < xmlactivities[0].childNodes.length; i++) {
			var xmlactv = xmlactivities[0].childNodes[i];
			if (xmlactv.nodeType == xml_presence.ELEMENT_NODE) {
				var m = /^(\S+:)?(\S+)/.exec(xmlactv.nodeName);
				var nodeName = m[2];
			
				if (nodeName == 'other') {
					// Asume que el texto es el único contenido
					if (xmlactv.childNodes.length > 0)
						this.activities.push(xmlactv.childNodes[0].nodeValue);
				} else {
					this.activities.push(nodeName);
				}
			}
		}
		
		this.status_icon = null;
		var xmlicons = xml_presence.getElementsByTagNameNS("urn:ietf:params:xml:ns:pidf:rpid", "status-icon");
		if (xmlicons.length > 0) {
			// Asume que el texto es el único contenido
			if (xmlicons[0].childNodes.length > 0)
				this.status_icon = xmlicons[0].childNodes[0].nodeValue;
		}
		
		var xmltuple = xml_presence.getElementsByTagName('tuple');
		if (xmltuple.length > 0) {
		
			this.open = false;
			var xmlstatus = xmltuple[0].getElementsByTagName('status');
			if (xmlstatus.length > 0) {
				var xmlbasic = xmlstatus[0].getElementsByTagName('basic');
				if (xmlbasic.length > 0) {
					this.open = ('open' == xmlbasic[0].childNodes[0].nodeValue);
				}
			}
			this.note = "Offline";
			var xmlnote = xmltuple[0].getElementsByTagName('note');
			if (xmlnote.length > 0) {
				this.note = xmlnote[0].childNodes[0].nodeValue;
			} else {
				// Blink no asigna la nota. Se sintetiza una en base a las actividades
				if (this.activities.indexOf('available') != -1) {
					this.note = 'Online';
				} else if (this.activities.indexOf('away') != -1) {
					this.note = 'Away';
				} else if (this.activities.indexOf('busy') != -1) {
					this.note = 'Busy (DND)';
				}
			}
		}
	}
	
	this.toString = function() {
		var xml = (new XMLSerializer()).serializeToString(this.toXML());
		xml = xml.replace(/ xmlns=""/g, '');
		return xml;
	}
	
	this.fromString = function(s) {
		return this.fromXML($.parseXML(s));
	}
	
	// Elegir el color a mostrar según notas y actividades
	this.suggestStateColor = function() {		
		var color = '#8cbe29';
		if (this.open) {
			if (this.activities.length == 1 && this.activities.indexOf('available') != -1) {
				// Estado disponible estilo Blink
				color = '#8cbe29';
			} else {
				if (this.activities.length > 0) color = 'orange';
				if (this.activities.indexOf('busy') != -1) color = 'red';
				if (this.activities.indexOf('on-the-phone') != -1) color = 'red';
			}
		} else {
			color = 'grey';
		}

		return color;
	}
}

function SIPPresence(ua)
{
	this.ua = ua;
	this.presentity = new Presentity();
	this.presentity.user = ua.configuration.authorizationUser;
	this.presentity.domain = ua.configuration.hostportParams;
	this.presenceETag = null;
	this.presenceTimer = null;
	this.publishRequest = null;
	this.roster = {}; // Subscripciones a lista de contactos
	this.subsWatch = null;	// Subscripción a presence.winfo
	
	this.getLocalContact = function() {
		return this.ua.configuration.authorizationUser + '@' + this.ua.configuration.hostportParams;
	}
	
	/**
	 * Método para iniciar la publicación de la presencia del usuario local.
	 * Para mitigar la situación de que no es posible cerrar la presencia de 
	 * forma síncrona, se establece la expiración de la presencia a 90 segundos,
	 * y se actualiza la presencia cada 60 segundos con un timer.
	 */
	this.publishPresence = function () {
	
		if (this.presenceETag == null) {
			// Verificar si se tiene un PUBLISH previo en el servidor
			$.get('index.php', {
				menu: '_elastixutils',
				action: 'getPublishState'			
			}, function(data) {
				if (data.message != "") {
					this.presenceETag = (data.message.ETag != "") ? data.message.ETag : null; // puede ser null
					this.presentity.note = data.message.note;
					this.presentity.activities = data.message.activities;
				}
				this._publishPresence();
			}.bind(this));
		} else {
			this._publishPresence();
		}
		
		$(".elx_li_contact").each(function(i, v) {
			this.subscribeToRoster($(v).data('uri'));
		}.bind(this));
	}
	this._publishPresence = function() {
		if (this.publishRequest == null) {
			var extrahdr = [
				'Event: presence',
				'Content-Type: application/pidf+xml',
				'Contact: ' + ua.contact.toString()
			];
			this.publishRequest = this.ua.request('PUBLISH', this.getLocalContact(), {
				body: this.presentity.toString(),
				extraHeaders: extrahdr
			});
			this.publishRequest.request.setHeader('Expires', '90');
			if (this.presenceETag != null) {
				this.publishRequest.request.setHeader('SIP-If-Match', this.presenceETag);
			}
			this.publishRequest.on('accepted', function (response, cause) {
				if (response.getHeader('Expires') != "0") {
					this.presenceETag = response.getHeader('Sip-Etag');
					// mandar this.presenceETag al servidor para recuperar 
					// SIP-If-Match luego de recargar la página
					$.post('index.php', {
						menu: '_elastixutils',
						action: 'setPublishState',
						ETag: this.presenceETag,
						note: this.presentity.note,
						activities: this.presentity.activities
					}, function(data) {});
					
					this.publishRequest.request.setHeader('SIP-If-Match', this.presenceETag);
					
					// Elegir el color a mostrar según notas y actividades
					$(".elx-content-photo").css('border-color', this.presentity.suggestStateColor());					
				} else {
					this.publishRequest = null;
					this.presenceETag = null;
					$.post('index.php', {
						menu: '_elastixutils',
						action: 'setPublishState',
						ETag: ''
					}, function(data) {});
					$(".elx-content-photo").css('border-color', 'gray');
				}
			}.bind(this));
			this.publishRequest.on('rejected', function (response, cause) {
				if (response.status_code == 412) {
					/* 412 Conditional request failed */
					if (this.presenceETag != null) {
						/* Si ocurre este error, entonces ha ocurrido un error 
						 * de sincronización, y el ETag almacenado es inválido.
						 */
						this.presenceETag = null;
						delete this.publishRequest.request.headers['Sip-If-Match'];
						this.publishRequest.send();
						return;
					}
				}
				
				// Ha ocurrido otro error que no se sabe manejar
				errorRegisterChatBar('Failed to PUBLISH presence: ' + response.status_code + ' ' + cause);
			}.bind(this));
		} else {
			this.publishRequest.request.body = this.presentity.toString();
			this.publishRequest.send();
		}

		if (null == this.presenceTimer)
			this.presenceTimer = window.setInterval(this.publishPresence.bind(this), 60 * 1000);
	}
	
	/**
	 * Método para retirar la presencia de la cuenta local, efectivamente indicado
	 * que se retira la sesión de chat.
	 */
	this.withdrawPresence = function () {
		
		if (null != this.presenceTimer) {
			window.clearInterval(this.presenceTimer);
			this.presenceTimer = null;
		}

		if (null == this.publishRequest) return;
		
		this.publishRequest.request.setHeader('Expires', '0');
		this.publishRequest.send();

		this._unsubscribeWithServerCheck(this.getLocalContact(), this.subsWatch);
		this.subsWatch = null;
		for (var contact in this.roster) this.unsubscribeFromRoster(contact);
		
	}
	
	this.setPresenceStatus = function (note, activities) {
		this.presentity.note = note;
		this.presentity.activities = activities;
		this.publishPresence();
	}
	
	/**
	 * Método privado que encapsula una subscripción a un contacto para que se
	 * verifique si hay un Call-ID previo para una subscripción previa a ese 
	 * mismo contacto. Si la hay, la subscripción resultante renueva la 
	 * subscripción anterior en lugar de crear una nueva subscripción.
	 */
	this._subscribeWithServerCheck = function(contact, event, checkCallback, notifyCallback) {
		var subscription = this.ua.subscribe(contact, event, {expires: 120});
		subscription.on('notify', notifyCallback);
		checkCallback(contact, subscription);
	}
	
	/**
	 * Método privado para deshacer una subscripción y anular el Call-ID de la
	 * subscripción que se destruye.
	 */
	this._unsubscribeWithServerCheck = function(contact, subscription) {
		if (subscription != null) {
			subscription.unsubscribe();
			subscription.close();
		}
	}
	
	/* Ejecutar la subscripción al evento presence.winfo . Este evento informa de
	 * quién desea observar al usuario local, y la política seguida aquí es la
	 * de autorizar de inmediato la escucha. */
	/*
	<?xml version="1.0"?>
	<watcherinfo xmlns="urn:ietf:params:xml:ns:watcherinfo" version="1" state="full">
	  <watcher-list resource="sip:avillacis@pbx.villacis.com" package="presence"/>
	</watcherinfo>
	
	<?xml version="1.0"?>
	<watcherinfo xmlns="urn:ietf:params:xml:ns:watcherinfo" version="2" state="partial">
	  <watcher-list resource="sip:avillacis@pbx.villacis.com" package="presence">
	    <watcher id="87590e851228150e5980f7ca45a1b9ce@0:0:0:0:0:0:0:0" event="subscribe" status="pending">sip:gmacas@pbx.villacis.com</watcher>
	  </watcher-list>
	</watcherinfo>

	*/
	this._subscribeWithServerCheck(this.getLocalContact(), 'presence.winfo', function(contact, subscription) {
		this.subsWatch = subscription;
	}.bind(this), function(notification) {
		var xmlwatch = $.parseXML(notification.request.body);

		$(xmlwatch).find('watcherinfo > watcher-list[package=presence] watcher[event=subscribe][status=pending]')
			.each(function(idx, value) {			
			//console.log("Aprobando ingreso a roster de contacto: " + $(value).text());
			this.subscribeToRoster($(value).text().replace(/^sip:/, ''));
		}.bind(this));		
	}.bind(this))
	
	/**
	 * Método para subscribirse a la presencia de un contacto, y recibir estado
	 * de presencia. Hasta ahora funciona con Jitsi. 
	 */
	this.subscribeToRoster = function(contact) {
		if (this.roster[contact] != null) return;
		
		this._subscribeWithServerCheck(contact, 'presence', function(contact, subscription) {
			this.roster[contact] = subscription;
		}.bind(this), function (notification) {
			var pres = new Presentity();
			pres.open = false;
			pres.note = 'Offline';
			pres.user = notification.request.from.uri.user;
			pres.domain = notification.request.from.uri.host;
			if ('' != notification.request.body) {
				pres.fromString(notification.request.body);
			}
			
			this._updateContactStatus(pres.user + "@" + pres.domain,
				pres.suggestStateColor(), pres.note);
		}.bind(this))
	}
	
	/**
	 * Método para quitar la subscripción del contacto.
	 */
	this.unsubscribeFromRoster = function(contact) {
		if (this.roster[contact] == null) return;
		this._unsubscribeWithServerCheck(contact, this.roster[contact]);
		delete this.roster[contact];

		this._updateContactStatus(contact, 'grey', '(unknown)');
	}
	
	this._updateContactStatus = function(contact, newColor, newNote) {
		var liContact = $(".elx_li_contact[data-uri='" + contact + "']");
		liContact.data('status', (newColor != 'grey') ? 'online' : 'offline');
		liContact.find('.box_status_contact').css('background-color', newColor);
		liContact.find('.extension_status').text(newNote);
		
		updateContactVisibility();
	}
}
