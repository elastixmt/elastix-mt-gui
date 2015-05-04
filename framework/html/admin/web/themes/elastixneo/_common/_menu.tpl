{* SE GENERA EL AUTO POPUP SI ESTA ACTIVADO *} 
{if $AUTO_POPUP eq '1'}
   {literal}
   	<script type='text/javascript'>
 	$('.togglestickynote').ready(function(e) {
            $("#neo-sticky-note-auto-popup").attr('checked', true);
	    note();
	});
	</script>
   {/literal}
{/if}

<div id="PopupElastix" style="position: absolute; top: 0px; left: 0px;"></div>

{literal}

<script type='text/javascript'>
//<![CDATA[
var themeName='elastixneo'; //nombre del tema
function mostrar_Menu(element)
{
    var subMenu;

    var idMenu = document.getElementById("idMenu");
    if(idMenu.value!="")
    {
        subMenu = document.getElementById(idMenu.value);
        subMenu.setAttribute("class", "vertical_menu_oculto");
    }
    if(element != idMenu.value)
    {
        subMenu = document.getElementById(element);
        subMenu.setAttribute("class", "vertical_menu_visible");
        idMenu.setAttribute("value", element);
    }
    else idMenu.setAttribute("value", "");
}

//<![CDATA[
    $(".menutabletaboff").mouseover(function(){
        $(this).css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_center2.gif)");
        $(this).css("height","47px");
        $(this).find('a:first').css("bottom","6px");
        $(this).parent().find('div:first').css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_left2.gif)");
        $(this).parent().find('div:last').css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_right2.gif)");
        $(this).parent().find('div:first').css("height","38px");
        $(this).parent().find('div:last').css("height","38px");
    });

    $(".menutabletaboff").mouseout(function(){
        $(this).css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_center.gif)");
        $(this).css("height","37px");
        $(this).find('a:first').css("bottom","0px");
        $(this).parent().find('div:first').css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_left.gif)");
        $(this).parent().find('div:last').css("background-image","url(web/themes/"+themeName+"/images/fondo_boton_right.gif)");
        $(this).parent().find('div:first').css("height","35px");
        $(this).parent().find('div:last').css("height","35px");
    });
