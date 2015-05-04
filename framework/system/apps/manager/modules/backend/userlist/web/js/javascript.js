function select_organization()
{
	var id=$("#organization").find('option:selected').val();
	var message = "";
	var arrAction = new Array();
	arrAction["menu"]="userlist";
	arrAction["action"]="get_groups";
	arrAction["idOrganization"]=id;
	arrAction["rawmode"]="yes";
	request("index.php", arrAction, false,
		function(arrData,statusResponse,error){
			if(error!=""){
				alert(error);
			}else{
				$("select[name='group'] option").remove();
				var i=0;
				for( x in arrData){
					var opcion=arrData[x][0];
					var valor=arrData[x][1];
					if(x<3){
						$('input[name="'+opcion+'"]').val(valor);
					}else if( x==3){
						$("select[name='group']").append("<option value="+opcion+" selected='selected'>"+valor+"</option>");
					}else
						$("select[name='group']").append("<option value="+opcion+">"+valor+"</option>");
				}
			}
	});
}
function mailbox_reconstruct(username){
    if(username==''){
        alert('Invalid Username');
    }
    
    ShowModalPopUP("Reconstruct Mailbox", 350, 350,'<div id="reconstruct_msg" style="margin:20px auto;"><img src="../web/_common/images/loading.gif">');
    var arrAction = new Array();
    arrAction["menu"]="userlist";
    arrAction["action"]="reconstruct_mailbox";
    arrAction["username"]=username;
    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            if(error!=""){
                $("#reconstruct_msg").html(error);
            }else{
                $("#reconstruct_msg").html(arrData);
            }
    });
}