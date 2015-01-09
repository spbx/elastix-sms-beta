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
require_once 'AbstractProcess.class.php';
require_once 'DB.php';
require_once '/var/www/html/libs/sms/IXXSMS.class.php';
require_once '/var/www/html/libs/paloSantoConfig.class.php';
require_once '/var/www/html/libs/paloSantoDB.class.php';

class IXXMassiveSMS extends AbstractProcess
{
    private $oMainLog;      // Log abierto por framework de demonio
    private $_dbConn; 
    private $process;     
    private $pDBSMS;

    var $limit = 5;
    var $DEBUG = FALSE;
    var $REPORTAR_TODO = FALSE;
    var $_iUltimoDebug = NULL;

    function inicioPostDemonio($infoConfig, &$oMainLog, $proceso)
    {
        $bContinuar = TRUE;

	$this->process = $proceso;

        // Guardar referencias al log del programa
        $this->oMainLog =& $oMainLog;
        
        $this->_iUltimoDebug = time();

        //Conectamos con la base de datos
        $bContinuar = $this->iniciarConexionBaseDatos();

        return $bContinuar;
    }

    function procedimientoDemonio()
    {
        $this->SMS();

        return TRUE;
    }

    function limpiezaDemonio($signum)
    {
    }

    private function iniciarConexionBaseDatos()
    {
    	//Obtenemos cadena de conexión
        $pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
        $arrConfig = $pConfig->leer_configuracion(false);

	    $dsn = $arrConfig['AMPDBENGINE']['valor']."://".$arrConfig['AMPDBUSER']['valor'].":".$arrConfig['AMPDBPASS']['valor']."@".$arrConfig['AMPDBHOST']['valor']."/massive_sms";

    	//Obrenemos objeto de bd
        $this->pDBSMS = new paloDB($dsn);

	    //Nos conectamos
        $dbConn =  DB::connect($dsn);
        if (DB::isError($dbConn)) {
            $this->oMainLog->output("FATAL: no se puede conectar a DB - ".($dbConn->getMessage()));
            return FALSE;
        } else {
            $dbConn->setOption('autofree', TRUE);
            $this->_dbConn = $dbConn;
            return TRUE;
        }
    } 

    function SMS() {
    	$this->marcarCampaignEnEjecucion();

        //Obtenemos un grupo de telefonos y mandamos el mensaje
        $telefonos = $this->grupoTelefonos();
	    if (count($telefonos) > 0) {
	    	foreach($telefonos as $telefono) {
	    		$sms = new IXXSMS($this->pDBSMS);

	    		$res = $sms->send($telefono['src'],$telefono['clid'],$telefono['number'],"",$telefono['message'],false,$telefono['trunk']);
	    		if ($res[0] == 0) {
	    		    //Enviado correctamente
	    		    $this->actualizarEstadoNumero($telefono['id'],'F',$res[0],$res[1]);
	            } elseif ($res[3]) {
	    		    //Con este error paramos la campaña
	    		    $this->stopCampaign($telefono['campaign'],$res[0],$res[1]);

                    break;
                } elseif ($res[4]) {
	    		    //Con este error el numero no es valido
	    		    $this->actualizarEstadoNumero($telefono['id'],'I',$res[0],$res[1]);
               	} else {
	    		    $this->actualizarEstadoNumero($telefono['id'],'N',$res[0],$res[1]);
                }
           	}
    	} else {
	    	sleep(60);
    	}		
    }

    function stopCampaign($campaign,$code="",$code_desc="")
    {
        $this->errMsg = "";

    	if ($code != "") {
	        $sql = "update campaign set status = 'S', code=$code, code_desc='$code_desc' where campaign = $campaign and (status = 'P' or status = 'E' or status = 'N')";
	    } else {
	        $sql = "update campaign set status = 'S' where campaign = $campaign and (status = 'P' or status = 'E' or status = 'N')";
    	}

        $result = $this->_dbConn->query($sql);
        if (DB::isError($result)) {
            $this->oMainLog->output("ERR: no se puede actualizar la campaña a estado STOP ".$result->getMessage());
	    return  false;
        }

    	return true;
    }

    function marcarCampaignEnEjecucion() {
            //Marcamos campañas en ejecucion
	    $result = $this->_dbConn->query(
	      "update campaign c, campaign_numbers cn set c.status = 'E', cn.status = 'P', cn.last = now(), cn.process = null
		where (now() >= c.start_date) and
		      (c.start_time <= now()) and
		      (now() <= c.end_time) and
                    ((c.status = 'P') or (c.status = 'N') or (c.status = 'E')) and
		      ((cn.status = 'P') or (cn.status = 'N') or (((cn.process = ?) or (cn.process is null)) and (cn.status = 'B') and (now() > addtime(cn.last,'0 0:4:0')))) and
		      (c.campaign = cn.campaign)",
		array($this->process));

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar campañas en ejecución ".$result->getMessage());
		return false;
	    }

