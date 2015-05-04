{literal}
<link rel="stylesheet" href="web/_common/js/jquery/css/blitzer/jquery-ui-1.8.24.custom.css">
{/literal}
<script type='text/javascript' src="web/_common/js/jquery.liteuploader.js"></script>
<div id='elx-compose-email'>
    <div id='compose-headers-div' class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <table id='compose-headers-table'>
                <tr id='compose-to'>
                    <td></td>
                    <td class='elx_compose_htd'><label for='compose_to'>{$TO}:</label><textarea name='compose_to' class='elx_compose_textarea'>{if $USERNAME}{$USERNAME}{/if}</textarea></td>
                </tr>
                <tr id='compose-cc' style='display:none'>
                    <td class='elx-del-compose-header'> <span class='glyphicon glyphicon-minus-sign'></span></td>
                    <td class='elx_compose_htd'><label for='compose_cc'>{$CC}:</label><textarea name='compose_cc' class='elx_compose_textarea' ></textarea></td>
                </tr>
                <tr id='compose-bcc' style='display:none'>
                    <td class='elx-del-compose-header'> <span class='glyphicon glyphicon-minus-sign'></span></td>
                    <td class='elx_compose_htd'><label for='compose_cco'>{$BCC}:</label><textarea name='compose_bcc' class='elx_compose_textarea'></textarea></td>
                </tr>
                <tr id='compose-reply_to' style='display:none'>
                    <td class='elx-del-compose-header'> <span class='glyphicon glyphicon-minus-sign'></span></td>
                    <td class='elx_compose_htd'><label for='compose_replay_to'>{$REPLYTO}:</label><textarea name='compose_replay_to' class='elx_compose_textarea'></textarea></td>
                </tr>
                <tr id='compose-extra-headers'>
                    <td></td>
                    <td class='elx_compose_htd'>
                    <a href="#cc" id='elx_link_cc' onclick='showComposeHeader("cc")' class='elx_compose_header_link'>{$CC}</a>
                    <span> | </span>
                    <a href="#bcc" id='elx_link_bcc' onclick='showComposeHeader("bcc")' class='elx_compose_header_link'>{$BCC}</a>
                    <span> | </span>
                    <a href="#reply_to" id='elx_link_reply_to' onclick='showComposeHeader("reply_to")' class='elx_compose_header_link'>{$REPLYTO}</a>
                    </td>
                </tr> 
                <tr id='compose-subject' style='margin:5px 0'>
                    <td></td>
                    <td class='elx_compose_htd'><label for='compose_to'>{$SUBJECT}:</label><input name='compose-subject' style='width:100%; border:1px solid #999;'></input><td>
                </tr>
            </table>
        </div>
    </div>
    <div id='elx-compose-msg-attach' style='margin-bottom: 2px;'>
        <div class='elx-compose-msg-attachitem' id='login_loading_attach' style='display:none'>
            <img src='{$WEBCOMMON}images/loading.gif' /> {$TEXT_UPLOADING}
        </div>
    </div>
    <div id='elx-compose-msg'>
    </div>
