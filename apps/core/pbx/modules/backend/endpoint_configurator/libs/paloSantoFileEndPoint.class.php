<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                 |
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
  $Id: paloSantoFileEndPoint.class.php,v 1.1 2008/01/22 15:05:57 asantos@palosanto.com Alberto Santos Exp $ */

if (file_exists("/var/lib/asterisk/agi-bin/phpagi-asmanager.php")) {
require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
}
include_once("paloSantoEndPoint.class.php");
class PaloSantoFileEndPoint
{
    var $directory;
    var $errMsg;
    var $ipAdressServer;
    var $PathDPMA;

    function PaloSantoFileEndPoint($dir,$endpoint_mask=NULL){
        $this->directory = $dir;
        $this->PathDPMA = "/etc/asterisk/res_digium_phone.conf";
    if(is_null($endpoint_mask))
        $this->ipAdressServer = $_SERVER['SERVER_ADDR'];
    else{
        $pNetwork = new paloNetwork();
        $pInterfaces = $pNetwork->obtener_interfases_red();
        $endpoint_mask = explode("/",$endpoint_mask);
        $endpoint_network = $pNetwork->getNetAdress($endpoint_mask[0],$endpoint_mask[1]);
        foreach($pInterfaces as $interface){
	$mask = $pNetwork->maskToDecimalFormat($interface["Mask"]);
        $network = $pNetwork->getNetAdress($interface["Inet Addr"],$mask);
        if($network == $endpoint_network){
            $this->ipAdressServer = $interface["Inet Addr"];
            break;
        }
        }
        if(!isset($this->ipAdressServer))
            $this->ipAdressServer = $_SERVER['SERVER_ADDR'];
    }
    }

    function AsteriskManagerAPI($action, $parameters, $return_data=false) 
    {
        $astman_host = "127.0.0.1";
        $astman_user = 'admin';
        $astman_pwrd = obtenerClaveAMIAdmin();

        $astman = new AGI_AsteriskManager();

        if (!$astman->connect("$astman_host", "$astman_user" , "$astman_pwrd")) {
            $this->errMsg = _tr("Error when connecting to Asterisk Manager");
        } else{
            $salida = $astman->send_request($action, $parameters);
            $astman->disconnect();
            if (strtoupper($salida["Response"]) != "ERROR") {
                if($return_data) return $salida;
                else return explode("\n", $salida["Response"]);
            }else return false;
        }
        return false;
    }

