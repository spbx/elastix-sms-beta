<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  CodificaciÃ³n: UTF-8
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
    include_once "libs/paloSantoGrid.class.php";
    include_once "libs/paloSantoDB.class.php";
    include_once "libs/paloSantoForm.class.php";
    include_once "libs/paloSantoConfig.class.php";
    include_once "libs/paloSantoACL.class.php";
    include_once "libs/sms/IXXSMS.class.php";
    require_once "libs/misc.lib.php";
    require_once "libs/ixx.date.lib.php";
    require_once "libs/IXXSMSCampaign.class.php";
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
    $pDBCampaign = new paloDB($dsn);

    if(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        $limit = "";
        $offset = 0;

        $field_name = $_GET['field_name'];
        $field_pattern = $_GET['field_pattern'];
        $status = $_GET['status'];
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        //header('Content-Length: '.strlen($this->buffer));
        header('Content-disposition: inline; filename="cdrreport.csv"');
        header('Content-Type: application/force-download');
        //header('Content-Length: '.strlen($this->buffer));
        //header('Content-disposition: attachment; filename="'.$name.'"');
    } else {
        //Creamos combos para las horas
        $horas = array();
        $i = 0;
        for( $i=-1;$i<24;$i++)
        {
            if($i == -1)     $horas["HH"] = "HH";
            else if($i < 10) $horas["0$i"] = '0'.$i;
            else             $horas[$i] = $i;
        }

        $minutos = array();
        $i = 0;
        for( $i=-1;$i<60;$i++)
        {
            if($i == -1)     $minutos["MM"] = "MM";
            else if($i < 10) $minutos["0$i"] = '0'.$i;
            else             $minutos[$i] = $i;
        }

	 //Creamos combos para troncales
	 $cvTrunk = new IXXSMS($pDBCampaign);
	 $arrList = $cvTrunk->getActiveTrunks();

	 $troncales = array();
	 $troncales[0] = '----';

	 foreach($arrList as $row) {
		$troncales[$row['id']] = $row['name'];
	 }

	 //Creamos combos para listas
	 $cvList = new IXXSMSList($pDBCampaign);
	 $arrList = $cvList->getLists(null,null);

	 $listas = array();
	 $listas['----'] = '----';

	 foreach($arrList as $row) {
		$listas[$row['list']] = $row['name'];
	 }

        //Formulario campaña     
        $arrFormElements = array(
            "use_list"   => array(
                "LABEL"                  => $arrLan["Use List"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $listas,
                "VALIDATION_TYPE"        => '',
                "VALIDATION_EXTRA_PARAM" => '',
             ),

            "trunk"   => array(
                "LABEL"                  => $arrLan["Trunk"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $troncales,
                "VALIDATION_TYPE"        => '',
                "VALIDATION_EXTRA_PARAM" => '',
             ),

            'pause'     => array("LABEL"                  => $arrLan["Create on Pause"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "CHECKBOX",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"pause"),
                                 "VALIDATION_TYPE"        => "",
                                 "VALIDATION_EXTRA_PARAM" => ""),
            'clid'      => array("LABEL"                  => $arrLan["CLID"],
                                 "REQUIRED"               => "no",
                                 "INPUT_TYPE"             => "TEXT",
                                 "INPUT_EXTRA_PARAM"      => array("id"=>"clid","size"=>"11"),
		                 "VALIDATION_TYPE"        => "",
                   		 "VALIDATION_EXTRA_PARAM" => ""),
            'nombre'    =>    array(
                "LABEL"                  => $arrLan["Name Campaign"],
                "EDITABLE"               => "no",
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "TEXT",
                "INPUT_EXTRA_PARAM"      => array("size"=>"72"),
                "VALIDATION_TYPE"        => "text",
                "VALIDATION_EXTRA_PARAM" => "",
            ),
            "fecha_ini"       => array(
                "LABEL"                  => $arrLan["Start"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "DATE",
                //"INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M","TIMEFORMAT" => "24"),
                "INPUT_EXTRA_PARAM"      => array("TIME" => false, "FORMAT" => "%d %b %Y"),
                "VALIDATION_TYPE"        => 'ereg',
                "VALIDATION_EXTRA_PARAM" => '^[[:digit:]]{2}[[:space:]]+[[:alpha:]]{3}[[:space:]]+[[:digit:]]{4}$'
            ),
            "hora_str"       => array(
                "LABEL"                  => $arrLan["Schedule per Day"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "",
                "INPUT_EXTRA_PARAM"      => "",
                "INPUT_EXTRA_PARAM"      => "",
                "VALIDATION_TYPE"        => '',
                "VALIDATION_EXTRA_PARAM" => ''
            ),
            "hora_ini_HH"   => array(
                "LABEL"                  => $arrLan["Start time"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $horas,
                "VALIDATION_TYPE"        => 'numeric',
                "VALIDATION_EXTRA_PARAM" => '',
             ),
            "hora_ini_MM"   => array(
                "LABEL"                  => $arrLan["Start time"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $minutos,
                "VALIDATION_TYPE"        => 'numeric',
                "VALIDATION_EXTRA_PARAM" => '',
             ),
             "hora_fin_HH"   => array(
                "LABEL"                  => $arrLan["End time"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $horas,
                "VALIDATION_TYPE"        => 'numeric',
                "VALIDATION_EXTRA_PARAM" => '',
             ),
             "hora_fin_MM"   => array(
                "LABEL"                  => $arrLan["End time"],
                "REQUIRED"               => "yes",
                "INPUT_TYPE"             => "SELECT",
                "INPUT_EXTRA_PARAM"      => $minutos,
                "VALIDATION_TYPE"        => 'numeric',
                "VALIDATION_EXTRA_PARAM" => '',
             ),
            "message"=> array(    
                 "LABEL"                 => $arrLan['Message'],
	          "REQUIRED"              => "yes",
                 "INPUT_TYPE"            => "TEXTAREA",
                 "EDITABLE"              => "si",
                 "COLS"                  => "50",
                 "ROWS"                  => "4",
        	   "VALIDATION_TYPE"       => "",
                 "INPUT_EXTRA_PARAM"     => array("onKeyUp" => "contar('campaign','message','count','messages')","id"=>"message"),
                 "VALIDATION_EXTRA_PARAM"=> ""),
            "count"=> array(
	          "LABEL"                 => $arrLan["Count"],
                 "EDITABLE"              => "yes",
                 "REQUIRED"              => "no",
                 "INPUT_TYPE"            => "TEXT",
                 "INPUT_EXTRA_PARAM"     => array("readonly"=>"true","size"=>"3","id"=>"count"),
                 "VALIDATION_TYPE"       => "",
                 "VALIDATION_EXTRA_PARAM"=> ""),
            "messages"=> array(
	          "LABEL"                 => $arrLan["Messages"],
                 "EDITABLE"              => "yes",
                 "REQUIRED"              => "no",
                 "INPUT_TYPE"            => "TEXT",
                 "INPUT_EXTRA_PARAM"     => array("readonly"=>"true","size"=>"3","id"=>"messages"),
                 "VALIDATION_TYPE"       => "",
                 "VALIDATION_EXTRA_PARAM"=> ""),
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
        $smarty->assign("PHONE_FILE", $arrLan["Archivo de Llamadas"]);
	 $smarty->assign("CONFIRM_DELETE", $arrLan["Are you sure you wish to delete campaign?"]);
    }

    if (isset($_POST['submit_create_campaign'])) {
        $content= nuevaCampaign($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements);
    } elseif (isset($_POST['submit_list_campaigns'])) {
 	 $content= listaCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['save'])) {
        $content = saveCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } elseif (isset($_POST['cancel'])) {
        $content= listaCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['apply_changes'])) {
        $content = updateCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } else if (isset($_GET['id']) && isset($_POST['delete'])) {
        $content = removeCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="viewnumbers") {
        $content = viewCampaignNumbers($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $content = viewCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="stop") {
        $content = stopCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="start") {
        $content = startCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else {
 	 $content= listaCampaign($pDBCampaign , $smarty, $module_name, $local_templates_dir);
    }

    return $content;
}

//Muestra el formulario para crear una campaña
function nuevaCampaign($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData = null) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    if ($arrData == null) {
        $arrData = array();
    }

    $arrData['pause'] = 'on';

    $oForm = new paloForm($smarty, $arrFormElements);

    $arrData['count'] = 0;
    $arrData['messages'] = 0;

    $smarty->assign("language", get_language());

    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrData);
    return $htmlForm;
}

//Muestra el formulario para editar una campaña
function viewCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_campaign) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    $oForm = new paloForm($smarty, $arrFormElements);

    $oCamp = new IXXSMSCampaign($pDB);
    $arrData = $oCamp->getCampaigns(null,null, $id_campaign);

    $arrTmp['trunk'] = $arrData[0]['trunk'];
    $arrTmp['nombre'] = $arrData[0]['name'];
    $arrTmp['message'] = $arrData[0]['message'];
    $arrTmp['clid'] = $arrData[0]['clid'];

    $arrTmp['fecha_ini'] = traducirFechaLetras($arrData[0]['start_date']);

    $partes = split(":",$arrData[0]['start_time']);
    $arrTmp['hora_ini_HH'] = $partes[0];
    $arrTmp['hora_ini_MM'] = $partes[1];

    $partes = split(":",$arrData[0]['end_time']);
    $arrTmp['hora_fin_HH'] = $partes[0];
    $arrTmp['hora_fin_MM'] = $partes[1];

    $arrTmp['count'] = 160 - strlen($arrTmp['message']);

    $smarty->assign("queue", ($id_campaign == 1));
    $smarty->assign("edit", ($arrData[0]['status'] != 'F'));

    $smarty->assign("language", get_language());

    $oForm->setEditMode();
    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrTmp);
    return $htmlForm;
}

//Muestra la lista de números asignados a una campaña y su estado
function viewCampaignNumbers($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_campaign) {
    global $arrLang;
    global $arrLan;
    $arrData = '';
    $oCampaign = new IXXSMSCampaign($pDB);

    // preguntando por el estado del filtro
    if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
        $_POST['cbo_estado'] = "A";
    }

    // para el pagineo
    if(!isset($_GET['exportcsv']) || $_GET['exportcsv']=='no') {
	    $limit = 25;
	    $offset = 0;
    }

    if( isset($_GET['cbo_estado']) ) {
        $url = construirURL()."&cbo_estado={$_GET['cbo_estado']}";
    } else {
        $url = construirURL()."&cbo_estado={$_POST['cbo_estado']}";
    }
    $smarty->assign("url", $url);

   if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
   } 

    $arrCampaign = $oCampaign->getCampaignsNumbers(null,null, $id_campaign, $_POST['cbo_estado']);
    $total = count($arrCampaign);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalCampaigns  = count($arrCampaign);
        // Mejorar el sgte. bloque.
        if(($totalCampaigns%$limit)==0) {
            $offset = $totalCampaigns - $limit;
        } else {
            $offset = $totalCampaigns - $totalCampaigns%$limit;
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

    if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
    }

    $arrCampaign = $oCampaign->getCampaignsNumbers($limit, $offset, $id_campaign, $_POST['cbo_estado']);

    $end = count($arrCampaign);

    $arrData = array();

    if (is_array($arrCampaign)) {
        foreach($arrCampaign as $campaign) {
            $arrTmp    = array();

            $arrTmp[0] = $campaign['number'];
            $arrTmp[1] = $campaign['message'];

	     if ($campaign['last'] == null) {
	            $arrTmp[2] = "";
	     } else {
	            $arrTmp[2] = traducirFechaHoraLarga($campaign['last']);
            }
            $arrTmp[3] = $arrLan['STATUSN'.$campaign['status']];
            $arrTmp[4] = $campaign['code'].($campaign['code']!=""?" (".$campaign['code_desc'].")":"");


            $arrData[] = $arrTmp;
        }
    }


    $arrGrid = array("title" => $arrLan["Campaigns List"],
	 "autoSize" => true,
        "icon"     => "images/list.png",
        "width"    => "100%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => $arrLan["Number"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["MessageS"], 
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Last"], 
                                       "property1" => ""),
                            3 => array("name"     => $arrLan["Status"], 
                                       "property1" => ""),
                            4 => array("name"     => $arrLan["RetCode"], 
                                       "property1" => ""),

                           )
    );


    $estadostmp = array("E"=>$arrLan["STATUSNE"], "F"=>$arrLan["STATUSNF"], "P"=>$arrLan["STATUSNP"],"N"=>$arrLan["STATUSNN"], "S"=>$arrLan["STATUSNS"], "I"=>$arrLan["STATUSNI"]);
    asort($estadostmp);
    $estados = array_merge(array("all"=>$arrLan["All"]),$estadostmp);

    $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".combo($estados,$_POST['cbo_estado'])."</select>";

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=ixx_sms_campaign&action=viewnumbers&id=".$id_campaign."'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_list_campaigns' value='{$arrLan['List Campaigns']}' class='button'></td>".
              "<td class='letra12' align='right'>".$arrLan["Status"]."&nbsp;$combo_estados</td>".
              "</tr></table>".
              "</form>");

    if(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        $contenidoModulo = $oGrid->fetchGridCSV($arrGrid, $arrData);
    } else {
        $oGrid->enableExport();
        $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    }

    return $contenidoModulo;
}