/*newwwww*/
$(document).ready(function(){
	$("#toggleleftcolumn, #neo-lengueta-minimized").click(function(){
        var webCommon=getWebCommon();
	    if(!$('#neo-lengueta-minimized').hasClass('neo-display-none')){
		  $("#neo-contentbox-leftcolumn").removeClass("neo-contentbox-leftcolumn-minimized");
		  $("#neo-contentbox-maincolumn").css("width", "1025px");
	      $("#neo-contentbox-leftcolumn").data("neo-contentbox-leftcolum-status", "visible");
		  $("#neo-lengueta-minimized").addClass("neo-display-none");
		  if($('#toggleleftcolumn')){
			  var labeli = $('#toolTip_hideTab').val();
			  $('#toggleleftcolumn').attr('title',labeli);
			  $('#toggleleftcolumn').attr('src',webCommon+"images/expand.png");
		  }
	    }else{
		  $("#neo-contentbox-leftcolumn").addClass("neo-contentbox-leftcolumn-minimized");
		  $("#neo-contentbox-maincolumn").css("width", "1245px");
		  $("#neo-contentbox-leftcolumn").data("neo-contentbox-leftcolum-status", "hidden");
		  $("#neo-lengueta-minimized").removeClass("neo-display-none");
		  if($('#toggleleftcolumn')){
			  var labeli = $('#toolTip_showTab').val();
			  $('#toggleleftcolumn').attr('title',labeli);
			  $('#toggleleftcolumn').attr('src',webCommon+"images/expandOut.png");
		  }
		}
	});
	$("#togglebookmark").click(function() {
		var imgBookmark = $("#togglebookmark").attr('src');
		if(/bookmarkon.png/.test(imgBookmark)) {
			$("#togglebookmark").attr('src',"web/themes/"+themeName+"/images/bookmark.png");
		} else {
			$("#togglebookmark").attr('src',"web/themes/"+themeName+"/images/bookmarkon.png");
		}
	});
	$("#neo-cmenu-cpallet").hover(
	  function () {
		$(this).addClass("neo-cmenutableft-hvr");
		$( "#search_module_elastix" ).autocomplete( "close" );
		$( "#search_module_elastix" ).val("");
	  },
	  function () {
		$(this).removeClass("neo-cmenutableft-hvr");
	  }
	);
	$("#neo-cmenu-search").hover(
	  function () {
		$(this).addClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
		$( "#search_module_elastix" ).autocomplete( "close" );
		$( "#search_module_elastix" ).val("");
	  },
	  function () {
		$(this).removeClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-search").addClass("neo-display-none");
	  }
	);
	$("#neo-cmenu-info").hover(
	  function () {
		$(this).addClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-info").removeClass("neo-display-none");
		$( "#search_module_elastix" ).autocomplete( "close" );
		$( "#search_module_elastix" ).val("");
	  },
	  function () {
		$(this).removeClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-info").addClass("neo-display-none");
	  }
	);
	$("#neo-cmenu-user").hover(
	  function () {
		$(this).addClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-user").removeClass("neo-display-none");
		$( "#search_module_elastix" ).autocomplete( "close" );
		$( "#search_module_elastix" ).val("");
	  },
	  function () {
		$(this).removeClass("neo-cmenutab-hvr");
		$("#neo-cmenu-showbox-user").addClass("neo-display-none");
	  }
	);
    
	$("#neo-cmenu-showbox-search").hover(
	  function() {
		$("#neo-cmenu-showbox-search").removeClass("neo-display-none");
	  },
	  function() {
		$("#neo-cmenu-showbox-search").addClass("neo-display-none");
	  }
	);

	$("#neo-cmenu-showbox-info").hover(
	  function() {
		$("#neo-cmenu-showbox-info").removeClass("neo-display-none");
	  },
	  function() {
		$("#neo-cmenu-showbox-info").addClass("neo-display-none");
	  }
	);
	$("#neo-cmenu-showbox-user").hover(
	  function() {
		$("#neo-cmenu-showbox-user").removeClass("neo-display-none");
	  },
	  function() {
		$("#neo-cmenu-showbox-user").addClass("neo-display-none");
	  }
	);

	$('.neo-tabh-rend').click(function() {
        if ($("#neo-second-showbox-menu").hasClass("neo-display-none")) {
            $("#neo-second-showbox-menu").removeClass("neo-display-none");
            $('body').one('click', function(e) {
                $("#neo-second-showbox-menu").addClass("neo-display-none");
            });
            e.stopPropagation();
        }
	});

    $('#neo-cmenu-cpallet').click(function(e){
		if($("#colorpicker_framework").css("display")=="none")
			$("#colorpicker_framework").fadeIn(500);
		else
			$("#colorpicker_framework").fadeOut(500);
		oneClickEvent();
	});

	$('#search_module_elastix').bind('click', function(e) {
		//$( "#search_module_elastix" ).autocomplete( "close" );
		$( "#search_module_elastix" ).val("");
	});

	$("#export_button").hover(
	  function () {
	      $(this).addClass("exportBorder");
	  },
	  function () {
	      $(this).removeClass("exportBorder");
	      $(this).attr("aria-expanded","false");
	      $(this).removeClass("exportBackground");
	      $(".letranodec").css("color","#444444");
	      $("#subMenuExport").addClass("neo-display-none");
	  }
	);
	$("#export_button").click(
	  function () {
	      if($(this).attr("aria-expanded") == "false"){
		  var exportPosition = $('#export_button').position();
		  var top = exportPosition.top + 41;
		  var left = exportPosition.left - 3;
		  $("#subMenuExport").css('top',top+"px");
		  $("#subMenuExport").css('left',left+"px");
		  $(this).attr("aria-expanded","true");
		  $(this).addClass("exportBackground");
		  $(".letranodec").css("color","#FFFFFF");
		  $("#subMenuExport").removeClass("neo-display-none");
	      }
	      else{
		  $(".letranodec").css("color","#444444");
		  $("#subMenuExport").addClass("neo-display-none");
		  $(this).removeClass("exportBackground");
		  $(this).attr("aria-expanded","false");
	      }
	  }
	);
	$(".menuItem").hover(
	  function () {
		if($(this).attr("aria-disabled") == "false")
		    $(this).css("background","#F4FA58");
	  },
	  function () {
		$(this).css("background","");
	  }
	);
	$("#subMenuExport").hover(
	  function () {
		$(this).removeClass("neo-display-none");
		$(".letranodec").css("color","#FFFFFF");
		$("#export_button").attr("aria-expanded","true");
		$("#export_button").addClass("exportBackground");
	  },
	  function () {
		$(this).addClass("neo-display-none");
		$(".letranodec").css("color","#444444");
		$("#export_button").removeClass("exportBackground");
		$("#export_button").attr("aria-expanded","false");
	  }
	);
	$('#neo-cmenu-cpallet').ColorPicker({
		color: '#0000ff',
		onShow: function (colpkr) {
			return false;
		},
		onHide: function (colpkr) {
			changeColorMenu();// lanzar el ajax
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#neo-smenubox').css('backgroundColor', '#' + hex);
			$('.neo-tabhon').css('backgroundColor', '#' + hex);
			$('#userMenuColor').val('#' + hex);
		},
		onSubmit: function(hsb, hex, rgb, el) {
			$('#neo-smenubox').css('backgroundColor', '#' + hex);
			$('.neo-tabhon').css('backgroundColor', '#' + hex);
			$('#userMenuColor').val('#' + hex);
        	        $(el).ColorPickerHide();
			changeColorMenu();// se lanza la peticion ajax
	        },
		id_colorPicker: 'colorpicker_framework'
	});
	var menu_color_user = $('#userMenuColor').val();
	$('#neo-smenubox').css('backgroundColor', menu_color_user);
	$('.neo-tabhon').css('backgroundColor', menu_color_user);
    $('#neo-cmenu-cpallet').ColorPickerSetColor(menu_color_user);

	  // Scroll automático en caso de que el contenido del menú de segundo nivel se reboce
    // ---------------------------------------------------------------------------------
    var smenuoverflow = false; var offsetright = 0; var lastleft = 0; var accumulated_width = 0; var longpaso = 60;
	var move = "";
	$("#neo-smenubox div.neo-tabv,div.neo-tabvon").each(function(index) {
		accumulated_width += $(this).outerWidth();
		// Si el offset.left del elemento anterior es mayor que el actual quiere decir que el elemento
		// actual hizo una especio de retorno de carro
		if(lastleft>$(this).offset().left) smenuoverflow = true;
		lastleft = $(this).offset().left;
		// Si el offset.left+width del elemento actual es mayor al area de neo-smenubox entonces
		// evidentemente se rebozo
		offsetright = $(this).offset().left+$(this).outerWidth();
		if(offsetright>$("#neo-smenubox").outerWidth()) smenuoverflow = true;
	});
	if(smenuoverflow==true) {
	  $("#neo-smenubox-innerdiv").width(accumulated_width+longpaso+"px");
	  $("#neo-smenubox-arrow-more").removeClass("neo-display-none");
	}
	$('.neo-smenubox-arrow-more-right').mouseup(function() {
	  clearInterval(move);
	}).mousedown(function(e) {
	  clearInterval(move);
	  move = setInterval("moveRight()",90);
	});
	$('.neo-smenubox-arrow-more-left').mouseup(function() {
	  clearInterval(move);
	}).mousedown(function(e) {
	  clearInterval(move);
	  move = setInterval("moveLeft()",90);
	});

	$('.neo-historybox-tab,.neo-historybox-tabmid').hover(function() {
	  $(this).find('div').removeClass('neo-display-none');
	}, function() {
	  $(this).find('div').addClass('neo-display-none');
	});
});