</div>
<input type='hidden' name='elx_language' value="{$USER_LANG}">
<input type='hidden' name='msg_emptyto' value="{$MSG_EMPTYTO}">
<input type='hidden' name='msg_emptysubject' value="{$MSG_SUBJECT}">
<input type='hidden' name='msg_emptycontent' value="{$MSG_CONTENT}">
<input type='hidden' name='elx_txtuploading' value='{$TEXT_UPLOADING}'>
<input type='hidden' name='elx_txtattach' value='{$TEXT_attach}'>
<input type='hidden' name='elx_txtsend' value='{$TEXT_send}'>
{literal}
    <style type="text/css">
        .mce-widget button {
            height: 28px;
        }
        .elx_compose_textarea{
            resize: none;
            width: 100%;
            border: 1px solid #999;
        }
        .elx-del-compose-header{
            vertical-align:top;
        }
        .elx_compose_htd{
            width: 100%;
        }
        #elx-compose-msg-attach{
            background-color: #FFFFFF;
            margin: 0px;
            padding: 0px;
            border: 0px;
            position: relative;
            float: none;
        }
        .elx-compose-msg-attachitem{
            margin: 3px 1px 2px 3px;
            padding: 2px 5px 2px 5px;
            border: 1px solid #aaa;
            border-radius: 5px 5px 5px 5px;
            -moz-border-radius: 5px 5px 5px 5px;
            -webkit-border-radius: 5px 5px 5px 5px;
            position: relative;
            display: inline-block;
        }
        .elx-compose-msg-attachitem img{
            width:18px;
            height:16px;
            padding-left: 2px;
            border:0;
            display:inline-block;
            float:none;
        }
        #elx_attachButton{
            position:relative;
            overflow:hidden;
            max-width:60px;
        }
        #attachFileButton{
            position: absolute;
            top: 0;
            right: 0;
            margin: 0;
            opacity: 0;
            -ms-filter: 'alpha(opacity=0)';
            font-size: 200px;
            direction: ltr;
            cursor: pointer;
        }
    </style>
    <script type="text/javascript">
        //mustra extra header al momento de componer un emial
        function showComposeHeader(header_field){
            $("#compose-"+header_field).show();
            //ocultar el link que muestra el extra header
            $("#elx_link_"+header_field).hide();
            if(header_field=='reply_to'){
                if($("#elx_link_bcc").is(':hidden')==false){
                    $("#elx_link_bcc").next('span').hide();
                }else if($("#elx_link_cc").is(':hidden')==false && $("#elx_link_bcc").is(':hidden')==true){
                    $("#elx_link_cc").next('span').hide();
                }
            }else{
                $("#elx_link_"+header_field).next('span').hide();
            }
        }

        function emailAttachFile(){
            var txtUploading=$("input[name='elx_txtuploading']").val();
            var divAttach=null;
            $('#attachFileButton').liteUploader(
            {
                script: '?menu=home&action=attach_file&rawmode=yes',
                allowedFileTypes: null,
                maxSizeInBytes: null,
                before: function (files)
                {   //mostrar que se esta cargando el archivo adjunto
                    var div_itemattach="<div class='elx-compose-msg-attachitem' id='login_loading_attach'>";
                    div_itemattach +="<img src='web/_common/images/loading.gif'/>"+txtUploading;
                    div_itemattach +="</div>";
                    $("#elx-compose-msg-attach").append(div_itemattach);
                    divAttach=$("#elx-compose-msg-attach").children(':last');
                },
                success: function (response)
                {
                    $("#login_loading_attach").hide();
                    var response = $.parseJSON(response);
                    if(response.error !== ''){
                        showElxUFMsgBar('error',response.error);
                        divAttach.remove();
                    }else{
                        //reemplazo en el div creado anteriormente el contenido por la informacion
                        //del archivo subido
                        var attachInfo =response.message['name'];
                        attachInfo +="<a href='#' id='"+response.message['idAttach']+"'  onclick='emailDetachFile(\""+response.message['idAttach']+"\")'><img src='admin/web/themes/elastixneo/images/bookmarks_equis.png'></a>";
                        divAttach.html(attachInfo);
                    }
                }
            });
        }
        function emailDetachFile(idAttach){
            var arrAction = new Array();
            arrAction["menu"]="home";
            arrAction["action"]="deattach_file";
            arrAction["idAttach"]=idAttach;
            arrAction["rawmode"]="yes";
            request("index.php", arrAction, false,
                function(arrData,statusResponse,error){
                    if(error!=""){
                        alert(error);
                    }else{
                        $("#"+idAttach).parent(':first').remove();
                    }   
            });
        }
        function richTextInit(){
            user_language=$("input[name='elx_language']").val();
            tinymce.init({
                selector: "#elx-compose-msg",
                plugins: [
                    "advlist autolink lists link image charmap print anchor",
                    "searchreplace visualblocks code ",
                    "insertdatetime media contextmenu paste textcolor emoticons"
                ],
                toolbar: " undo redo | fontselect | fontsizeselect | bold italic underline textcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | emoticons link image",
                language : user_language,
                resize: true,
                menubar : false,
                //auto_focus: "elx-compose-msg",
                /**
                * Pendiente de implementar la funcion usada para subir imagenes 
                file_browser_callback: function(field_name, url, type, win) {
                    win.document.getElementById(field_name).value = 'my browser value';
                }
                **/
            });
            $('textarea[name=compose_to]').focus();
        }
        /**
        * esta funcion es invocada cuando se quiere responder o reenviar un mensaje
        * recibe como parametros la acción que estamos realizando y la plantilla vacia usada para componer 
        * un mensaje
        **/
        function composeEmail(fromaction){
            //verificar que el campo to no este vacio
            //verificar si el subject esta vacio, si esta vacio preguntar si realmente lo quiere mandar asi
            //verificar si el contenido del mail esta vacio, si está vacío y no existe ningún
            //archivo adjunto preguntar si realmente se lo quiere mandar asi
            
            //cabeceras
            var headers=new Array();
            composeTo=$("textarea[name='compose_to']").val();
            if(typeof composeTo!=="string" || composeTo==''){
                alert($("input[name=msg_emptyto]").val());
                return false;
            }
            
            //la cabeceras reply_to, bcc y cc solo deben ser agregadas si los campos estan visibles
            if($("#compose-cc").is(":visible")){
                var composeCC = $("textarea[name='compose_cc']").val();
            }
            
            if($("#compose-bcc").is(":visible")){
                var composeBCC = $("textarea[name='compose_bcc']").val();
            }
            
            if($("#compose-reply_to").is(":visible")){
                var composeReplayTo = $("textarea[name='compose_replay_to']").val();
            }
            
            var subject = $("input[name=compose-subject]").val();
            if(subject==''){
                if(!confirm($("input[name='msg_emptysubject']").val()))
                    return false;
            }
            
            //pendiente revisar que hacer cuando el email contiene imágenes
            //la imágenes que contiene en email deben haber sido previamente obtenenidas y 
            //estar subidas en el servidor
            //deben ser enviado como attachment inline
            var bodyMsg = tinyMCE.get('elx-compose-msg').getContent();        
            if(fromaction!='popup'){
                showElastixUFStatusBar("Sending...");
            }
            var arrAction = new Array();
            arrAction["menu"]="home";
            arrAction["action"]="compose_email";
            arrAction["to"]=composeTo;
            arrAction["cc"]=composeCC;
            arrAction["bcc"]=composeBCC;
            arrAction["reply_to"]=composeReplayTo;
            arrAction["subject"]=subject;
            arrAction["bodyMsg"]=escape(bodyMsg);
            arrAction["rawmode"]="yes";
            request("index.php", arrAction, false,
                function(arrData,statusResponse,error){
                    if(fromaction!='popup'){
                        hideElastixUFStatusBar();
                    }
                    if(error!=""){
                        alert(error);
                    }else{
                        if(fromaction!='popup'){
                            return_mailbox();
                        }else{
                            alert(arrData);
                            $('#elx_general_popup').modal('hide');
                        }
                    }   
            }); 
        }
    </script>
{/literal}