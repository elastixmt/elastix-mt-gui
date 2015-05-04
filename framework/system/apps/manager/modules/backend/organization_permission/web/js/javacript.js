var estado="off";
$(document).ready(function(){
	$("#idOrganization").on("change", function() {
        $("#idformgrid").submit();
    });
     $('#selectAll').change(function(){
		if($(this).is(":checked")){
			var resource = new Array();
			resource=$('td input:checkbox[class=resource]');
			if(resource.length > 0){
				$('td').children("input:checkbox[class=resource]").each(function(){
					if($(this).attr("disabled")!="disabled"){
						$(this).val("on");
						$(this).attr("checked","checked");
						var group=$(this).parents("tr:first").children("td").children("input:checkbox[class=group]");
						group.removeAttr("disabled");
					}
				});
			}
			$('#selectAll').val("on");
		}else{
			$('#selectAll').val("off");
			//se obtiene una lista de los recursos de la pagina y los grupos de la organizacion
			if($('td input:checkbox[class=resource]').length > 0){
				var idOrg=$("#idOrganization").val();
				var arrAction = new Array();
				arrAction["action"]   = "getSelected";
				arrAction["menu"]     = "organization_permission";
				arrAction["rawmode"]  = "yes";
				arrAction["idOrg"]    = idOrg;
				request("index.php",arrAction,false,
				function(arrData,statusResponse,error)
				{
					if(error!=""){
						alert(error);
					}else{
						//retiro todos los checkbox
						$('td').children("input:checkbox[class=resource]").each(function(){
							$(this).val("off");
							$(this).removeAttr("checked");
							if($(this).attr("disabled")!="disabled"){
								var group=$(this).parents("tr:first").children("td").children("input:checkbox[class=group]");
								group.attr("disabled","disabled");
							}
							for (var i=0; i < arrData.length; i++){
								if($("#"+arrData[i]).attr("id")==$(this).attr("id")){
									$(this).val("on");
									$(this).attr("checked","checked");
									if($(this).attr("disabled")!="disabled"){
									var group2=$(this).parents("tr:first").children("td").children("input:checkbox[class=group]");
										group2.removeAttr("disabled");
									}
									break;
								}
							}
						});
					}
				});
			}
		} 
	 });

	 $('td input:checkbox[class=resource]').change(function(){
		 if($(this).is(":checked")){
			if($(this).attr("disabled")!="disabled"){
				$(this).val("on");
				$(this).attr("checked","checked");
				var group=$(this).parents("tr:first").children("td").children("input:checkbox[class=group]");
				group.removeAttr("disabled");
			}
		 }else{
			$(this).val("off");
			if($(this).attr("disabled")!="disabled"){
				var group=$(this).parents("tr:first").children("td").children("input:checkbox[class=group]");
				group.attr("disabled","disabled");
			}
		}
	 });
});


