<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +-------------------------------------------------------------------------------+
  | SMS MODULE VERSION 1.0                                                        |
  | http://www.iberoxarxa.es                                                      |
  +-------------------------------------------------------------------------------+
  | Copyright (c) 2011 Iberoxarxa Servixios Integrales, S.L.                      |
  +-------------------------------------------------------------------------------+
  | C.E.T.I.C CITILAB                                                             |
  | IBEROXARXA SERVICIOS INTEGRALES, SL                                           |
  | 08940 - CORNELLA DE LLOBREGAT                                                 |
  | BARCELONA - SPAIN                                                             |
  | http://www.iberoxarxa.es                                                      |
  | contactar@iberoxarxa.es                                                       |
  +-------------------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License           |
  | (GPL) Version 2 (the "License"); you may not use this file except in          |
  | compliance with the License. You may obtain a copy of the License at          |
  | http://www.opensource.org/licenses/gpl-license.php                            |
  |                                                                               |
  | Software distributed under the License is distributed on an "AS IS"           |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See           |
  | the License for the specific language governing rights and                    |
  | limitations under the License.                                                |
  +-------------------------------------------------------------------------------+
  | The Original Code is: Iberoxarxa Servicios Integrales                         |
  | The Initial Developer of the Original Code is Iberoxarxa Servicios Integrales |
  +-------------------------------------------------------------------------------+
  */

$arrFormElements = array();

function _moduleContent(&$smarty,$module_name) {
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoACL.class.php";
    require_once "libs/misc.lib.php";
    require_once "libs/sms/IXXSMS.class.php";

    //Incluir librería de lenguaje
    $arrLan=get_language();
    $script_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $arrLan_file="modules/$module_name/lang/$arrLan.lang";
    if (file_exists("$script_dir/$arrLan_file"))
        include_once($arrLan_file);
    else
        include_once("modules/$module_name/lang/en.lang");

    //include module files
    include_once "modules/$module_name/configs/default.config.php";

    global $arrConf;
    global $arrLan;

    //folder path for custom templates
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConfig['templates_dir']))?$arrConfig['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    $pDB = new paloDB("sqlite3:////var/www/db/acl.db");
    $pAcl = new paloACL($pDB);

    global $arrFormElements;

    //Leemos configuración de conexión a la base de datos de campañas y nos conectamos
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBSMS = new paloDB($dsn);

    $arrFormElements =  array("country_code"    => array("LABEL"                  => $arrLan['Country Code'],
                                                         "REQUIRED"               => "yes",
                                                         "INPUT_TYPE"             => "TEXT",
                                                           "INPUT_EXTRA_PARAM"      => array("size"=>4),
                                                         "VALIDATION_TYPE"        => "ereg",
                                                         "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]*$"),

                              "mobile_prefixes" => array("LABEL"                  => $arrLan['Mobile Prefixes'],
                 			                             "REQUIRED"               => "yes",
                       	             			         "INPUT_TYPE"             => "TEXTAREA",
                                                         "EDITABLE"               => "si",
                                                         "COLS"                   => "50",
                                                         "ROWS"                   => "4",
        		        	                             "VALIDATION_TYPE"        => "",
                                                         "INPUT_EXTRA_PARAM"      => "",
                                     		             "VALIDATION_EXTRA_PARAM" => ""),

                   			  "min_mobile_length" => array("LABEL"                  => $arrLan["Minimun Mobile Length"],
                                                           "REQUIRED"               => "yes",
                                                           "INPUT_TYPE"             => "TEXT",
                                                           "INPUT_EXTRA_PARAM"      => array("size"=>2),
                                                           "VALIDATION_TYPE"        => "ereg",
                                                           "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]*$"),

                   			  "max_mobile_length" => array("LABEL"                  => $arrLan["Maximun Mobile Length"],
                                                           "REQUIRED"               => "yes",
                                                           "INPUT_TYPE"             => "TEXT",
                                                           "INPUT_EXTRA_PARAM"      => array("size"=>2),
                                                           "VALIDATION_TYPE"        => "ereg",
                                                           "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]*$"),
                             );

    if (isset($_POST['save'])) {
        $content = saveConfig($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } else {
        $content = show($smarty, $module_name, $local_templates_dir, $pDBSMS, $arrLan, $arrFormElements);
    }

    return $content;
}

function show($smarty, $module_name, $local_templates_dir, $pDB, $arrLan, $arrFormElements) {
    require_once "modules/$module_name/configs/default.config.php";

    $smarty->assign("TITLE", $arrLan["Title"]);
    $smarty->assign("SAVE", $arrLan["Save"]);
    $smarty->assign("CANCEL", $arrLan["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLan["Required field"]);
    $smarty->assign("language", get_language());

    $oForm = new paloForm($smarty, $arrFormElements);

    $oSMS = new IXXSMS($pDB);
    $arrData = $oSMS->getConfig();
    $arrData = $arrData[0];

    $oForm->setEditMode();
    $htmlForm = $oForm->fetchForm("$local_templates_dir/config.tpl", $arrLan['Title'], $arrData);

    return $htmlForm ;
}

function saveConfig($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $update=false) {
    global $arrLang;
    global $arrLan;

    $strErrorMsg = "";
    $oForm = new paloForm($smarty, $arrFormElements);

    if(!$oForm->validateForm($_POST)) {
        $arrData = $_POST;
    
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores=$oForm->arrErroresValidacion;
        $strErrorMsg = "";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
		  if ($strErrorMsg <> "") {
			$strErrorMsg .= ", ";
		  }
                $strErrorMsg .= $k;
            }
        }
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>".$strErrorMsg;
        $smarty->assign("mb_message", $strErrorMsg);
    }else{
        $error = false;
    }

    if (($strErrorMsg != "") || $error) {
    	 $arrData = $_POST;
         $contenidoModulo = show($smarty, $module_name, $local_templates_dir, $pDB, $arrLan, $arrFormElements);
    } else {
        $oSMS = new IXXSMS($pDB);

        $oSMS->updateConfig($_REQUEST['country_code'],$_REQUEST['mobile_prefixes'],$_REQUEST['min_mobile_length'],$_REQUEST['max_mobile_length']);

        $contenidoModulo = show($smarty, $module_name, $local_templates_dir, $pDB, $arrLan, $arrFormElements);
    }

    return $contenidoModulo;
}

?>
