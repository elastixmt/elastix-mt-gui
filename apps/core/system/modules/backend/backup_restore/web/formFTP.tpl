

<table width="100%" border="0" cellspacing="0" cellpadding="4" align="center">
    <tr class="letra12">
	<td align="left">
	    <input class="button" type="submit" name="save_new_FTP" value="{$SAVE}">&nbsp;&nbsp;
	    <input class="button" type="submit" name="cancel" value="{$CANCEL}">
	</td>
	<td align="right" nowrap><span class="letra12"><span  class="required">*</span> {$REQUIRED_FIELD}</span></td>
    </tr>
</table>

<div id="table_center" class="tabFormFTP">
    <div class="letra12">
        <b><br/>&nbsp;&nbsp;{$TITLE}</b>
    </div>
    <div id="content">
        <div class="divs">
            <table style="font-size: 26px;" width="75%" >
                <tr class="letra12">
                    <td align="left"><b>{$local.LABEL}: </b></td>
                    <td align="center"><b>{$server_ftp.LABEL}: </b></td>
                </tr>
            </table>
        </div>
        <div id="home">
            <div id="lef">
                <ul id="sortable1" class='droptrue'>
                {* {$LOCAL_LI} *}
                {foreach from=$local_files item=file}
                <li class='ui-state-default' id='inn_{$file}'><b class='item'>{$file}</b></li>
                {/foreach}
                </ul>
            </div>
            <div id="med">
            </div>
            <div id="cen">
                <ul id="sortable2" class='droptrue2'>
                {* {$REMOTE_LI} *}
                {foreach from=$remote_files item=file}
                <li class='ui-state-default' id='out_{$file}'><b class='item'>{$file}</b></li>
                {/foreach}
                </ul>
            </div>
            <div id="rig">
                <table style="font-size: 16px;" width="25%" >
                    <tr class="letra12">
                        <td align="left"><b>{$server.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$server.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$port.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$port.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$user.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$user.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$password.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$password.INPUT}</td>
                    </tr>
                    <tr class="letra12">
                        <td align="left"><b>{$pathServer.LABEL}: <span  class="required">*</span></b></td>
                    </tr>
                    <tr class="letra12">
                        <td align="left">{$pathServer.INPUT}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
