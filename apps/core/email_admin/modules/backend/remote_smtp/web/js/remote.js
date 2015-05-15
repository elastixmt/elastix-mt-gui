$(document).ready(function(){

    setSelectedDomain();
    changeActivateDefault();
    $('#SMTP_Server').change(function(){
        var domain = $('#SMTP_Server option:selected').val();
        if(domain == "custom"){
            $('input[name=relayhost]').val("");
            $('input[name=port]').val("");
        }else{ 
            $('input[name=relayhost]').val(domain);
            $('input[name=port]').val("587");
        }
    });

    $('input[name=chkoldstatus]').iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#status").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

//	changeActivateDefault();
//	setSelectedDomain();

    $('#SMTP_Server').change(function(){
        var server = $('#SMTP_Server option:selected').text();
        var example = "";

        if(server=="GMAIL" || server=="HOTMAIL"){
            $('input[name=chkoldautentification]').attr("checked", "checked");
            $('#autentification').val("on");
        }else{
            $('input[name=chkoldautentification]').removeAttr("checked");
            $('#autentification').val("off");
        }
        if(server=="GMAIL" || server=="HOTMAIL" || server=="YAHOO"){
            example = "example@"+server.toLowerCase()+".com";
	    $('.validpass').show();
	}else{
            example = "example@domain.com";
	    $('.validpass').hide();
	}
        $('#example').text(example);
    });
});


function setSelectedDomain(){
	
    $('#SMTP_Server option').each(function(){
        var dominio = $('input[name=relayhost]').val();
        var relay   = $(this).text();
        var server  = "";
        if(/smtp\.gmail\.com/.test(dominio))
            server = "GMAIL";
        if(/smtp\.mail\.yahoo\.com/.test(dominio))
            server = "YAHOO";
        if(/smtp\.live\.com/.test(dominio))
            server = "HOTMAIL";
        if(relay==server){
            var example = "example@"+server.toLowerCase()+".com";
            $(this).attr("selected", "selected");
            $('#example').text(example);
        }
    });
    
    var server = $('#SMTP_Server option:selected').val();
	if(server=="custom")
		$('.validpass').hide();
    else
		$('.validpass').show();
    
}

// cambia el estado del hidden "status" de on a off
function changeActivateDefault()
{
    var status = $('#status').val();
    if(status=="on"){
        $("input[name=chkoldstatus]").attr("checked", "checked");
        $("#status").val("on");
    }else{
        $("input[name=chkoldstatus]").removeAttr("checked");
        $("#status").val("off");
    }
}