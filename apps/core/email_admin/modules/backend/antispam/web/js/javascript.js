$(document).ready(function (){
    verifySieve();

   $(":checkbox[name='chkoldstatus']").iButton({
        labelOn: "On",
        labelOff: "Off",
        change: function ($input){
            $("#status").val($input.is(":checked") ? "on" : "off");
        }
    }).trigger("change");

	changeActivateDefault();

    $( "#slider-range-max" ).slider({
            range: "max",
            min: 1,
            max: 10,
            value: $('#levelnum').val(),
            slide: function( event, ui ) {
                $("#amount").text(ui.value);
                $("#levelnum").val(ui.value);
            }
    });

    $("#amount").text($("#slider-range-max").slider("value"));
    $("#levelnum").val($("#slider-range-max").slider("value"));

    $("#politica").change(function(){
        var opcion = $("#politica option:selected").val();
        if(opcion == "capturar_spam"){
            $('#time_spam').show();
            $("input[name=header]").hide();
        }else{
            $('#time_spam').hide();
            $("input[name=header]").show();
        }
    });

});

function changeActivateDefault()
{
    var status = $('#statusSpam').val();
    if(status=="active"){
        $("input[name=chkoldstatus]").attr("checked", "checked");
        $("#status").val("on");
    }else{
        $("input[name=chkoldstatus]").removeAttr("checked");
        $("#status").val("off");
    }
}

function verifySieve()
{
    var statusSieve = $('#statusSieve').val();
    if(statusSieve == "on"){
        $('#time_spam').show();
        $("input[name=header]").hide();
    }else{
        $('#time_spam').hide();
        $("input[name=header]").show();
    }
}
