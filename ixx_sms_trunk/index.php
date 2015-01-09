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
function _moduleContent(&$smarty,$module_name) {
    require_once "libs/xajax/xajax.inc.php";
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoACL.class.php";
    require_once "libs/sms/IXXSMS.class.php";
    require_once "modules/ixx_sms_list/libs/IXXSMSList.class.php";

    //Incluir librerÃ­a de lenguaje
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

    //Leemos configuración de conexión a la base de datos de campañas y nos conectamos
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBSMS = new paloDB($dsn);

    //Creamos combos para listas
    $oSMS = new IXXSMS($pDBSMS);
    $arrList = $oSMS->getServiceTypes();

    $serviceTypes = array();

    foreach($arrList as $row) {
        $serviceTypes[$row['type']] = $row['name'];
    }

    $smarty->assign("service_types", $serviceTypes);

    //Obtenemos el service type para determinar los campos obligatorios
    $serviceType = (isset($_REQUEST['service_type'])?$_REQUEST['service_type']:'SMPP');
    $sms = new IXXSMS($pDBSMS);
    $service = $sms->getServiceType($serviceType);
    $service = $service[0];

    //Formulario
    $arrFormElements = array(
            'clid'      => array("LABEL"                  => $arrLan["CLID"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"clid"),
                                 "VALIDATION_TYPE"        => "ereg",
                                 "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]*$"),
            'name'      => array("LABEL"                  => $arrLan["Trunk Name"],
                                 "EDITABLE"               => "yes",
                                 "REQUIRED"               => "yes",
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("size"=>"80"),
                                 "VALIDATION_TYPE"        => "text",
                                 "VALIDATION_EXTRA_PARAM" => ""),
            'server'    => array("LABEL"                  => $arrLan["Server"],
                                 "EDITABLE"               => "yes",
                                 "REQUIRED"               => ($service['has_server']=='1'?'yes':'no'),
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("size"=>"40","id"=>"server"),
                                 "VALIDATION_TYPE"        => "text",
                                 "VALIDATION_EXTRA_PARAM" => ""),
            'port'      => array("LABEL"                  => $arrLan["Port"],
            			         "REQUIRED"               => ($service['has_port']=='1'?'yes':'no'),
       	                   		 "INPUT_TYPE"             => "TEXT",
			                     "VALIDATION_TYPE"        => "ereg",
                                 "INPUT_EXTRA_PARAM"      => array("size"=>"4","id"=>"port"),
                         		 "VALIDATION_EXTRA_PARAM"=> "^[[:digit:]]{1,4}$"),
            'trunk_priority' => array("LABEL"                  => $arrLan["Priority"],
            			         "REQUIRED"               => "no",
       	                   		 "INPUT_TYPE"             => "TEXT",
			                     "VALIDATION_TYPE"        => "ereg",
                                 "INPUT_EXTRA_PARAM"      => array("size"=>"2"),
                         		 "VALIDATION_EXTRA_PARAM"=> "^[[:digit:]]{1,2}$"),
            'active'    => array("LABEL"                  => $arrLan["Trunk Active"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "CHECKBOX",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"active"),
                                 "VALIDATION_TYPE"        => "",
                                 "VALIDATION_EXTRA_PARAM" => ""),
            'user'  	=> array("LABEL"                  => $arrLan['User'],
	        		             "REQUIRED"               => ($service['has_user']=='1'?'yes':'no'),
                        		 "INPUT_TYPE"             => "TEXT",
		        	             "VALIDATION_TYPE"        => "text",
                         		 "VALIDATION_EXTRA_PARAM" => ""),
            'password' 	=> array("LABEL"                  => $arrLan['Password'],
		        	           	 "REQUIRED"               => ($service['has_password']=='1'?'yes':'no'),
       	                  		 "INPUT_TYPE"             => "PASSWORD",
			                     "VALIDATION_TYPE"        => "text",
                        		 "VALIDATION_EXTRA_PARAM" => ""),
            'clid'      => array("LABEL"                  => $arrLan["CLID"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"clid","size"=>"11"),
        		                 "VALIDATION_TYPE"        => "text",
                           		 "VALIDATION_EXTRA_PARAM" => ""),
            'script'    => array("LABEL"                  => $arrLan["Script"],
                                 "REQUIRED"               => ($service['has_script']=='1'?'yes':'no'),
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"script","size"=>"80"),
        		                 "VALIDATION_TYPE"        => "text",
                           		 "VALIDATION_EXTRA_PARAM" => ""),

            'append_country_code' => array("LABEL"        => $arrLan["Append Country Code"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "CHECKBOX",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"append_country_code"),
                                 "VALIDATION_TYPE"        => "",
                                 "VALIDATION_EXTRA_PARAM" => ""),

            'service_type'=> array("LABEL"                 => $arrLan["Service Type"],
                                   "REQUIRED"               => "no",
                                   "INPUT_TYPE"             => "TEXT",
                                   "INPUT_EXTRA_PARAM"      => "",
        		                   "VALIDATION_TYPE"        => "text",
                           		   "VALIDATION_EXTRA_PARAM" => ""),

            'system_type' => array("LABEL"                 => $arrLan["System Type"],
                                   "REQUIRED"               => "no",
                                   "INPUT_TYPE"             => "TEXT",
                                   "INPUT_EXTRA_PARAM"      => "",
        		                   "VALIDATION_TYPE"        => "text",
                           		   "VALIDATION_EXTRA_PARAM" => ""),

        );

    //Mostramos contenido del módulo
    $content = "";

    global $arrLang;

    $smarty->assign("TITLE", $arrLan["Title"]);
    $smarty->assign("APPLY_CHANGES", $arrLang["Apply changes"]);
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("DELETE", $arrLang["Delete"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("CONFIRM_DELETE", $arrLan["Are you sure you wish to delete trunk?"]);

    if (isset($_POST['submit_create_trunk'])) {
        $content= nuevoTrunk($smarty, $module_name, $local_templates_dir, $pDBSMS, $arrFormElements);
    } elseif (isset($_POST['save'])) {
        $content = saveTrunk($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } elseif (isset($_POST['cancel'])) {
        $content= listaTrunks($pDBSMS , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['apply_changes'])) {
        $content = updateTrunk($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } else if (isset($_GET['id']) && isset($_POST['delete'])) {
        $content = removeTrunk($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="edit") {
        $content = viewTrunk($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $content = viewTrunk($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="stop") {
        $content = stopCampaign($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="start") {
        $content = startCampaign($pDBSMS , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else {
 	 $content= listaTrunks($pDBSMS , $smarty, $module_name, $local_templates_dir);
    }

    $xajax = new xajax();
    $xajax->registerFunction("retrieve_providers");
    $xajax->registerFunction("retrieve_provider");
    $xajax->processRequests();

    $content .= $xajax->printJavascript("libs/xajax/");

    return $content;
}

function nuevoTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData = null) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    if ($arrData == null) {
        $arrData = array();
    }

    $arrData['pause'] = 'on';

    $oForm = new paloForm($smarty, $arrFormElements);

    $arrData['count'] = 0;
    $arrData['messages'] = 0;

    if (!isset($arrData['trunk_priority'])) {
        $arrData['trunk_priority'] = 1;
    }

    if (!isset($arrData['active'])) {
        $arrData['active'] = 'on';
    }

    if (!isset($arrData['append_country_code'])) {
        $arrData['append_country_code'] = 'off';
    }

    if (!isset($arrData['service_type'])) {
        $arrData['service_type'] = 'SMPP';
    }

    $smarty->assign("current_service_type", $arrData['service_type']);

    retrieve_providers($arrData['service_type'],false);

    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrData);
    $htmlForm .= set_opts($pDB,$arrData['service_type']);

    return $htmlForm;
}

function viewTrunk($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_trunk) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    $smarty->assign("id_trunk", $id_trunk);

    $oForm = new paloForm($smarty, $arrFormElements);

    $oSMS = new IXXSMS($pDB);
    $arrData = $oSMS->getTrunk($id_trunk);
    $arrData = $arrData[0];

    $arrData['active'] = ($arrData['active']==1?'on':'off');
    $arrData['append_country_code'] = ($arrData['append_country_code']==1?'on':'off');

    $smarty->assign("current_service_type", $arrData['service_type']);
    retrieve_providers($arrData['service_type'],false);

    $oForm->setEditMode();
    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrData);
    $htmlForm .= set_opts($pDB,$arrData['service_type']);

    return $htmlForm;
}

function removeTrunk($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_trunk) {
    require_once "modules/$module_name/configs/default.config.php";

    $oSMS = new IXXSMS($pDB);
    $arrData = $oSMS->removeTrunk($id_trunk);

    $content = listaTrunks($pDB , $smarty, $module_name, $local_templates_dir);

    return $content;
}

function saveTrunk($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $update=false) {
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
         $contenidoModulo = nuevoTrunk($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData);
    } else {
        $oSMS = new IXXSMS($pDB);

        if (!$update) {
    		$oSMS->createTrunk($_REQUEST['name'],$_REQUEST['active'],$_REQUEST['service_type'],
            				   $_REQUEST['server'],$_REQUEST['user'],$_REQUEST['password'],$_REQUEST['port'],
            				   $_REQUEST['trunk_priority'],$_REQUEST['clid'],$_REQUEST['system_type'],
                               $_REQUEST['append_country_code'],$_REQUEST['script']);
    	} else {
    		$oSMS->updateTrunk($_REQUEST['id_trunk'],$_REQUEST['name'],$_REQUEST['active'],$_REQUEST['service_type'],
            				   $_REQUEST['server'],$_REQUEST['user'],$_REQUEST['password'],$_REQUEST['port'],
            				   $_REQUEST['trunk_priority'],$_REQUEST['clid'],$_REQUEST['system_type'],
                               $_REQUEST['append_country_code'],$_REQUEST['script']);
    	}

        $contenidoModulo = listaTrunks($pDB , $smarty, $module_name, $local_templates_dir);
    }

    return $contenidoModulo;
}

function updateTrunk($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements) {
    return saveTrunk($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, true);
}

function listaTrunks($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;
    global $arrLan;
    $arrData = '';

    $oSMS = new IXXSMS($pDB);

    // para el pagineo
    $limit = 50;
    $offset = 0;

    $url = construirURL();
    $smarty->assign("url", $url);

    $arrSMS = $oSMS->getTrunks();
    $total = count($arrSMS);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalSMS  = count($arrSMS);
        // Mejorar el sgte. bloque.
        if(($totalSMS%$limit)==0) {
            $offset = $totalSMS - $limit;
        } else {
            $offset = $totalSMS - $totalSMS%$limit;
        }
    }

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="next") {
        $offset = $_GET['start'] + $limit - 1;
    }

    // Si se quiere retroceder
    if(isset($_GET['nav']) && $_GET['nav']=="previous") {
        $offset = $_GET['start'] - $limit - 1;
    }

    $arrSMS = $oSMS->getTrunks();

    $end = count($arrSMS);

    $arrData = array();

    if (is_array($arrSMS)) {
        foreach($arrSMS as $sms) {
            $arrTmp = array();

            $arrTmp[0] = $sms['name'];
            $arrTmp[1] = $sms['service_type'];
            $arrTmp[2] = ($sms['active']==1?$arrLan["yes"]:$arrLan["no"]);
	        $arrTmp[3] = "<a href=\"?menu=ixx_sms_trunk&action=edit&id=".$sms['id']."\">".$arrLan["Show"]."</a>";

            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title" => $arrLan["SMS Trunks"],
	"autoSize" => true,
        "icon"     => "images/list.png",
        "width"    => "100%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => $arrLan["Trunk Name"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Trunk Type"], 
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Trunk Active"], 
                                       "property1" => ""),
                            3 => array("name"     => $arrLan["Options"], 
                                       "property1" => ""),

                           )
    );

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_create_trunk' value='{$arrLan['Create New Trunk']}' class='button'></td>".
              "</tr></table>".
              "</form>");

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