            //Marcamos campañas que estaban en ejecucion y que ya no lo tienen que estar, porque han pasado su hora de
            //ejecución
	    $result = $this->_dbConn->query(
	      "update campaign c set c.status = 'P'
		where (now() >= c.start_date) and
		      ((c.start_time > now()) or
		      (now() > c.end_time)) and
		      (c.status = 'E')",
		array());

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar campañas en ejecución ".$result->getMessage());
		return false;
	    }

            //Marcamos campñas que deben estar finalizadas          
	    $result = $this->_dbConn->query(
	      "update campaign c set c.status = 'F'
		where (c.campaign <> 1) and (c.status = 'E') and c.campaign not in (select distinct cn.campaign from campaign_numbers cn where cn.status = 'P' or cn.status = 'E' or cn.status = 'B' or cn.status='N')",
		array());

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar campañas en finalizadas ".$result->getMessage());
		return false;
	    }

            //Marcamos colas que deben estar pendientes
	    $result = $this->_dbConn->query(
	      "update campaign c set c.status = 'P'
		where (c.campaign = 1) and (c.status = 'E') and c.campaign not in (select distinct cn.campaign from campaign_numbers cn where cn.status = 'P' or cn.status = 'E' or cn.status = 'B' or cn.status='N')",
		array());

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar campañas en finalizadas ".$result->getMessage());
		return false;
	    }

	    return true;
    }

    function grupoTelefonos() {
	    $telefonos = array();

            //Reservamos un grupo de telefonos
	    $result = $this->_dbConn->query(
	      "update campaign_numbers cn set cn.status = 'B', cn.process = ?, cn.last = now()
		where (cn.status = 'P') limit ?",
		array($this->process,$this->limit));

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede reservar grupo de telefonos ".$result->getMessage());
		return array();
	    } else {
		 //Obtenemos nuestros números de teléfono
	         $result = $this->_dbConn->query(
	      		"select cn.src, c.clid, c.campaign, cn.id,cn.number,c.message mc, cn.message mn, cn.trunk trunk, t.clid tclid 
				from campaign_numbers cn
				inner join campaign c on cn.campaign = c.campaign
				left join sms_trunk t on t.id = cn.trunk
				where (cn.status = 'B') and
                                  (cn.process = ?) and
                                  (c.status = 'E')",
				array($this->process));

		 if (DB::isError($result)) {
			$this->oMainLog->output("ERR: no se puede leer grupo de telefonos ".$result->getMessage());
			return array();
	         } else {
	                while ($telefono  = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
				if ($telefono->mn != "") {
	                                $message = $telefono->mn;
				} else {
        	                        $message = $telefono->mc;				
                   		}

				$telefonos[] = array("src"=>$telefono->src,"clid"=>($telefono->tclid!=""?$telefono->tclid:$telefono->clid),"campaign"=>$telefono->campaign,
						     "id"=>$telefono->id,"number"=>$telefono->number,"message"=>$message,
						     "trunk"=>$telefono->trunk);
        		}
		  }
            } 

	    return $telefonos;
    }

    function  actualizarEstadoNumero($id,$status,$code,$code_desc="") {
            //Reservamos un grupo de telefonos
	    $result = $this->_dbConn->query(
	      "update campaign_numbers cn set cn.status = ?, cn.code = ?, cn.process = null, cn.last = now(), cn.code_desc=?
		where (cn.id = ?)",
		array($status,$code,$code_desc,$id));

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar el estado del número ".$result->getMessage());
		return false;
	    } else {
		return true;
	    }
    }

    function  actualizarEstadoCampanya($id,$status,$code) {
           //Reservamos un grupo de telefonos
	    $result = $this->_dbConn->query(
	      "update campaign c set c.status = ?, c.code = ? 
		where (c.campaign = ?)",
		array($status,$code,$id));

	    if (DB::isError($result)) {
		$this->oMainLog->output("ERR: no se puede actualizar el estado de la campaña ".$result->getMessage());

		return false;
	    } else {
		if ($status == 'N') {
		    $result = $this->_dbConn->query(
		      "update campaign_numbers cn set cn.status = ?, cn.code = ?
			where (((cn.status = 'N') or (cn.status = 'B') or (cn.status = 'P')) and (cn.campaign = ?))",
			array($status,$code,$id));

		    if (DB::isError($result)) {
			$this->oMainLog->output("ERR: no se puede actualizar el estado de los números de la campaña ".$result->getMessage());
			return false;
		    } else {
			return true;
		    }
		}
	    }

	    return true;
    }
}
?>