function removeNeoDisplayOnMouseOut(ref){
	$(ref).find('div').addClass('neo-display-none');
}

function removeNeoDisplayOnMouseOver(ref){
	$(ref).find('div').removeClass('neo-display-none');
}

function moveLeft()
{
	var img = $('#neo-smenubox-arrow-more').children(':first-child').attr('src');
	var longpaso = 60;
	var leftvar = $('#neo-smenubox-innerdiv').css("left");
	leftvarArr = leftvar.split("px");
	if($('#neo-smenubox-innerdiv').offset().left<-longpaso && leftvarArr[0] < 0 ) {
		$('#neo-smenubox-innerdiv').animate({left:'+='+longpaso}, 70, function() {});
		$('#neo-smenubox-arrow-more').children(':first-child').attr('src', 'web/themes/'+themeName+'/images/icon_arrowleft.png');
		$('#neo-smenubox-arrow-more').children(':last-child').attr('src', 'web/themes/'+themeName+'/images/icon_arrowright.png');
	} else {
		$('#neo-smenubox-innerdiv').css("left", "0px");
		if(/icon_arrowleft.png/.test(img))
			$('#neo-smenubox-arrow-more').children(':first-child').attr('src', 'web/themes/'+themeName+'/images/icon_arrowleft_no.png');
	}
}

