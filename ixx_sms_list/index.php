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
    require_once "libs/misc.lib.php";
    require_once "libs/IXXSMSList.class.php";

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

    //Leemos configuración de conexión a la base de datos de listas y nos conectamos
    $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
    $arrConfig = $pConfig->leer_configuracion(false);

    $dsn = $arrConfig['AMPDBENGINE']['valor']."://asteriskuser:".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";
    $pDBList = new paloDB($dsn);

    if(isset($_GET['exportcsv']) && $_GET['exportcsv']=='yes') {
        $limit = "";
        $offset = 0;

        $field_name = $_GET['field_name'];
        $field_pattern = $_GET['field_pattern'];
        $status = $_GET['status'];
        header("Cache-Control: private");
        header("Pragma: cache");
        header('Content-Type: application/octec-stream');
        header('Content-disposition: inline; filename="cdrreport.csv"');
        header('Content-Type: application/force-download');
    } else {
        $arrFormElements = array(
			         "nombre"=> array(
					                "LABEL"                  => $arrLan["Name List"],
					                "EDITABLE"               => "no",
					                "REQUIRED"               => "yes",
					                "INPUT_TYPE"             => "TEXT",
					                "INPUT_EXTRA_PARAM"      => array("size"=>"72"),
					                "VALIDATION_TYPE"        => "text",
					                "VALIDATION_EXTRA_PARAM" => ""),
       		         "cdr"=> array(   
							  "LABEL"                 => $arrLan["Crear desde CDR"],
	             	                              "REQUIRED"              => "no",
       	                    	                "INPUT_TYPE"            => "CHECKBOX",
                                   	         "INPUT_EXTRA_PARAM"     => array("id"=>"cdr"),
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
        $smarty->assign("PHONE_FILE", $arrLan["Archivo de Numeros"]);
	 $smarty->assign("CONFIRM_DELETE", $arrLan["Are you sure you wish to delete list?"]);
    }
    if (isset($_POST['submit_create_list'])) {
        $content= nuevaList($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements);
    } elseif (isset($_POST['submit_list_Add'])) {
 	 $content = addNumberToList($pDBList , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['submit_list_lists'])) {
 	 $content= listaList($pDBList , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['save'])) {
        $content = saveList($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } elseif (isset($_POST['cancel'])) {
        $content= listaList($pDBList , $smarty, $module_name, $local_templates_dir);
    } elseif (isset($_POST['apply_changes'])) {
        $content = updateList($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements);
    } else if (isset($_GET['id']) && isset($_POST['delete'])) {
        $content = removeList($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="viewnumbers") {
        $content = viewListNumbers($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="include") {
        $content = includeExclude($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id'],$_GET['number'],'include');
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="exclude") {
        $content = includeExclude($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id'],$_GET['number'],'exclude');
    } else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action']=="view") {
        $content = viewList($pDBList , $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
    } else {
 	 $content= listaList($pDBList , $smarty, $module_name, $local_templates_dir);
    }

    return $content;
}

//Muestra el formulario para crear una lista
function nuevaList($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData = null) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    if ($arrData == null) {
        $arrData = array();
    }

    $oForm = new paloForm($smarty, $arrFormElements);

    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrData);
    return $htmlForm;
}

//Muestra el formulario para editar una lista
function viewList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_list) {
    require_once "modules/$module_name/configs/default.config.php";

    global $arrLan;

    $oForm = new paloForm($smarty, $arrFormElements);

    $oCamp = new IXXSMSList($pDB);
    $arrData = $oCamp->getLists(null,null,$id_list);

    $arrTmp['nombre'] = $arrData[0]['name'];

    $oForm->setEditMode();
    $htmlForm= $oForm->fetchForm("$local_templates_dir/new.tpl", $arrLan['Title'], $arrTmp);
    return $htmlForm;
}

function includeExclude($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_list, $number, $type) {
       $oList = new IXXSMSList($pDB);

	if ($type == 'include') {
		$oList->includeNumber($id_list,$number);
	}

	if ($type == 'exclude') {
		$oList->excludeNumber($id_list,$number);
	}

	$content = viewListNumbers($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_list);

	return $content;
}

//Muestra la lista de números asignados a una lista y su estado
function viewListNumbers($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_list) {
    global $arrLang;
    global $arrLan;
    $arrData = '';
    $oList = new IXXSMSList($pDB);

    // para el pagineo
    if(!isset($_GET['exportcsv']) || $_GET['exportcsv']=='no') {
	    $limit = 25;
	    $offset = 0;
    }

    $filter = (isset($_REQUEST['filter'])?$_REQUEST['filter']:"");
    $arrList = $oList->getListsNumbers(null,null,$id_list,$filter);

    $total = count($arrList);

    $url = construirURL();
    $smarty->assign("url", $url);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalLists  = count($arrList);
        // Mejorar el sgte. bloque.
        if(($totalLists%$limit)==0) {
            $offset = $totalLists - $limit;
        } else {
            $offset = $totalLists - $totalLists%$limit;
        }
    }

    $start = (isset($_GET['start'])?$_GET['start']:"");

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="next") {
        $offset = $_GET['start'] + $limit - 1;
    }

    // Si se quiere retroceder
    if(isset($_GET['nav']) && $_GET['nav']=="previous") {
        $offset = $_GET['start'] - $limit - 1;
    }
    
    if ($offset == 0) {
    	if (isset($_GET['offset'])) {
    		$offset = $_GET['offset'];
    	}
    }

    $arrList = $oList->getListsNumbers($limit, $offset, $id_list,$filter);

    $end = count($arrList);

    $arrData = array();

    if (is_array($arrList)) {
        foreach($arrList as $List) {
            $arrTmp    = array();

            $arrTmp[0] = $List['number'];
            $arrTmp[1] = $arrLan["NumberStatus".$List['status']];

            if(!isset($_GET['exportcsv'])) {
                $arrTmp[2] = "<a href=\"?menu=ixx_sms_list&offset=$offset&start=$start&action=include&id=$id_list&number=".$List['number']."\">".$arrLan["Include"]."</a>"."&nbsp;&nbsp;<a href=\"?menu=ixx_sms_list&action=exclude&offset=$offset&start=$start&id=$id_list&number=".$List['number']."\">".$arrLan["Exclude"]."</a>";
            }

            $arrData[] = $arrTmp;
        }
    }

    if(!isset($_GET['exportcsv'])) {
        $arrGrid = array("title" => $arrLan["Lists List"],
	     "autoSize" => true,
            "icon"     => "images/list.png",
            "width"    => "100%",
            "start"    => ($total==0) ? 0 : $offset + 1,
            "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
            "total"    => $total,
            "columns"  => array(0 => array("name" => $arrLan["Number"],
                                           "property1" => ""),
	    			1 => array("name" => $arrLan["Status"],
                                           "width" => "90px",
                                           "property1" => ""),
	    			2 => array("name" => $arrLan["Options"],
                                           "width" => "90px",
                                           "property1" => ""),
                               )    
        );
    } else {
        $arrGrid = array("title" => $arrLan["Lists List"],
	     "autoSize" => true,
            "icon"     => "images/list.png",
            "width"    => "100%",
            "start"    => ($total==0) ? 0 : $offset + 1,
            "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
            "total"    => $total,
            "columns"  => array(0 => array("name" => $arrLan["Number"],
                                           "property1" => ""),
	    			1 => array("name" => $arrLan["Status"],
                                           "width" => "90px",
                                           "property1" => ""),
                               )    
        );
    }

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=ixx_sms_list&action=viewnumbers&id=".$id_list."'>" .
              "<table width='100%' border='0'><tr>".
              "<td style=\"font-size:12px;\">".
		"<input type='submit' name='submit_list_lists' value='{$arrLan['List Lists']}' class='button'>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
		"{$arrLan['Add Number']}:&nbsp;&nbsp;<input type=\"text\" name=\"addNumber\" value=\"\"  size = \"12\"/>&nbsp;&nbsp;<input type='submit' name='submit_list_Add' value='{$arrLan['Add']}' class='button'>".
		"</td>".
              "<td align=\"right\" style=\"font-size:12px;\">".
		"{$arrLan['Filter']}:&nbsp;&nbsp;<input type=\"text\" name=\"filter\" value=\"\"  size = \"12\"/>&nbsp;&nbsp;<input type='submit' name='submit_list_Lists' value='{$arrLan['Filter List']}' class='button'>".
		"</td>".
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

//Borra una lista y pasa a la lista de listas
function removeList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $id_list) {
    require_once "modules/$module_name/configs/default.config.php";

    $oCamp = new IXXSMSList($pDB);
    $arrData = $oCamp->removeList($id_list);

    $content = listaList($pDB , $smarty, $module_name, $local_templates_dir);

    return $content;
}

//Guarda una lista y pasa a la lista de listas
function saveList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $update=false) {
    global $arrLang;
    global $arrLan;

    $strErrorMsg = "";
    $oForm = new paloForm($smarty, $arrFormElements);
    $oCamp = new IXXSMSList($pDB);

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

        $pDB->genQuery("SET autocommit=0");

        if (!$update) {          
            $id_list = $oCamp->createEmptyList($_POST['nombre']);
            if (!is_null($id_list)) {
                $bExito1=false;

                if ($_POST['cdr'] == 'off') {
                    $bExito1 = $oCamp->addListNumbersFromFile($id_list, $_FILES['phonefile']['tmp_name']);
                } else {
                    $bExito1 = $oCamp->addListNumbersFromCdr($id_list);
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
            if ($_POST['cdr'] == 'off') {
                $bExito1 = $oCamp->updateList($_REQUEST['id'], $_FILES['phonefile']['tmp_name']);
            } else {
                $bExito1 = $oCamp->updateList($_REQUEST['id'],'');
            }

            if ($bExito1) {
                header("Location: ?menu=$module_name");
            } else {
                $arrData = $_POST;		
                $smarty->assign("mb_title", $arrLang["Validation Error"]);
                $smarty->assign("mb_message", $oCamp->errMsg);
            }
        }

        $pDB->genQuery("SET autocommit=1");
    }

    if (($oCamp->errMsg != "") || ($strErrorMsg != "") || $error) {
        $arrData = $_POST;

        if ((isset($_GET['action'])?$_GET['action']:"")=='view') {
            $contenidoModulo = viewList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $_GET['id']);
        } else {
            $contenidoModulo = nuevaList($smarty, $module_name, $local_templates_dir, $pDB, $arrFormElements, $arrData);
        }
    } else {
        $contenidoModulo= listaList($pDB , $smarty, $module_name, $local_templates_dir);
    }

    return $contenidoModulo;
}