function retrieve_providers($type,$ajax=true) {
    //Leemos configuración de conexión a la base de datos de campañas y nos conectamos
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBCampaign = new paloDB($dsn);

    $pDBSMS = new paloDB($dsn);
    
    //Obtenemos los proveedores
    $sms = new IXXSMS($pDBSMS);
    $result = $sms->getProviders($type);

    //Construimos el select de proveedores
    if (count($result) > 0) {
        $html = "<select onchange=\"provider_changed();\" id=\"provider_select\">";
        $html .= "<option value=\"----\">----</option>";

        foreach($result as $row) {
            $html .= "<option value=\"{$row['provider']}\">{$row['name']}</option>";
        }
        $html .= "</select>";
    } else {
        $html = "";
    }

    if ($ajax) {
        $respuesta = new xajaxResponse();

        $respuesta->addAssign("provider","innerHTML",$html);  

        $service = $sms->getServiceType($type);
        $service = $service[0];

        $respuesta->addAssign("opt_server1","style.display",$service['has_server']=='1'?'':'none');
        $respuesta->addAssign("opt_server2","style.display",$service['has_server']=='1'?'':'none');

        $respuesta->addAssign("opt_user1","style.display",$service['has_user']=='1'?'':'none');
        $respuesta->addAssign("opt_user2","style.display",$service['has_user']=='1'?'':'none');

        $respuesta->addAssign("opt_password1","style.display",$service['has_password']=='1'?'':'none');
        $respuesta->addAssign("opt_password2","style.display",$service['has_password']=='1'?'':'none');

        $respuesta->addAssign("opt_port1","style.display",$service['has_port']=='1'?'':'none');
        $respuesta->addAssign("opt_port2","style.display",$service['has_port']=='1'?'':'none');

        $respuesta->addAssign("opt_system_type1","style.display",$service['has_system_type']=='1'?'':'none');
        $respuesta->addAssign("opt_system_type2","style.display",$service['has_system_type']=='1'?'':'none');

        $respuesta->addAssign("opt_script1","style.display",$service['has_script']=='1'?'':'none');
        $respuesta->addAssign("opt_script2","style.display",$service['has_script']=='1'?'':'none');

        return $respuesta;
    } else {
        global $smarty;

        $smarty->assign("providers",$html);
    }
}