function moveRight()
{
	var img = $('#neo-smenubox-arrow-more').children(':last-child').attr('src');
	var longpaso = 60;
	if(($('#neo-smenubox-innerdiv').offset().left+$('#neo-smenubox-innerdiv').outerWidth()+longpaso)>($("#neo-smenubox").offset().left+$("#neo-smenubox").outerWidth())) {
		$('#neo-smenubox-innerdiv').animate({left:'-='+longpaso}, 70, function() {});
		$('#neo-smenubox-arrow-more').children(':first-child').attr('src', 'web/themes/'+themeName+'/images/icon_arrowleft.png');
	} else {
		if(/icon_arrowright.png/.test(img))
			$('#neo-smenubox-arrow-more').children(':last-child').attr('src', 'web/themes/'+themeName+'/images/icon_arrowright_no.png');
	}
}

function oneClickEvent()
{
    $('body').one('click', function(e) {
	var element = e.target;
	var hide = true;
	if($(element).parents('#colorpicker_framework').length > 0)
	    hide = false
	if(hide)
	    $("#colorpicker_framework").fadeOut(500);
	else
	    oneClickEvent();
	e.stopPropagation();
    });
}
//]]>
</script>
{/literal}

<input type="hidden" id="lblTextMode" value="{$textMode}" />
<input type="hidden" id="lblHtmlMode" value="{$htmlMode}" />
<input type="hidden" id="lblRegisterCm"   value="{$lblRegisterCm}" />
<input type="hidden" id="lblRegisteredCm" value="{$lblRegisteredCm}" />
<input type="hidden" id="lblCurrentPassAlert" value="{$CURRENT_PASSWORD_ALERT}" />
<input type="hidden" id="lblNewRetypePassAlert"   value="{$NEW_RETYPE_PASSWORD_ALERT}" />
<input type="hidden" id="lblPassNoTMatchAlert" value="{$PASSWORDS_NOT_MATCH}" />
<input type="hidden" id="lblChangePass" value="{$CHANGE_PASSWORD}" />
<input type="hidden" id="lblCurrentPass" value="{$CURRENT_PASSWORD}" />
<input type="hidden" id="lblRetypePass" value="{$RETYPE_PASSWORD}" />
<input type="hidden" id="lblNewPass" value="{$NEW_PASSWORD}" />
<input type="hidden" id="btnChagePass" value="{$CHANGE_PASSWORD_BTN}" />
<input type="hidden" id="userMenuColor" value="{$MENU_COLOR}" />
<input type="hidden" id="lblSending_request" value="{$SEND_REQUEST}" />
<input type="hidden" id="toolTip_addBookmark" value="{$ADD_BOOKMARK}" />
<input type="hidden" id="toolTip_removeBookmark" value="{$REMOVE_BOOKMARK}" />
<input type="hidden" id="toolTip_addingBookmark" value="{$ADDING_BOOKMARK}" />
<input type="hidden" id="toolTip_removingBookmark" value="{$REMOVING_BOOKMARK}" />
<input type="hidden" id="toolTip_hideTab" value="{$HIDE_IZQTAB}" />
<input type="hidden" id="toolTip_showTab" value="{$SHOW_IZQTAB}" />
<input type="hidden" id="toolTip_hidingTab" value="{$HIDING_IZQTAB}" />
<input type="hidden" id="toolTip_showingTab" value="{$SHOWING_IZQTAB}" />
<input type="hidden" id="amount_char_label" value="{$AMOUNT_CHARACTERS}" />
<input type="hidden" id="save_note_label" value="{$MSG_SAVE_NOTE}" />
<input type="hidden" id="get_note_label" value="{$MSG_GET_NOTE}" />
<input type="hidden" id="elastix_theme_name" value="{$THEMENAME}" />
<input type="hidden" id="lbl_no_description" value="{$LBL_NO_STICKY}" />

