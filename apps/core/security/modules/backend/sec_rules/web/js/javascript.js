var changeRule = false;

$(document).ready(function(){
    showElementByTraffic();
    showElementByProtocol();
    $('.fielform').css("border-color","#FFF");
    $('#id_protocol').change(function(){
        var valor = $('#id_protocol option:selected').val();
        var arrAction              = new Array();
            arrAction["action"]    = "getPorts";
			arrAction["menu"]	   = "sec_rules";
            arrAction["rawmode"]   = "yes";
            arrAction["protocol"]  =  valor;
            request("index.php",arrAction,false,
                function(arrData,statusResponse,error)
                {
                    var html = "";
                    $('#port_in').html("");
                    $('#port_out').html("");
                    var key = "";
                    for(key in arrData){
                        valor = arrData[key];
                        html += "<option value = "+key+">"+valor+"</option>";
                    }
                    $('#port_in').html(html);
                    $('#port_out').html(html);
                }
            );

    });


  $(".up,.down").click(function(){
		var div_msg = document.getElementById("message_error");
        var aviso = "Running Action...";
        if(changeRule){
			if(document.getElementById("neo-contentbox-maincolumn")){
				document.getElementById("msg_status").style.border = "1px solid #AAA";
				$("#msg_status").html(aviso);
				setTimeout('$("#msg_status").html("")',300);
				setTimeout('document.getElementById("msg_status").style.border = ""',300);
			}else{
				$("#msg_status").html("<span style='color:red;'>"+aviso+"</span>");
				setTimeout('$("#msg_status").html("")',300);
			}
		}else{
		changeRule = true;
        var row  = $(this).parents("tr:first");
        var info = $(this).attr("id");
        var neighborrow = "";
        var changing = "";
        var p1 = "";
		var element = $(this);
		var changeToOtherPage = false;
        if ($(this).is(".up")) {
			var orderId=info.split('_');
            if(orderId[2]==1 || row.parents("tbody").children("tr:first").children().contents().next().attr("id")==info || row.prev().attr("class") == "neo-table-title-row" || row.prev().attr("class") == "table_title_row"){//si soy el primer elemento
				changeToOtherPage = true;
				var direction = "up";
            }// si no soy el primer elemento
            else{
				if($(this).parents("td:first").attr("class")=="table_data_last_row" || $(this).parents("td:first").attr("class")=="neo-table-data-row table_data_last_row"){ //si soy el ultimo
					//si tema es elastixneo
					if(row.next().attr("class") == "neo-table-title-row"){ // si soy el ultimo elemento
						row.children().attr("class","neo-table-data-row table_data"); // dejo de ser el ultimo elemento
						row.prev().children().attr("class","neo-table-data-row table_data_last_row"); // al que estaba antes de mi lo hago ultimo elemento
					}else{
						row.children().attr("class","table_data"); // dejo de ser el ultimo elemento
						row.prev().children().attr("class","table_data_last_row"); // al que estaba antes de mi lo hago ultimo elemento
					}
				}
                p1 = row.prev().children().contents();
                neighborrow = p1.next().attr("id");
                changing = "rulerup";
			}
        } else {
            if($(this).parents("td:first").attr("class")=="table_data_last_row" || $(this).parents("td:first").attr("class")=="neo-table-data-row table_data_last_row"){ // si soy el ultimo elemento
				changeToOtherPage = true;
				var direction = "down";
            }
            else{
				if(row.next().children("td:first").attr("class")=="table_data_last_row" || row.next().children("td:first").attr("class")=="neo-table-data-row table_data_last_row"){ //si soy el penultimo elemento
					if(row.next().next().attr("class") == "neo-table-data-row table_data_last_row"){ //tema elastixneo
						row.children().attr("class","neo-table-data-row table_data_last_row");
						row.next().children().attr("class","neo-table-data-row table_data");
					}else{
						row.children().attr("class","table_data_last_row"); // me convierto en el ultimo elemento
						row.next().children().attr("class","table_data"); // el que estab antes de mi sube
					}
                }
                p1 = row.next().children().contents();
                neighborrow = p1.next().attr("id");
                changing = "rulerdown";
			}
        }

	if(!changeToOtherPage){
	    var arrAction                    = new Array();
		arrAction["action"]          = "change";
		arrAction["menu"]	     = "sec_rules";
		arrAction["rawmode"]         = "yes";
		arrAction["neighborrow"]     = neighborrow;
		arrAction["actualrow"]       = info;
		request("index.php",arrAction,false,
		    function(arrData,statusResponse,error)
		    {
			if(error){
				alert(error);
				changeRule = false;
			}else if(p1!=""){
			    response = statusResponse.split(':');
				button = response[1] + "<form  method='POST' style='margin-bottom:0;' action='?menu=sec_rules'><input class='button' type='submit' name='exec' value="+response[2]+"></form>"
				createMsg(response[4],button,response[3],response[0]);
			    neighborrow = neighborrow.split('_');
			    actualrow = info.split('_');

			    p1.next().attr("id","rulerup_" + neighborrow[1] + "_" + actualrow[2]);
			    p1.next().next().attr("id","rulerdown_" + neighborrow[1] + "_" + actualrow[2]);

			    $("#div_"+actualrow[1]).html(neighborrow[2]);
			    $("#div_"+neighborrow[1]).html(actualrow[2]);

			    var nodo = $("#"+info);

			    if(changing == "rulerup"){
					row.insertBefore(row.prev());
					nodo.attr("id","rulerup_" + actualrow[1] + "_" + neighborrow[2]);
					nodo.next().attr("id","rulerdown_" + actualrow[1] + "_" + neighborrow[2]);
			    }
			    else{
					row.insertAfter(row.next());
					nodo.attr("id","rulerdown_" + actualrow[1] + "_" + neighborrow[2]);
					nodo.prev().attr("id","rulerup_" + actualrow[1] + "_" + neighborrow[2]);
			    }
			    changeRule = false;
			}else{
				alert(statusResponse);
				changeRule = false;
			}
		    }
		);
	}
	else{
	    var arrAction                = new Array();
		arrAction["action"]      = "changeOtherPage";
		arrAction["menu"]	 = "sec_rules";
		arrAction["direction"]	 = direction;
		arrAction["rawmode"]     = "yes";
		arrAction["actualrow"]   = info;
		request("index.php",arrAction,false,
		    function(arrData,statusResponse,error)
		    {
			if(error){
				alert(error);
				changeRule = false;
			}else if(arrData){
			    response = statusResponse.split(':');
				button = response[1] + "<form  method='POST' style='margin-bottom:0;' action='?menu=sec_rules'><input class='button' type='submit' name='exec' value="+response[2]+"></form>"
				createMsg(response[4],button,response[3],response[0]);
			    actualrow = info.split('_');
			    if(direction == "up"){
				element.attr("id","rulerup_" + arrData["id"] + "_" + actualrow[2]);
				element.next().attr("id","rulerdown_" + arrData["id"] + "_" + actualrow[2]);
				element.prev().attr("id","div_" + arrData["id"]);
			    }
			    else{
				element.attr("id","rulerdown_" + arrData["id"] + "_" + actualrow[2]);
				element.prev().attr("id","rulerup_" + arrData["id"] + "_" + actualrow[2]);
				element.prev().prev().attr("id","div_" + arrData["id"]);
			    }
			    var tdParent = element.parents("td:first");
			    tdParent.prev().children().attr("name","id_" + arrData["id"]);
			    tdParent.next().html("<a><img src='"+arrData["traffic"]["image"]+"' border=0 title='"+arrData["traffic"]["title"]+"'</a>");
			    tdParent.next().next().html("<a><img src='"+arrData["target"]["image"]+"' border=0 title='"+arrData["target"]["title"]+"'</a>");
			    tdParent.next().next().next().html(arrData["interface"]);
			    tdParent.next().next().next().next().html(arrData["ipSource"]);
			    tdParent.next().next().next().next().next().html(arrData["ipDestiny"]);
			    tdParent.next().next().next().next().next().next().html(arrData["protocol"]);
			    tdParent.next().next().next().next().next().next().next().html(arrData["details"]);
			    var href = tdParent.next().next().next().next().next().next().next().next().children().attr("href");
			    tdParent.next().next().next().next().next().next().next().next().html(arrData["activate"]);
			    href = href.split('&');
			    if(href[3] && href[4]){
				var nav   = href[3];
				var start = href[4];				tdParent.next().next().next().next().next().next().next().next().children().attr("href",tdParent.next().next().next().next().next().next().next().next().children().attr("href") + "&" + nav + "&" + start);
			    }
			    tdParent.next().next().next().next().next().next().next().next().next().html(arrData["edit"]);
				changeRule = false;
			}
			else{
				alert(statusResponse);
				changeRule = false;
			}
		    });
	}}
    });

});


