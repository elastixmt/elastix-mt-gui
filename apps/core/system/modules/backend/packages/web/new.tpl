    <table border='0' cellpadding='0' callspacing='0' width='100%' height='44'>
        <tr class="letra12">
            <td width='70%'>{$nombre_paquete.LABEL} &nbsp; {$nombre_paquete.INPUT}
                <input type='submit' class='button' id="submit_nombre" name='submit_nombre' value='{$Search}' />
            </td>
            <td rowspan='2' id='relojArena' style="text-align:center;">
            </td>
        </tr>
        <tr class="letra12">
            <td width='200'>{$submitInstalado.LABEL} &nbsp; {$submitInstalado.INPUT}</td>
        </tr>
    </table>
    <input type='hidden' id='estaus_reloj' value='apagado' />
{literal}
<script type='text/javascript'>
    function mostrarReloj()
    {
       var estatus = $("#estaus_reloj").val();
       if(estatus=='apagado'){
            $("#estaus_reloj").val('prendido');
            $("#relojArena").html("<img src='modules/packages/images/loading.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$UpdatingRepositories}{literal}...</font>");
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
            $("#neo-tabla-header-row-filter-1").click();
		var arrAction                = new Array();
		arrAction["action"]          = "updateRepositories";
		arrAction["menu"]	     = "packages";
		arrAction["rawmode"]         = "yes";
		request("index.php",arrAction,false,
		    function(arrData,statusResponse,error){
				alert(statusResponse);
				$("#relojArena").html("");
				$("#estaus_reloj").val('apagado');
				$("#submit_nombre").click();
		});
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    }
    function installaPackage(paquete,val)
    {   
        var estatus = $("#estaus_reloj").val();
        if(estatus=='apagado'){
	      
            $("#estaus_reloj").val('prendido');
            $("#relojArena").html("");
	    if(val==0)
	      $("#relojArena").html("<img src='images/loading.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$InstallPackage}{literal}: "+ paquete +"...</font>");
            else
	      $("#relojArena").html("<img src='images/loading.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$UpdatePackage}{literal}: "+ paquete +"...</font>");
            
	    $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
            $("#neo-tabla-header-row-filter-1").click();
		var arrAction                    = new Array();
		arrAction["action"]      = "install";
		arrAction["menu"]        = "packages";
		arrAction["paquete"]	 = paquete;
		arrAction["val"]	 = val;
		arrAction["rawmode"]     = "yes";
		request("index.php",arrAction,false,
         	    function(arrData,statusResponse,error){
                                alert(statusResponse);
                                $("#relojArena").html("");
                                $("#estaus_reloj").val('apagado');
                                $("#submit_nombre").click();

		});
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    }

    function uninstallPackage(paquete)
    {
        var estatus = $("#estaus_reloj").val();
        if(estatus=='apagado'){
            $("#estaus_reloj").val('prendido');
            $("#relojArena").html("<img src='images/loading.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$UninstallPackage}{literal}: "+ paquete +"...</font>");
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
            $("#neo-tabla-header-row-filter-1").click();
                var arrAction                    = new Array();
                arrAction["action"]      = "uninstall";
                arrAction["menu"]        = "packages";
                arrAction["paquete"]     = paquete;
                arrAction["rawmode"]     = "yes";
                request("index.php",arrAction,false,
                    function(arrData,statusResponse,error){
                                alert(statusResponse);
                                $("#relojArena").html("");
                                $("#estaus_reloj").val('apagado');
                                $("#submit_nombre").click();

                });
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    }


</script>

<script>
function confirmDelete(paquete) {
  if (confirm("{/literal}{$msgConfirmDelete}{literal}")) {
             uninstallPackage(paquete);
  }
}
function confirmUpdate(paquete) {
  if (confirm("{/literal}{$msgConfirmUpdate}{literal}")) {
             installaPackage(paquete,1);
  }
}
</script>

{/literal}
