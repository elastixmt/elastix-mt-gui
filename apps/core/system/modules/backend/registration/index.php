<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-31                                             |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2010-08-09 10:08:51 Mercy Anchundia manchundia@palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoACL.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoRegistration.class.php";

    load_language_module($module_name);

    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);

 

    $pDBACL = new paloDB($arrConf['elastix_dsn']['acl']);
// dos bases de datos setting.db and register.db
    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save":
            $content = saveRegister($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$pDBACL);
            break;
	case "getDataRegisterServer":
	    $content = getDataRegistration($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$pDBACL);
	    break;
        case "showAboutAs":
            $content = showFormAboutAs($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
        case "showRPMS_Version":
            $content = showFormRPMS_Version($smarty, $module_name, $local_templates_dir, $arrConf);
            break;
        default: // view_form
            $content = viewFormRegister($smarty, $module_name, $local_templates_dir, $pDB, $arrConf,$pDBACL);
            break;
    }
    return $content;
}

function showFormAboutAs($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $oForm = new paloForm($smarty,array());
    
    $smarty->assign("ABOUT_ELASTIX",  _tr('About Elastix')." ".$arrConf['elastix_version']);
    $smarty->assign("ABOUT_ELASTIX2", _tr('About Elastix2'));
    $smarty->assign("ABOUT_ELASTIX_CONTENT", _tr('About Elastix Content'));
    $smarty->assign("ABOUT_CLOSED", _tr('About Elastix Closed'));

    $jsonObject   = new PaloSantoJSON();

    $response['html']  = $oForm->fetchForm("$local_templates_dir/_aboutas.tpl","", "");
    $response['title'] = _tr('About Elastix')." ".$arrConf['elastix_version'];

    if($arrConf['mainTheme']=="elastixwave" || $arrConf['mainTheme']=="elastixneo")
        $response['title'] = _tr('About Elastix2');

    $jsonObject->set_message($response);
    return $jsonObject->createJSON();
}

function showFormRPMS_Version($smarty, $module_name, $local_templates_dir, $arrConf)
{
    $oForm = new paloForm($smarty,array());
    
    $smarty->assign("VersionDetails", _tr('VersionDetails'));
    $smarty->assign("VersionPackage", _tr('VersionPackage'));
    $smarty->assign("textMode", _tr('textMode'));
    $smarty->assign("htmlMode", _tr('htmlMode'));

    $jsonObject   = new PaloSantoJSON();

    $response['html']  = $oForm->fetchForm("$local_templates_dir/_rpms_version.tpl","", "");
    $response['title'] = _tr('VersionPackage');

    $jsonObject->set_message($response);
    return $jsonObject->createJSON();
}

function viewFormRegister($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,&$pDBACL)
{
    $pRegister = new paloSantoRegistration($pDB);
    $pACL = new paloACL($pDBACL);
    $arrFormRegister = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormRegister);
    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $registered = "";
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl
    $smarty->assign("identitykeylbl", _tr("Your Server ID"));
    $smarty->assign("registration", _tr("registration"));
    $smarty->assign("alert_message", _tr("alert_message"));
    $smarty->assign("Cancel", _tr("Cancel"));
    $smarty->assign("module_name", $module_name);
    $smarty->assign("sending", _tr("Save information and sending data"));
    $smarty->assign("errorMsg", _tr("Impossible connect to Elastix Web services. Please check your internet connection."));
    $smarty->assign("getinfo", _tr("Getting infomation from Elastix Web Services."));
    $user = isset($_SESSION['elastix_user'])?$_SESSION['elastix_user']:"";

    

    if(!is_file("/etc/elastix.key")){
	$smarty->assign("Activate_registration", _tr("Activate registration"));
    }else{
	$registered = "registered";
	$smarty->assign("Activate_registration", _tr("Update Information"));
    }
    $smarty->assign("registered", $registered);
    $smarty->assign("displayError", "display: none;");
    if($pACL->isUserAdministratorGroup($user)){
	$htmlForm = $oForm->fetchForm("$local_templates_dir/_registration.tpl","", "");
    }else
	$htmlForm = "<div align='center' style='font-weight: bolder;'>"._tr("Not user allowed to access this content")."</div>";
    return $htmlForm;
}