    /*
        La funcion createFiles nos permite crear los archivos de configuracion de un EndPoint
        Para ello recibimos un arreglo con los datos necesarios para crear estos archivos,
        Entre los datos tenemos el nombre del vendor, nombre de archivo, mac address.
     */
    function createFiles($ArrayData)
    {
        include_once "vendors/{$ArrayData['vendor']}.cfg.php";
    $return = false;
	switch($ArrayData['vendor']){
            case 'Polycom':
                //Header Polycom
                $contentHeader = HeaderFilePolycom($ArrayData['data']['filename']);

                if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentHeader)){
                    //Archivo Principal
                    $contentFilePolycom = PrincipalFilePolycom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters']);

                    if($this->createFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg", $contentFilePolycom))
                        $return = true;
                    else $return = false;
                }else $return = false;

                break;

            case 'Linksys':
                $contentFileLinksys = PrincipalFileLinksys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
                if($this->createFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg", $contentFileLinksys)){
                    if(conexionHTTP($ArrayData['data']['ip_endpoint'], $this->ipAdressServer, $ArrayData['data']['filename']))
                        $return = true;
                    else $return = false;
                }
                else $return = false;

                break;

            case 'Aastra':
                $contentFileAastra = PrincipalFileAastra($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'], $ArrayData['data']['arrParameters'], $this->ipAdressServer);
                if($this->createFileConf($this->directory, strtoupper($ArrayData['data']['filename']).".cfg", $contentFileAastra) )
                    $return = true;
                else $return = false;

                break;

            case 'Cisco':
                 $contentFileCisco = PrincipalFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer, $this->find_version() );
                if($this->createFileConf($this->directory, strtoupper("SIP".$ArrayData['data']['filename']).".cnf", $contentFileCisco))
                    $return = true;
                else $return = false;

                break;

            case 'Atcom':
                if($ArrayData['data']['model'] == "AT320"){
                    if($ArrayData['data']['tech'] == "iax2")
                       $contentFileAtcom = PrincipalFileAtcom320IAX($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename']);
                    else
                       $contentFileAtcom = PrincipalFileAtcom320SIP($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename']);
                    $result = $this->telnet($ArrayData['data']['ip_endpoint'], "", "12345678", $contentFileAtcom);
                    if($result) $return = true;
                    else $return = false;
                }
                else if($ArrayData['data']['model'] == "AT530" || $ArrayData['data']['model'] == "AT620" || $ArrayData['data']['model'] == "AT610" || $ArrayData['data']['model'] == "AT640"){
                    $currentVersion = getVersionConfigFileATCOM($ArrayData['data']['ip_endpoint'],"admin","admin");
                    $tmpVersion['versionCfg'] = $currentVersion;
                    $newVersion = $this->updateArrParameters("Atcom", $ArrayData['data']['model'], $tmpVersion);
                    $ArrayData['data']['arrParameters']['versionCfg'] = $newVersion['versionCfg'];
                    $version = $ArrayData['data']['arrParameters']['versionCfg'];

                    if($ArrayData['data']['tech'] == "iax2")
                        $contentFileAtcom = PrincipalFileAtcom530IAX($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'], $version);
                    else
                        $contentFileAtcom = PrincipalFileAtcom530SIP($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'], $version);

                    if($this->createFileConf($this->directory,"atc".$ArrayData['data']['filename'].".cfg", $contentFileAtcom)) {
                        $arrComandos = arrAtcom530($this->ipAdressServer, $ArrayData['data']['filename']);
                        $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $arrComandos);
                        if($result) $return = true;
                        else $return = false;
                    }
                    else $return = false;
                }

                break;
	    
	    case 'Fanvil':
                if($ArrayData['data']['model'] == "C62"||$ArrayData['data']['model'] == "C60"||$ArrayData['data']['model'] == "C58/C58P"||$ArrayData['data']['model'] == "C56/C56P"){
	          if($ArrayData['data']['model'] == "C56/C56P")
		     $currentVersion = getVersionConfigFileFANVILC56($ArrayData['data']['ip_endpoint'],"admin","admin"); 
		  else
		     $currentVersion = getVersionConfigFileFANVIL($ArrayData['data']['ip_endpoint'],"admin","admin");  
		   
		    $tmpVersion['versionCfg'] = $currentVersion;
                    $newVersion = $this->updateArrParameters("Fanvil", $ArrayData['data']['model'], $tmpVersion);
                    $ArrayData['data']['arrParameters']['versionCfg'] = $newVersion['versionCfg'];
                    $version = $ArrayData['data']['arrParameters']['versionCfg'];
		   
                    if($ArrayData['data']['tech'] == "iax2")
                       $contentFileFanvil = PrincipalFileFanvilC62IAX($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'],$version);
                    else
                       $contentFileFanvil = PrincipalFileFanvilC62SIP($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'],$version);
                    
		    $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $contentFileFanvil);
                   
		    if($result) $return = true;
                    else $return = false;
                }
		if($this->createFileConf($this->directory,$ArrayData['data']['filename'].".cfg", $contentFileFanvil)) {
                        $arrComandos = arrFanvil($this->ipAdressServer, $ArrayData['data']['filename']);
                        $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $arrComandos);
                        if($result) $return = true;
                        else $return = false;
                }else $return = false;
               

                break;
	     case 'Voptech':
                if($ArrayData['data']['model'] == "VI2007"||$ArrayData['data']['model'] == "VI2008"||$ArrayData['data']['model'] == "VI2006"){
		            $currentVersion = getVersionConfigFileVOPTECH($ArrayData['data']['ip_endpoint'],"admin","admin");  
		            $tmpVersion['versionCfg'] = $currentVersion;
                    $newVersion = $this->updateArrParameters("Voptech", $ArrayData['data']['model'], $tmpVersion);
                    $ArrayData['data']['arrParameters']['versionCfg'] = $newVersion['versionCfg'];
                    $version = $ArrayData['data']['arrParameters']['versionCfg'];
                    $contentFileVoptech = PrincipalFileVoptech($ArrayData['data']['model'],$ArrayData['data']['tech'],$ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'],$version);

		            $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $contentFileVoptech);
                   
		            if($result) $return = true;
                    else $return = false;
                }

		        if($this->createFileConf($this->directory,$ArrayData['data']['filename'].".cfg", $contentFileVoptech)) {
                    $arrComandos = arrVoptech($this->ipAdressServer, $ArrayData['data']['filename']);
                    $result = $this->telnet($ArrayData['data']['ip_endpoint'], "admin", "admin", $arrComandos);
                    if($result) $return = true;
                    else $return = false;
                }else $return = false;

                break;
	     case 'Escene':
                if($ArrayData['data']['model'] == "ES620"){
	          $contentFileEscene = PrincipalFileEscene620($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
		   if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".xml", $contentFileEscene)){
		       $arrComandos = arrEscene($this->ipAdressServer, $ArrayData['data']['filename']);
	               if ($result = $this->telnet($ArrayData['data']['ip_endpoint'], "root", "root", $arrComandos,2)){
			    $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
			    $result      = $this->AsteriskManagerAPI('Command',$parameters);
			    if($result) $return = true;
			    else $return = false;
			} else $return = false;
		   }
		   else 
		      $return = false;
		}
	        break;

	     case 'Damall':
                if($ArrayData['data']['model'] == "D-3310"){
		   if ($contentFileDamall = PrincipalFileDamallD3310($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['filename'],$ArrayData['data']['ip_endpoint']))
		   {
		       if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileDamall)){
			      if(set_update_conf($ArrayData['data']['ip_endpoint'],"admin","admin",$this->ipAdressServer,$ArrayData['data']['filename']))
				 {
				    $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
				    $result      = $this->AsteriskManagerAPI('Command',$parameters);
				    if($result) $return = true;
				    else $return = false;
				 }
			       else
				    $return = false;  
  			}
			else 
			    $return = false;
		    }else $return = false;
		}
                break;
	    
	    case 'Elastix':
		if($ArrayData['data']['model'] == "LXP200"){
		  $contentFileElastix = PrincipalFileElastixLXP200($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['model']);
		  $sConfigBin = elastix_encode_config($ArrayData['data']['filename'], $contentFileElastix);
		  if($this->createFileConf($this->directory, "cfg{$ArrayData['data']['filename']}", $sConfigBin))
		  {
		     $result = $this->configElastixPhone($ArrayData['data']['ip_endpoint'],"admin",2);
          	     if($result) $return = true;
                     else $return = false;
		  }else $return = false;
		}
		break;

	     case 'Atlinks':
               if($ArrayData['data']['model'] == "ALCATEL Temporis IP800"){
                  $contentFileAlacatel =PrincipalFileAlcatelTemporisIP800($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
          	  if(set_provision_server($ArrayData['data']['ip_endpoint'],"admin","admin",$this->ipAdressServer,$ArrayData['data']['filename']))
		  {
		      if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileAlacatel)){
			  $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
			  $result      = $this->AsteriskManagerAPI('Command',$parameters);
			  if($result===false)
			    $return = false;
			  else
			    $return = true;
		      }else $return = false;
		  }
		  else $return = false;
	       }
               break;
            
