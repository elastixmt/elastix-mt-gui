$(document).ready(function(){
    wrap_list_messages = $('#elx_div_email_listmsg');
    div_list_msg=$("#elx_list_mail_messages");
    wrap_elx_elx_viewcmpmsg = $("#elx_elx_viewcmpmsg");
    elx_bodymail= $('#elx_bodymail');
    pull2 = $('#icn_disp1');
    pull = $('#pull');
    leftdiv = $('#leftdiv');
    centerdiv = $('#centerdiv');
    rightdiv = $('#rightdiv');
    pull3 = $('#icn_disp2');                    
    mail_toolbar = $('#mail_toolbar');
    paneldiv = $('#paneldiv');
    main_content_div = $('#main_content_elastix');
    pagingdiv=$("#elx_mail_pagingbar");
 
    
    //this is necesary to avoid appeard scroll in the windown
    main_content_div.css('overflow','hidden');
    
    $(pull2).on('click', function(e) {
        var w = $(window).width();
        var content_w=main_content_div.width();
        //panel está oculto y lo vamos a abrir
        if(leftdiv.is(':hidden')){
            $(pull2).children('span').removeClass("glyphicon-folder-open").addClass("glyphicon-folder-close");
            leftdiv.show(10);
            if(w>=600){
                centerdiv.css('margin-left',140);
            }else{
                centerdiv.css('margin-left',0);
            }
            $('#display1').css('left',140);
        }else{
            //panel está abierto lo vamos a ocultar
            $(pull2).children('span').removeClass("glyphicon-folder-close").addClass("glyphicon-folder-open");
            leftdiv.hide(10);
            centerdiv.css('margin-left',0);
            $('#display1').css('left',0);
        }
    });
    $(window).resize(function(){
        w = $(window).width();
        var content_w=main_content_div.width();
        if(w>=600){
            var center_w = centerdiv.width();
            if(leftdiv.is(':hidden')==false){ //el panel izquierdo esta abierto
                centerdiv.css('margin-left',140);
            }else{ //el panel izquierdo esta cerrado
                centerdiv.css('margin-left',0);
            }
        }else{
            centerdiv.css('margin-left',0);
        }
        resizedivmails();
        heightListMails();
    });
    
    resizedivmails();
    
    $(this).on("click",".folder-item",function(e){
        var foldername=$(this).attr("data-foldername");
        show_messages_folder(foldername,$(this));
    });
    
    $(".elx_close_email_msg").click(function() {
        $("#initial_message_area").slideUp();
        $("#message_area").slideUp();
    });
    
    $("#email_refresh").click(function() {
        $("input[name='elx_sel_view_filter_h']").val('all');
        show_email_msg(false);
    });
    
    $("#email_trash").click(function() {
        //necesito obtener la lista de los mails seleccionados
        var listUIDs='';
        $('.checkmail:checked').each(function (e){
            if(typeof $(this).val() === "string"){
                listUIDs +=$(this).val()+",";
            }
        });
        if(listUIDs!=''){
            if($("input[name='current_mailbox']").val()=='Trash'){
                delete_msg_trash(listUIDs);
            }else{
                mv_msg_to_folder('Trash',listUIDs);
            }
        }
    });
    
    $(this).on("click",".elx_amvfolder",function(e){
        //necesito obtener la lista de los mails seleccionados
        var listUIDs='';
        $('.checkmail:checked').each(function (e){
            if(typeof $(this).val() === "string"){
                listUIDs +=$(this).val()+",";
            }
        });
        var newFolder=$(this).attr('data-nameFolder');
        if(listUIDs!='' && newFolder!=''){
            mv_msg_to_folder(newFolder,listUIDs);
        }
    });
    
    $(this).on("click",".elx_row_email_msg",function(e){
        var UID=$(this).parent('.elx_row').attr('id');
        view_body(UID)
    });
    //mark msg as important
    $(this).on("click",".elx_unflagged_email",function(e){
        var UID=$(this).parents('.elx_row').attr('id');
        toggle_important('flagged',UID);
    });
    //mark msg as unimportant
    $(this).on("click",".elx_flagged_email",function(e){
        var UID=$(this).parents('.elx_row').attr('id');
        toggle_important('unflagged',UID);
    });
    //accion que controla cuando damos enter el cuadro de texto para crear un nuevo mailbox
    $(this).on("keydown","input[name='new_mailbox_name']", function( event ) {
            // Ignore TAB and ESC.
            if (event.which == 9 || event.which == 27) {
                return false;
                // Enter pressed? so send chat.
            }else if ( event.which == 13 && $(this).val()!='') {
                event.preventDefault();
                //debemos mandar el mensaje y 
                //hacer que el texto del text area desaparezca y sea enviado la divdel chat al que corresponde
                var new_folder=$(this).val();
                create_new_mailbox(new_folder);
                // Ignore Enter when empty input.
            }else if (event.which == 13 && $(this).val() == "") {
                event.preventDefault();
                return false;
            }
        }
    );
    $(this).on("click",".elx-del-compose-header",function( event ) {
        $(this).parent("tr:first").hide();
        var idtr=$(this).parent("tr:first").attr('id');
        var header_field=idtr.substring(8);
        $("textarea[name='compose_"+header_field+"']").val('');
        $("#elx_link_"+header_field).show();
        var elx_count=0;
        var prev_id='';
        $("#compose-extra-headers > td.elx_compose_htd").children().each( function () {
            if($(this).attr('class')=='elx_compose_header_link' && $(this).is(':hidden')!=true){
                elx_count=elx_count+1;
                if(elx_count==2){
                    elx_count=1;
                    $("#"+prev_id).next('span').show();
                }
                prev_id=$(this).attr('id');
            }
        });
    });
    
    //paging function
    //esta funcion se llama cuando de da click en alguno de los íconos del pagineo
    $(this).on("click",".elx_mail_pagingbar_icon",function( event ) {
        var actionpage=$(this).attr('data-actionpage');
        if(actionpage=='start' || actionpage=='prev' || actionpage=='next' || actionpage=='end'){
            $("input[name='action_paging']").val(actionpage);
            show_email_msg(true);
        }
    });
    
    //funcion que pregunta si ahi mensajes nuevos
    refreshMessage();
    
    //llamamos a la funcion que le dará el alto al div que contiene la lista de correos
    heightListMails();
    
});