//Actualiza una lista y pasa a la lista de listas
function updateList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements) {
    return saveList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, true);
}

//Mustra la lista de listas actuales según el filtro seleccionado
function listaList($pDB, $smarty, $module_name, $local_templates_dir) {
    global $arrLang;
    global $arrLan;
    $arrData = '';

    $oList = new IXXSMSList($pDB);

    // para el pagineo
    $limit = 25;
    $offset = 0;

    $arrList = $oList->getLists(null,null);

    $total = count($arrList);

    // Si se quiere avanzar a la sgte. pagina
    if(isset($_GET['nav']) && $_GET['nav']=="end") {
        $totalLists  = count($arrList);
        // Mejorar el sgte. bloque.
        if(($totalLists%$limit)==0) {
            $offset = $totalLists - $limit;
        } else {
            $offset = $totalLists - $totalLists%$limit;
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

    $arrList = $oList->getLists($limit, $offset);

    $end = count($arrList);

    $arrData = array();

    if (is_array($arrList)) {
        foreach($arrList as $List) {
            $arrTmp    = array();

            $arrTmp[0] = $List['name'];
            $arrTmp[1] = $List['num'];

            $arrTmp[2] = "<a href=\"?menu=ixx_sms_list&action=viewnumbers&id=".$List['list']."\">".$arrLan["Numbers"]."</a>";
	     $arrTmp[2] .= "&nbsp;&nbsp;<a href=\"?menu=ixx_sms_list&action=view&id=".$List['list']."\">".$arrLan["Show"]."</a>";

            $arrData[] = $arrTmp;
        }
    }

    $arrGrid = array("title" => $arrLan["Lists List"],
	 "autoSize" => true,
        "icon"     => "images/list.png",
        "width"    => "100%",
        "start"    => ($total==0) ? 0 : $offset + 1,
        "end"      => ($offset+$limit)<=$total ? $offset+$limit : $total,
        "total"    => $total,
        "columns"  => array(0 => array("name"      => $arrLan["Name List"],
                                       "property1" => ""),
                            1 => array("name"   => $arrLan["Nums Number"], 
                                       "width" => "90px",
                                       "property1" => ""),
                            2 => array("name"   => $arrLan["Options"], 
                                       "width" => "90px",
                                       "property1" => ""),
                           )
    );

    $oGrid = new paloSantoGrid($smarty);
    $oGrid->showFilter(
              "<form style='margin-bottom:0;' method='POST' action='?menu=$module_name'>" .
              "<table width='100%' border='0'><tr>".
              "<td><input type='submit' name='submit_create_list' value='{$arrLan['Create New List']}' class='button'></td>".
              "</tr></table>".
              "</form>");

    $contenidoModulo = $oGrid->fetchGrid($arrGrid, $arrData,$arrLang);
    return $contenidoModulo;
}

//Guarda una lista y pasa a la lista de listas
function addNumberToList($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $update=false) {
    global $arrLang;
    global $arrLan;

    //Validamos
    
    $arrErrores = array();
    $number = $_REQUEST['addNumber'];

    if (!$number != "") {
	$arrErrores[$arrLan['Add Number'].": ".$arrLan['Invalid number']] = '';
    }

    if (!is_numeric($number)) {
	$arrErrores[$arrLan['Add Number'].": ".$arrLan['Invalid number']] = '';
    }

    if(count($arrErrores) > 0) {
        $arrData = $_POST;

        $smarty->assign("mb_title", $arrLang["Validation Error"]);
	 $strErrorMsg = "";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v) {
		  if ($strErrorMsg <> "") {
			$strErrorMsg .= "<br> ";
		  }
                $strErrorMsg .= $k;
            }
        }
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>".$strErrorMsg;
        $smarty->assign("mb_message", $strErrorMsg);
    }else{
        $oCamp = new IXXSMSList($pDB);
        $error = false;

	 $bExito = $oCamp->addNumber($_REQUEST['id'],$number);

        if (!$bExito) {
	      $arrData = $_POST;		
	      $smarty->assign("mb_title", $arrLang["Validation Error"]);
             $smarty->assign("mb_message", $oCamp->errMsg);
	 }
    }

    $contenidoModulo = viewListNumbers($pDB, $smarty, $module_name, $local_templates_dir, $arrFormElements, $_REQUEST['id']);


    return $contenidoModulo;
}
?>