	    case 'Snom':
                if($ArrayData['data']['model'] == "821"){
		   $contentFileSnom = PrincipalFileSnom821($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
		   if(!set_provision_server($ArrayData['data']['ip_endpoint'],$this->ipAdressServer))
		      $return = false;
		   if($this->createFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm", $contentFileSnom))
                      $return = true;
                   else $return = false;
 
		}
		elseif ($ArrayData['data']['model'] == "m9"){
		   $contentFileSnom = PrincipalFileSnom_m9($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
		   if(!set_provision_server_m9($ArrayData['data']['ip_endpoint'],$this->ipAdressServer))
		      $return = false;
		    if($this->createFileConf($this->directory, "snom-".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".xml", $contentFileSnom))
		      $return = true;
		    else $return = false;
		}
         	else{
		   $contentFileSnom = PrincipalFileSnom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
		     if($this->createFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm", $contentFileSnom))
                        $return = true;
	             else $return = false;
	       }
               
               break;

            case 'Grandstream':
                $contentFileGrandstream = PrincipalFileGrandstream($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['model']);
                $sConfigBin = grandstream_codificar_config($ArrayData['data']['filename'], $contentFileGrandstream);
                $return = $this->createFileConf($this->directory, "cfg{$ArrayData['data']['filename']}", $sConfigBin);
                break;

            case 'Zultys':
                //Common file Zultys models ZIP 2x1 and ZIP 2x2
                $contentCommon = CommonFileZultys($ArrayData['data']['model'],$this->ipAdressServer);
                if($this->createFileConf($this->directory,"{$ArrayData['data']['model']}_common.cfg",$contentCommon)){
                    //Archivo Principal
                    $contentFileZultys = PrincipalFileZultys($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters']);
                    if($this->createFileConf("{$this->directory}/{$ArrayData['data']['model']}",strtoupper($ArrayData['data']['filename']).".cfg",$contentFileZultys))
                        $return = true;
                    else $return = false;
                }
                else $return = false;

                break;

            case 'AudioCodes':
                $contentAudioCodes = PrincipalFileAudioCodes($ArrayData['data']['id_device'],$ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer,$ArrayData['data']['model'],$ArrayData['data']['filename']);
                if($this->createFileConf($this->directory, $ArrayData['data']['model']."_".$ArrayData['data']['filename'].".cfg", $contentAudioCodes))
                    $return = true;
                else $return = false;
            break;

            case 'Digium':
                $timeZone= date_default_timezone_get();
                $MAC= "mac=".$ArrayData['data']['filename'];
                $contentDigium = PrincipalFileDigiumDPMA($ArrayData['data']['DisplayName'],$ArrayData['data']['id_device'],$ArrayData['data']['arrParameters'], $ArrayData['data']['filename'],$timeZone);
                if($this->createRegisterDPMA($contentDigium,$MAC)){
                    $return = true;
                }
                else $return = false;
                
            break;

            case 'Yealink':
               if($ArrayData['data']['model']== "VP530"||$ArrayData['data']['model']== "SIP-T38G"){
                     $contentFileYealink =PrincipalFileYealinkVP530($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
		     if(!setServerURL($ArrayData['data']['ip_endpoint'],"admin","admin",$this->ipAdressServer,$ArrayData['data']['model']))
		        $return = false;

	       }
	       if($ArrayData['data']['model'] == "SIP-T20/T20P" || $ArrayData['data']['model'] == "SIP-T22/T22P" || $ArrayData['data']['model'] == "SIP-T26/T26P" || $ArrayData['data']['model'] == "SIP-T28/T28P"){
                    $contentFileYealink =PrincipalFileYealink($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'],$this->ipAdressServer);
               }
               if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileYealink)){
                  $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
                  $result      = $this->AsteriskManagerAPI('Command',$parameters);
                  if($result===false)
                     $return = false;
                  else
                     $return = true;
                  }
               else $return = false;

                break;
            
        case 'Xorcom':
               if($ArrayData['data']['model'] == "XP0120P" || $ArrayData['data']['model'] == "XP0100P"){
                   $contentFileXorcom =PrincipalFileXorcom($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer);
                        if($this->createFileConf($this->directory, $ArrayData['data']['filename'].".cfg", $contentFileXorcom)){
                            $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
                            $result      = $this->AsteriskManagerAPI('Command',$parameters);
                            if(!$result)
                                $return = false;
                            $return = true;
                        }
                        $return = false;
                }
                break;
                

        case 'LG-ERICSSON':
                if($ArrayData['data']['model'] == "IP8802A"){
                    $contentFileLG_Ericsson = PrincipalFileLG_IP8802A($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$ArrayData['data']['arrParameters'], $this->ipAdressServer);
                    if($this->createFileConf($this->directory, $ArrayData['data']['filename'], $contentFileLG_Ericsson)){
                        $parameters  = array('Command'=>'sip notify reboot-yealink '.$ArrayData['data']['ip_endpoint']);
                        $result = $this->AsteriskManagerAPI('Command',$parameters);
                        if($result===false)
                            $return = false;
                        else $return = true;
                    }
                    else $return = false;
                }
                break;
        }
    if(isset($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']])){
        if(is_array($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']]) && count($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']]) > 0){
            foreach($_SESSION['endpoint_configurator']['extensions_registered'][$ArrayData['data']['ip_endpoint']] as $extension){
                $tmp = explode(":",$extension);
                $tech = strtolower($tmp[0]);
                $number = $tmp[1];
                $parameters  = array('Command'=>"$tech unregister $number");
                    $result  = $this->AsteriskManagerAPI('Command',$parameters);
                   // if($result===false)
                   //     $return = false;
                   // else $return = true;
            }
        }
    }
    return $return;
    }
      
    function getModelElastix($user,$password,$ip,$sw){
      if ($fsock = @fsockopen($ip, 23, $errno, $errstr, 10))
      {
	      $result = $this->read($fsock,$sw);
	      fclose ($fsock);
	      $result = preg_replace('([^A-Za-z0-9])','', $result);
	      if(preg_match("/^Elastix/",$result,$arrTokens)){
		 return true;
	      }else
		 return false;
	    
      }else{
	      $this->errMsg = _tr("Unable to telnet to ").$ip;
	      return false;
      }

    }


    //Funcion que valida si el telefono es Voptech teniendo en cuenta que inicialemnte se cree que es fanvil por la direccion MAC
    function isVendorVoptech($user,$password,$ip,$sw){  
      include_once "vendors/Voptech.cfg.php";  
      $nonce=getNonceVOPTECH($ip);
      usleep(500000);
      $fileP =getInitialPageVOPTECH($ip,$nonce,$user,$password);
      usleep(500000);
      $logou = logoutVOPTECH($ip,$nonce);
      if ($fileP!=null)
      {
	     if(!preg_match("/title.htm/", $fileP)){
	     	if(preg_match("/currentstat.htm/", $fileP)){
	     		return true;
	     	}
	     }    
      }else{
	      $this->errMsg = _tr("Unable to connect to ").$ip;
	      return false;
      } 
	  return false;
    }

    function buildPattonConfFile($arrData,$tone_set)
    {
    include_once "vendors/Patton.cfg.php";
    $config = getPattonConfiguration($arrData,$tone_set);
    if(!$this->createFileConf($this->directory,$arrData["mac"]."_Patton.cfg",$config))
        return false;
    $arrCommands = getPattonCommands($arrData,$this->ipAdressServer);
    $result = $this->checkTelnetCredentials($arrData["ip_address"],$arrData["telnet_username"],$arrData["telnet_password"],2);
    if($result === true){
        if(!$this->telnet($arrData["ip_address"],"","",$arrCommands,2)){
        $this->errMsg = _tr("Unable to telnet to ").$arrData["ip_address"];
        return false;
        }
        else
        return true;
    }
    else
        return $result;
    }
   
    function getSangomaModel($ip,$mac,$dsnAsterisk,$dsnSqlite,$sw)
    {
       $paloEndpoint = new PaloSantoEndpoint($dsnAsterisk, $dsnSqlite);
       $credential = $paloEndpoint->getPassword($mac);
       $user=$credential["user"];
       $password=$credential["password"];

        $result="";
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            $this->read($fsock,$sw);
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "SHOW VERSION\r");
            $this->read($fsock,$sw);
            fputs($fsock, "\r");
            $result = $this->read($fsock,$sw);
            fputs($fsock, "exit\r");
            $posi = strpos($result, "Hardware Platform : ");
            $posf = stripos($result, "Serial");
            $cad1 = explode("Hardware Platform : ", $result);
            $cad2 = explode("Serial",$cad1[1]); 
            //$cadena = substr($result, $posi+20,$posf);

            //$posfin = stripos($cadena, "Serial");	
            //$rcadena = substr($cadena, 0,$posfin);
            return $cad2[0];  
        }
        else{
            return $result="";
        }
    }