//necesario setear la altura maxima del div que contine la data del home
//asi como la altura maxima del panel lateral donde se muestran la lista de las carpetas
function resizedivmails(){
    var height_content_browser = $( window ).height();
    //menuheight = $("#tooldiv").height()+$("#elx-slide-menu-mini").height();
    $('#main_content_elastix').css('height',(height_content_browser - 60)+'px');
    
    h_main_content_div=main_content_div.height();
    mailtoolbar_h=mail_toolbar.height();
    leftdiv.css('height',main_content_div.height()+'px');
    //div que se usa para ver o componer un mail
    elx_bodymail.css('max-height',(h_main_content_div - mailtoolbar_h)+'px');
    //div que se usa para visualizar la lista de correos
    //a este div ahi que quitarle la latura del div del pagineo
    pagingdiv_h=pagingdiv.height();
    div_list_msg.css('max-height',(h_main_content_div - mailtoolbar_h - pagingdiv_h)+'px');
}

/**
 * funcion para seleccionar todos los emails de la lista
 **/
function select_all_emailview(){
    if($("#select_all_emailview").is(":checked")){
        $(".checkmail").prop('checked', true);
    }else{
        $(".checkmail").prop('checked', false);
    }
}
function show_email_msg(paging){
    showElastixUFStatusBar("Searching...");
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="show_messages_folder";
    arrAction["folder"]=$("input[name='current_mailbox']").val();
    arrAction["email_filter1"]=$("input[name='elx_sel_view_filter_h']").val();
    arrAction["rawmode"]="yes";
    //if paging=true significa que se ha da click a uno de los iconos del pagineo 
    //por lo tanto ahi que mandar los siguientes parametros 
    // * nav=bypage 
    // * page=numero de la pagina que queremos consultar
    if(paging){
        var currentPage=$("input[name=elx_currentpage]").val();
        if(isNaN(currentPage)){
            currentPage=1;
        }else{
            currentPage=parseInt(currentPage);
        }
        
        var numPage=$("input[name=elx_numpages]").val();
        if(isNaN(numPage)){
            numPage=1;
        }else{
            numPage=parseInt(numPage);
        }
        
        //sacamos cual es la pagina que el usuario quiere mostrat
        var actionpaging=$("input[name=action_paging]").val();
        if(actionpaging=='end'){
            var page=numPage;
        }else if(actionpaging=='prev'){
            var page=currentPage - 1;
        }else if(actionpaging=='next'){
            var page=currentPage + 1;
        }else{
            //start
            page=1;
        }
        //validar si page es un numero
        if(isNaN(page)){
            page=1;
        }else{
            if (page % 1 != 0) {
                page=1;
            } 
        }
        
        if(page>numPage){
            page=numPage;
        }
        arrAction["page"]=page;
        arrAction["nav"]='bypage';
    }else{
        var currentPage=$("input[name=elx_currentpage]").val();
        if(isNaN(currentPage)){
            currentPage=1;
        }else{
            currentPage=parseInt(currentPage);
        }
        arrAction["page"]=currentPage;
        arrAction["nav"]='bypage';
    }
    
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if(error!=""){
                alert(error);
            }else{
                //este es para marcar por el valor correcto en el filtro1 (seen,unseen, ...)
                $("input[name='elx_sel_view_filter_h']").val(arrData['email_filter1']);
                var name_tag=$("#elx_email_vsel_"+arrData['email_filter1']).html();
                $("#elx_sel_view_filter").html(name_tag);
                
                createListEmailMsg(arrData);
            }     
    });
}
function show_messages_folder(folder,element){
    showElastixUFStatusBar("Loading...");
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="show_messages_folder";
    arrAction["folder"]=folder;
    arrAction["email_filter1"]=$("#email_filter1 option:selected").val();
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if(error!=""){
                alert(error);
            }else{
                $("input[name='current_mailbox']").val(folder);
                
                $(".folder-item").css('color',"rgb(68, 68, 68)");
                element.css('color','#dd271d');
                
                createListEmailMsg(arrData);
            }
    });
}
function refreshMessage(){
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="refreshMail";
    arrAction["folder"]=$("input[name='current_mailbox']").val();
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, true,
        function(arrData,statusResponse,error){
            if(error!=""){
                alert(error);
                return true; //paramos recursividad
            }else{
                if(statusResponse=='CHANGED'){
                    show_email_msg(false);
                }
            }
    });
}
function createListEmailMsg(arrData){
    var mailMsg=arrData['email_content'];
    if(mailMsg.length>0){
        var messaje_list='';
        for( var i=0; i<mailMsg.length; i++){
            for( var j=0; j<mailMsg[i].length; j++){
                messaje_list +=mailMsg[i][j];
            }
        }
    }else{
        //no ahi mensaje para mostrar mostramos un mensaje
        messaje_list='<div class="elx_row elx_unseen_email" style="text-align:center">There is not message</div>';
    }
    
    if(arrData['imap_alerts']!=''){
        //se produjeron alertas por parte de las funciones imap
        //posibles errores. Debemos mostrar estos errores en pantalla
        showElxUFMsgBar('error',arrData['imap_alerts']);
    }
    
    //actualizamos el listado de carpetas a las que podemos mover los mensajes seleccionados
    var li_mailbox_mv='';
    var listMailboxMv=arrData['move_folders'];
    for( var x in listMailboxMv){
        li_mailbox_mv +="<li><a href='#' data-nameFolder='"+x+"' class='elx_amvfolder'>"+listMailboxMv[x]+"</a></li>";
    }
    $("#elx_email_mv_ul").html(li_mailbox_mv);
    
    //modificar los datos del pagineo
    $("#elx_mail_pagingbar_nummails > span").html(arrData['paging']['total']);
    $("input[name=elx_currentpage]").val(arrData['paging']['currentPage']);
    $("#elx_mail_pagingbar_currentpg > span").html(arrData['paging']['currentPage']);
    $("input[name=elx_numpages]").val(arrData['paging']['numPages']);
    
    div_list_msg.html(messaje_list);
    wrap_elx_elx_viewcmpmsg.hide(10);
    $("#elx-bodymsg-tools").hide(10);
    $("#tools-mail_toolbar").show(11);
    wrap_list_messages.show(10);
}
function search_email_message_view(id_tag){
    $("input[name='elx_sel_view_filter_h']").val(id_tag);
    show_email_msg(false);
}
function mv_msg_to_folder(folder,listUIDs){
    
    if(listUIDs!=''){
        showElastixUFStatusBar("Doing...");
        var arrAction = new Array();
        arrAction["menu"]="home";
        arrAction["action"]="mv_msg_to_folder";
        arrAction["current_folder"]=$("input[name='current_mailbox']").val();
        arrAction["new_folder"]=folder;
        arrAction["UIDs"]=listUIDs;
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                hideElastixUFStatusBar();
                if(error!=""){
                    alert(error);
                    showElxUFMsgBar('error',error);
                }else{
                    showElxUFMsgBar('success',arrData);
                    show_email_msg(false);
                }     
        });
    }
}
function mark_email_msg_as(tag){
    //necesito obtener la lista de los mails seleccionados
    var listUIDs='';
    $('.checkmail:checked').each(function (e){
        if(typeof $(this).val() === "string"){
            listUIDs +=$(this).val()+",";
        }
    });
    if(listUIDs!=''){
        showElastixUFStatusBar("Doing...");
        var arrAction = new Array();
        arrAction["menu"]="home";
        arrAction["action"]="mark_msg_as";
        arrAction["folder"]=$("input[name='current_mailbox']").val();
        arrAction["tag"]=tag;
        arrAction["UIDs"]=listUIDs;
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                hideElastixUFStatusBar();
                if(error!=""){
                    alert(error);
                    showElxUFMsgBar('error',error);
                }else{
                    showElxUFMsgBar('success',arrData);
                    show_email_msg(false);
                }     
        });
    }
}
function toggle_important(tag,uid){
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="toggle_important";
    arrAction["folder"]=$("input[name='current_mailbox']").val();
    arrAction["tag"]=tag;
    arrAction["uid"]=uid;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error!=""){
                alert(error);
            }else{
                if(tag=='flagged'){
                    $("#"+uid+" > div.ic > div.star > span").removeClass('elx_unflagged_email').addClass('elx_flagged_email');
                }else{
                    $("#"+uid+" > div.ic > div.star > span").removeClass('elx_flagged_email').addClass('elx_unflagged_email');
                }
            }     
    });
}
function delete_msg_trash(listUIDs){
    if(listUIDs!=''){
        showElastixUFStatusBar("Doing...");
        var arrAction = new Array();
        arrAction["menu"]="home";
        arrAction["action"]="delete_msg_trash";
        arrAction["UIDs"]=listUIDs;
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                hideElastixUFStatusBar();
                if(error!=""){
                    alert(error);
                    showElxUFMsgBar('error',error);
                }else{
                    showElxUFMsgBar('success',arrData);
                    show_email_msg(false);
                }     
        });
    }
}
function view_body(UID){
    showElastixUFStatusBar("Loading...");
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="view_bodymail";
    arrAction["uid"]=UID;
    arrAction["current_folder"]=$("input[name='current_mailbox']").val();
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                hideElastixUFStatusBar();
                if(error!=""){
                    alert(error);
                }else{
                    createBodyMsg(arrData,UID);
                    wrap_list_messages.hide(10);
                    $("#tools-mail_toolbar").hide(10);
                    wrap_elx_elx_viewcmpmsg.show(10);
                    elx_bodymail.css('overflow','');
                    $("#elx-bodymsg-tools").show(11);
                    $("#elx-bodymsg-tools-view").show();
                    $("#elx-bodymsg-tools-sent").hide();
                    $('#'+UID).removeClass('elx_unseen_email').addClass('elx_seen_email');
                }     
        });
}
function createBodyMsg(arrData,UID){
    var current_folder=$("input[name='current_mailbox']").val();
    
    var subject="<div id='elx_bodymsg_subject'>";
    subject +="<h1>"+arrData['header']['subject']+"</h1>";
    subject +="</div>";
    
    var hTable=new Array('from','to','date','cc','bcc');
    var header="<div id='elx_bodymsg_header'>";
    header +="<table id='elx_bodymsg_theader'>";
    for( var x in hTable){
        if(typeof arrData['header'][hTable[x]] !== 'undefined'){
            if(arrData['header'][hTable[x]]['content'] != ''){
                header +="<tr class='elx_bodymsg_trheader' id='elx_eh_"+hTable[x]+"'>";
                header +="<td class='elx_bodymsg_tdheader'>"+arrData['header'][hTable[x]]["tag"]+":</td>";
                header +="<td class='elx_bodymsg_tdheader'>"+arrData['header'][hTable[x]]["content"]+"</td>";
                header +='</tr>';
            }
        }
    }
    
    var reply_to='';
    if(typeof arrData['header']['reply_to'] !== 'undefined'){
        reply_to = arrData['header']['reply_to'];
    }
    header +="</table>";
    header +="</div>";
    
    var divattachment='';
    if(typeof arrData['attachment'] !== 'undefined'){
        var attachment=arrData['attachment'];
        if(attachment.length > 0){
            divattachment="<div id='elx_bodymsg_attachment'>";
            divattachment +="<img src='web/apps/home/images/Paper-Clip.png' style='background-color: white;'  class='elx_bodymsg_file_att' />";
            for( var i=0; i<attachment.length ; i++){
                divattachment +="<div class='elx_bodymsg_file_att'><a href='index.php?menu=home&action=download_attach&rawmode=yes&uid="+UID+"&enc="+arrData['attachment'][i]['enc']+"&partnum="+arrData['attachment'][i]['partNum']+"&current_folder="+current_folder+"'>" +arrData['attachment'][i]['name']+"</a></div>";
            }
            divattachment +="</div>";
        }
    }
    
    
    var content="<div id='elx_bodymsg_body'>";
    if(typeof arrData['body']!=='undefined'){
         content +=arrData['body'];
    }
    content +="</div>";

    hidden="<div id='elx_bodymsg_hidden'>";
    hidden +="<input type='hidden' name='elx_UID' value='"+UID+"'>";
    hidden +="<input type='hidden' name='elx_reply_to' value='"+reply_to+"'>";
    hidden +="<div>"; 
    
    var bodymail=subject+header+divattachment+content+hidden;
    elx_bodymail.html(bodymail);
}
function new_folder(){
    $("input[name='new_mailbox_name']").parent().css('display','block');
    $("input[name='new_mailbox_name']").focus();
}
function create_new_mailbox(new_folder){
    showElastixUFStatusBar("Loading...");
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="create_mailbox";
    arrAction["new_folder"]=new_folder;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
            function(arrData,statusResponse,error){
                hideElastixUFStatusBar();
                $("input[name='new_mailbox_name']").parent().css('display','none');
                if(error!=""){
                    alert(error);
                }else{
                    //agregamos la carpeta recien creada a la lista
                    $("input[name=new_mailbox_name]").parent().before(
                    "<div class='folder' onclick='show_messages_folder("+"'"+new_folder+"');>"+new_folder+"</div>");
                }   
        });
}
function return_mailbox(){
    wrap_elx_elx_viewcmpmsg.hide(10);
    $("#elx-bodymsg-tools").hide(10);
    wrap_list_messages.show(10);
    $("#tools-mail_toolbar").show(10);
}
function elx_email_prev_msg(){
    var UID=$("input[name='elx_UID']").val();
    var prev_uid=$("#"+UID).prev('.elx_row').attr('id');
    if(typeof prev_uid!=='undefined'){
        view_body(prev_uid);
        return true;
    }else{
        return false;
    }
}
function elx_email_next_msg(){
    var UID=$("input[name='elx_UID']").val();
    var next_uid=$("#"+UID).next('.elx_row').attr('id');
    if(typeof next_uid!=='undefined'){
        view_body(next_uid);
        return true;
    }else{
        return false;
    }
}
function actions_email_msg(action){
    var UID=$("input[name='elx_UID']").val();
    if(typeof UID === 'undefined'){
        alert('Invalid Message');
    }
    
    if(action=='reply' || action=='reply_all' || action=='forward'){
        showElastixUFStatusBar("Loading...");
        var arrAction = new Array();
        arrAction["menu"]="home";
        arrAction["action"]="get_templateEmail";
        arrAction["rawmode"]="yes";
        request("index.php", arrAction, false,
                function(arrData,statusResponse,error){
                    hideElastixUFStatusBar();
                    if(error!=""){
                        alert(error);
                    }else{
                        //mostrar la barra de acciones al mandar un menu
                        $("#elx-bodymsg-tools-view").hide(10);
                        $("#elx-bodymsg-tools-sent").show(10);
                        
                        //esto es importante hacer para asegurarmos que no haya 
                        //oculto otro elemente con el mismo id
                        $("#elx-compose-email").remove();
                        
                        formComposeMsg(action,arrData['modulo']);
                        mailList(arrData['contacts']); 
                    }   
        });
    }else if(action=='delete'){
        if($("input[name='current_mailbox']").val()=='Trash'){
            delete_msg_trash(UID);
        }else{
            mv_msg_to_folder('Trash',UID);
        }
    }else if(action=='flag_important'){
        toggle_important('flagged',UID);
    }else if(action=='flag_unimportant'){
        toggle_important('unflagged',UID);
    }else{
        alert('Invalid Message');
    }
}
/**
 * Cuando se reenvia un correo los archivos adjuntos tambien
 * se deben reenviar. Por ello es necesario que dado el id del correo
 * que estamos reenviando obtengamos de este sus archivos adjuntos
 * para adjuntarlos al nuevo correo
 */