//Detiene una campaña
function stopCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_campaign) {
    require_once "modules/$module_name/configs/default.config.php";

    $oCamp = new IXXSMSCampaign($pDB);
    $arrData = $oCamp->stopCampaign($id_campaign);

    header("Location: ?menu=$module_name");

    return "";
}

//Arranca una campaña detenida
function startCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_campaign) {
    require_once "modules/$module_name/configs/default.config.php";

    $oCamp = new IXXSMSCampaign($pDB);
    $arrData = $oCamp->startCampaign($id_campaign);

    header("Location: ?menu=$module_name");

    return "";
}

//Borra una campaña y pasa a la lista de campañas
function removeCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_campaign) {
    require_once "modules/$module_name/configs/default.config.php";

    $oCamp = new IXXSMSCampaign($pDB);
    $arrData = $oCamp->removeCampaign($id_campaign);

    $content = listaCampaign($pDB , $smarty, $module_name, $local_templates_dir);

    return $content;
}

//Guarda una campaña y pasa a la lista de campañas
function saveCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $update=false) {
    global $arrLang;
    global $arrLan;

    $oCamp = new IXXSMSCampaign($pDB);
    $oForm = new paloForm($smarty, $arrFormElements);
    $strErrorMsg = "";

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
        $time_ini = $_POST['hora_ini_HH'].":".$_POST['hora_ini_MM'];
        $time_fin = $_POST['hora_fin_HH'].":".$_POST['hora_fin_MM'];

        $iFechaIni = strtotime(traducirFechaLetrasEn($_POST['fecha_ini']));
        $iHoraIni  = strtotime($time_ini);
        $iHoraFin  = strtotime($time_fin); 

        $error = false;

        if ($iFechaIni == -1 || $iFechaIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start date specification']);
    	    $error = true;
        } elseif ($iHoraIni == -1 || $iHoraIni === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse start time specification']);
    	    $error = true;
        } elseif ($iHoraFin == -1 || $iHoraFin === FALSE) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Unable to parse end time specification']);
	        $error = true;
        } elseif ($iHoraFin < $iHoraIni) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['Hora inicial / final incorrecta']);
	        $error = true;
	    } elseif (($_FILES['phonefile']['tmp_name'] == '') && ($_REQUEST['use_list'] == '----')) {
            $smarty->assign("mb_title", $arrLang["Validation Error"]);
            $smarty->assign("mb_message", $arrLan['List not found']);
	        $error = true;
        } else {
            $pDB->genQuery("SET autocommit=0");

	        if (!$update) {
		        $paused = false;
		        if ($_REQUEST['pause'] == 'on') {
			        $paused = true;
		        }

	            $id_campaign = $oCamp->createEmptyCampaign(
       	                    $_POST['nombre'],
       	                    date('Y-m-d', $iFechaIni),
              	            $time_ini,
                     	    $time_fin,
	                        $_POST['message'],
	                        $_POST['clid'],
        				    $paused,
	                        $_POST['trunk']);

	            if (!is_null($id_campaign)) {
			        if ($_FILES['phonefile']['tmp_name'] != '') {
	       	            $bExito1=false;
       	       	        $bExito1 = $oCamp->addCampaignNumbersFromFile($id_campaign, $_FILES['phonefile']['tmp_name'],$_POST['trunk']);
			        }

			        if ($_REQUEST['use_list'] != '----') {
	       	            $bExito1=false;
       	       	        $bExito1 = $oCamp->addCampaignNumbersFromList($id_campaign, $_REQUEST['use_list'],$_POST['trunk']);
			        }

	                if ($bExito1) {
       	                $pDB->genQuery("COMMIT");
              	        header("Location: ?menu=$module_name");
	                } else {
			            $arrData = $_POST;		
              	        $pDB->genQuery("ROLLBACK");
	                    $smarty->assign("mb_title", $arrLang["Validation Error"]);
       	                $smarty->assign("mb_message", $oCamp->errMsg);
	                }
           	    } else {
                    $smarty->assign("mb_title", $arrLang["Validation Error"]);
           	        $smarty->assign("mb_message", $oCamp->errMsg);
                }
            } else {
                $id_campaign = $oCamp->UpdateCampaign(
                                       $_GET['id'],
               	                       date('Y-m-d', $iFechaIni),
                      	               $time_ini,
                             	       $time_fin,
	                                   $_POST['message'],
	                                   $_POST['clid'],
	                                   $_POST['TRUNK']);
            }

            $pDB->genQuery("SET autocommit=1");
        }

    }

    if (($oCamp->errMsg != "") || ($strErrorMsg != "") || $error) {
        $arrData = $_POST;

        if ($_GET['action']=='view') {
            $contenidoModulo = viewCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
        } else {
            $contenidoModulo = nuevaCampaign($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData);
        }
    } else {
        $contenidoModulo= listaCampaign($pDB , $smarty, $module_name, $local_templates_dir);
    }

    return $contenidoModulo;
}

