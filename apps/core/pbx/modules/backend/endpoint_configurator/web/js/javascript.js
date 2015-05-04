function getDevices(model,mac)
{
    var arrAction              = new Array();
	arrAction["action"]    = "getDevices";
	arrAction["menu"]      = "endpoint_configurator";
	arrAction["rawmode"]   = "yes";
	arrAction["id_model"]  = model.value;
	request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
		    if(error != "yes"){
			$('#id_device_'+mac).html("");
			var html = "";
			for(key in arrData){
			    valor = arrData[key];
			    html += "<option value = "+key+">"+valor+"</option>";
			}
			$('#id_device_'+mac).html(html);
		    }
                }
            );
}

function activate_option_lan()
{
    var static = document.getElementById('lan_static');
    var dhcp   = document.getElementById('lan_dhcp');
    if(static){
	if(static.checked==true)
	{
	    document.getElementById('lan_ip').style.display = '';
	    document.getElementById('lan_mask').style.display = '';
	}
	else
	{
	    document.getElementById('lan_ip').style.display = 'none';
	    document.getElementById('lan_mask').style.display = 'none';
	}
    }
}

function activate_option_wan()
{
    var static = document.getElementById('wan_static');
    var dhcp   = document.getElementById('wan_dhcp');
    if(static){
	if(static.checked==true)
	{
	    document.getElementById('wan_ip').style.display = '';
	    document.getElementById('wan_mask').style.display = '';
	}
	else
	{
	    document.getElementById('wan_ip').style.display = 'none';
	    document.getElementById('wan_mask').style.display = 'none';
	}
    }
}

function changeFields(element)
{
    var value = $(element).val();
    var static_wan = document.getElementById('wan_static');
    if(value == "yes"){
	document.getElementById('side').style.display='';
	document.getElementById('wan').style.display='';
	document.getElementById('check_wan').style.display='';
	if(static_wan){
	    if(static_wan.checked==true){
		document.getElementById('wan_ip').style.display='';
		document.getElementById('wan_mask').style.display='';
	    }
	}
    }else if(value == "no"){
	document.getElementById('side').style.display='none';
	document.getElementById('wan').style.display='none';
	document.getElementById('check_wan').style.display='none';
	document.getElementById('wan_ip').style.display='none';
	document.getElementById('wan_mask').style.display='none';
    }
}
$(function () {
$('.checkall').click(function () {
		$(".resp").find(':checkbox').attr('checked', this.checked);
	});


    $("#localization_data").css("display","none");
});

/*$(document).ready(function() {
	$(":checkbox").click(function() {
	var param = $(this).attr("id");
	var toRemove = 'chk_';
	var param = param.replace(toRemove,'');

	 ////changeInput(param);
	});
*/
   /*     if($("input[name='analog_trunk_lines']").val()!="")
		$("input[name='analog_trunk_lines']").prop('disabled', true);

         if($("input[name='analog_extension_lines']").val()!="")
                $("input[name='analog_extension_lines']").prop('disabled', true);
      */
   //      $("input[name='telnet_username']").prop('disabled', true);
       

//});
/*

function verifyIP(IPvalue) {
    errorString = "";
    theName = "IPaddress";

    var ipPattern = /^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/;
    var ipArray = IPvalue.match(ipPattern);

    if (IPvalue == "0.0.0.0")
        errorString = errorString + theName + ': ' + IPvalue + ' is a special IP address and cannot be used here.';
    else if (IPvalue == "255.255.255.255")
        errorString = errorString + theName + ': ' + IPvalue + ' is a special IP address and cannot be used here.';
    if (ipArray == null)
        errorString = errorString + theName + ': ' + IPvalue + ' is not a valid IP address.';
    else {
        for (i = 0; i < 5; i++) {
            thisSegment = ipArray[i];
            if (thisSegment > 255) {
                errorString = errorString + theName + ': ' + IPvalue + ' is not a valid IP address.';
                i = 4;
            }
            if ((i == 0) && (thisSegment > 255)) {
                errorString = errorString + theName + ': ' + IPvalue + ' is a special IP address and cannot be used here.';
                i = 4;
            }
        }
    }
    extensionLength = 3;
    if (errorString == "") {
        return true
    }
    else {
        return false
    }
}

function changeInput(param) {
	var ip = $("#hid_"+param).val();
	   if($("#chk_"+param).is(':checked')) {
		$(".resp_"+param).append("<input type='text' size='13' id='text_"+param+"' value='"+ip+"' style='text-align:center; float:left' />");     
		$("#text_"+param).css("display","block");
		$("#a_"+param).css("display","none");
	   }
	   else{
		var new_ip = $("#text_"+param).val();
		
		var valid = verifyIP(new_ip);
				
		if(valid==true){
			$("#text_"+param).remove();
			$("#hid_"+param).val(new_ip);
			$("#a_"+param).html(new_ip);
			$("#a_"+param).css("display","block");
		}
		else{
			var old_ip = $("#hid_"+param).val();
			$("#chk_"+param).prop("checked", true);
			alert("Invalid IP");
			$("#text_"+param).val(old_ip);
		}
	   }
}*/