    function setSangomaProvisioningTftp($ip,$ip_provision,$mac,$dsnAsterisk,$dsnSqlite,$sw)
    {
       $paloEndpoint = new PaloSantoEndpoint($dsnAsterisk, $dsnSqlite);
       $credential = $paloEndpoint->getPassword($mac);
       $user=$credential["user"];
       $password=$credential["password"];

        $result="";
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            $this->read($fsock,$sw);
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "set .tftp.ip=$ip_provision\r");
            $this->read($fsock,$sw);
            fputs($fsock, "set .lan.file_transfer_method=TFTP");
            $this->read($fsock,$sw);
            fputs($fsock, "exit\r");
            return true;
        }
        else{
            return $false;
        }
    }


    function getSangomaPorts($ip,$mac,$dsnAsterisk,$dsnSqlite,$sw)
    {
       $paloEndpoint = new PaloSantoEndpoint($dsnAsterisk, $dsnSqlite);

        $credential = $paloEndpoint->getPassword($mac);
        $user=$credential["user"];
        $password=$credential["password"];

        $result="";
        $arrPorts= null;
        
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            $this->read($fsock,$sw);
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "SHOW PORTS\r");
            $result = $this->read($fsock,$sw);
            fputs($fsock, "exit\r");
            $posi = strpos($result, "POTS");
            $posf = stripos($result, "\n\n");
            $nfxs=0;
            $nfxo=0;
            $cadena = explode("POTS port", $result);
            $nports = count($cadena)-1;
            for($i=1;$i<count($cadena);$i++){   
                $fxo = stripos($cadena[$i], "FXO"); 
                if($fxo===false)
                   $nfxs++;
                else
                   $nfxo++;
            
            }
            $arrPorts['fxs']=$nfxs;
            $arrPorts['fxo']=$nfxo;		
            $arrPorts['ports']=$nports;
            $posfin = stripos($cadena, "Serial");
            $rcadena = substr($cadena, 0,$posfin);
            return $arrPorts;
        }
        else{
            return $arrPorts=null;
        }
    }

       
    function buildSangomaConfFile($arrData,$tone_set,$dsnAsterisk, $dsnSqlite)
    {
       include_once "vendors/Sangoma.cfg.php";
       $mac=$arrData["mac"];
       $this->setSangomaProvisioningTftp($arrData["ip_address"],$arrData["pbx_address"],$mac,$dsnAsterisk,$dsnSqlite,2);
       $config = getSangomaConfiguration($arrData,$tone_set);
       if(!$this->createFileConf($this->directory,"config.txt",$config))
           return false;
       $arrCommands = getSangomaCommands($arrData,$this->ipAdressServer);
       $result = $this->configSangomaTelnet($arrData["ip_address"],$arrData["telnet_username"],$arrData["telnet_password"],2);
       
        if(!$result === true){
                return false;
        }
        else
            return $result;
    }

    /*
    function changePasswordSangoma($ip,$user,$last_password,$new_password,$sw)
    {
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$last_password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$new_password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$new_password\r");
            $this->read($fsock,$sw);
            $result = $this->read($fsock,$sw);
            fputs($fsock, "exit\r");
            return true;
        }
        else{
            $this->errMsg = _tr("Unable to telnet to ").$ip;
            return false;
        }
    }
*/

    function configSangomaTelnet($ip,$user,$password,$sw)
    {
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            fputs($fsock, "$user\r");
            $this->read($fsock,$sw);
            fputs($fsock, "$password\r");
            $this->read($fsock,$sw);
            fputs($fsock, "get tftp:config.txt\r");
            $this->read($fsock,$sw);
            fputs($fsock, "apply\r");
            $this->read($fsock,$sw);
            fputs($fsock, "save\r");
            $this->read($fsock,$sw);
            fputs($fsock, "reboot system\r");
            $result = $this->read($fsock,$sw);
            if(preg_match("/Authentication failed/",$result)){
                $this->errMsg = _tr("The username or password are incorrect");
                return null;
            }
            else
                return true;
        }
        else{
            $this->errMsg = _tr("Unable to telnet to ").$ip;
            return false;
        }
    }
 

    function checkTelnetCredentials($ip,$user,$password,$sw)
    {
    if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
            fputs($fsock, "$user\r");
        $this->read($fsock,$sw);
        fputs($fsock, "$password\r");
        $result = $this->read($fsock,$sw);
        if(preg_match("/Authentication failed/",$result)){
        $this->errMsg = _tr("The username or password are incorrect");
        return null;
        }
        else
        return true;
    }
    else{
        $this->errMsg = _tr("Unable to telnet to ").$ip;
        return false;
    }
    }

    /*
        Esta funcion nos permite crear un archivo de configuracion
        Recibe el directorio, nombre de archivo, contenido del archivo.
     */
    function createFileConf($tftpBootPath, $nameFileConf, $contentConf)
    {
        global $arrLang;
        if(!is_dir($tftpBootPath)) mkdir($tftpBootPath,0755,true);

        if (file_exists("$tftpBootPath/$nameFileConf") && !is_writable("$tftpBootPath/$nameFileConf")) {
            unlink("$tftpBootPath/$nameFileConf");
        }
        $fd = fopen ("$tftpBootPath/$nameFileConf", "w");
        if ($fd){
            fputs($fd,$contentConf,strlen($contentConf)); // write config file
        fclose ($fd);
            return true;
        }
        $this->errMsg = $arrLang['Unable write the file'].": $nameFileConf";
        return false;
    }

    function createRegisterDPMA($contentConf, $MAC)
    {
        $líneas = file($this->PathDPMA);
        $flag=true;
        foreach ($líneas as $num_línea => $línea) {
            $tmp=trim($línea);
            if(preg_match("/$MAC/","$tmp")){
               $flag=false;
                if($this->registerPhoneDpma($contentConf)){
                    if($this->deletePhoneDpma($num_línea))
                        return true;
                    else return false;
                }
                else
                    return false;
            }
        }
        if($flag){
            if($this->registerPhoneDpma($contentConf)){ 
                return true;
            }else
                return false;
        }
    }

    function deletePhoneDpma($num_línea, $MAC=null)
    {   
        $líneas = file($this->PathDPMA);
        if($MAC!=null){
            $MAC= "mac=".$MAC;
            foreach ($líneas as $num_línea => $línea){ 
                $tmp=trim($línea);
                if(preg_match("/$MAC/","$tmp"))
                    break;
            }
        }
        
        $cont=count($líneas);
        for ($i = $num_línea; $i > 0; $i--) {
            if(preg_match("/^\[/",$líneas[$i]))
                break;
        } 
        for ($f = $num_línea; $f < $cont; $f++) {
            if(preg_match("/^\[/",$líneas[$f]))
                break;
        }
        for ($i ; $i < $f; $i++) {
            unset($líneas[$i]);
        }

        if(file_put_contents($this->PathDPMA,$líneas)){
            exec("/usr/sbin/asterisk -r -x 'module reload res_digium_phone.so'");
            return true;
        }else 
            return false;
    }
    
    function registerPhoneDpma($contentConf)
    {  
        $fd = fopen ($this->PathDPMA, "a+");
        if ($fd){
            fwrite($fd,$contentConf); // write config file
            fclose ($fd);
            exec("/usr/sbin/asterisk -r -x 'module reload res_digium_phone.so'");
        }    
        return true;
    }

    /*
        La funcion deleteFiles nos permite eliminar los archivos de configuracion de un
        EndPoint. Para ello recibimos un arreglo con los datos necesarios para eliminar
        estos archivos. Los datos recibidos son el nombre del vendor, nombre de archivo.
     */
    function deleteFiles($ArrayData)
    {
        switch($ArrayData['vendor']){
            case 'Polycom':
                if($this->deleteFileConf($this->directory, $ArrayData['data']['filename']."reg.cfg")){
                    return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                } else return false;
                break;

            case 'Linksys':
                return $this->deleteFileConf($this->directory, "spa".$ArrayData['data']['filename'].".cfg");
                break;

            case 'Aastra':
                return $this->deleteFileConf($this->directory, strtoupper($ArrayData['data']['filename']).".cfg");
                break;

            case 'Cisco':
                return $this->deleteFileConf($this->directory, strtoupper("SIP".$ArrayData['data']['filename']).".cnf");
                break;

            case 'Atcom':
                return $this->deleteFileConf($this->directory, "atc".$ArrayData['data']['filename'].".cfg");
                break;
	    
	    case 'Fanvil':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break;
	    
        case 'Voptech':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break;
	    
	    case 'Escene':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".xml");
                break;
	    
	    case 'Damall':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break;  
	  
	    case 'Elastix':
                return $this->deleteFileConf($this->directory, "cfg".$ArrayData['data']['filename']);
                break; 
	    
	    case 'Atlinks':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
                break; 
	    
            case 'Snom':
                return $this->deleteFileConf($this->directory, "snom".$ArrayData['data']['model']."-".strtoupper($ArrayData['data']['filename']).".htm");
                break;

            case 'Grandstream':
                if($this->deleteFileConf($this->directory, "cfg".$ArrayData['data']['filename'])){
                    return $this->deleteFileConf($this->directory, "gxp".$ArrayData['data']['filename']);
                }else return false;
                break;

            case 'Zultys':
                return $this->deleteFileConf("{$this->directory}/{$ArrayData['data']['model']}", strtoupper($ArrayData['data']['filename']).".cfg");
                break;

            case 'AudioCodes':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['model']."_".$ArrayData['data']['filename'].".cfg");
            break;
            
            case 'Digium':
                return $this->deletePhoneDpma(null, $ArrayData['data']['filename']);
            break;

            case 'Yealink':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
            break;

            case 'LG-ERICSSON':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename']);
            break;

	    case 'Xorcom':
                return $this->deleteFileConf($this->directory, $ArrayData['data']['filename'].".cfg");
            break;

        }
    }

    /*
        Esta funcion nos permite eliminar un archivo de configuracion
        Recibe el directorio, nombre de archivo.
     */
    function deleteFileConf($tftpBootPath, $nameFileConf)
    {
        global $arrLang;

        if (file_exists("$tftpBootPath/$nameFileConf")) {
            if(!unlink("$tftpBootPath/$nameFileConf")){
                $this->errMsg = $arrLang['Unable delete the file'].": $nameFileConf";
                return false;
            }
            return true;
        }
    }

    function createFilesGlobal($vendor)
    {
        include_once "vendors/{$vendor}.cfg.php";

        switch($vendor){
            case 'Polycom':
                //PASO 1: Creo los directorios Polycom.
                if(mkdirFilePolycom($this->directory)){
                    $contentFilePolycom = serverFilePolycom($this->ipAdressServer);

                    //PASO 2: Creo el archivo server.cfg
                    if($this->createFileConf($this->directory, "server.cfg", $contentFilePolycom)){
                        $contentFilePolycom = sipFilePolycom($this->ipAdressServer);

                        //PASO 3: Creo el archivo sip.cfg
                        return $this->createFileConf($this->directory, "sip.cfg", $contentFilePolycom);
                    } else return false;
                } else return false;

                break;

            case 'Linksys':
                //Creando archivos de ejemplo.
                $contentFileLinksys = templatesFileLinksys($this->ipAdressServer);
                $this->createFileConf($this->directory, "spaxxxxxxxxxxxx.template.cfg", $contentFileLinksys);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Aastra':
                //Creando archivos de ejemplo.
                $contentFileAatra = templatesFileAastra($this->ipAdressServer);
                $this->createFileConf($this->directory, "aastra.cfg", $contentFileAatra);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Cisco':
                //Creando archivos de ejemplo.
                $contentFileCisco = defaultFileCisco($ArrayData['data']['DisplayName'], $ArrayData['data']['id_device'], $ArrayData['data']['secret'],$this->ipAdressServer, $this->find_version());
                $this->createFileConf($this->directory, "SIPDefault.cnf", $contentFileCisco);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Atcom':
                //Creando archivos de ejemplo.
                $contentFileAtcom = templatesFileAtcom($this->ipAdressServer);
                $this->createFileConf($this->directory, "atcxxxxxxxxxxxx.template.cfg", $contentFileAtcom);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
	
	    case 'Fanvil':
                //Creando archivos de ejemplo.
                $contentFileFanvil = templatesFileFanvil($this->ipAdressServer);
                $this->createFileConf($this->directory, "fanvilxxxxxxxx.template.cfg", $contentFileFanvil);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

        case 'Voptech':
                //Creando archivos de ejemplo.
                $contentFileVoptech = templatesFileVoptech($this->ipAdressServer);
                $this->createFileConf($this->directory, "voptechxxxxxxxx.template.cfg", $contentFileVoptech);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

	    case 'Escene':
                //Creando archivos de ejemplo.
                $contentFileEscene = templatesFileEscene($this->ipAdressServer);
                $this->createFileConf($this->directory, "ES000000.xml", $contentFileEscene);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
	    
	    case 'Damall':
                //Creando archivos de ejemplo.
                $contentFileDamall = templatesFileDamall($this->ipAdressServer);
                $this->createFileConf($this->directory, "Damall00.cfg", $contentFileDamall);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
	    
	    case 'Elastix':
                //Creando archivos de ejemplo.
                $contentFileElastix = templatesFileElastix($this->ipAdressServer);
                $this->createFileConf($this->directory, "cfgElastix.template", $contentFileElastix);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
	    
	    case 'Atlinks':
                //Creando archivos de ejemplo.
                $contentFileYealink = templatesAlcatel($this->ipAdressServer);
                $this->createFileConf($this->directory, "alcatel.template.cfg", $contentFileYealink);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Snom':
                //Creando archivos de ejemplo.
                //SNOM reguires a separate file for each model. The file contents of each file
                //is the same.
                $contentFileSnom = generalSettingsFileSnom($this->ipAdressServer);
                $this->createFileConf($this->directory, "snom300.htm", $contentFileSnom);
                $this->createFileConf($this->directory, "snom320.htm", $contentFileSnom);
                $this->createFileConf($this->directory, "snom360.htm", $contentFileSnom);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Grandstream':
                //Creando archivos de ejemplo.
                $contentFileAatra = templatesFileGrandstream($this->ipAdressServer);
                $this->createFileConf($this->directory, "gxp_config_1.1.6.46.template", $contentFileAatra);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Zultys':
                //Creando archivos de ejemplo.
                $contentFileZultys = templatesFileZultys("ZIP2x1",$this->ipAdressServer);
                $this->createFileConf($this->directory, "ZIP2x1_common.template.cfg", $contentFileZultys);
                 $contentFileZultys = templatesFileZultys("ZIP2x2",$this->ipAdressServer);
                $this->createFileConf($this->directory, "ZIP2x2_common.template.cfg", $contentFileZultys);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;
            case 'AudioCodes':
                $contentAudioCodes = templatesFileAudioCodes($this->ipAdressServer);
                $this->createFileConf($this->directory, "AudioCodes.template", $contentAudioCodes);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'Yealink':
                //Creando archivos de ejemplo.
                $contentFileYealink = templatesFileYealink($this->ipAdressServer);
                $this->createFileConf($this->directory, "y000000000000.cfg", $contentFileYealink);
                return true; //no es tan importante la necesidad de estos archivos solo son de ejemplo.
                break;

            case 'LG-ERICSSON':
                $contentFileLG_Ericsson = templatesFileLG_Ericsson($this->ipAdressServer);
                $this->createFileConf($this->directory, "l000000000000", $contentFileLG_Ericsson);
                return true;
                break;

            case 'Xorcom':
                $contentFileXorcom = templatesFileXorcom($this->ipAdressServer);
                $this->createFileConf($this->directory, "y000000000010.cfg", $contentFileXorcom);
                $this->createFileConf($this->directory, "y000000000011.cfg", $contentFileXorcom);
                return true; 
                break;
                
        }
    }
    
    function configElastixPhone($ip,$password,$sw)
    {
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
	    $this->read($fsock,$sw);
	    fputs($fsock, "$password\r");
	    $this->read($fsock,$sw);
	    fputs($fsock, "upgrade\r");
	    $this->read($fsock,$sw);
	    fputs($fsock, "upgrade\r");
	    $this->read($fsock,$sw);
	    fputs($fsock, "y\r");
	    stream_set_blocking($fsock, TRUE);
	    while(true) {
		$char = fgetc($fsock);
		if(empty($char)) break;
	    }
	    fclose($fsock);
            return true;
        }
        else{
            $this->errMsg = _tr("Unable to telnet to ").$ip;
            return false;
        }
    }

   function telnet($ip, $user, $password, $arrComandos, $sw=1)
    {
        if ($fsock = fsockopen($ip, 23, $errno, $errstr, 10))
        {
	   if(is_array($arrComandos) && count($arrComandos)>0)
            {
                if($user!="" && $user!=null){
                    fputs($fsock, "$user\r");
                    $this->read($fsock,$sw);
                }
                if($password!="" && $password!=null){
                    fputs($fsock, "$password\r");
                    $this->read($fsock,$sw);
                }
                foreach($arrComandos as $comando => $valor)
                {
		    $line = $comando;
		    if($valor!="")
                        $line = "$comando $valor";
		    fputs($fsock, "$line\r");
                    $this->read($fsock,$sw);
                }
            }
            fclose($fsock);
            return true;
        }else return false;
    }

    function read($fsock, $sw=1 ,$seg=1)
    {
    $s = ""; 
    if($sw==1){
    $s = fread($fsock,1024);
    }
    else if($sw==2){
    stream_set_blocking($fsock, TRUE);
    stream_set_timeout($fsock,$seg);
    $info = stream_get_meta_data($fsock);
    while (true) {
        $char = fgetc($fsock);
        if(empty($char) && $info['timed_out']) break;
        $s .= "$char";
        $info = stream_get_meta_data($fsock);
    }
    }
    return $s;
    }


    function updateArrParameters($vendor, $model, $arrParametersOld)
    {
        switch($vendor){
            case 'Polycom':
                break;

            case 'Linksys':
                break;

            case 'Aastra':
                break;

            case 'Cisco':
                break;

            case 'Atcom':
                if($model == 'AT530' || $model == 'AT620' || $model == 'AT610' || $model == 'AT640'){
                    if(isset($arrParametersOld['versionCfg'])){
                        $arrParametersOld['versionCfg'] = $arrParametersOld['versionCfg'] + 0.0001;
            if(strlen($arrParametersOld['versionCfg']) == 1)
                $arrParametersOld['versionCfg'] .= ".0";	
            while(strlen($arrParametersOld['versionCfg']) < 6)
                $arrParametersOld['versionCfg'] .= "0";
            }
                    else
                        $arrParametersOld['versionCfg'] = '2.0005';
                }
                break;
	    case 'Fanvil':
                if($model == 'C62'||$model == 'C60'||$model == 'C58/C58P'||$model == 'C56/C56P'){
                    if(isset($arrParametersOld['versionCfg'])){
                        $arrParametersOld['versionCfg'] = $arrParametersOld['versionCfg'] + 0.0001;
			    if(strlen($arrParametersOld['versionCfg']) == 1)
				$arrParametersOld['versionCfg'] .= ".0";	
			    while(strlen($arrParametersOld['versionCfg']) < 6)
				 $arrParametersOld['versionCfg'] .= "0";
                    }
                    else
                        $arrParametersOld['versionCfg'] = '2.0002';
                }
                break;

            case 'Voptech':
                if($model == 'VI2007' || $model == 'VI2008' || $model == 'VI2006' ){
                    if(isset($arrParametersOld['versionCfg'])){
                        $arrParametersOld['versionCfg'] = $arrParametersOld['versionCfg'] + 0.0001;
                        if(strlen($arrParametersOld['versionCfg']) == 1)
                            $arrParametersOld['versionCfg'] .= ".0";	
                        while(strlen($arrParametersOld['versionCfg']) < 6)
                            $arrParametersOld['versionCfg'] .= "0";
                    }
                    else
                        $arrParametersOld['versionCfg'] = '2.0005';
                }
                break;
	    
            case 'Snom':
                break;

            case 'Grandstream':
                break;

            case 'Zultys':
                break;

            case 'AudioCodes':
                break;

            case 'Yealink':
                break;

            case 'LG-ERICSSON':
                break;

            case 'Xorcom':
                break;

        }

        return $arrParametersOld;
    }

        /*  The function find_version() find the files included P0S as, P0S3-xx-x-xx.sb2 or other.
        This function return only the file name and not the extension.
        Add by Franck danard.
        Maybe there's several solution to do it!
    */
    function find_version()
    {
            // Replace this code by the good directory tftp.
            $monrep = opendir($this->directory);
            while ($entryname = readdir($monrep)){
                // Finding begin file P0S
                $pos = strripos($entryname,"P0S");
                if ($pos === 0) // Cut the file extension .sb2
                    $image_version=strtok($entryname,".sb2");
            }
            return $image_version;
	}
}
?>
