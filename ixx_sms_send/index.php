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
    require_once "libs/xajax/xajax.inc.php";
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

	//Creamos combos para troncales

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBCampaign = new paloDB($dsn);

	$cvTrunk = new IXXSMS($pDBCampaign);
	$arrList = $cvTrunk->getActiveTrunks();

	$troncales = array();
	$troncales[0] = '----';

	foreach($arrList as $row) {
	    $troncales[$row['id']] = $row['name'];
	}

    $arrFormElements =  array(
                              "text"    => array("LABEL"                  => $arrLan['Text'],
         			                             "REQUIRED"               => "yes",
               	             			         "INPUT_TYPE"             => "TEXTAREA",
                                                 "EDITABLE"               => "si",
                                                 "COLS"                   => "50",
                                                 "ROWS"                   => "4",
        			                             "VALIDATION_TYPE"        => "",
                                                 "INPUT_EXTRA_PARAM"      => array("onKeyUp" => "contar('SendSMS','text','count','messages')","id"=>"text"),
                             		             "VALIDATION_EXTRA_PARAM" => ""),
                   			  "call_to"=>  array("LABEL"                  => $arrLan["Phone Number"],
                                                 "REQUIRED"               => "yes",
                                                 "INPUT_TYPE"             => "TEXT",
                                                 "INPUT_EXTRA_PARAM"      => array("id"=>"call_to"),
                                                 "VALIDATION_TYPE"        => "ereg",
                                                 "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]*$"),
                   			  "count"=> array(   "LABEL"                  => $arrLan["Count"],
                                                 "EDITABLE"               => "no",
                                                 "REQUIRED"               => "no",
                                                 "INPUT_TYPE"             => "TEXT",
                                                 "INPUT_EXTRA_PARAM"      => array("readonly"=>"true","size"=>"3","id"=>"count"),
                                                 "VALIDATION_TYPE"        => "",
                                                 "VALIDATION_EXTRA_PARAM" => ""),
                   			  "messages"=> array("LABEL"                  => $arrLan["Messages"],
                                                 "EDITABLE"               => "no",
                                                 "REQUIRED"               => "no",
                                                 "INPUT_TYPE"             => "TEXT",
                                                 "INPUT_EXTRA_PARAM"      => array("readonly"=>"true","size"=>"3","id"=>"messages"),
                                                 "VALIDATION_TYPE"        => "",
                                                 "VALIDATION_EXTRA_PARAM" => ""),
               		         "encolar"  => array("LABEL"                  => $arrLan["Encolar"],
	             	                             "REQUIRED"               => "no",
       	                    	                 "INPUT_TYPE"             => "CHECKBOX",
                                   	             "INPUT_EXTRA_PARAM"      => array("id"=>"encolar"),
                                          	     "VALIDATION_TYPE"        => "",
	                                             "VALIDATION_EXTRA_PARAM" => ""),
                            "trunk"     => array("LABEL"                  => $arrLan["Trunk"],
                                                 "REQUIRED"               => "yes",
                                                 "INPUT_TYPE"             => "SELECT",
                                                 "INPUT_EXTRA_PARAM"      => $troncales,
                                                 "VALIDATION_TYPE"        => '',
                                                 "VALIDATION_EXTRA_PARAM" => ''),
                             );

    $xajax = new xajax();
    $xajax->registerFunction("sendSMS");
    $xajax->processRequests();

    $contenido = $xajax->printJavascript("libs/xajax/");

    $content = show($smarty, $module_name, $local_templates_dir, $pDB, $arrLan, $arrFormElements);

    return $contenido.$content;
}

function show($smarty, $module_name, $local_templates_dir, $pDB, $arrLan, $arrFormElements) {
    require_once "modules/$module_name/configs/default.config.php";

    $smarty->assign("TITLE", $arrLan["Title"]);
    $smarty->assign("SEND", $arrLan["SEND"]);
    $smarty->assign("CANCEL", $arrLan["CANCEL"]);
    $smarty->assign("ADDRESS_BOOK", $arrLan["ADDRESS_BOOK"]);
    $smarty->assign("REQUIRED_FIELD", $arrLan["Required field"]);
    $smarty->assign("language", get_language());

    $oForm = new paloForm($smarty, $arrFormElements);

    //$smarty->template_dir = "themes/".strtolower($_SESSION['brand']);

    $arrData['count'] = 0;
    $arrData['messages'] = 0;
    $arrData['encolar'] = 'off';

    $htmlForm= $oForm->fetchForm("$local_templates_dir/sms_send.tpl", $arrLan['Title'], $arrData);
    $contenidoModulo = "<form id='SendSMS' method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $contenidoModulo ;
}

function sendSMS($phone,$text,$encolar,$trunk) {
   global $arrLan;

   $respuesta = new xajaxResponse();

   $error = false;
   $msg = "";
   $text = trim($text);

   //Leemos configuraciÃ³n de conexiÃ³n a la base de datos y nos conectamos
   $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
   $arrConfig = $pConfig->leer_configuracion(false);

   $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
   $pDBSMS = new paloDB($dsn);

   $sms = new IXXSMS($pDBSMS);

   $config = $sms->getConfig();

   if (ereg("^[[:digit:]]*$",$phone)) {
       if (strlen($phone) < $config[0]['min_mobile_length']) {
	     $msg = $arrLan["Invalid Number"];
  	     $error = true;           
   	   }
    } else {
       $msg = $arrLan["Invalid Number"];
       $error = true;
   }

   if (($text == "") && (!$error)) {
       $error = true;
       $msg = $arrLan["No ha entrado un texto para enviar."];
   }

   //Mandamos SMS
   if (!$error) {
        $result = $sms->send("","",$phone,"",$text,$encolar == "on",$trunk);

        $msg = $result[1];
   }

   $respuesta->addAssign("relojArena","innerHTML","");
   $respuesta->addAssign("nombre_paquete","value","");
   $respuesta->addAssign("estaus_reloj","value","apagado");
   $respuesta->addScript("document.getElementById('form_dectect').submit();\n");

   $respuesta->addAlert($msg);

   return $respuesta;
}

?>
