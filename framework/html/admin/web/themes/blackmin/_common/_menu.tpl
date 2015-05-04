<div>
<hr />
<ul>{foreach from=$arrMainMenu key=idMenu item=menu}
<li {if $idMenu eq $idMainMenuSelected}class="selected"{/if} >
    <a href="index.php?menu={$idMenu}">{$menu.description}&nbsp;&nbsp;&nbsp;{if count($menu.children) > 0}<span class="raquo">&raquo;</span>{/if}</a>
    {if count($menu.children) > 0}
    <ul>{foreach from=$menu.children key=idSubMenu item=subMenu}
    <li {if $idSubMenu eq $idSubMenuSelected}class="selected"{/if} >
        <a href="index.php?menu={$idSubMenu}">{$subMenu.description}&nbsp;&nbsp;&nbsp;{if count($subMenu.children) > 0}<span class="raquo">&raquo;</span>{/if}</a>{if $idSubMenu eq $idSubMenuSelected}{if empty($idSubMenu2Selected)}<input type="hidden" id="elastix_framework_module_id" value="{$idSubMenuSelected}" />{/if}{/if}
        {if count($subMenu.children) > 0}
        <ul>{foreach from=$subMenu.children key=idSubMenu2 item=subMenu2}
            <li {if $idSubMenu2 eq $idSubMenu2Selected}class="selected"{/if}>
                <a href="index.php?menu={$idSubMenu2}">{$subMenu2.description}&nbsp;&nbsp;&nbsp;</a>{if $idSubMenu2 eq $idSubMenu2Selected}<input type="hidden" id="elastix_framework_module_id" value="{$idSubMenu2Selected}" />{/if}
            </li>
        {/foreach}</ul>
        {/if}
    </li>
    {/foreach}</ul>
    {/if}
</li>
{/foreach}</ul>
<hr />
<ul>
<li class="selected"><a href='http://www.elastix.org' target='_blank'>Website</a></li>
<li class="selected"><a href="index.php?logout=yes">{$LOGOUT}</a></li>
</ul>
<hr />
</div>