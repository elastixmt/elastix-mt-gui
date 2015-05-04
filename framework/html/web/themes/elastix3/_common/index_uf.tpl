<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, target-densitydpi=device-dpi"/>
        <title>Elastix</title>
        <link rel="stylesheet" href="{$WEBCOMMON}css/bootstrap.min.css" />
        <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/styles.css" />
        {$HEADER_LIBS_JQUERY}
        <script type='text/javascript' src="{$WEBCOMMON}js/sip-0.6.2.js"></script>
	<script type='text/javascript' src="{$WEBCOMMON}js/bootstrap.min.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/bootstrap-paginator.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/jquery-title-alert.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/base.js"></script>
        <script type='text/javascript' src="{$WEBCOMMON}js/uf.js"></script>
        <script type="text/javascript" src="web/apps/home/tinymce/js/tinymce/tinymce.min.js"></script>
        {$HEADER}
        {$HEADER_MODULES}
    </head>
    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="mainBody" {$BODYPARAMS}>
        <input type="hidden" id="elastix_framework_module_id" value="" />
        <input type="hidden" id="elastix_framework_webCommon" value="" />
    
        <div id='elastix_app_body' class='elx_app_body'>
            {$MENU} <!-- Viene del tpl menu.tlp-->   
            <div id='main_content_elastix'>
                <div id='notify_change_elastix' style='height: 20px;'>
                    <div class="progress progress-striped active">
                        <div class="progress-bar progress-bar-warning progress-bar-elastix" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 100% ">
                            Loading...
                        </div>
                    </div>
                </div>
                <div id="elx_msg_area" class="alert {if $MSG_ERROR_FIELD || $MSG_FIELD}elx_msg_visible {else} elx_msg_oculto{/if} alert-dismissable" style="text-align:center;margin:0;">
                    <button type="button" class="elx-msg-area-close close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <p id="elx_msg_area_text" class='{if $MSG_ERROR_FIELD}alert-danger{else}alert-success{/if}'></p> 
                </div>
                <div id='module_content_framework'>
                    {$CONTENT}
                </div>
            </div>
        </div>
        <div id="rightdiv"> <!--Este es el Div que se usa para el chat-->
            <div id="b3_1" style='display:none'>
                <div id='head_rightdiv'>
                    <!--
                    <div id='elx_im_personal_info'>
                        
                    </div>
                    -->
                    <div id='elx_im_contact_search'>
                        <input type='text' maxlength='50' id='im_search_filter' name='im_search_filter' class='im_search_filter form-control input-sm' />
                        <div class='contactSearchResult' class='contactSearchResult'>
                        </div>
                    </div>
                </div>
                <div class="checkbox">                    
                    <label><input type="checkbox" id="elx-chk-show-offline-contacts" />Show offline</label>
                </div>
                <div id='elx_im_list_contacts'>
		            <!-- Dentro de este ul se encuentra la plantilla que define un item de contacto -->
		            <ul id="elx_template_contact_status" style="display: none">
		                <li class="margin_padding_0">
		                    <div class="elx_contact">
		                        <div id="elx_im_status_user" class="elx_im_status_user">
		                            <div class="box_status_contact"></div>
		                        </div>
		                        <div class="elx_contact_div">
		                            <div class="elx_im_name_user"></div>
		                            <div class="extension_status"></div>
		                        </div>
		                    </div>
		                </li>                
		            </ul>
                    <!-- El ul de abajo es la lista de contactos reales -->
                    <ul id='elx_ul_list_contacts' class='margin_padding_0'>
                    </ul>
                </div>
            </div>
            <div id='startingSession' style='position:relative'>
                <img id='login_loading_chat' style='display:inline' src='{$WEBCOMMON}images/loading.gif' /><span class='elx_contact_starting'>{$INT_SESSION}</span>
            </div>
        </div>
        <div id='elx_chat_space'>
            <div id='elx_notify_min_chat'  class='elx_nodisplay'>
                <div id='elx_list_min_chat' style='visibility:hidden'> 
                    <div>
                        <!-- Dentro de este ul se encuentra la plantilla que define un chat minimizado -->
			            <ul id="elx_template_min_chat_ul" style="display: none;">
                            <li class="elx_list_min_chat_li">
                                <span class="elx_min_span">
                                    <div class='glyphicon glyphicon-remove elx_min_remove'></div>
                                    <div class='elx_min_name'>
                                        <span class='elx_min_chat_num' style='visibility:hidden'>*</span>
                                        <span class='elx_min_chat_name'></span>
                                    </div>
                                </span>
                            </li>
			            </ul>
			            <!-- El ul de abajo es la lista de chats minimizados reales -->
                        <ul class='elx_list_min_chat_ul'>
                        </ul>
                    </div>
                </div>
                <input type='hidden' id='elx_hide_min_list' value='no' />
                <a id='elx_notify_min_chat_box' href="#" rel="toggle" role="button">
                    <span class="icn_d elx_icn_notify_chat">h</span>
                    <span id='elx_num_mim_chat'>0</span>
                </a>
            </div>
            <div id='elx_chat_space_tabs'>
                <!-- Dentro de este div se encuentra la plantilla que define un recuadro de chat -->
                <div id="elx_template_tab_chat" style="display: none">
                    <!-- El código asume que el chat inicia minimizado (elx_chat_min) -->
                    <div class='elx_tab_chat elx_chat_min'>
	                    <div class='elx_header_tab_chat'><div 
	                       class='elx_tab_chat_name'><span class='elx_tab_chat_name_span'><!-- Aquí va el nombre del contacto --></span></div><div
	                       class='elx_tab_tittle_icon'><span 
                                class='glyphicon glyphicon-minus elx_icon_chat elx_min_chat'
                                alt='Minimize' data-tooltip='Minimize' aria-label='Minimize'
                                ></span><span 
                                class='glyphicon glyphicon-remove elx_icon_chat elx_close_chat'
                                alt='Close' data-tooltip='Close' aria-label='Close'
                            ></span></div>
                        </div>
	                    <div class='elx_body_tab_chat'>
	                        <div class='elx_header2_tab_chat'>
                                <span
                                    class='glyphicon glyphicon-earphone elx_icon_chat elx_icon_chat2'
                                    alt='Call' data-tooltip='Call' aria-label='Call'
                                ></span><span
                                    class='glyphicon glyphicon-envelope elx_icon_chat elx_icon_chat2'
                                    alt='Send E-Mail' data-tooltip='Send E-Mail' aria-label='Send E-Mail'
                                ></span><span 
                                    class='glyphicon glyphicon-print elx_icon_chat elx_icon_chat2'
                                    alt='Send Fax' data-tooltip='Send Fax' aria-label='Send Fax'
                                ></span>
                            </div>
	                        <div class='elx_content_chat'></div>
	                        <div class='elx_text_area_chat'>
                                <textarea class='elx_chat_input'></textarea>
                            </div>
	                    </div>
                    </div>
                </div>
            </div>            
        </div>
        <div id="elx_template_videocall" style="display: none">
			<div class="modal-content">
			    <div class="modal-header">
			        <button type="button" class="close elx_close_popup_profile" data-dismiss="modal" aria-hidden="true">&times;</button>
			        <h3 id="myModalLabel">Call Window</h3>
			    </div>
			    <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8"><input name="elx_videocall_dialstring" style="width: 100%;" /></div>
                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 elx_video_call_buttons">
                            <button id="elx_videocall_dial" class="btn btn-primary" type="button">Dial</button>
                            <button id="elx_videocall_accept" class="btn btn-primary" type="button">Accept</button>
                            <button id="elx_videocall_reject" class="btn btn-danger" type="button">Reject</button>
                            <button id="elx_videocall_cancel" class="btn btn-danger" type="button">Cancel</button>
                            <button id="elx_videocall_hangup" class="btn btn-default" type="button">Hangup</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 elx_video_callstatus"></div>
                    </div>
                    <div class="row elx_video_row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div style="position: relative; width: 512px; height: 384px;">
                                <video width="512" height="384" id="elx_video_remote" style="position: absolute; top: 0; left: 0;"></video>
                                <video width="128" height="96" id="elx_video_local" muted="muted" style="position: absolute; bottom: 0; right: 0;"></video>
                            </div>
                            <audio id="elx_audio_remote" autoplay="autoplay"></audio>
                            <audio id="elx_audio_local" autoplay="autoplay"></audio>
                        </div>
                    </div>
			    </div>
			</div><!-- /.modal-content -->
        </div>
    </body>
</html>
