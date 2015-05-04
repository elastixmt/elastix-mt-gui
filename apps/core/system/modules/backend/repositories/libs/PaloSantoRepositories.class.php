<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
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
  $Id: PaloSantoRepositories.php $ */

include_once("libs/paloSantoDB.class.php");

class PaloSantoRepositories
{
    var $errMsg;

    function PaloSantoRepositories()
    {

    }

     /**
     * Procedimiento para obtener el listado de los repositorios 
     *
     * @return array    Listado de los repositorios 
     */
    function getRepositorios($ruta,$typeRepository,$mainRepos)
    {
        $arrArchivosRepo = $this->getArchivosRepo($ruta,$typeRepository,$mainRepos);
        $repositorios = array();
        foreach($arrArchivosRepo as $key => $archivoRepo){
            $auxRepo      = $this->scanFileRepo($ruta,$archivoRepo);
            $repositorios = array_merge($repositorios,$auxRepo); 
        }
        return $repositorios;
    }

    function setRepositorios($ruta,$arrReposActivos,$typeRepository,$mainRepos)
    {
	//Se obtienen los otros repos que no correspondan al tipo de repositorio seleccionado, para mantener los repos de ese tipo activos
	$otherRepos = array();
	if($typeRepository == "main")
	    $otherRepos = $this->getRepositorios($ruta,"others",$mainRepos);
	elseif($typeRepository == "others")
	    $otherRepos = $this->getRepositorios($ruta,"main",$mainRepos);
	if(is_array($otherRepos) && count($otherRepos)>0){
	    foreach($otherRepos as $key => $repo){
		if($repo["activo"])
		    $arrReposActivos[] = $repo['id'];
	    }
	}

	//Se obtienen los repos del tipo correspondiente, para convertir los id que contenga puntos a subguion debido a que asi llegan de la variable $_POST
	$arrRepos = $this->getRepositorios($ruta,$typeRepository,$mainRepos);
	$arrConvertRepo = array();
	foreach($arrRepos as $repo){
	    $arrConvertRepo[$repo["id"]] = str_replace(".","_",$repo["id"]);
	}
	foreach($arrReposActivos as $key => $value){
	    if(in_array($value,$arrConvertRepo)){
		$unconvertedValue = array_keys($arrConvertRepo,$value);
		$arrReposActivos[$key] = $unconvertedValue[0];
	    }
	}

        $sComando = '/usr/bin/elastix-helper repoconfig '.
            implode(' ', array_map('escapeshellarg', $arrReposActivos)).
            ' 2>&1';
        $output = $ret = NULL;
        exec($sComando, $output, $ret);
        if ($ret != 0) {
            $this->errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    }

    private function getArchivosRepo($dir='/etc/yum.repos.d/',$typeRepository='main',$mainRepos=array())
    {
        global $arrLang;
        $arr_repositorios  = scandir($dir);
        $arr_respuesta = array();

	$doFilter = true;
	$doInverseFilter = false;
        if($typeRepository=="all")
	    $doFilter = false;
	elseif($typeRepository=="others")
	    $doInverseFilter = true;

        if (is_array($arr_repositorios) && count($arr_repositorios) > 0) {
            foreach($arr_repositorios as $key => $repositorio){
		if($doInverseFilter)
		    $isMainRepo = !in_array($repositorio,$mainRepos);
		else
		    $isMainRepo = in_array($repositorio,$mainRepos);
                if(!is_dir($dir.$repositorio) && $repositorio!="." && $repositorio!=".." && strstr($repositorio,".repo") && (!$doFilter || $isMainRepo)) //que se un archivo y que el archivo tenga extension .repo
                    $arr_respuesta[$repositorio] = $repositorio;
            }
        } 
        else 
            $this->errMsg = $arrLang["Repositor not Found"];
        return $arr_respuesta;
    }


    private function scanFileRepo($ruta,$file)
    {
        $repositorios = array();
        $indice = NULL;
        foreach (file($ruta.$file) as $linea) {
            $regs = NULL;
            if (preg_match('/^\[(\S+)\]/', $linea, $regs)) {
                $indice = count($repositorios);
                $repositorios[$indice] = array(
                    'id'        =>  $regs[1],
                    'name'      =>  NULL,
                    'file'      =>  $file,
                    'activo'    =>  '1',
                );
            } elseif (preg_match('/^enabled\s*=\s*(\d+)/', $linea, $regs) && !is_null($indice)) {
                $repositorios[$indice]['activo'] = $regs[1];
            } elseif (preg_match('/^name\s*=\s*(.+\S)\s*$/', $linea, $regs) && !is_null($indice)) {
                $repositorios[$indice]['name'] = $regs[1];
            }
       	
        }
        return $repositorios;
    } 

    function obtenerVersionDistro()
    {
        exec("rpm -q --queryformat '%{VERSION}' centos-release",$arrSalida,$flag);
        if($flag==0)
            return $arrSalida[0];
        else
            return '?';
    }

    function obtenerArquitectura()
    {
        exec("uname -m",$arquitectura,$flag);
        if($flag==0)
            return $arquitectura[0];
        else
            return '?';
    }
}
?>