function createMsg(tittle,message_data,button_tittle,aviso){
	if(tittle && message_data){
		$("#message_error").remove();
		if(document.getElementById("neo-contentbox-maincolumn")){
			var message= "<div class='div_msg_errors' id='message_error'>" +
						"<div style='float:left;'>" +
						"<b style='color:red;'>&nbsp;&nbsp;"+tittle+": </b>" +
						"</div>" +
						"<div style='text-align:right; padding:5px'>" +
						"<input type='button' onclick='hide_message_error();' value='"+button_tittle+"'/>" +
						"</div>" +
						"<div style='position:relative; top:-12px; padding: 0px 5px'>" +
						message_data +
						"</div>" +
					"</div>";

			$(".neo-module-content:first").prepend(message);
			document.getElementById("msg_status").style.border = "1px solid #AAA";
			$("#msg_status").html(aviso);
				setTimeout('$("#msg_status").html("")',300);
				setTimeout('document.getElementById("msg_status").style.border = ""',300);
		}
		else if(document.getElementById("elx-blackmin-content")){
			var message = "<div class='ui-state-highlight ui-corner-all' id='message_error'>" +
						"<p>" +
						"<span style='float: left; margin-right: 0.3em;' class='ui-icon ui-icon-info'></span>" +
						"<span id='elastix-callcenter-info-message-text'>"+ tittle + ": " + message_data +"</span>" +
						"</p>" +
					"</div>";
			$("#elx-blackmin-content").prepend(message);
			$("#msg_status").html("<span style='color:white;'>"+aviso+"</span>");
				setTimeout('$("#msg_status").html("")',300);
		}
		else{
			$(".message_board").remove();
			var message= "<div id='message_error'><table width='100%'><tr><td align='left'><b style='color:red;'>" +
					tittle + ": </b>" + message_data + "</td> <td align='right'><input type='button' onclick='hide_message_error();' value='" +
					button_tittle+ "'/></td></tr></table></div>";
			$("body > table > tbody > tr > td:last").prepend(message);
			$("#msg_status").html("<span style='color:red;'>"+aviso+"</span>");
				setTimeout('$("#msg_status").html("")',300);
		}
	}
}