<div id="neo-headerbox">
	<div id="neo-logobox"><img src="{$WEBPATH}themes/{$THEMENAME}/images/elastix_logo_mini2.png" width="200" height="59" alt="elastix" longdesc="http://www.elastix.org" /></div>
	<div id="neo-mmenubox"> <!-- mostrando contenido del menu principal -->
	  {foreach from=$arrMainMenu key=idMenu item=menu name=menuMain}
		{if $idMenu eq $idMainMenuSelected && $smarty.foreach.menuMain.iteration lt 8}
		  <div class="neo-tabhon"><a class='menutable2' href="index.php?menu={$idMenu}">{$menu.description}</a></div>
		{elseif $smarty.foreach.menuMain.first}
		  <div class="neo-tabh-lend2"><a class="menutable" href="index.php?menu={$idMenu}">{$menu.description}</a></div>
		{elseif $smarty.foreach.menuMain.iteration lt 8 && $smarty.foreach.menuMain.last}
		  <div class="neo-tabh-lend3"><a class="menutable" href="index.php?menu={$idMenu}">{$menu.description}</a></div>
		{elseif $smarty.foreach.menuMain.iteration lt 8}
		  <div class="neo-tabh"><a class="menutable" href="index.php?menu={$idMenu}">{$menu.description}</a></div>
		{elseif $smarty.foreach.menuMain.iteration eq 8}
		  <div class="neo-tabh-rend"><img src="{$WEBPATH}themes/{$THEMENAME}/images/arrowdown.png" width="17" height="15" alt="arrowdown" /></div>
		  <div id="neo-second-showbox-menu" class="neo-second-showbox-menu neo-display-none">
			<p><a class="menutable" href="index.php?menu={$idMenu}">{$menu.description}</a></p>
		{elseif $smarty.foreach.menuMain.iteration ge 8}
			<p><a class="menutable" href="index.php?menu={$idMenu}">{$menu.description}</a></p>
		{/if}
        {if $smarty.foreach.menuMain.iteration ge 8 && $smarty.foreach.menuMain.last}
           </div>
        {/if}
	  {/foreach}
		  
	</div>
	<div id="neo-smenubox"> <!-- mostrando contenido del menu secundario -->
	  <div id="neo-smenubox-innerdiv">
		{foreach from=$arrSubMenu key=idSubMenu item=subMenu}
		  {if $idSubMenu eq $idSubMenuSelected}
			<div class="neo-tabvon"><a href="index.php?menu={$idSubMenu}" class="submenu_on">{$subMenu.description}</a></div>
		  {else}
			<div class="neo-tabv"><a href="index.php?menu={$idSubMenu}">{$subMenu.description}</a></div>
		  {/if}
		{/foreach}
	  </div>
	  <div id="neo-smenubox-arrow-more" class="neo-display-none">
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/icon_arrowleft_no.png" width="15" height="17" alt="arrowleft" class="neo-smenubox-arrow-more-left" style="cursor: pointer;" />
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/icon_arrowright.png" width="15" height="17" alt="arrowright" class="neo-smenubox-arrow-more-right" style="cursor: pointer;" />
	  </div>
	</div>
	<div id="neo-topbar">
	  <div id="neo-cmenubox">
		<div id="neo-cmenu-cpallet" class="neo-cmenutableft"><img src="{$WEBPATH}themes/{$THEMENAME}/images/cpallet.png" width="19" height="21" alt="color" /></div>
		<div id="neo-cmenu-search" class="neo-cmenutab"><img src="{$WEBPATH}themes/{$THEMENAME}/images/searchw.png" width="19" height="21" alt="user_search" border="0" /></div>
		<div id="neo-cmenu-info" class="neo-cmenutab"><img src="{$WEBPATH}themes/{$THEMENAME}/images/information.png" width="19" height="21" alt="user_info" border="0" /></div>
		<div id="neo-cmenu-user" class="neo-cmenutab"><img src="{$WEBPATH}themes/{$THEMENAME}/images/user.png" width="19" height="21" alt="user" border="0" /></div>
	  </div>
	</div>
	<div id="neo-cmenu-showbox-search" class="neo-cmenu-showbox neo-display-none">
	  <p>{$MODULES_SEARCH}</p>
	  <p><input type="search"  id="search_module_elastix" name="search_module_elastix"  value="" autofocus="autofocus" placeholder="search" /></p>
	</div>
	<div id="neo-cmenu-showbox-info" class="neo-cmenu-showbox neo-display-none">
	  <!--<p><span><a class="register_link" style="color: {$ColorRegister}; cursor: pointer; font-weight: bold; font-size: 13px;" onclick="showPopupElastix('registrar','{$Register}',538,500)">{$Registered}</a></span></p>
	  <p><span><a id="viewDetailsRPMs">{$VersionDetails}</a></span></p>-->
	  <p><span><a href="http://www.elastix.org" target="_blank">Elastix Website</a></span></p>
	  <p><span><a href="javascript:mostrar();">{$ABOUT_ELASTIX2}</a></span></p>
	</div>
	<div id="neo-cmenu-showbox-user" class="neo-cmenu-showbox neo-display-none">
	  <p><span><a style="cursor: pointer;" onclick="setAdminPassword();">{$CHANGE_PASSWORD}</a></span></p>
	  <p><span><a class="logout" href="?logout=yes">{$LOGOUT} (<font style='color:#FFFFFF;font-style:italic'>{$USER_LOGIN}</font>)</a></span></p>
	</div>
