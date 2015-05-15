var frmvalidator = null;
$( document ).ready(function() {

/* desabilitar el boton guardar si existe el error inicial*/   
    if( $('#initial_message_area').length )
    {
        $('button[name=save_new]').attr('disabled', 'disabled');
    }


/* desactivamos el imput del email address*/
    $('input[name=EMAIL_ADDRESS]').attr('disabled', true); 

/* js que funciona para los renge date */
$(function() {
    $( "#inputFrom" ).datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: 0,
    onClose: function( selectedDate ) {
        $( "#inputTo" ).datepicker( "option", "minDate", selectedDate );
    }
    });
    $( "#inputTo" ).datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: 0,
    onClose: function( selectedDate ) {
        $( "#inputFrom" ).datepicker( "option", "maxDate", selectedDate );
    }
    });
});


/*calcular numero de dias de vacaciones cada vez que cambie los campos de fecha*/
    $(document).ready(function(){	    
            var cadenaFecha1 = $("#inputFrom").val();
            var cadenaFecha2 = $("#inputTo").val();
            var strDate1 = new Date(cadenaFecha1);
            var strDate2 = new Date(cadenaFecha2);

            //Resta fechas y redondea
            var diferencia = strDate2.getTime() - strDate1.getTime();
            var dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            var segundos = Math.floor(diferencia / 1000);
            $('#num_days').text(" "+ dias + "  ");
    });

    $(document).ready(function(){
        $("input[id^='input']").change(function(){
            var cadenaFecha1 = $("#inputFrom").val();
            var cadenaFecha2 = $("#inputTo").val();
            var strDate1 = new Date(cadenaFecha1);
            var strDate2 = new Date(cadenaFecha2);

            //Resta fechas y redondea
            var diferencia = strDate2.getTime() - strDate1.getTime();
            var dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));
            var segundos = Math.floor(diferencia / 1000);
            $('#num_days').text(" "+ dias + "  ");
        });

    });
/*********************************************************************************/


});

function saveVacation(){
    showElastixUFStatusBar("Saving...");
    var arrAction = new Array();
    arrAction["menu"]="vacations";
    arrAction["action"]="editVacation";
    arrAction["intiDate"]=$("input[name='FROM']").val();
    arrAction["endDate"]=$("input[name='TO']").val();
    arrAction["emailSubject"]=$("input[name='EMAIL_SUBJECT']").val();
    arrAction["emailBody"]=$("textarea[name='EMAIL_CONTENT']").val();

    arrAction["rawmode"]="yes";
    request("index.php", arrAction, false,
        function(arrData,statusResponse,error){
            hideElastixUFStatusBar();
            if (error != '' ){
                showElxUFMsgBar('error',error['stringError']);
                // se recorre todos los elementos erroneos y se agrega la clase error (color rojo)
                $(".flag").removeClass("has-error");
                $(".visible-tooltip").removeClass("visible-tooltip").addClass("hidden-tooltip");
                for(var i=0;i<error['field'].length; i++){     
                    $("[name='"+error['field'][i]+"']").parents(':first').addClass("has-error flag");
                    $("[name='"+error['field'][i]+"']").next().tooltip().removeClass("hidden-tooltip").addClass("visible-tooltip");
                }
            }else{
                //se elimina el borde rojo a los campos que estaban erroneos, y que hayan sido ingresados 
                $(".flag").removeClass("has-error");
                $(".visible-tooltip").removeClass("visible-tooltip").addClass("hidden-tooltip");
                showElxUFMsgBar('success',arrData);
            }
    });
}
