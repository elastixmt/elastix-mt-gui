  // Shows and hides the sticky note
function readyFn() {
  $("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
  
  $(document).click(function() {
	 if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
	   $("#neo-sticky-note").addClass("neo-display-none");
       $("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
	 }
  });

  $("#neo-sticky-note-text-edit-delete").click(function(){
	$("#neo-sticky-note").addClass("neo-display-none");
	$("#neo-sticky-note-text").removeClass("neo-display-none");
	$("#neo-sticky-note-text-edit").addClass("neo-display-none");
	$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
  });

  $("#neo-sticky-note").click(function(e) {
    e.stopPropagation();
  });
 
 
   $('.togglestickynote').click(function(e) {
	e.stopPropagation(); // Para evitar q el click se propague hasta el "document"
	note();

   });
}

function readyFn3() { 
  
  $("#neo-sticky-note-text").click(function() {
	$("#neo-sticky-note-text").addClass("neo-display-none");
    $("#neo-sticky-note-text-edit").removeClass("neo-display-none");
	showCharCount();
  });

  $("#neo-sticky-note-textarea").keyup(function() {
    showCharCount();
  });
}

/**
 * Esta Funcion es un ajax que pide la informacion de la nota de un módulo
 */

var note = function() { 
 
	
	if($("#neo-sticky-note").data("neo-sticky-note-status")=="hidden") {
		var arrAction = new Array();
		arrAction["menu"] = "_elastixutils";
		arrAction["id_menu"] = getCurrentElastixModule();
		arrAction["action"]  = "get_sticky_note";
		arrAction["rawmode"] = "yes";
        var webCommon=getWebCommon();
		var urlImaLoading = "<div style='margin: 10px;'><div align='center'><img src='"+webCommon+"images/loading2.gif' /></div><div align='center'><span style='font-size: 14px; '>"+$('#get_note_label').val()+"</span></div></div>";
		$.blockUI({
		  message: urlImaLoading
		});
		request("index.php",arrAction,false,
			function(arrData,statusResponse,error,popup)
			{
				$.unblockUI();
				var description = arrData;
				
				var desc = description.replace(/ /gi, "&nbsp;");
				desc = desc.replace(/\n/gi, "<br>");
				if(statusResponse == "OK"){
					if(description != "no_data"){
						if(description != "")
							$("#neo-sticky-note-text").html(desc);
						else{
							var lbl_no_description = $("#lbl_no_description").val();
							$("#neo-sticky-note-text").text(lbl_no_description);
						}
						$("#neo-sticky-note-textarea").val(description);
						
						if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
							$("#neo-sticky-note").addClass("neo-display-none");
							$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
						} else {
							$("#neo-sticky-note").removeClass("neo-display-none");
							$("#neo-sticky-note").data("neo-sticky-note-status", "visible");
						}
					}
				}else{
					if(error != "no_data")
						alert(error);
					$("#neo-sticky-note-text").html(description);
					if($("#neo-sticky-note").data("neo-sticky-note-status")=="visible") {
						$("#neo-sticky-note").addClass("neo-display-none");
						$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
					} else {
						$("#neo-sticky-note").removeClass("neo-display-none");
						$("#neo-sticky-note").data("neo-sticky-note-status", "visible");
					}
				}
			}
		);
	}else{
		$("#neo-sticky-note").addClass("neo-display-none");
		$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
	}
  
}

 $(document).ready(readyFn);
 $(document).ready(readyFn3);

/**
 * Funcion que cuenta la cantidad de caracteres de un textarea para mostrar
 * la cantidad de caracteres que el usuario puede tipear.
 */
function showCharCount() {
	var charlimit        = 300;
	var textareacontent  = $("#neo-sticky-note-textarea").val();
	var textareanumchars = textareacontent.length;
	var charleft         = charlimit - textareanumchars;
	var lbl = $("#amount_char_label").val();
	if(textareanumchars>charlimit) {
	  $("#neo-sticky-note-textarea").val(textareacontent.substr(0,charlimit));
	  charleft = 0;
	}
	$("#neo-sticky-note-text-char-count").html(charleft + " " + lbl);
}

/**
 * Funcion que envia la peticion de guardar o editar una nota.
 */
function send_sticky_note(){
	var arrAction = new Array();
	arrAction["menu"] = "_elastixutils";
	arrAction["id_menu"] = getCurrentElastixModule();
	arrAction["action"]  = "save_sticky_note";
	arrAction["description"]  = $("#neo-sticky-note-textarea").val();
	var checkeado=$("#neo-sticky-note-auto-popup").attr("checked");
	if(checkeado) {
		arrAction["popup"]  = 1;
	} else {
		arrAction["popup"]  = 0;
	}
	arrAction["rawmode"] = "yes";
    var webCommon=getWebCommon();
	var urlImaLoading = "<div style='margin: 10px;'><div align='center'><img src='"+webCommon+"images/loading2.gif' /></div><div align='center'><span style='font-size: 14px; '>"+$('#save_note_label').val()+"</span></div></div>";
	$.blockUI({
	  message: urlImaLoading
	});
	request("index.php",arrAction,false,
		function(arrData,statusResponse,error)
		{
			$.unblockUI();
			if(statusResponse == "OK"){
				$("#neo-sticky-note").addClass("neo-display-none");
				$("#neo-sticky-note-text").removeClass("neo-display-none");
				$("#neo-sticky-note-text-edit").addClass("neo-display-none");
				$("#neo-sticky-note").data("neo-sticky-note-status", "hidden");
				var themeName = $('#elastix_theme_name').val();
				if(themeName == "elastixneo"){
					if(arrAction['description'] != ""){
						var imgName = "web/themes/elastixneo/images/tab_notes_on.png";
						$('#togglestickynote1').attr('src',imgName);
					}else{
						var imgName = "web/themes/elastixneo/images/tab_notes.png";
						$('#togglestickynote1').attr('src',imgName);
					}
				}
			}else{
				alert(error);
			}
		}
	);
}
