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

var SMS = new Array();

SMS["warning send more than one sms per message"] = "El mensaje que ha escrito se enviará con %messages% mensajes SMS, ya que supera el máximo de 160 caracteres por mensaje, o tiene que ser enviado en formato UNICODE (máximo 70 caracteres por mensaje) porque contiene caracteres especiales. ¿Seguro que desea enviarlo?.";

SMS["warning campaign is in pause"] = "Recuerde que la campaña se creará en pausa, y que para iniciar la campaña tendrá que pulsar sobre el enlace 'Reanudar'.";
SMS["warning campaign more than one sms per message"] = "Cada mensaje de esta campaña se enviará con %messages% mensajes SMS, ya que supera el máximo de 160 caracteres por mensaje, o tiene que ser enviado en formato UNICODE (máximo 70 caracteres por mensaje) porque contiene caracteres especiales. ¿Seguro que desea continuar?.";

SMS["sending"]="Enviando...";
