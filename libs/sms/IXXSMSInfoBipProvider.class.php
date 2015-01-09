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

require_once "/var/www/html/libs/paloSantoConfig.class.php";
require_once "/var/www/html/libs/paloSantoDB.class.php";

class IXXSMSInfoBipProvider {  
    var $OK = 0;
	var $NOT_ENOUGHCREDITS = -2;
	var $NETWORK_NOTCOVERED = -3;
	var $INVALID_USER_OR_PASS = -5;
	var $MISSING_DESTINATION_ADDRESS = -6;
	var $MISSING_SMSTEXT = -7;
	var $MISSING_SENDERNAME = -8;
	var $DESTADDR_INVALIDFORMAT = -9;
	var $MISSING_USERNAME = -10;
	var $MISSING_PASS = -11;

    function getMessage($code) {
        $msg = array(
                        $this->OK => 'Message delivered for sending',
                        $this->NOT_ENOUGHCREDITS  => 'Not enough credits',
                        $this->NETWORK_NOTCOVERED  => 'Network not covered',
                        $this->INVALID_USER_OR_PASS  => 'Invalid user or pass',
                        $this->MISSING_DESTINATION_ADDRESS => 'Missing destination address',
                        $this->MISSING_SMSTEXT => 'Missing SMS text',
                        $this->MISSING_SENDERNAME => 'Missing sender name',
                );

        return $msg[$code];
    }

    //Determina si el error cometido es suficiento como para parar una
    //campaña en ejecución
    function stopTrunk($error) {
        return in_array($error, array(  $this->NOT_ENOUGHCREDITS,
                                        $this->INVALID_USER_OR_PASS,
                						$this->MISSING_PASS));
    }

    //Determina si el error cometido es suficiento como para descartar
    //un número en una campaña en ejecución
    function stopMessage($error) {
        return in_array($error, array(  $this->NETWORK_NOTCOVERED,
                						$this->MISSING_DESTINATION_ADDRESS,
                						$this->MISSING_SMSTEXT,
                						$this->MISSING_SENDERNAME,
                						$this->DESTADDR_INVALIDFORMAT));
    }

	function sendToCarrier($user,$password,$clid,$destination,$text,$unicode) {
       $ch = curl_init("https://www.infobip.com/Addon/SMSService/SendSMS.aspx?user=$user&password=$password&sender=".$clid."&SMSText=".urlencode($text)."&IsFlash=0&GSM=$destination");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        $html=curl_exec($ch);
    
        curl_close($ch); 

        $html = trim($html);
        
        return $html;
    }	

	function send($clid,$destination,$text,$trunk,$unicode) {
        $messageId = "";

		$result = $this->sendToCarrier($trunk['user'],$trunk['password'],$clid,$destination,$text,$unicode);
        if ($result > 0) {
            $result = 0;
            $messageId = $result;
        }

		return array($result,$this->getMessage($result),$messageId,$this->stopTrunk($ok),$this->stopMessage($ok));
	}
}

?>
