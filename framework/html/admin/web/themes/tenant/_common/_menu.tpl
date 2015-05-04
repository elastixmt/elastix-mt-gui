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

{literal}
<script type='text/javascript'>
var themeName='elastixneo'; //nombre del tema
$(document).ready(function(){
    $("#togglebookmark").click(function() {
        var imgBookmark = $("#togglebookmark").attr('src');
        if(/bookmarkon.png/.test(imgBookmark)) {
            $("#togglebookmark").attr('src',"web/themes/"+themeName+"/images/bookmark.png");
        } else {
            $("#togglebookmark").attr('src',"web/themes/"+themeName+"/images/bookmarkon.png");
        }
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
});

function removeNeoDisplayOnMouseOut(ref){
    $(ref).find('div').addClass('neo-display-none');
}

function removeNeoDisplayOnMouseOver(ref){
    $(ref).find('div').removeClass('neo-display-none');
}
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

<!-- inicio del menú tipo acordeon-->
<div class="sidebar-menu">
    <header class="logo-env"> 
        <!-- logo -->
        <div class="logo">
            <a href="index.php">
                <img src="{$WEBPATH}themes/{$THEMENAME}/images/elastix_logo_mini2.png" width="120" alt="" />
            </a>
        </div>
        <!-- logo collapse icon -->            
        <div class="sidebar-collapse">
            <a href="#" class="sidebar-collapse-icon with-animation"><!-- add class "with-animation" if you want sidebar to have animation during expanding/collapsing transition -->
                <i class="entypo-menu"></i>
            </a>
        </div>
        <!-- open/close menu icon (do not remove if you want to enable menu on mobile devices) -->
        <div class="sidebar-mobile-menu visible-xs">
            <a href="#" class="with-animation"><!-- add class "with-animation" to support animation -->
                <i class="entypo-menu"></i>
            </a>
        </div>
    </header>
             
    <ul id="main-menu" class="">
        <!-- add class "multiple-expanded" to allow multiple submenus to open -->
        <!-- class "auto-inherit-active-class" will automatically add "active" class for parent elements who are marked already with class "active" -->
        <!-- Search Bar -->
        <li id="search">
            <form method="get" action="">
                <input type="text" id="search_module_elastix" name="search_module_elastix" class="search-input" placeholder="{$MODULES_SEARCH}"/>
                <button type="submit">
                    <i class="entypo-search"></i>
                </button>
            </form>
        </li>
        <!--recorremos el arreglo del menu nivel primario-->
        {foreach from=$arrMainMenu key=idMenu item=menu name=menuMain}
            {if $idMenu eq $idMainMenuSelected}
                <li class="active opened active">
            {else}
                <li>
            {/if}
                    <a href="index.php?menu={$idMenu}">
                         <i class="{$menu.icon}"></i>

                        <span>{$menu.description}</span>
                    </a>
                    <ul>
                        <!--recorremos el arreglo del menu nivel secundario-->
                        {foreach from=$menu.children key=idSubMenu item=subMenu}
                            {if $idSubMenu eq $idSubMenuSelected}
                                <li class="active opened active">
                            {else}
                                <li>
                            {/if}
                                    <a href="index.php?menu={$idSubMenu}">
                                        <span>{$subMenu.description}</span>
                                    </a>
                                    {if $subMenu.children}
                                        <ul>
                                            <!--recorremos el arreglo del menu de tercer nivel-->
                                            {foreach from=$subMenu.children key=idSubMenu2 item=subMenu2}
                                                <li>
                                                    <a href="index.php?menu={$idSubMenu2}">
                                                        <span>{$subMenu2.description}</span>
                                                    </a>
                                                </li>
                                            {/foreach}	
                                        </ul>
                                    {/if}
                                </li>
                        {/foreach}
                    </ul>
                </li>
        {/foreach}
        
        {$SHORTCUT}
        
    </ul>            
</div>
<!-- fin del menú tipo acordeon-->

<!-- inicio del head principal-->
<div style="height:71px;background-color:#EB2B06;padding:15px;"> 

    <!-- Profile Info and Notifications -->
    <div class="col-md-6 col-sm-8 clearfix">

        <ul class="user-info pull-left pull-none-xsm">
        
            <!-- Profile Info -->
            <li class="profile-info dropdown"><!-- add class "pull-right" if you want to place this from right -->
                
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">

                    <img  style="border:0px" src="index.php?menu=_elastixutils&action=getImage&ID={$USER_ID}&rawmode=yes" alt="" class="img-circle" width="44" />
                    {$USER_LOGIN}
                </a>
                
                <ul class="dropdown-menu">
                    
                    <!-- Reverse Caret -->
                    <li class="caret"></li>
                    
                    <!-- Profile sub-links -->
                    <li>
                        <a style="cursor: pointer;" onclick="setAdminPassword();">
                            <i class="entypo-user"></i>
                            {$CHANGE_PASSWORD}
                        </a>
                    </li>
                </ul>
            </li>
        
        </ul>
       
    </div>
    
    <!-- Raw Links -->
    <div class="col-md-6 col-sm-4 clearfix pull-none-xsm">
        
        <ul class="list-inline links-list pull-right">
            
            <!-- Language Selector -->			
            <li class="dropdown language-selector profile-info">
                <a href="index.php?menu=language">
                    Language: &nbsp;
                         
                            <img  style="border:0px" src="{$WEBPATH}themes/{$THEMENAME}/images/flags/{$LANG}.png" />
                       
                </a>
            </li>
            
            <li class="sep"></li>
            
            <!-- Information -->          
            <li class="dropdown language-selector profile-info">
                
               
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">
                    <i class="entypo-info"></i> Info
                </a>
                
                <ul class="dropdown-menu pull-right">
                    <li>
                        <a href="http://www.elastix.org">
                            <span>Elastix Website</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:mostrar();">
                            <span>{$ABOUT_ELASTIX2}</span>
                        </a>
                    </li>
                </ul>
                
            </li>

            
            <li class="sep"></li>
            
            <li class="dropdown profile-info">
                <a href="?logout=yes">
                    Log Out <i class="entypo-logout right"></i>
                </a>
            </li>
        </ul>
        
    </div>
    
</div>

				<!-- Breadcrumb 3 -->
<ol class="breadcrumb bc-2">
   
    {foreach from=$BREADCRUMB item=value name=menu}
        {if $smarty.foreach.menu.first} 
             <li>
                <a href="/"> <i class="entypo-home"></i></a>
                <a href="#"> {$value}</a>
             </li>
        {elseif $smarty.foreach.menu.last} 
            <li class="active"><strong>{$value}</strong></li>
        {else} 
            <li><a href="#">{$value}</a></li>
        {/if} 
   {/foreach}
</ol>

<!-- contenido del modulo-->
<div id="neo-contentbox">
    <div id="neo-contentbox-maincolumn">
        <!--<div class="neo-module-title"><div class="neo-module-name-left"></div>
            <span class="neo-module-name">
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
            {if $viewMenuTab}
            {/if}
            {if $IMG_BOOKMARKS eq 'bookmark.png'}
                <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$ADD_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
            {else}
                <img src="{$WEBPATH}themes/{$THEMENAME}/images/{$IMG_BOOKMARKS}" width="24" height="24" alt="bookmark" title="{$REMOVE_BOOKMARK}" id="togglebookmark" style="cursor: pointer;" onclick='addBookmark()' />
            {/if}
            </span><div class="neo-module-title-buttonstab-left"></div>
        </div>-->
        <input type="hidden" id="elastix_framework_module_id" value="{$idSubMenu2Selected}" />
        <input type="hidden" id="elastix_framework_webCommon" value="{$WEBCOMMON}" />
        <div class="neo-module-content">