</div>
<div id="neo-contentbox">
	{if !empty($idSubMenu2Selected)}
		{if $viewMenuTab eq 'true'}
	<div id="neo-contentbox-leftcolumn" class="neo-contentbox-leftcolumn-minimized">
		{elseif $viewMenuTab eq 'false'}
	<div id="neo-contentbox-leftcolumn">
		{else}
	<div id="neo-contentbox-leftcolumn">
		{/if}
		<div id="neo-3menubox">  <!-- mostrando contenido del menu tercer nivel -->
			{foreach from=$arrSubMenu2 key=idSubMenu2 item=subMenu2}
			  {if $idSubMenu2 eq $idSubMenu2Selected}
				<div class="neo-3mtabon"><a href="index.php?menu={$idSubMenu2}" style="text-decoration: none;">{$subMenu2.description}</a></div>
			  {else}
				<div class="neo-3mtab"><a href="index.php?menu={$idSubMenu2}" style="text-decoration: none;">{$subMenu2.description}</a></div>
			  {/if}
			{/foreach}
		</div>
		<div id="neo-historybox">
			{$SHORTCUT}
		</div>
	</div>
		{if $viewMenuTab eq 'true'}
	<div id="neo-contentbox-maincolumn" style="width: 1245px;">
		{elseif $viewMenuTab eq 'false'}
	<div id="neo-contentbox-maincolumn" style="width: 1025px;">
		{else}
	<div id="neo-contentbox-maincolumn" style="width: 1025px;">
		{/if}
	    <div class="neo-module-title"><div class="neo-module-name-left"></div><span class="neo-module-name">
	      {if $icon ne null}
	      <img src="{$icon}" width="22" height="22" align="absmiddle" />
	      {/if}
	      &nbsp;{$title}</span><div class="neo-module-name-right"></div>
	      <div class="neo-module-title-buttonstab-right"></div><span class="neo-module-title-buttonstab">
	      {if $STATUS_STICKY_NOTE eq 'true'}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/tab_notes_on.png" width="23" height="21" alt="tabnotes" id="togglestickynote1" class="togglestickynote" style="cursor: pointer;" />
		  {else}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/tab_notes.png" width="23" height="21" alt="tabnotes" id="togglestickynote1" class="togglestickynote" style="cursor: pointer;" />
		  {/if}
		  {if $viewMenuTab eq 'true'}
	      <img src="{$WEBCOMMON}images/expandOut.png" width="24" height="24" alt="expand" id="toggleleftcolumn" class="neo-picker" border="0" onclick='saveToggleTab()' title="{$SHOW_IZQTAB}" />
		  {elseif $viewMenuTab eq 'false'}
		   <img src="{$WEBCOMMON}images/expand.png" width="24" height="24" alt="expand" id="toggleleftcolumn" class="neo-picker" border="0" onclick='saveToggleTab()' title="{$HIDE_IZQTAB}" />
		  {else}
		   <img src="{$WEBCOMMON}images/expand.png" width="24" height="24" alt="expand" id="toggleleftcolumn" class="neo-picker" border="0" onclick='saveToggleTab()' title="{$HIDE_IZQTAB}" />
		  {/if}
		  {if $IMG_BOOKMARKS eq 'bookmark.png'}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$ADD_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
		  {else}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$REMOVE_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
		  {/if}
	      </span><div class="neo-module-title-buttonstab-left"></div></div>
          <input type="hidden" id="elastix_framework_module_id" value="{$idSubMenu2Selected}" />
          <input type="hidden" id="elastix_framework_webCommon" value="{$WEBCOMMON}" />
	      <div class="neo-module-content">
	{else}
	<div id="neo-contentbox-leftcolumn" class="neo-contentbox-leftcolumn-minimized">
		<div id="neo-historybox">
			{$SHORTCUT}
		</div>
	</div>
	<div id="neo-contentbox-maincolumn" style="width: 1245px;">
	    <div class="neo-module-title"><div class="neo-module-name-left"></div><span class="neo-module-name">
	      {if $icon ne null}
	      <img src="{$icon}" width="22" height="22" align="absmiddle" />
	      {/if}
	      &nbsp;{$title}</span><div class="neo-module-name-right"></div>
	      <div class="neo-module-title-buttonstab-right"></div><span class="neo-module-title-buttonstab">
	      {if $STATUS_STICKY_NOTE eq 'true'}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/tab_notes_on.png" width="23" height="21" alt="tabnotes" id="togglestickynote1" style="cursor: pointer;" class="togglestickynote" />
		  {else}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/tab_notes.png" width="23" height="21" alt="tabnotes" id="togglestickynote1" style="cursor: pointer;" class="togglestickynote" />
		  {/if}
		  <img src="{$WEBCOMMON}images/expandOut.png" width="24" height="24" alt="expand" id="toggleleftcolumn" class="neo-picker" border="0"  title="{$SHOW_IZQTAB}" />
		  {if $IMG_BOOKMARKS eq 'bookmark.png'}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$ADD_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
		  {else}
		  <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$REMOVE_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
		  {/if}

</span>

<div class="neo-module-title-buttonstab-left">

</div></div>
          <input type="hidden" id="elastix_framework_module_id" value="{$idSubMenuSelected}" />
          <input type="hidden" id="elastix_framework_webCommon" value="{$WEBCOMMON}" />
	 <div class="neo-module-content">
	{/if}