//Actualiza una campaña y pasa a la lista de campañas
function updateCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements) {
    return saveCampaign($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, true);
}

//Mustra la lista de campañas actuales según el filtro seleccionado
function listaCampaign($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;
    global $arrLan;
    $arrData = '';
    $oCampaign = new IXXSMSCampaign($pDB);

    // preguntando por el estado del filtro
    if (!isset($_POST['cbo_estado']) || $_POST['cbo_estado']=="") {
        $_POST['cbo_estado'] = "A";
    }

    // para el pagineo
    $limit = 50;
    $offset = 0;

    if( isset($_GET['cbo_estado']) ) {
        $url = construirURL()."&cbo_estado={$_GET['cbo_estado']}";
    } else {
        $url = construirURL()."&cbo_estado={$_POST['cbo_estado']}";
    }
    $smarty->assign("url", $url);

    if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
    } 

    $arrCampaign = $oCampaign->getCampaigns(null,null, "", $_POST['cbo_estado']);
    $total = count($arrCampaign);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalCampaigns  = count($arrCampaign);
        // Mejorar el sgte. bloque.
        if(($totalCampaigns%$limit)==0) {
            $offset = $totalCampaigns - $limit;
        } else {
            $offset = $totalCampaigns - $totalCampaigns%$limit;
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

    if(isset($_GET['cbo_estado'])) {
        $_POST['cbo_estado'] = $_GET['cbo_estado'];
    }

    $arrCampaign = $oCampaign->getCampaigns($limit, $offset, NULL, $_POST['cbo_estado']);

    $end = count($arrCampaign);

    $arrData = array();

    if (is_array($arrCampaign)) {
        foreach($arrCampaign as $campaign) {
            $arrTmp    = array();

            $arrTmp[0] = $campaign['name'];
            $arrTmp[1] = traducirFechaNumerica($campaign['start_date']);
            $arrTmp[2] = $campaign['start_time'];
            $arrTmp[3] = $campaign['end_time'];
            $arrTmp[4] = $campaign['messages'];

	     if ($arrTmp[4] == 0) {
	            $arrTmp[5] = 0;
	     } else {
	            $arrTmp[5] = ($campaign['completed'] / $arrTmp[4]) * 100;
	     }

	     $arrTmp[5] = number_format($arrTmp[5],2,',','.')." %";

        if ($campaign['code'] != 0) {
            $arrTmp[6] = $arrLan['STATUSC'.$campaign['status']]." (".$campaign['code_desc'].")";
        } else {
            $arrTmp[6] = $arrLan['STATUSC'.$campaign['status']];
        }

            $arrTmp[7] = "<a href=\"?menu=ixx_sms_campaign&action=viewnumbers&id=".$campaign['campaign']."\">".$arrLan["Numbers"]."</a>";

	     //if ($campaign['status'] != 'F') {
	         $arrTmp[7] .= "&nbsp;&nbsp;<a href=\"?menu=ixx_sms_campaign&action=view&id=".$campaign['campaign']."\">".$arrLan["Show"]."</a>";
            //}

	     if (($campaign['status'] == 'P') || ($campaign['status'] == 'E') || ($campaign['status'] == 'N')) {
 		  $arrTmp[7] .= "&nbsp;&nbsp;<a href=\"?menu=ixx_sms_campaign&action=stop&id=".$campaign['campaign']."\">".$arrLan["Stop"]."</a>";
            }

	     if ($campaign['status'] == 'S') {
 		  $arrTmp[7] .= "&nbsp;&nbsp;<a href=\"?menu=ixx_sms_campaign&action=start&id=".$campaign['campaign']."\">".$arrLan["Resume"]."</a>";
            }

            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title" => $arrLan["Campaigns List"],
	 "autoSize" => true,
        "icon"     => "images/list.png",
        "width"    => "100%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => $arrLan["Name Campaign"],
                                       "property1" => ""),
                            1 => array("name"      => $arrLan["Start"], 
                                       "width" => "120px",
                                       "property1" => ""),
                            2 => array("name"      => $arrLan["Start Time"], 
                                       "width" => "80px",
                                       "property1" => ""),
                            3 => array("name"      => $arrLan["End Time"],
                                       "width" => "80px",
                                       "property1" => ""),
                            4 => array("name"      => $arrLan["Messages"],
                                       "align" => "center",
                                       "width" => "70px",
                                       "property1" => ""),
                            5 => array("name"      => $arrLan["Completed"], 
                                       "width" => "70px",
                                       "align" => "right",
                                       "property1" => ""),
                            6 => array("name"     => $arrLan["Status"], 
                                       "width" => "90px",
                                       "property1" => ""),
                            7 => array("name"     => $arrLan["Options"], 
                                       "width" => "90px",
                                       "property1" => ""),
                           )
    );


    $estadostmp = array("all"=>$arrLan["All"], "E"=>$arrLan["STATUSCE"], "F"=>$arrLan["STATUSCF"], "P"=>$arrLan["STATUSCP"],"N"=>$arrLan["STATUSCN"], "S"=>$arrLan["STATUSCS"]);
    asort($estadostmp);
    $estados = array_merge(array("all"=>$arrLan["All"]),$estadostmp);
    $combo_estados = "<select name='cbo_estado' id='cbo_estado' onChange='submit();'>".combo($estados,$_POST['cbo_estado'])."</select>";

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_create_campaign' value='{$arrLan['Create New Campaign']}' class='button'></td>".
              "<td class='letra12' align='right'>".$arrLan["Status"]."&nbsp;$combo_estados</td>".
              "</tr></table>".
              "</form>");

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

?>