// primero se guarda de manera local y luego se llama al webservice donde envia los datos a almacenar y responde con un valor si se almaceno correctamente
function saveRegister($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,&$pDBACL)
{
    $pRegister    = new paloSantoRegistration($pDB);
    $jsonObject   = new PaloSantoJSON();
    $message      = "";
    $contact_name = trim(getParameter("contactNameReg"));
    $email        = trim(getParameter("emailReg"));
    $phone        = trim(getParameter("phoneReg"));
    $company      = trim(getParameter("companyReg"));
    $address      = trim(getParameter("addressReg"));
    $city         = trim(getParameter("cityReg"));
    $country      = trim(getParameter("countryReg"));
    $idPartner    = trim(getParameter("idPartnerReg"));
    $status       = FALSE;
	$msgResponse  = array();
	$str_error    = "";
    // proceso de validacion de datos
    if($contact_name == ''){
        $str_error .= _tr("* Contact Name: Only text ")."\n";
	}
    if(!preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/",$email)){
        $str_error .= _tr("* Email: Only format email ")."\n";
	}
    if(!preg_match("/^[0-9\(\)\+-]+\d$/",$phone)){
        $str_error .= _tr("* Phone: text or number ")."\n";
	}
	if($company == ''){
        $str_error .= _tr("* Company: text ")."\n";
	}
    if($city == ''){
        $str_error .= _tr("* City: text ")."\n";
	}
    if(!preg_match("/^.+$/",$country)){
        $str_error .= _tr("* Country: Selected a country ")."\n";
	}
	if($str_error !== ""){
		$errMsg = _tr("Please fill the correct values in fields: ")."\n".$str_error;
		$jsonObject->set_status("FALSE");
		$jsonObject->set_message($errMsg);
		return $jsonObject->createJSON();
	}

    /*if($idPartner == "")
        return "fieldsNoComplete";*/
    
    // Verifico si la tabla register.db existe
    $table_created = true;
    if(!$pRegister->tableRegisterExists()){
	if(!$pRegister->createTableRegister())
	    $table_created = false;
    }
    if($table_created){
	// primero se debe verificar si ya existe algo enla base, si existe entonces es una actualizacion si no es una insercion
	$DATA = $pRegister->getDataRegister();
	$pDB->beginTransaction();
	$address = (isset($address) & $address !="")?$address:"";
	$idPartner = (isset($idPartner) & $idPartner !="")?$idPartner:"";
	if(isset($DATA) & $DATA != ""){ // actualizacion
	    $data = array($contact_name, $email, $phone, $company, $address, $city, $country, $idPartner, "1");
	    $status = $pRegister->updateDataRegister($data);
	}else{ // insercion
	    $data = array($contact_name, $email, $phone, $company, $address, $city, $country, $idPartner);
	    $status = $pRegister->insertDataRegister($data);
	}

	if($status){
		    $rsa_key = "";
		    if(!is_file("/etc/elastix.key")){
			    // saving to web service
			    $rsa_key = file_get_contents('/etc/ssh/ssh_host_rsa_key.pub');
		    }else{
			    $rsa_key = file_get_contents("/etc/elastix.key");
		    }
		    $rsa_key = trim($rsa_key);
		    $datas = array($contact_name, $email, $phone, $company, $address, $city, $country, $idPartner, $rsa_key);
		    $band = $pRegister->sendDataWebService($datas);
		    if($band==null){
			    $pDB->rollBack();
			    $msgResponse['status']  = "FALSE";
			    $msgResponse['response'] = _tr("Impossible connect to Elastix Web services. Please check your internet connection.");
		    }elseif($band==="FALSE"){
			    $pDB->rollBack();
			    $msgResponse['status']  = "FALSE";
			    $msgResponse['response'] = _tr("Your information cannot be saved. Please try again.");
		    }else{
                $h = popen('/usr/bin/elastix-helper elastixkey', 'w');
                fwrite($h, $band);
                pclose($h);
			    $pDB->commit();
			    $msgResponse['status']  = "TRUE";
			    $msgResponse['response'] = _tr("Your information has been saved.");
		    }
	}else{
		    $msgResponse['status']  = "FALSE";
		$msgResponse['response'] = _tr("There are some problem with the local database. Information cannot be saved in database.");
	}
    }else{
	$msgResponse['status']  = "FALSE";
	$msgResponse['response'] = _tr("The table register does not exist and could not be created");
    }
    $jsonObject->set_status($msgResponse['status']);
    $jsonObject->set_message($msgResponse['response']);
    return $jsonObject->createJSON();
}