function forwardGetAttachments(UID){
    //mostrar que se esta cargando los archivos adjuntos
    $("#login_loading_attach").show();
    var arrAction = new Array();
    arrAction["menu"]="home";
    arrAction["action"]="forwardGetAttachs";
    arrAction["uid"]=UID;
    arrAction["current_folder"]=$("input[name='current_mailbox']").val();
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            $("#login_loading_attach").hide();
            if(error!=""){
                //mostrar error de que no se pudieron obtener los archivos adjuntos
                alert(error);
            }else{
                //crear el div para cada archivo adjunto
                for(var x in arrData){
                    var attachFile_item="<div class='elx-compose-msg-attachitem'>";
                    attachFile_item +=arrData[x]['name'];
                    attachFile_item +="<a href='#' id='"+arrData[x]['idAttach']+"' onclick='emailDetachFile(\""+arrData[x]['idAttach']+"\")'><img src='admin/web/themes/elastixneo/images/bookmarks_equis.png' width='18' height='16' align='absmiddle' border='0'></a>";
                    attachFile_item +="</div>";
                    $("#elx-compose-msg-attach").append(attachFile_item);
                }
            }   
    });
}

/**
 * esta funcion es invocada cuando se quiere responder o reenviar un mensaje
 * recibe como parametros la acción que estamos realizando y la plantilla vacia usada para componer 
 * un mensaje
 **/