function showElementByTraffic()
{
    var traffic = document.getElementById('id_traffic');

    if(traffic){
        if( traffic.value == 'INPUT' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = 'none';
        }
        else if( traffic.value == 'OUTPUT' ){
            document.getElementById('id_interface_in').style.display = 'none';
            document.getElementById('id_interface_out').style.display = '';
        }
        else if( traffic.value == 'FORWARD' ){
            document.getElementById('id_interface_in').style.display = '';
            document.getElementById('id_interface_out').style.display = '';
        }
    }
}

function showElementByProtocol()
{
    var protoc = document.getElementById('id_protocol');

    if(protoc){
        if( protoc.value == 'TCP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'UDP' ){
            document.getElementById('id_port_in').style.display = '';
            document.getElementById('id_port_out').style.display = '';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'ICMP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = '';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'IP' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_id_ip').style.display = '';
        }
        else if( protoc.value == 'ALL' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_established').style.display = 'none';
            document.getElementById('id_related').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
        }
        else if( protoc.value == 'STATE' ){
            document.getElementById('id_port_in').style.display = 'none';
            document.getElementById('id_port_out').style.display = 'none';
            document.getElementById('id_type_icmp').style.display = 'none';
            document.getElementById('id_id_ip').style.display = 'none';
            var state = document.getElementById('state');
            var input_ = state.getElementsByTagName('input');
            var established_check = false;
            var related_check = false;
            if(input_[0].value == ""){
                 established_check = false;
                 related_check = false;
            }else{
                var tmp = input_[0].value.split(",");
                if(tmp[0]=="Established"){
                     established_check = true;
                    if(tmp[1]=="Related")
                         related_check = true;
                }else if(tmp[0]=="Related")
                        related_check = true;
            }
            var established = document.getElementById('id_established');
            established.style.display = '';
            var checkbox1 = established.getElementsByTagName("input");
            checkbox1[0].checked = established_check;
            if(established_check)
                document.getElementById('established').value = "on";
            else
                document.getElementById('established').value = "off";
            var related = document.getElementById('id_related');
            related.style.display = '';
            var checkbox2 = related.getElementsByTagName("input");
            checkbox2[0].checked = related_check;
            if(related_check)
                document.getElementById('related').value = "on";
            else
                document.getElementById('related').value = "off";
        }
    }
}