function getDataRegistration($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf,&$pDBACL)
{
    $pRegister   = new paloSantoRegistration($pDB);
    $jsonObject   = new PaloSantoJSON();
    if(is_file("/etc/elastix.key")){
	$_DATA = $pRegister->getDataServerRegistration();
	if($_DATA === null){ // no se puede conectar al web service o existe un problema de red
	    $jsonObject->set_error(_tr("Impossible connect to Elastix Web services. Please check your internet connection."));
	    $jsonObject->set_status("error");
	    $jsonObject->set_message($_DATA);
	}elseif($_DATA === "FALSE"){ // su elastix no esta registrado, el idServer enviado no existe en la base de datos
	    $_DATA = $pRegister->getDataRegister();
	    $jsonObject->set_error(_tr("Your Server ID is not valid. Please update your information to generate a new Server ID."));
	    $jsonObject->set_status("error-update");
	    $jsonObject->set_message($_DATA);
	}else{
	    $jsonObject->set_message($_DATA);
	}
    }else{// elastix no registrado
	$jsonObject->set_error("no registrado");
	$jsonObject->set_status("error");
	$jsonObject->set_message("empty");
    }
    return $jsonObject->createJSON();

}

function createFieldForm()
{
    $arrCountry = array();

    $arrCountry["Afghanistan"] = "Afghanistan";
    $arrCountry["Akrotiri"] = "Akrotiri";
    $arrCountry["Albania"] = "Albania";
    $arrCountry["Algeria"] = "Algeria";
    $arrCountry["American Samoa"] = "American Samoa";
    $arrCountry["Andorra"] = "Andorra";
    $arrCountry["Angola"] = "Angola";
    $arrCountry["Anguilla"] = "Anguilla";
    $arrCountry["Antarctica"] = "Antarctica";
    $arrCountry["Antigua and Barbuda"] = "Antigua and Barbuda";
    $arrCountry["Arctic Ocean"] = "Arctic Ocean";
    $arrCountry["Argentina"] = "Argentina";
    $arrCountry["Armenia"] = "Armenia";
    $arrCountry["Aruba"] = "Aruba";
    $arrCountry["Ashmore and Cartier Islands"] = "Ashmore and Cartier Islands";
    $arrCountry["Atlantic Ocean"] = "Atlantic Ocean";
    $arrCountry["Australia"] = "Australia";
    $arrCountry["Austria"] = "Austria";
    $arrCountry["Azerbaijan"] = "Azerbaijan";
    $arrCountry["Bahamas, The"] = "Bahamas, The";
    $arrCountry["Bahrain"] = "Bahrain";
    $arrCountry["Baker Island"] = "Baker Island";
    $arrCountry["Bangladesh"] = "Bangladesh";
    $arrCountry["Barbados"] = "Barbados";
    $arrCountry["Bassas da India"] = "Bassas da India";
    $arrCountry["Belarus"] = "Belarus";
    $arrCountry["Belgium"] = "Belgium";
    $arrCountry["Belize"] = "Belize";
    $arrCountry["Benin"] = "Benin";
    $arrCountry["Bermuda"] = "Bermuda";
    $arrCountry["Bhutan"] = "Bhutan";
    $arrCountry["Bolivia"] = "Bolivia";
    $arrCountry["Bosnia and Herzegovina"] = "Bosnia and Herzegovina";
    $arrCountry["Botswana"] = "Botswana";
    $arrCountry["Bouvet Island"] = "Bouvet Island";
    $arrCountry["Brazil"] = "Brazil";
    $arrCountry["British Indian Ocean Territory"] = "British Indian Ocean Territory";
    $arrCountry["British Virgin Islands"] = "British Virgin Islands";
    $arrCountry["Brunei"] = "Brunei";
    $arrCountry["Bulgaria"] = "Bulgaria";
    $arrCountry["Burkina Faso"] = "Burkina Faso";
    $arrCountry["Burma"] = "Burma";
    $arrCountry["Burundi"] = "Burundi";
    $arrCountry["Cambodia"] = "Cambodia";
    $arrCountry["Cameroon"] = "Cameroon";
    $arrCountry["Canada"] = "Canada";
    $arrCountry["Cape Verde"] = "Cape Verde";
    $arrCountry["Cayman Islands"] = "Cayman Islands";
    $arrCountry["Central African Republic"] = "Central African Republic";
    $arrCountry["Chad"] = "Chad";
    $arrCountry["Chile"] = "Chile";
    $arrCountry["China"] = "China";
    $arrCountry["Christmas Island"] = "Christmas Island";
    $arrCountry["Clipperton Island"] = "Clipperton Island";
    $arrCountry["Cocos (Keeling) Islands"] = "Cocos (Keeling) Islands";
    $arrCountry["Colombia"] = "Colombia";
    $arrCountry["Comoros"] = "Comoros";
    $arrCountry["Democratic Republic of the Congo"] = "Democratic Republic of the Congo";
    $arrCountry["Cook Islands"] = "Cook Islands";
    $arrCountry["Coral Sea Islands"] = "Coral Sea Islands";
    $arrCountry["Costa Rica"] = "Costa Rica";
    $arrCountry["Cote d'Ivoire"] = "Cote d'Ivoire";
    $arrCountry["Croatia"] = "Croatia";
    $arrCountry["Cuba"] = "Cuba";
    $arrCountry["Cyprus"] = "Cyprus";
    $arrCountry["Czech Republic"] = "Czech Republic";
    $arrCountry["Denmark"] = "Denmark";
    $arrCountry["Dhekelia"] = "Dhekelia";
    $arrCountry["Djibouti"] = "Djibouti";
    $arrCountry["Dominica"] = "Dominica";
    $arrCountry["Dominican Republic"] = "Dominican Republic";
    $arrCountry["East Timor"] = "East Timor";
    $arrCountry["Ecuador"] = "Ecuador";
    $arrCountry["Egypt"] = "Egypt";
    $arrCountry["El Salvador"] = "El Salvador";
    $arrCountry["Equatorial Guinea"] = "Equatorial Guinea";
    $arrCountry["Eritrea"] = "Eritrea";
    $arrCountry["Estonia"] = "Estonia";
    $arrCountry["Ethiopia"] = "Ethiopia";
    $arrCountry["Europa Island"] = "Europa Island";
    $arrCountry["Falkland Islands (Islas Malvinas)"] = "Falkland Islands (Islas Malvinas)";
    $arrCountry["Faroe Islands"] = "Faroe Islands";
    $arrCountry["Fiji"] = "Fiji";
    $arrCountry["Finland"] = "Finland";
    $arrCountry["France"] = "France";
    $arrCountry["French Guiana"] = "French Guiana";
    $arrCountry["French Polynesia"] = "French Polynesia";
    $arrCountry["French Southern and Antarctic Lands"] = "French Southern and Antarctic Lands";
    $arrCountry["Gabon"] = "Gabon";
    $arrCountry["Gambia, The"] = "Gambia, The";
    $arrCountry["Gaza Strip"] = "Gaza Strip";
    $arrCountry["Georgia"] = "Georgia";
    $arrCountry["Germany"] = "Germany";
    $arrCountry["Ghana"] = "Ghana";
    $arrCountry["Gibraltar"] = "Gibraltar";
    $arrCountry["Glorioso Islands"] = "Glorioso Islands";
    $arrCountry["Greece"] = "Greece";
    $arrCountry["Greenland"] = "Greenland";
    $arrCountry["Grenada"] = "Grenada";
    $arrCountry["Guadeloupe"] = "Guadeloupe";
    $arrCountry["Guam"] = "Guam";
    $arrCountry["Guatemala"] = "Guatemala";
    $arrCountry["Guernsey"] = "Guernsey";
    $arrCountry["Guinea"] = "Guinea";
    $arrCountry["Guinea-Bissau"] = "Guinea-Bissau";
    $arrCountry["Guyana"] = "Guyana";
    $arrCountry["Haiti"] = "Haiti";
    $arrCountry["Heard Island and McDonald Islands"] = "Heard Island and McDonald Islands";
    $arrCountry["Holy See (Vatican City)"] = "Holy See (Vatican City)";
    $arrCountry["Honduras"] = "Honduras";
    $arrCountry["Hong Kong"] = "Hong Kong";
    $arrCountry["Howland Island"] = "Howland Island";
    $arrCountry["Hungary"] = "Hungary";
    $arrCountry["Iceland"] = "Iceland";
    $arrCountry["India"] = "India";
    $arrCountry["Indian Ocean"] = "Indian Ocean";
    $arrCountry["Indonesia"] = "Indonesia";
    $arrCountry["Iran"] = "Iran";
    $arrCountry["Iraq"] = "Iraq";
    $arrCountry["Ireland"] = "Ireland";
    $arrCountry["Isle of Man"] = "Isle of Man";
    $arrCountry["Israel"] = "Israel";
    $arrCountry["Italy"] = "Italy";
    $arrCountry["Jamaica"] = "Jamaica";
    $arrCountry["Jan Mayen"] = "Jan Mayen";
    $arrCountry["Japan"] = "Japan";
    $arrCountry["Jarvis Island"] = "Jarvis Island";
    $arrCountry["Jersey"] = "Jersey";
    $arrCountry["Johnston Atoll"] = "Johnston Atoll";
    $arrCountry["Jordan"] = "Jordan";
    $arrCountry["Juan de Nova Island"] = "Juan de Nova Island";
    $arrCountry["Kazakhstan"] = "Kazakhstan";
    $arrCountry["Kenya"] = "Kenya";
    $arrCountry["Kingman Reef"] = "Kingman Reef";
    $arrCountry["Kiribati"] = "Kiribati";
    $arrCountry["Korea, North"] = "Korea, North";
    $arrCountry["Korea, South"] = "Korea, South";
    $arrCountry["Kuwait"] = "Kuwait";
    $arrCountry["Kyrgyzstan"] = "Kyrgyzstan";
    $arrCountry["Laos"] = "Laos";
    $arrCountry["Latvia"] = "Latvia";
    $arrCountry["Lebanon"] = "Lebanon";
    $arrCountry["Lesotho"] = "Lesotho";
    $arrCountry["Liberia"] = "Liberia";
    $arrCountry["Libya"] = "Libya";
    $arrCountry["Liechtenstein"] = "Liechtenstein";
    $arrCountry["Lithuania"] = "Lithuania";
    $arrCountry["Luxembourg"] = "Luxembourg";
    $arrCountry["Macau"] = "Macau";
    $arrCountry["Macedonia"] = "Macedonia";
    $arrCountry["Madagascar"] = "Madagascar";
    $arrCountry["Malawi"] = "Malawi";
    $arrCountry["Malaysia"] = "Malaysia";
    $arrCountry["Maldives"] = "Maldives";
    $arrCountry["Mali"] = "Mali";
    $arrCountry["Malta"] = "Malta";
    $arrCountry["Marshall Islands"] = "Marshall Islands";
    $arrCountry["Martinique"] = "Martinique";
    $arrCountry["Mauritania"] = "Mauritania";
    $arrCountry["Mauritius"] = "Mauritius";
    $arrCountry["Mayotte"] = "Mayotte";
    $arrCountry["Mexico"] = "Mexico";
    $arrCountry["Micronesia, Federated States of"] = "Micronesia, Federated States of";
    $arrCountry["Midway Islands"] = "Midway Islands";
    $arrCountry["Moldova"] = "Moldova";
    $arrCountry["Monaco"] = "Monaco";
    $arrCountry["Mongolia"] = "Mongolia";
    $arrCountry["Montserrat"] = "Montserrat";
    $arrCountry["Morocco"] = "Morocco";
    $arrCountry["Mozambique"] = "Mozambique";
    $arrCountry["Namibia"] = "Namibia";
    $arrCountry["Nauru"] = "Nauru";
    $arrCountry["Navassa Island"] = "Navassa Island";
    $arrCountry["Nepal"] = "Nepal";
    $arrCountry["Netherlands"] = "Netherlands";
    $arrCountry["Netherlands Antilles"] = "Netherlands Antilles";
    $arrCountry["New Caledonia"] = "New Caledonia";
    $arrCountry["New Zealand"] = "New Zealand";
    $arrCountry["Nicaragua"] = "Nicaragua";
    $arrCountry["Niger"] = "Niger";
    $arrCountry["Nigeria"] = "Nigeria";
    $arrCountry["Niue"] = "Niue";
    $arrCountry["Norfolk Island"] = "Norfolk Island";
    $arrCountry["Northern Mariana Islands"] = "Northern Mariana Islands";
    $arrCountry["Norway"] = "Norway";
    $arrCountry["Oman"] = "Oman";
    $arrCountry["Pacific Ocean"] = "Pacific Ocean";
    $arrCountry["Pakistan"] = "Pakistan";
    $arrCountry["Palau"] = "Palau";
    $arrCountry["Palmyra Atoll"] = "Palmyra Atoll";
    $arrCountry["Panama"] = "Panama";
    $arrCountry["Papua New Guinea"] = "Papua New Guinea";
    $arrCountry["Paracel Islands"] = "Paracel Islands";
    $arrCountry["Paraguay"] = "Paraguay";
    $arrCountry["Peru"] = "Peru";
    $arrCountry["Philippines"] = "Philippines";
    $arrCountry["Pitcairn Islands"] = "Pitcairn Islands";
    $arrCountry["Poland"] = "Poland";
    $arrCountry["Portugal"] = "Portugal";
    $arrCountry["Puerto Rico"] = "Puerto Rico";
    $arrCountry["Qatar"] = "Qatar";
    $arrCountry["Reunion"] = "Reunion";
    $arrCountry["Romania"] = "Romania";
    $arrCountry["Russia"] = "Russia";
    $arrCountry["Rwanda"] = "Rwanda";
    $arrCountry["Saint Helena"] = "Saint Helena";
    $arrCountry["Saint Kitts and Nevis"] = "Saint Kitts and Nevis";
    $arrCountry["Saint Lucia"] = "Saint Lucia";
    $arrCountry["Saint Pierre and Miquelon"] = "Saint Pierre and Miquelon";
    $arrCountry["Saint Vincent and the Grenadines"] = "Saint Vincent and the Grenadines";
    $arrCountry["Samoa"] = "Samoa";
    $arrCountry["San Marino"] = "San Marino";
    $arrCountry["Sao Tome and Principe"] = "Sao Tome and Principe";
    $arrCountry["Saudi Arabia"] = "Saudi Arabia";
    $arrCountry["Senegal"] = "Senegal";
    $arrCountry["Serbia and Montenegro"] = "Serbia and Montenegro";
    $arrCountry["Seychelles"] = "Seychelles";
    $arrCountry["Sierra Leone"] = "Sierra Leone";
    $arrCountry["Singapore"] = "Singapore";
    $arrCountry["Slovakia"] = "Slovakia";
    $arrCountry["Slovenia"] = "Slovenia";
    $arrCountry["Solomon Islands"] = "Solomon Islands";
    $arrCountry["Somalia"] = "Somalia";
    $arrCountry["South Africa"] = "South Africa";
    $arrCountry["South Georgia and the South Sandwich Islands"] = "South Georgia and the South Sandwich Islands";
    $arrCountry["Southern Ocean"] = "Southern Ocean";
    $arrCountry["Spain"] = "Spain";
    $arrCountry["Spratly Islands"] = "Spratly Islands";
    $arrCountry["Sri Lanka"] = "Sri Lanka";
    $arrCountry["Sudan"] = "Sudan";
    $arrCountry["Suriname"] = "Suriname";
    $arrCountry["Svalbard"] = "Svalbard";
    $arrCountry["Swaziland"] = "Swaziland";
    $arrCountry["Sweden"] = "Sweden";
    $arrCountry["Switzerland"] = "Switzerland";
    $arrCountry["Syria"] = "Syria";
    $arrCountry["Taiwan"] = "Taiwan";
    $arrCountry["Tajikistan"] = "Tajikistan";
    $arrCountry["Tanzania"] = "Tanzania";
    $arrCountry["Thailand"] = "Thailand";
    $arrCountry["Togo"] = "Togo";
    $arrCountry["Tokelau"] = "Tokelau";
    $arrCountry["Tonga"] = "Tonga";
    $arrCountry["Trinidad and Tobago"] = "Trinidad and Tobago";
    $arrCountry["Tromelin Island"] = "Tromelin Island";
    $arrCountry["Tunisia"] = "Tunisia";
    $arrCountry["Turkey"] = "Turkey";
    $arrCountry["Turkmenistan"] = "Turkmenistan";
    $arrCountry["Turks and Caicos Islands"] = "Turks and Caicos Islands";
    $arrCountry["Tuvalu"] = "Tuvalu";
    $arrCountry["Uganda"] = "Uganda";
    $arrCountry["Ukraine"] = "Ukraine";
    $arrCountry["United Arab Emirates"] = "United Arab Emirates";
    $arrCountry["United Kingdom"] = "United Kingdom";
    $arrCountry["United States"] = "United States";
    $arrCountry["United States Pacific Island Wildlife Refuges"] = "United States Pacific Island Wildlife Refuges";
    $arrCountry["Uruguay"] = "Uruguay";
    $arrCountry["Uzbekistan"] = "Uzbekistan";
    $arrCountry["Vanuatu"] = "Vanuatu";
    $arrCountry["Venezuela"] = "Venezuela";
    $arrCountry["Vietnam"] = "Vietnam";
    $arrCountry["Virgin Islands"] = "Virgin Islands";
    $arrCountry["Wake Island"] = "Wake Island";
    $arrCountry["Wallis and Futuna"] = "Wallis and Futuna";
    $arrCountry["West Bank"] = "West Bank";
    $arrCountry["Western Sahara"] = "Western Sahara";
    $arrCountry["Yemen"] = "Yemen";
    $arrCountry["Zambia"] = "Zambia";
    $arrCountry["Zimbabwe"] = "Zimbabwe";

    $arrFields = array(
            "contactNameReg"   => array(      "LABEL"            => _tr("Contact Name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "contactNameReg","style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
            "emailReg"   => array(      "LABEL"                        => _tr("Email"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "emailReg", "style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
            "phoneReg"   => array(      "LABEL"                  => _tr("Phone"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "phoneReg","style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "countryReg"   => array(      "LABEL"                  => _tr("Country"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrCountry,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "companyReg"   => array(      "LABEL"                        => _tr("Company"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "companyReg","style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "",
                                            ),
            "addressReg"   => array(      "LABEL"                  => _tr("Address"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "COLS"                   => "",
                                            "ROWS"                   => "1",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "addressReg","style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            ),
            "cityReg"   => array(      "LABEL"                  => _tr("City"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "cityReg", "style" => "width: 230px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "idPartnerReg"   => array(      "LABEL"                  => _tr("idPartner"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("id" => "idPartnerReg", "style" => "width: 230px; margin: 2px 0px; margin: 2px 0px;"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            );
    return $arrFields;

}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("action")=="saveregister")
        return "save";
    else if(getParameter("action")=="getDataRegisterServer")
        return "getDataRegisterServer";
    else if(getParameter("action")=="showAboutAs")
        return "showAboutAs";
    else if(getParameter("action")=="showRPMS_Version")
        return "showRPMS_Version";
    else
        return "report"; //cancel
}
?>