function formComposeMsg(action,compose_template){
    //primero mandamos el contenido actual a un nivel mas bajo
    //encapsulando en un nuevo div
    //a ese div debemoa agregarle la cabera del mensaje
    //creamos un nuevo div que es el que contendrá el mensajes que vamos a componer
    //cambiamos el subject del mensaje
    
    //en caso de reply y reply all es necesario llenar los destinatarios
    
    var old_subject=$("#elx_bodymsg_subject > h1").text();
    if(action=='reply' || action=='reply_all'){
        var subject="RE: "+old_subject;
    }else{
        var subject="FW: "+old_subject;
    }
    
    var UID=$("input[name='elx_UID']").val();
    var reply_to=$("input[name='elx_reply_to']").val();
    
    //old header
    var old_header = new Array();
    old_header['to']={'content':'','tag':''};
    old_header['from']={'content':'','tag':''};
    old_header['date']={'content':'','tag':''};
    old_header['cc']={'content':'','tag':''};
    for(x in old_header){
        var htr=$("#elx_eh_"+x);
        if(typeof htr !== 'undefined'){
            old_header[x]['tag']=htr.children('td:nth-child(1)').text();
            old_header[x]['content']=htr.children('td:nth-child(2)').text();
        }
    }
    old_header['subject']={'content':old_subject,'tag':'Subject'};
    
    
    var oldHeaderdiv="<div id='old_header'>";
    for(x in old_header){
        if(old_header[x]['content']!=''){
            oldHeaderdiv +="<p>"+old_header[x]['tag']+old_header[x]['content']+"</p>";
        }
    }
    oldHeaderdiv +="</div>";
    
    var oldContent=$("#elx_bodymsg_body");
    var oldAttach=$("elx_bodymsg_attachment");
    
    elx_bodymail.html('');
    elx_bodymail.html(compose_template);
           
    $("input[name='compose-subject']").val(subject);
    if(action=='forward'){
        forwardGetAttachments(UID);
        $("#elx-compose-msg").append("<div id='compose_n_msg_content' style='min-height:20px;'></div>");
        $("#elx-compose-msg").append("<hr>");
        $("#elx-compose-msg").append(oldHeaderdiv);
        $("#elx-compose-msg").append(oldContent);
    }else{
        //reply and reply_all
        //si existe el campo reply_to, se reenvia el mensaje a esta direccion 
        //caso contrario se reenvia el mensaje a la direccion que aparece en from
        if(reply_to!='' && typeof reply_to !=='undefined'){
            $("textarea[name='compose_to']").val(reply_to);
        }else
            $("textarea[name='compose_to']").val(old_header['from']['content']);
        
        if(action=='reply_all'){
            //en el caso de reply_all se debe contestar tambien a las direcciones en el campo cc
            if(old_header['cc']['content']!=''){
                $("textarea[name='compose_cc']").val(old_header['cc']['content']);
                $("#compose-cc").show();
                $("#elx_link_hcc").hide();
                $("#elx_link_hcc").next('span').hide();
            }
        }
        $("#elx-compose-msg").append("<div id='compose_n_msg_content' style='min-height:20px;'></div>");
        elx_bodymail.append("<hr>");
        elx_bodymail.append(oldHeaderdiv);
        elx_bodymail.append(oldContent);
    }
    
    $('#attachFileButton').replaceWith("<input type='file' name='attachFileButton' id='attachFileButton'>");
    emailAttachFile();
    richTextInit();
}

function heightListMails(){
    //calculamos la distancia para el div que contiene los correos electronicos
    var height_browser = $(window).height();
    distancia=$("#elx_list_mail_messages").offset();
    posy=distancia.top;
    var result= height_browser - posy - 55;
    $("#elx_list_mail_messages").css("height",result +"px");
}