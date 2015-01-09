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
include_once("/var/www/html/libs/paloSantoDB.class.php");

class IXXSMSList
{
    var $_DB; 
    var $errMsg;

    function IXXSMSList(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }
    
    function getLists($limit, $offset, $id_list = NULL,$estatus='all')
    {
        global $arrLang;
        global $arrLan;
        $where = "";

        $this->errMsg = "";

        $arr_result = FALSE;
        if (!is_null($id_list) && !ereg('^[[:digit:]]+$', "$id_list")) {
            $this->errMsg = $arrLan["Campaign ID is not valid"];
        } 
        else {
            if ($where=="") {
                $where = (is_null($id_list) ? '' : " WHERE list = $id_list");
            } else {
                $where =  $where." ".(is_null($id_list) ? '' : " and list = $id_list");
            }
            
            $sPeticionSQL = "SELECT *, (select count(*) from list_numbers ln where ln.list = l.list) as num FROM list l $where";

            if (!is_null($limit)) {
                $sPeticionSQL .= " LIMIT $limit OFFSET $offset";
            }

            $arr_result =& $this->_DB->fetchTable($sPeticionSQL, true);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }
        return $arr_result;
    }

    function getListsNumbers($limit, $offset, $id_list = NULL, $filter="")
    {
        global $arrLang;
        global $arrLan;
        $where = "";
        $this->errMsg = "";

        $arr_result = FALSE;
        if (!is_null($id_list) && !ereg('^[[:digit:]]+$', "$id_list")) {
            $this->errMsg = $arrLan["List ID is not valid"];
        } 
        else {
            if ($where=="") {
                $where = (is_null($id_list) ? '' : " WHERE list = $id_list");
            } else {
                $where =  $where." ".(is_null($id_list) ? '' : " and list = $id_list");
            }

            if ($filter!="") {
        		$where .= " and number = '$filter'";
            }

            $sPeticionSQL = "SELECT * FROM list_numbers ".$where;

            if ($limit != '') {
                $sPeticionSQL .= " LIMIT $limit OFFSET $offset";
            }

            $arr_result =& $this->_DB->fetchTable($sPeticionSQL, true);
            if (!is_array($arr_result)) {
                $arr_result = FALSE;
                $this->errMsg = $this->_DB->errMsg;
            }
        }

        return $arr_result;
    }

    function createEmptyList($sNombre) {
        global $arrLang;
        global $arrLan;
        $id_list = NULL;
        $bExito = FALSE;

        $sNombre = trim($sNombre);

        $this->errMsg = "";
        $id_list = null;

        // Verificar que el nombre de la lista es �nico
        $recordset =& $this->_DB->fetchTable("SELECT * FROM list WHERE name  = ".paloDB::DBCAMPO($sNombre));
        if (is_array($recordset) && count($recordset) > 0) {
            // Ya existe una lista duplicada
            $this->errMsg = $arrLan["Name List already exists"];
        } else {
            // Construir y ejecutar la orden de inserci� SQL
            $sPeticionSQL = paloDB::construirInsert(
                "list",
                array(
                    "name"  =>  paloDB::DBCAMPO($sNombre),
                )
            );

            $result = $this->_DB->genQuery($sPeticionSQL);
        	 if ($result) {
                // Leer el ID insertado por la operaci�n
                $sPeticionSQL = 'SELECT MAX(list) FROM list WHERE name = '.paloDB::DBCAMPO($sNombre);
                $tupla =& $this->_DB->getFirstRowQuery($sPeticionSQL);
                if (!is_array($tupla)) {
                    $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                } else {
                    $id_list = (int)$tupla[0];
                    $bExito = TRUE;
                }
            } else {
                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
            }
        }

    	return $id_list;
    }

    function updateList($list,$sFilePath)
    {
        global $arrLang;
        global $arrLan;
        $id_list = NULL;
        $bExito = FALSE;

        $this->errMsg = "";

        $list= trim($list);

    	 if ($sFilePath != '') {
	    	 $this->addListNumbersFromFile($list,$sFilePath);
	     } else {
	    	 $this->addListNumbersFromCDR($list);
	     }
    }

    function removeList($list)
    {
        $this->errMsg = "";

        $this->_DB->genQuery("SET autocommit=0");

        $sql = "delete from list where list = $list";

        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
   	      $this->_DB->genQuery("ROLLBACK");
	      return FALSE;
        }

