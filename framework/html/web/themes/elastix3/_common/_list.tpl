<div id="contsetting">
    
    <div class="my_settings">

        <div class="row">
            <div class="col-lg-9">
            
                {foreach from=$arrActions key=k item=accion name=actions}
                    {if $accion.type eq 'button'}
                        {if $accion.task eq 'elx_export_data'}
                            <div class="btn-group">
                                <button class="btn btn-default btn-sm dropdown-toggle" type="button" name="{$accion.task}" data-toggle="dropdown"> {$accion.alt} {$lblExport} <span class="caret"></span> </button>
                                <ul class="dropdown-menu" role="menu" id="export-data">
                                    <li id="exportcsv"><a href="{$url}&exportcsv=yes&rawmode=yes"><span class="glyphicon glyphicon-file"></span> CSV (Legacy)</a></li>
                                    <li id="exportspreadsheet"><a href="{$url}&exportspreadsheet=yes&rawmode=yes"><span class="glyphicon glyphicon-file"></span> XML</a></li>
                                    <li id="exportpdf"><a href="{$url}&exportpdf=yes&rawmode=yes"><span class="glyphicon glyphicon-file"></span> PDF </a></li>
                                </ul>                                
                            </div>
                        {else}
                            <button class="btn btn-default btn-sm" type="button" name="{$accion.task}" id="{$accion.task}" {if !empty($accion.onclick)} onclick="{$accion.onclick}" {/if}'> {$accion.alt}</button>    
                        {/if}
                    {/if}
                {/foreach}
                
            </div>
            
            <div class="col-lg-3" style="text-align:right;">
                <div id="elx_pager">
                
                </div>
            </div>
        </div>

        <div class="row" >
            <div class="col-lg-12">
                {if !empty($contentFilter)}
                    {$contentFilter}
                {/if}
            </div>
        </div>
        
        {foreach from=$arrActions key=k item=accion name=actions}
            {if $accion.task eq 'elx_export_data'}
                <div class="row oculto" id="elx_row_upload_file" >
                    <div class="col-lg-12">
                        <input type="file" id="elx_uploadFile" name='elx_uploadFile'>
                    </div>
                </div>
            {/if}
        {/foreach}
        
        <div class="row elx-modules-content">
            <div id='elx_data_grid' class="col-lg-12 table-responsive">
                <table class="table table-condensed table-hover table-bordered">
                    <thead>
                        <tr class="danger">
                            {section name=columnNum loop=$numColumns start=0 step=1}
                                <td class="name-label">{$header[$smarty.section.columnNum.index].name}&nbsp;</td>
                            {/section}
                        </tr>
                    </thead>
                    <tbody>
                    
                        {foreach from=$arrData key=k item=data name=filas}
                            <tr>
                                {section name=columnNum loop=$numColumns start=0 step=1}
                                    <td>
                                        {if $data[$smarty.section.columnNum.index] eq ''}&nbsp;{/if}
                                        {$data[$smarty.section.columnNum.index]}
                                    </td>
                                {/section}
                            </tr>
                        {/foreach}

                    </tbody>
                </table>
            </div>
        </div>
<!--         
        <div class="row" >
            <div id="prueba" class="col-lg-12">

            </div>
        </div>
 -->
    </div>
</div>
<input type="hidden" value="{$currentPage}" id="elxGridCurrent">
<input type="hidden" value="{$numPage}" id="elxGridNumPage">


{literal}
    <script type="text/Javascript">
        $( document ).ready(function() {
        
        if($("#elxGridCurrent").val()==0){
            $("#elxGridCurrent").val()=1;
        }
        
        if($("#elxGridNumPage").val()==0){
            $("#elxGridNumPage").val()=1;
        }
       
        /* pagination */
            var options = {
                    currentPage: $("#elxGridCurrent").val(),
                    totalPages: $("#elxGridNumPage").val(),
                    numberOfPages: 3,
                    onPageClicked: function(e,originalEvent,type,page){
                        elxGridSearch(page);
                    }
                }
            $('#elx_pager').bootstrapPaginator(options);  
        });
    </script>   
{/literal}
