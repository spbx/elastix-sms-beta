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
  | 08940 - CORNELLÃ€ DE LLOBREGAT                                                 |
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

class IXXSMSCampaign
{
    var $_DB; 
    var $errMsg;

    function IXXSMSCampaign(&$pDB)
    {
        // Se recibe como parÃ¡metro una referencia a una conexiÃ³n paloDB
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
    
    function normalizeText($text) {
	$text = utf8_decode($text);

	$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,ñ,ç");
	$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,n,c");

	$text = str_replace($search, $replace, $text);

	$text = utf8_encode($text);

	return $text;
    }

    function getCampaigns($limit, $offset, $id_campaign = NULL,$estatus='all')
    {
        global $arrLang;
        global $arrLan;
        $where = "";

        $this->errMsg = "";

        if($estatus=='all')
            $where .= " where 1";
        else if($estatus=='P')
            $where .= " where status='P'";
        else if($estatus=='E')
            $where .= " where status='E'";
        else if($estatus=='F')
            $where .= " where status='F'";
        else if($estatus=='S')
            $where .= " where status='S'";
        else if($estatus=='N')
            $where .= " where status='N'";

        $arr_result = FALSE;
        if (!is_null($id_campaign) && !ereg('^[[:digit:]]+$', "$id_campaign")) {
            $this->errMsg = $arrLan["Campaign ID is not valid"];
        } 
        else {
            if ($where=="") {
                $where = (is_null($id_campaign) ? '' : " WHERE campaign = $id_campaign");
            } else {
                $where =  $where." ".(is_null($id_campaign) ? '' : " and campaign = $id_campaign");
            }
            
            $sPeticionSQL = "SELECT *, "
			      ."(select count(*) from campaign_numbers cn where cn.campaign = c.campaign) as messages, "
			      ."(select count(*) from campaign_numbers cn where cn.campaign = c.campaign and cn.status = 'F') as completed "
			      ."FROM campaign c ".$where;

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

    function getCampaignsNumbers($limit, $offset, $id_campaign = NULL,$estatus='all')
    {
        global $arrLang;
        global $arrLan;
        $where = "";

        $this->errMsg = "";

        if($estatus=='all')
            $where .= " where 1";
        else if($estatus=='P')
            $where .= " where (cn.status='P')";
        else if($estatus=='E')
            $where .= " where (cn.status='E')";
        else if($estatus=='F')
            $where .= " where (cn.status='F')";
        else if($estatus=='S')
            $where .= " where (cn.status='S')";
        else if($estatus=='N')
            $where .= " where (cn.status='N')";
        else if($estatus=='I')
            $where .= " where (cn.status='I')";

        $arr_result = FALSE;
        if (!is_null($id_campaign) && !ereg('^[[:digit:]]+$', "$id_campaign")) {
            $this->errMsg = $arrLan["Campaign ID is not valid"];
        } 
        else {
            if ($where=="") {
                $where = (is_null($id_campaign) ? '' : " WHERE campaign = $id_campaign");
            } else {
                $where =  $where." ".(is_null($id_campaign) ? '' : " and campaign = $id_campaign");
            }
            
            $sPeticionSQL = "SELECT * "
			      ."FROM campaign_numbers cn ".$where;

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

    function addCampaignNumbersFromList($campaign,$list,$trunk) {
	$arr_result = TRUE;

	$sql = "insert into campaign_numbers(campaign,number,trunk) "
	      ."select $campaign, number, $trunk from list_numbers where list = $list and status='I'";

	$result = $this->_DB->genQuery($sql);
       if (!$result) {
           $arr_result = FALSE;
           $this->errMsg = $this->_DB->errMsg;
       }

	return $arr_result;
    }

    function createEmptyCampaign($sNombre, $sFechaInicial, $sHoraInicio, $sHoraFinal, $sMessage, $sClid, $paused, $trunk)
    {
        global $arrLang;
        global $arrLan;
        $id_campaign = NULL;
        $bExito = FALSE;

 	 $status = 'P';
	 if ($paused) {
		$status = 'S';
	 }

        $sNombre = trim($sNombre);
        $sHoraInicio = trim($sHoraInicio);
        $sHoraFinal = trim($sHoraFinal);

	    //$sMessage = $this->normalizeText($sMessage);

        $this->errMsg = "";

        // Verificar que el nombre de la campaña es único
        $recordset =& $this->_DB->fetchTable("SELECT * FROM campaign WHERE name  = ".paloDB::DBCAMPO($sNombre));
        if (is_array($recordset) && count($recordset) > 0) {
            // Ya existe una campaña duplicada
            $this->errMsg = $arrLan["Name Campaign already exists"];
        } else {
            // Construir y ejecutar la orden de inserció SQL
            $sPeticionSQL = paloDB::construirInsert(
                "campaign",
                array(
                    "name"           =>  paloDB::DBCAMPO($sNombre),
                    "start_date"     =>  paloDB::DBCAMPO($sFechaInicial),
                    "start_time"     =>  paloDB::DBCAMPO($sHoraInicio ),
                    "end_time"       =>  paloDB::DBCAMPO($sHoraFinal ),
                    "message"        =>  paloDB::DBCAMPO($sMessage),
                    "clid"           =>  paloDB::DBCAMPO($sClid),
                    "status"         =>  paloDB::DBCAMPO($status),
                    "trunk"          =>  ($trunk==0?"null":paloDB::DBCAMPO($trunk)),
                )
            );

            $result = $this->_DB->genQuery($sPeticionSQL);
        	 if ($result) {
                // Leer el ID insertado por la operación
                $sPeticionSQL = 'SELECT MAX(campaign) FROM campaign WHERE name = '.paloDB::DBCAMPO($sNombre);
                $tupla =& $this->_DB->getFirstRowQuery($sPeticionSQL);
                if (!is_array($tupla)) {
                    $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
                } else {
                    $id_campaign = (int)$tupla[0];
                    $bExito = TRUE;
                }
            } else {
                $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
            }
        }

        return $id_campaign;
    }

    function updateCampaign($campaign, $sFechaInicial, $sHoraInicio, $sHoraFinal, $sMessage, $sClid, $trunk)
    {
        global $arrLang;
        global $arrLan;
        $id_campaign = NULL;
        $bExito = FALSE;

        $this->errMsg = "";

        $campaign= trim($campaign);
        $sNombre = trim($sNombre);
        $sHoraInicio = trim($sHoraInicio);
        $sHoraFinal = trim($sHoraFinal);

        $sMessage = $this->normalizeText($sMessage);

        // Construir y ejecutar la orden de update SQL
        $sPeticionSQL = paloDB::construirUpdate(
            "campaign",
            array(
                "start_date"     =>  paloDB::DBCAMPO($sFechaInicial),
                "start_time"     =>  paloDB::DBCAMPO($sHoraInicio ),
                "end_time"       =>  paloDB::DBCAMPO($sHoraFinal ),
                "message"        =>  paloDB::DBCAMPO($sMessage),
                "clid"           =>  paloDB::DBCAMPO($sClid),
                "trunk"          =>  ($trunk==0?"null":paloDB::DBCAMPO($trunk)),
            ),
            " campaign = $campaign"
        );

        $result = $this->_DB->genQuery($sPeticionSQL);
        if (!$result) {
            $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        }
    }

    function removeCampaign($campaign)
    {
        $this->errMsg = "";

        $sql = "delete from campaign where campaign = $campaign";

        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        }
    }

    function stopCampaign($campaign)
    {
        $this->errMsg = "";

        $sql = "update campaign set status = 'S', code=null, code_desc = null where campaign = $campaign and (status = 'P' or status = 'E' or status = 'N')";

        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        }
    }

    function startCampaign($campaign)
    {
        $this->errMsg = "";

        $sql = "update campaign set status = 'P', code = null, code_desc = null where campaign = $campaign and status = 'S'";

        $result = $this->_DB->genQuery($sql);
        if (!$result){ 
             $this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
        }
    }

    function addCampaignNumbersFromFile($idCampaign, $sFilePath,$trunk=0)
    {
    	$bExito = FALSE;
    	
    	$listaNumeros = $this->parseCampaignNumbers($sFilePath,$trunk); 
    	if (is_array($listaNumeros)) {
    		$bExito = $this->addCampaignNumbers($idCampaign, $listaNumeros);
    	}

    	return $bExito;
    }
    
    function parseCampaignNumbers($sFilePath,$trunk=0)
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
    					// LÃ­nea vacÃ­a
	    			} elseif ($tupla[0]{0} == '#') {
    					// LÃ­nea que empieza por numeral
    				} elseif (!ereg('^[[:digit:]#*]+$', $tupla[0])) {
                     	           if ($iNumLinea == 1) {
                            	        // PodrÃ­a ser una cabecera de nombres de columnas
                                   	 array_shift($tupla);
	                                    $clavesColumnas = $tupla;
       	                         } else {
              	                      // TelÃ©fono no es numÃ©rico
                     	               $this->errMsg = $arrLan["Invalid CSV File Line"]." "."$iNumLinea: ".$arrLan["Invalid number"];
                            	        $leido = false;
						 break;
	                                }
    				} else {
       	             // Como efecto colateral, $tupla pierde su primer elemento
    					$tuplaLista = array('__PHONE_NUMBER' => array_shift($tupla));

	                    // Asignar atributos de la tupla
    					for ($i = 0; $i < count($tupla); $i++) {
              	          // Si alguna fila tiene mÃ¡s elementos que la lista inicial de nombres, el resto de columnas tiene nÃºmeros
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

		$numero['__TRUNK'] = $trunk;

		$listaNumeros2[] = $numero;
	}

    	return $listaNumeros2;
    }
    
    /**
     * Procedimiento que agrega nÃºmeros a una campaÃ±a existente. La lista de
     * nÃºmeros consiste en un arreglo de tuplas, cuyo elemento __PHONE_NUMBER
     * es el nÃºmero de telÃ©fono, y el resto de claves es el conjunto clave->valor
     * a guardar en la tabla call_attribute para cada llamada
     *
     * @param int $idCampaign   ID de CampaÃ±a
     * @param array $listaNumeros   Lista de nÃºmeros como se describe arriba
     *      array('__PHONE_NUMBER' => '1234567', 'Name' => 'Fulano de Tal', 'Address' => 'La Conchinchina')
     *
     * @return bool VERDADERO si todos los nÃºmeros fueron insertados, FALSO en error
     */
    function addCampaignNumbers($idCampaign, $listaNumeros)
    {
       global $arrLan;
    	$bExito = FALSE;
    	
    	if (!ereg('^[[:digit:]]+$', $idCampaign)) {
    		$this->errMsg = $arrLan["Invalid Campaign ID"];//'ID de campaÃ±a no es numÃ©rico';
    	} elseif (!is_array($listaNumeros)) {
    		$this->errMsg = $arrLang[""];//'Lista de nÃºmeros tiene que ser un arreglo';
    	} else {
        	$bContinuar = TRUE;
        	$listaValidada = array(); // Se usa copia porque tupla se modifica en validaciÃ³n
        	
        	// Verificar si todos los elementos son de max. 4 parametros y son
        	// todos numÃ©ricos o NULL
        	if ($bContinuar) {
        		foreach ($listaNumeros as $tuplaNumero) {
                    if (!isset($tuplaNumero['__PHONE_NUMBER'])) {
        				$this->errMsg = $arrLan["Element without phone number"];//"Encontrado elemento sin nÃºmero telefÃ³nico";
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

                        $campos = array(
				'campaign'	=>	$idCampaign,
				'number'	=>	paloDB::DBCAMPO($tuplaNumero['__PHONE_NUMBER']),
				'message'	=>	paloDB::DBCAMPO($tuplaNumero['__MESSAGE']),
				'src	'	=>	paloDB::DBCAMPO($tuplaNumero['__SRC']),
				'trunk	'	=>	paloDB::DBCAMPO($tuplaNumero['__TRUNK']),
			   );

                        $sPeticionSQL = paloDB::construirInsert("campaign_numbers", $campos);
			   $result = $this->_DB->genQuery($sPeticionSQL);
			   if (!$result) {
			 	$bContinuar = FALSE;
				$this->errMsg = $this->_DB->errMsg."<br/>$sPeticionSQL";
				break;
 			   }
    
                        $bExito = $bContinuar;
			}
        	}
    	}
    	
    	return $bExito;
    }
}
?>