        $sql = "delete from list_numbers where list = $list";

        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
   	      $this->_DB->genQuery("ROLLBACK");
	      return FALSE;
        }

        $this->_DB->genQuery("COMMIT");

        $this->_DB->genQuery("SET autocommit=1");   	 
    }

    function incorporarNumeros($idList) {
	 //De la tabla temporal borramos los que ya est�n en la lista
	 $sql = "delete from tmp_list_numbers where number in (select l.number from list_numbers l where l.list = $idList)";
        $result = $this->_DB->genQuery($sql);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg."<br/>$sql";
   	     $this->_DB->genQuery("ROLLBACK");

	     return FALSE;
        }

        //Copiamos los numeros a la lista
        $sql = "insert into list_numbers(list,number,status) select distinct list,number,'I' from tmp_list_numbers";

        $result = $this->_DB->genQuery($sql);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg."<br/>$sql";
 	     $this->_DB->genQuery("ROLLBACK");

	     return FALSE;
        }

        //Borramos la tabla temporal
        $sql = "delete from tmp_list_numbers where list = $idList";

        $result = $this->_DB->genQuery($sql);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg."<br/>$sql";
   	     $this->_DB->genQuery("ROLLBACK");

	     return FALSE;
        }

    }
    
    function addListNumbersFromCDR($idList) {
        //Obtenemos configuraci�n
        $sql = "select * from config";

   		$config = $this->_DB->fetchTable($sql,true);
   		if (count($config) == 0) {
   			return FALSE;
   		}

        //Construimos el where
        $where = "";

        $prefixes = split("\r\n",$config[0]['mobile_prefixes']);
        foreach($prefixes as $prefix) {
            if ($where != "") {
                $where .= " or ";
            }

            $where .= "src like '$prefix%'";
        }

        //Construimos el select
        $select = "";

        foreach($prefixes as $prefix) {
            if ($select != "") {
                $select .= ",if(";
            }

            if (strlen($prefix) > 1) {
                $select .= "mid(trim(src),1,".strlen($prefix).")='$prefix',mid(trim(src),".strlen($prefix).")";
            } else {
                $select .= "mid(trim(src),1,".strlen($prefix).")='$prefix',trim(src)";
            }
        }

        if ($select != "") {
            $select .= ",trim(src)";
        }

        for($i=1;$i<count($prefixes);$i++) {
            $select .= ")";
        }

        $this->_DB->genQuery("SET autocommit=0");

	    //Ponemos los n�meros en una tabla temporal
    	$sql =  "insert into tmp_list_numbers(list,number) "
	    	."select distinct $idList,if($select) number "
	    	."from asteriskcdrdb.cdr "
	    	."where ($where) and length(trim(src)) >= ".$config[0]['min_mobile_length']." and length(trim(src)) <= ".$config[0]['max_mobile_length'];

            $result = $this->_DB->genQuery($sql);
            if (!$result) {
                $this->errMsg = $this->_DB->errMsg."<br/>$sql";
   	         $this->_DB->genQuery("ROLLBACK");
    
	         return FALSE;
        }

        $this->incorporarNumeros($idList);

        $this->_DB->genQuery("COMMIT");
        $this->_DB->genQuery("SET autocommit=1");

        return TRUE;
    }

    function addListNumbersFromFile($idList, $sFilePath)
    {
    	$bExito = FALSE;

    	$listaNumeros = $this->parseListNumbers($sFilePath); 

    	if (is_array($listaNumeros)) {
    		$bExito = $this->addListNumbers($idList, $listaNumeros);
    	}

    	return $bExito;
    }
    
    function parseListNumbers($sFilePath)
    {
       global $arrLang;
       global $arrLan;

    	$listaNumeros = NULL;
    	
    	$hArchivo = fopen($sFilePath, 'rt');
    	if (!$hArchivo) {
    		$this->errMsg = $arrLan["Invalid CSV File"];//'No se puede abrir archivo especificado para leer CSV';
    	} else {
		$separador = "";
		while ((!$leido) && ($separador != ";")) {
	    		$iNumLinea = 0;
	    		$listaNumeros = array();
    			$clavesColumnas = array();

			if ($separador == "") {
				$separador = ",";
			} else {
				if ($separador == ",") {
					$separador = ";";
				}
			}

			$leido = true;
    			while ($tupla = fgetcsv($hArchivo, 2048,$separador)) {
    				$iNumLinea++;
	                        $tupla[0] = trim($tupla[0]);
    				if (count($tupla) == 1 && trim($tupla[0]) == '') {
    					// Línea vacía
	    			} elseif ($tupla[0]{0} == '#') {
    					// Línea que empieza por numeral
    				} elseif (!ereg('^[[:digit:]#*]+$', $tupla[0])) {
                     	           if ($iNumLinea == 1) {
                            	        // Podría ser una cabecera de nombres de columnas
                                   	 array_shift($tupla);
	                                    $clavesColumnas = $tupla;
       	                         } else {
              	                      // Teléfono no es numérico
                     	               $this->errMsg = $arrLan["Invalid CSV File Line"]." "."$iNumLinea: ".$arrLan["Invalid number"];
                            	        $leido = false;
						 break;
	                                }
    				} else {
       	             // Como efecto colateral, $tupla pierde su primer elemento
    					$tuplaLista = array('__PHONE_NUMBER' => array_shift($tupla));

	                    // Asignar atributos de la tupla
    					for ($i = 0; $i < count($tupla); $i++) {
              	          // Si alguna fila tiene más elementos que la lista inicial de nombres, el resto de columnas tiene números
    					    $sClave = "$i";
    					    if ($i < count($clavesColumnas) && $clavesColumnas[$i] != '') $sClave = $clavesColumnas[$i];    				    
	    				    $tuplaLista[$sClave] = $tupla[$i];
    					}
  						$listaNumeros[] = $tuplaLista;
    				}
	    		}

			if (!$leido) {
				$listaNumeros = NULL;
				$this->errMsg = "";

				if ($separador != ";") {
					fclose($hArchivo);
					$hArchivo = fopen($sFilePath, 'rt');
				}
			}
		}

    		fclose($hArchivo);
    	}

	$listaNumeros2 = array();

	foreach($listaNumeros as $numero) {
		$valores = array_values($numero);

		if ((!isset($numero['__MESSAGE'])) && (count($valores) > 1)) {
			$numero['__MESSAGE'] = $valores[1];
		}

		$listaNumeros2[] = $numero;
	}

    	return $listaNumeros2;
    }
    
    function addListNumbers($idList, $listaNumeros) {
        global $arrLan;
    	$bExito = FALSE;
    	
      	$bContinuar = TRUE;
       	$listaValidada = array(); // Se usa copia porque tupla se modifica en validación
        	
       	// Verificar si todos los elementos son de max. 4 parametros y son
       	// todos numéricos o NULL
       	if ($bContinuar) {
       		foreach ($listaNumeros as $tuplaNumero) {
                if (!isset($tuplaNumero['__PHONE_NUMBER'])) {
       				$this->errMsg = $arrLan["Element without phone number"];//"Encontrado elemento sin número telefónico";
       				$bContinuar = FALSE;
                } elseif (!ereg('^[[:digit:]#*]+$', $tuplaNumero['__PHONE_NUMBER'])) {
       				$this->errMsg = $arrLan["Invalid number"];
       				$bContinuar = FALSE;
                } else {
       				if ($bContinuar) $listaValidada[] = $tuplaNumero;
                }
      			if (!$bContinuar) break;                       			
       		}
       	}
        	
       	if ($bContinuar) {
   			foreach ($listaValidada as $tuplaNumero) {
                $numero = $tuplaNumero['__PHONE_NUMBER'];

                $campos = array('list' => $idList,'number' => paloDB::DBCAMPO($numero));
                $sPeticionSQL = paloDB::construirInsert("tmp_list_numbers", $campos);
                $result = $this->_DB->genQuery($sPeticionSQL);
		        if (!$result) {
		 	        $bContinuar = FALSE;
			        $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
			        break;
		        }
    
                $bExito = $bContinuar;
		    }
       	}

	    if ($bContinuar) {
		    $this->incorporarNumeros($idList);
	    }
    	
   	    return $bExito;
    }

    function includeNumber($list,$number) {
    	$sql = "update list_numbers set status='I' where list=$list and number='$number'";
    	$result = $this->_DB->genQuery($sql);
    }

    function excludeNumber($list,$number) {
    	$sql = "update list_numbers set status='E' where list=$list and number='$number'";
    	$result = $this->_DB->genQuery($sql);
    }

    function addNumber($list,$number) {
        $this->_DB->genQuery("SET autocommit=0");

        $sql = "insert into tmp_list_numbers(list,number) values($list,$number)";
        $result = $this->_DB->genQuery($sql);
        if (!$result) {            
	        $this->_DB->genQuery("ROLLBACK");
    		$this->errMsg = $this->_DB->errMsg."<br/>$sql";		
    		return false;
        } else {
    		 $this->incorporarNumeros($list);
	    	 $this->_DB->genQuery("COMMIT");
             $this->_DB->genQuery("SET autocommit=1");
        }
    
        return true;
    }
}
?>
