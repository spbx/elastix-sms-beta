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

class IXXSMSBASHProvider {
	function sendToCarrier($script,$clid,$destination,$text,$unicode) {
        if (file_exists("/var/www/html/libs/sms/scripts/$script")) {
            if (((fileperms("/var/www/html/libs/sms/scripts/$script") & 511) & 73)) {
                exec("/var/www/html/libs/sms/scripts/$script \"$clid\" \"$destination\" \"".str_replace("\"","\\\"",$text)."\" \"$unicode\"",$out,$ret);

                $codes = preg_split("/;/", $out[0]);

                $codes[2] = ($codes[2]==1);
                $codes[3] = ($codes[3]==1);

                return $codes;
            } else {
                return array(-6,"Script hasn't right permissions",0,true,false);
            }
        } else {
            return array(-5,"Script doesn't exists",0,true,false);
        }
	}	

	function send($clid,$destination,$text,$trunk,$unicode) {
		$ret = $this->sendToCarrier($trunk['script'],$clid,$destination,$text,$unicode);

		return array($ret[0],$ret[1],0,$ret[2],$ret[3]);
	}
}

?>
