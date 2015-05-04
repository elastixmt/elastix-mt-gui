<form  method="POST" style="margin-bottom:0;" action="{$url}">
    <table width="{$width}" align="center" border="0" cellpadding="0" cellspacing="0">
        {if !empty($arrActions) || !empty($contentFilter)}
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" class="filterForm" width="100%">
                  {if !empty($arrActions)}
                    <tr>
                      <td style="padding:0px;">
                        {foreach from=$arrActions key=k item=accion name=actions}
                            {if $smarty.foreach.actions.first}
                                {assign var="clase" value="table-header-row-filter-first"}
                            {else}
                                {assign var="clase" value="table-header-row-filter"}
                            {/if}

                            {if $accion.type eq 'link'}
                                <a href="{$accion.task}" class="table-action" {if !empty($accion.onclick)} onclick="{$accion.onclick}" {/if} >
                                    <div class="{$clase}" >
                                        {if !empty($accion.icon)}
                                            <img border="0" src="{$accion.icon}" align="absmiddle"  />&nbsp;
                                        {/if}
                                        {$accion.alt}
                                    </div>
                                </a>
                            {elseif $accion.type eq 'button'}
                                <div class="{$clase}">
                                    {if !empty($accion.icon)}
                                        <img border="0" src="{$accion.icon}" align="absmiddle"  />
                                    {/if}
                                    <input type="button" name="{$accion.task}" value="{$accion.alt}" {if !empty($accion.onclick)} onclick="{$accion.onclick}" {/if} class="table-action" />
                                </div> 
                            {elseif $accion.type eq 'submit'}
                                <div class="{$clase}">
                                    {if !empty($accion.icon)}
                                        <img border="0" src="{$accion.icon}" align="absmiddle"  />
                                    {/if}
                                    <input type="submit" name="{$accion.task}" value="{$accion.alt}" {if !empty($accion.onclick)} onclick="{$accion.onclick}" {/if} class="table-action" />
                                </div>                 
                            {elseif $accion.type eq 'text'}
                                <div class="{$clase}" style="cursor:default">                    
                                    <input type="text"   id="{$accion.name}" name="{$accion.name}" value="{$accion.value}" {if !empty($accion.onkeypress)} onkeypress="{$accion.onkeypress}" {/if} style="height:22px" />
                                    <input type="submit" name="{$accion.task}" value="{$accion.alt}" class="table-action" />
                                </div>                 
                            {elseif $accion.type eq 'combo'}
                                <div class="{$clase}" style="cursor:default">
                                    <select name="{$accion.name}" id="{$accion.name}" {if !empty($accion.onchange)} onchange="{$accion.onchange}" {/if}>
                                        {if !empty($accion.selected)}
                                            {html_options options=$accion.arrOptions selected=$accion.selected}
                                        {else}
                                            {html_options options=$accion.arrOptions}
                                        {/if}
                                    </select>
                                    {if !empty($accion.task)} 
                                        <input type="submit" name="{$accion.task}" value="{$accion.alt}" class="table-action" />
                                    {/if}
                                </div> 
                            {elseif $accion.type eq 'html'}
                                <div class="{$clase}">
                                    {$accion.html}
                                </div>
                            {/if}
                        {/foreach}
                      </td>
                    </tr>
                  {/if}
                  {if !empty($contentFilter)}
                    <tr>
                        <td>{$contentFilter}</td>
                    </tr>
                  {/if}
                </table>
            </td>
        </tr>
      {/if}
    <tr>
        <td>
        <table class="table_data" align="center" cellspacing="0" cellpadding="0" width="100%">
            <tr class="table_navigation_row">
            <td colspan="{$numColumns}" class="table_navigation_row">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
                <tr>
                    <td align="left">
                        &nbsp;
                        {if $enableExport==true}
                            <img src="{$WEBCOMMON}images/export.gif" border="0" align="absmiddle" />&nbsp;
                            <font class="letranodec">{$lblExport}</font>&nbsp;&nbsp;
                            <a href="{$url}&exportcsv=yes&rawmode=yes"><img src="{$WEBCOMMON}images/csv.gif"         border="0" align="absmiddle" title="CSV" /></a>&nbsp;
                            <a href="{$url}&exportspreadsheet=yes&rawmode=yes"><img src="{$WEBCOMMON}images/spreadsheet.gif" border="0" align="absmiddle" title="SPREAD SHEET" /></a>&nbsp;
                            <a href="{$url}&exportpdf=yes&rawmode=yes"><img src="{$WEBCOMMON}images/pdf.png"         border="0" align="absmiddle" title="PDF" /></a>&nbsp;
                        {/if}
                    </td>
                    <td align="left" id="msg_status"></td>
                    <td align="right"> 
                    {if $pagingShow}  
                        {if $start<=1}
                        <img
                        src='{$WEBCOMMON}/images/start_off.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'>&nbsp;{$lblStart}&nbsp;&nbsp;<img 
                        src='{$WEBCOMMON}/images/previous_off.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'>
                        {else}
                        <a href="{$url}&nav=start&start={$start}"><img
                        src='{$WEBCOMMON}/images/start.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'></a>&nbsp;{$lblStart}&nbsp;&nbsp;<a href="{$url}&nav=previous&start={$start}"><img 
                        src='{$WEBCOMMON}/images/previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'></a>
                        {/if}
                        &nbsp;{$lblPrevious}&nbsp;<span 
                        class='pageNumbers'>({$start} - {$end} of {$total})</span>&nbsp;{$lblNext}&nbsp;
                        {if $end==$total}
                        <img 
                        src='{$WEBCOMMON}/images/next_off.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$lblEnd}&nbsp;<img 
                        src='{$WEBCOMMON}/images/end_off.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'>
                        {else}
                        <a href="{$url}&nav=next&start={$start}"><img
                        src='{$WEBCOMMON}/images/next.gif' 
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;{$lblEnd}&nbsp;<a 
                        href="{$url}&nav=end&start={$start}"><img 
                        src='{$WEBCOMMON}/images/end.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'></a>
                        {/if}
                    {/if}
                    </td>
                </tr>
                </table>
            </td>
            </tr>
            <tr class="table_title_row">
                {section name=columnNum loop=$numColumns start=0 step=1}
                    <td class="table_title_row">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                {/section}
            </tr>
            {foreach from=$arrData key=k item=data name=filas}
                {if $data.ctrl eq 'separator_line'}
                    <tr>
                        {if $data.start > 0}
                            <td colspan="{$data.start}"></td>
                        {/if}
                        {assign var="data_start" value="`$data.start`"}
                        <td colspan="{$numColumns-$data.start}" style='background-color:#AAAAAA;height:1px;'></td>
                    </tr>
                {else}
                    <tr onMouseOver="this.style.backgroundColor='#f2f2f2';" onMouseOut="this.style.backgroundColor='#ffffff';">
                        {if $smarty.foreach.filas.last}
                            {section name=columnNum loop=$numColumns start=0 step=1}
                            <td class="table_data_last_row">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
                            {/section}
                        {else}
                            {section name=columnNum loop=$numColumns start=0 step=1}
                            <td class="table_data">{if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}{$data[$smarty.section.columnNum.index]}</td>
                            {/section}
                        {/if}
                    </tr>
                {/if}
            {/foreach}
            <tr class="table_navigation_row">
            <td colspan="{$numColumns}" class="table_navigation_row">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="table_navigation_text">
                <tr>
                    <td align="left">&nbsp;</td>
                    <td align="right">
                    {if $pagingShow}  
                        {if $start<=1}
                        <img
                        src='{$WEBCOMMON}/images/start_off.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'>&nbsp;{$lblStart}&nbsp;&nbsp;<img
                        src='{$WEBCOMMON}/images/previous_off.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'>
                        {else}
                        <a href="{$url}&nav=start&start={$start}"><img
                        src='{$WEBCOMMON}/images/start.gif' alt='{$lblStart}' align='absmiddle'
                        border='0' width='13' height='11'></a>&nbsp;{$lblStart}&nbsp;&nbsp;<a href="{$url}&nav=previous&start={$start}"><img
                        src='{$WEBCOMMON}/images/previous.gif' alt='{$lblPrevious}' align='absmiddle' border='0' width='8' height='11'></a>
                        {/if}
                        &nbsp;{$lblPrevious}&nbsp;<span
                        class='pageNumbers'>({$start} - {$end} of {$total})</span>&nbsp;{$lblNext}&nbsp;
                        {if $end==$total}
                        <img
                        src='{$WEBCOMMON}/images/next_off.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$lblEnd}&nbsp;<img
                        src='{$WEBCOMMON}/images/end_off.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'>
                        {else}
                        <a href="{$url}&nav=next&start={$start}"><img
                        src='{$WEBCOMMON}/images/next.gif'
                        alt='{$lblNext}' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;{$lblEnd}&nbsp;<a
                        href="{$url}&nav=end&start={$start}"><img
                        src='{$WEBCOMMON}/images/end.gif' alt='{$lblEnd}' align='absmiddle' border='0' width='13' height='11'></a>
                        {/if}
                    {/if}
                    </td>
                </tr>
                </table>
            </td>
            </tr>
        </table>
        </td>
    </tr>
    </table>
</form>