function retrieve_provider($provider) {
    //Leemos configuración de conexión a la base de datos de campañas y nos conectamos
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBCampaign = new paloDB($dsn);

    $pDBSMS = new paloDB($dsn);
    
    //Obtenemos los proveedores
    $sms = new IXXSMS($pDBSMS);
    $result = $sms->getProvider($provider);

    $respuesta = new xajaxResponse();

    //Copiamos los valores de configuración
    if (count($result) > 0) {
        $respuesta->addAssign("server","value",$result[0]['server']);
        $respuesta->addAssign("port","value",$result[0]['port']);
        $respuesta->addAssign("append_country_code","value",($result[0]['append_country_code']==1?'on':'off'));

        $respuesta->addScript("document.getElementsByName('chkoldappend_country_code')[0].checked=".($result[0]['append_country_code']==1).";\n");
    } else {
        $respuesta->addAssign("server","value","");
        $respuesta->addAssign("port","value","");
        $respuesta->addAssign("append_country_code","value","off");

        $respuesta->addScript("document.getElementsByName('chkoldappend_country_code')[0].checked=false;\n");
    }
    
    return $respuesta;
}

function set_opts($pDB,$type) {
    $sms = new IXXSMS($pDB);
    $service = $sms->getServiceType($type);
    $service = $service[0];

    $tmp = "<script>";
    $tmp .= "document.getElementById('opt_server1').style.display='".($service["has_server"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_server2').style.display='".($service["has_server"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_user1').style.display='".($service["has_user"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_user2').style.display='".($service["has_user"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_password1').style.display='".($service["has_password"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_password2').style.display='".($service["has_password"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_port1').style.display='".($service["has_port"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_port2').style.display='".($service["has_port"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_system_type1').style.display='".($service["has_system_type"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_system_type2').style.display='".($service["has_system_type"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_script1').style.display='".($service["has_script"]=='1'?'':'none')."';\n";
    $tmp .= "document.getElementById('opt_script2').style.display='".($service["has_script"]=='1'?'':'none')."';\n";
    $tmp .= "</script>";

    return $tmp;
}

?>
