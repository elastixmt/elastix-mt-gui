<div id="tooldiv">
    <div id="cont_logo">
        <div id="logo"></div>
    </div>
    <div class="dropdown tooldivicons">
    <div id="icn_prof" class=' hidden-xs dropdown-toggle' data-toggle="dropdown">
        <div id="name" class="hidden-xs hidden-sm"><p id="elx-username">{$USER_NAME}<br/>{$USER_ESTENSION}</p></div>
        <div class="elx-content-photo"><img id="photo" alt="image" src="index.php?menu=_elastixutils&action=getImage&ID={$ID_ELX_USER}&rawmode=yes"/></div>
    </div>
    <ul class="dropdown-menu" role="menu">
       <li><a id="elx_presence_online" href="#"><div class="box_status_contact" style="display: inline-block; background-color: #8cbe29"></div>Online</a></li>
       <li><a id="elx_presence_away" href="#"><div class="box_status_contact" style="display: inline-block; background-color: orange"></div>Away</a></li>
       <li><a id="elx_presence_meeting" href="#"><div class="box_status_contact" style="display: inline-block; background-color: orange"></div>On a meeting</a></li>
       <li><a id="elx_presence_busy" href="#"><div class="box_status_contact" style="display: inline-block; background-color: red"></div>Busy</a></li>
       <li class="divider"></li>
       <li><a id="elx_presence_offline" href="#"><div class="box_status_contact" style="display: inline-block; background-color: gray"></div>Offline</a></li>
    </ul>
    </div>
    <div id="elx-navbar-min" class="visible-xs"><a href="#"><img src="web/_common/images/elastix3_icon-mini-menu.png"></a></div>
    
    <!-- div que contiene las imagenes que conforman el menú -->
    <div id="elx-nav-buttons" class="hidden-xs">
        <div id="elx-button-setup" class="btn-group">
            <img class="dropdown-toggle" data-toggle="dropdown" src="web/_common/images/elastix3_icon-setup.png" alt="Setup">
            <ul class="dropdown-menu" role="menu">
                <li><a class="elx-display-dialog-show-profile" href="#">{$Profile_l}</a></li>
                <li class="divider"></li>
                {foreach from=$arrMainMenu key=idMenu item=menu}
                    {if $menu.id eq "mysettings"}
                        {foreach from=$menu.children key=idSubMenu item=subMenu}
                            <li><a href="index.php?menu={$idSubMenu}">{$subMenu.description}</a></li>
                        {/foreach}
                    {/if}
                {/foreach}    
                <li class="divider"></li>
                <li><a href="?logout=yes">{$LOGOUT}</a></li>
            </ul>
        </div>
        {foreach from=$arrMainMenu key=idMenu item=menu}
            {foreach from=$menu.children key=idSubMenu item=subMenu}
                {if $subMenu.id eq "contacts"}
                    <a href="index.php?menu={$idSubMenu}" title="{$subMenu.description}"><img src="web/_common/images/elastix3_icon-contacts.png" alt="{$subMenu.description}"></a>
                {/if}
                {if $subMenu.id eq "calendar"}
                    <a href="index.php?menu={$idSubMenu}" title="{$subMenu.description}"><img src="web/_common/images/elastix3_icon-calendar.png" alt="{$subMenu.description}"></a>
                {/if}
            {/foreach}
        {/foreach}
        {foreach from=$arrMainMenu key=idMenu item=menu}
            {if $menu.id eq "home"}
                <a href="index.php?menu={$idMenu}" title="Mail"><img src="web/_common/images/elastix3_icon-mail.png" alt="Mail"></a>
            {/if}
        {/foreach}
        <a class="elx-display-dialog-show-sendfax" href="#" title="Fax"><img src="web/_common/images/elastix3_icon-fax.png" alt="Fax"></a>
        <a href="#" title="Chat"><img id="icn_disp2" src="web/_common/images/elastix3_icon-chat.png" alt="Chat"></a>
        <a href="#" title="Call"><img id="icn_call" src="web/_common/images/call.png" alt="Call"></a>
    </div>
    
</div>

<!-- este div esta oculto, solo aparece en < 480px  -->
<div id="elx-slide-menu-mini" class="oculto elx-slide-menu-mini" style="" >
    <!-- div que contiene las imagenes que conforman el menú -->
    <div id="elx-nav-buttons">
        <div id="elx-button-setup" class="btn-group">
            <img class="dropdown-toggle" data-toggle="dropdown" src="web/_common/images/elastix3_icon-setup.png" alt="Setup">
            <ul class="dropdown-menu" role="menu">
                <li><a class="elx-display-dialog-show-profile" href="#">{$Profile_l}</a></li>
                <li class="divider"></li>
                {foreach from=$arrMainMenu key=idMenu item=menu}
                    {if $menu.id eq "mysettings"}
                        {foreach from=$menu.children key=idSubMenu item=subMenu}
                            <li><a href="index.php?menu={$idSubMenu}">{$subMenu.description}</a></li>
                        {/foreach}
                    {/if}
                {/foreach}    
                <li class="divider"></li>
                <li><a href="?logout=yes">{$LOGOUT}</a></li>
            </ul>
        </div>
        {foreach from=$arrMainMenu key=idMenu item=menu}
            {foreach from=$menu.children key=idSubMenu item=subMenu}
                {if $subMenu.id eq "contacts"}
                    <a href="index.php?menu={$idSubMenu}"><img src="web/_common/images/elastix3_icon-contacts.png" alt="{$subMenu.description}"></a>
                {/if}
                {if $subMenu.id eq "calendar"}
                    <a href="index.php?menu={$idSubMenu}"><img src="web/_common/images/elastix3_icon-calendar.png" alt="{$subMenu.description}"></a>
                {/if}
            {/foreach}
        {/foreach}
        {foreach from=$arrMainMenu key=idMenu item=menu}
            {if $menu.id eq "home"}
                <a href="index.php?menu={$idMenu}"><img src="web/_common/images/elastix3_icon-mail.png" alt="Mail"></a>
            {/if}
        {/foreach}
        <a class="elx-display-dialog-show-sendfax" href="#"><img src="web/_common/images/elastix3_icon-fax.png" alt="Fax"></a>
        <a href="#"><img id="icn_disp2" src="web/_common/images/elastix3_icon-chat.png" alt="Chat"></a>
        <a href="#"><img id="icn_call" src="web/_common/images/call.png" alt="Call"></a>
    </div>
</div>


<!-- The overlay and the box general popup -->
<div class="overlay" id="overlay" style="display:none;"></div>
<div class="modal fade" id="elx_general_popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" id='elx_popup_content'>
        <!-- se llama al profile_uf.tpl -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

