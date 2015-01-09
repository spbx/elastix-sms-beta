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

class IXXSMSSIPTrafficProvider {  
    var $OK = 0;
	var $NOT_CREDIT = 1;         
	var $ERROR = 2;              
	var $INVALID_NUMBER = 19;    
	var $INVALID_CREDENTIALS = 20;
	var $BAD_XML = 21;           

    function getMessage($code) {
        $msg = array(
                        $this->OK  => 'Message delivered for sending',
                        $this->NOT_CREDIT  => 'Sorry, you do not have enough credit to send this sms. Go to your accountpage to buy credit!',
                        $this->ERROR  => 'Error',
                        $this->INVALID_NUMBER => 'Invalid Number',
                        $this->INVALID_CREDENTIALS => 'Wrong Username/password combination',
                        $this->BAD_XML => 'Bad XML',
                );

        return $msg[$code];
    }
  
    //Determina si el error cometido es suficiento como para parar una
    //campaña en ejecución
    function stopTrunk($error) {
        return in_array($error, array(  $this->NOT_CREDIT,
                                        $this->ERROR,
                						$this->INVALID_CREDENTIALS,
                						$this->BAD_XML));
    }

    //Determina si el error cometido es suficiento como para descartar
    //un número en una campaña en ejecución
    function stopMessage($error) {
        return in_array($error, array(  $this->INVALID_NUMBER));
    }
	function sendToCarrier($user,$password,$clid,$destination,$text) {
        $ch = curl_init("https://www.siptraffic.com/myaccount/sendsms.php?username=$user&password=$password&from=$clid&to=$destination&text=".urlencode($text));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        $html=curl_exec($ch);

        curl_close($ch);

        $html = str_replace("\x09",'',$html);
        $html = str_replace("\n",'',$html);
        $html = str_replace("\r",'',$html);
        $html = str_replace('  ','',$html);	
        $html = str_replace('  ','',$html);
        $html = str_replace('> <','><',$html);

        try {
            $sxe = @new SimpleXMLElement($html);

            $val = $sxe->xpath('/SmsResponse/version');
            $resp_version = (string)$val[0];

            $val = $sxe->xpath('/SmsResponse/result');
            $resp_result = (string)$val[0];

            $val = $sxe->xpath('/SmsResponse/endcause');
            $resp_cause = (string)$val[0];

            if ($resp_cause == '') {
                $resp_cause = 2;
            }

            if ($resp_result == 0) {
                if ($resp_cause != "") {
                    return $resp_cause;
                } else {
                    return $this->INVALID_CREDENTIALS;
                }
            }

            return 0;
        } catch (Exception $e) {
            return $this->BAD_XML;
        } 
    }

	function send($clid,$destination,$text,$trunk,$unicode) {
		$ok = $this->sendToCarrier($trunk['user'],$trunk['password'],$clid,$destination,$text);

		return array($ok,$this->getMessage($ok),0,false,false);
	}
}

?>
