/* 
 * Copyright 2008 por Robert Baumgartner; the.godking.com
 * todos los derechos reservados
 * 
 * 
 */

tooltipClass=".tooltip"; // reemplaza por tu clase, para usar con muchas clases, separar con comas.

function str_replace(search, replace, subject) { return subject.split(search).join(replace); }
this.tooltip=function(){xOffset=10;yOffset=20;fadeInTime=400;jQuery(tooltipClass).hover(function(e){this.t=this.title;this.title="";this.t=str_replace("::", "<br />", this.t);this.t=str_replace("[!]", "<span class='tooltipTitle'>", this.t);this.t=str_replace("[/!]", "</span><br />", this.t);this.t=str_replace("[", "<", this.t);this.t=str_replace("]", ">", this.t);if(this.t != "") {jQuery("body").append("<p id='tooltip'>"+this.t+"</p>");jQuery("#tooltip").css("top",(e.pageY-xOffset)+"px").css("left",(e.pageX+yOffset)+"px").fadeIn(fadeInTime);}},function(){this.title=this.t;jQuery("#tooltip").remove();});jQuery(tooltipClass).mousemove(function(e){jQuery("#tooltip").css("top",(e.pageY-xOffset)+"px").css("left",(e.pageX+yOffset)+"px");});};jQuery(document).ready(function(){tooltip();});

/*http://micodigobeta.com.ar*/