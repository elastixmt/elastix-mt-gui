<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF8" />
  <title>Elastix</title>
  <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/styles.css" /> 
  <link rel="stylesheet" href="{$WEBPATH}themes/{$THEMENAME}/help.css" /> 
  {$HEADER_LIBS_JQUERY}  
  <script type="text/javascript" src="{$WEBCOMMON}js/base.js"></script>
  <script type="text/javascript" src="{$WEBCOMMON}js/iframe.js"></script>
  {$HEADER}
  {$HEADER_MODULES}
</head>
<body {$BODYPARAMS}>
<div id="elx-blackmin-content-menu">
<div id="elx-blackmin-menu">
<img align="absmiddle" src="{$WEBPATH}themes/{$THEMENAME}/images/elastix_logo_mini.png" height="36" alt="elastix" longdesc="http://www.elastix.org" />
{$MENU}
</div>
&nbsp;{if $icon ne null}<img src="{$icon}" border="0" align="absmiddle" />&nbsp;&nbsp;{/if}{$title}
<span id="elx-blackmin-quicklink">
{if !empty($idSubMenu2Selected)}
    <a href="javascript:popUp('help/?id_nodo={$idSubMenu2Selected}&name_nodo={$nameSubMenu2Selected}','1000','460')">
{else}
    <a href="javascript:popUp('help/?id_nodo={$idSubMenuSelected}&name_nodo={$nameSubMenuSelected}','1000','460')">
{/if}<img src="../web/_common/images/icon-help.png" border="0" align="absmiddle"></a>
&nbsp;<a class="register_link" style="color: {$ColorRegister}; cursor: pointer; font-weight: bold; font-size: 13px;" onclick="showPopupElastix('registrar','{$Register}',538,500)">{$Registered}</a>
&nbsp;<a id="viewDetailsRPMs">{$VersionDetails}</a>
&nbsp;<a href="javascript:mostrar();">{$ABOUT_ELASTIX}</a>
&nbsp;<a href="index.php?logout=yes">{$LOGOUT}</a>
</span>
</div>
<div id="elx-blackmin-wrap">
<div id="elx-blackmin-content">
{if !empty($mb_message)}
<div class="ui-state-highlight ui-corner-all" id="message_error">
    <p>
        <span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
        <span id="elastix-callcenter-info-message-text">{if !empty($mb_title)}{$mb_title} - {/if}{$mb_message}</span>
    </p>
</div>
{/if}
{$CONTENT}
</div>
</div>

{* Diálogo de Acerca De *}
<div id="acerca_de" title="{$ABOUT_ELASTIX}">
    {$ABOUT_ELASTIX_CONTENT}<br />
    <a href='http://www.elastix.org' target='_blank'>www.elastix.org</a>
</div>

{* Popup genérico *}
<div id="PopupElastix" style="position: absolute; top: 0px; left: 0px;">
</div>
<!-- Neo Progress Bar -->
		<div class="neo-modal-elastix-popup-box">
			<div class="neo-modal-elastix-popup-title"></div>
			<div class="neo-modal-elastix-popup-close"></div>
			<div class="neo-modal-elastix-popup-content"></div>
		</div>
		<div class="neo-modal-elastix-popup-blockmask"></div>
<div id="fade_overlay" class="black_overlay"></div>
</body>
<script language="javascript" type="text/javascript">
{literal}
$(document).ready(function() {
    $('#about_elastix2').click(function() { $('#acerca_de').dialog('open'); });
    $('#acerca_de').dialog({
        autoOpen: false,
        width: 500,
        height: 220,
        modal: true,
        buttons: [
            {
                text: "{/literal}{$ABOUT_CLOSED}{literal}",
                click: function() { $(this).dialog('close'); }
            }
        ]
    });
});
{/literal}
</script>
<input type="hidden" id="lblTextMode" value="{$textMode}" />
<input type="hidden" id="lblHtmlMode" value="{$htmlMode}" />
</html>
