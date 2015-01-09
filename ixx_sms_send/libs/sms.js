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

//Convierte un valor decimal en hexadecimal
function bin2hex(s) {
    var i, f = 0,
    a = [];
    s += '';
    f = s.length;

    for (i = 0; i < f; i++) {
        a[i] = s.charCodeAt(i).toString(16).replace(/^([\da-f])$/, "0$1");    }
 
    return a.join('').toUpperCase();
}

//Esta función es la equivanlente de la misma en php
function in_array (needle, haystack, argStrict) {
    var key = '', strict = !! argStrict;

    if (strict) {
       	for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
       	    }
        }
    } else {
       	for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
       	    }
        }
    } 
    return false;
}

//Comprueba si el sms está escrito íntegramente en alfabeto sms o no
function is_sms_alphabet(str) { 
	var arr = new Array (
                            "0x40","0xA3","0x24","0xA5","0xE8","0xE9","0xF9","0xEC","0xF2","0xC7","0x0A","0xD8","0xF8","0x0D","0xC5","0xE5",
                            "0x5F","0x0C","0x5E","0x7B","0x7D","0x5C","0x5B","0x7E","0x5D","0x7C","0xC6","0xE6","0xDF","0xC9","0x20","0x21",
                            "0x22","0x23","0xA4","0x25","0x26","0x27","0x28","0x29","0x2A","0x2B","0x2C","0x2D","0x2E","0x2F","0x30","0x31",
                            "0x32","0x33","0x34","0x35","0x36","0x37","0x38","0x39","0x3A","0x3B","0x3C","0x3D","0x3E","0x3F","0xA1","0x41",
                            "0x42","0x43","0x44","0x45","0x46","0x47","0x48","0x49","0x4A","0x4B","0x4C","0x4D","0x4E","0x4F","0x50","0x51",
                            "0x52","0x53","0x54","0x55","0x56","0x57","0x58","0x59","0x5A","0xC4","0xD6","0xD1","0xDC","0xA7","0xBF","0x61",
                            "0x62","0x63","0x64","0x65","0x66","0x67","0x68","0x69","0x6A","0x6B","0x6C","0x6D","0x6E","0x6F","0x70","0x71",
                            "0x72","0x73","0x74","0x75","0x76","0x77","0x78","0x79","0x7A","0xE4","0xF6","0xF1","0xFC","0xE0",
                            "0x20AC","0x394","0x3A6","0x393","0x39B","0x3A9","0x3A0","0x3A8","0x3A3","0x398","0x39E"

                        ); 

	var i,c;

	for (i = 0;i < str.length; i++) { 
		c = '0x' + bin2hex(str.substring(i,i+1)); 
 	    if (!in_array(c,arr)) {
            return false;
        } 
	} 

    return true;
} 

//Cuenta los caracteres del mensaje y de cuántos mensajes estará formado el mensahe
function contar(form,name,count,messages) {
    var msg = document.forms[form][name].value;
    var length = msg.length;
    var numMessages = 0;

    if (is_sms_alphabet(msg)) {
        numMessages = (length>0?Math.floor(length / 160) + 1:0);
    } else {
        numMessages = (length>0?Math.floor(length / 70) + 1:0);
    }

    document.forms[form][count].value = length;
    document.forms[form][messages].value = numMessages;
}

function popup_phone_number(url_popup){
   var ancho = 600;
   var alto = 400;
   my_window = window.open(url_popup,"my_window","width="+ancho+",height="+alto+",location=yes,status=yes,resizable=yes,scrollbars=yes,fullscreen=no,toolbar=yes");
   my_window.moveTo((screen.width-ancho)/2,(screen.height-alto)/2);
   my_window.document.close();
}
  
function send_sms() {
    var nodoReloj = document.getElementById('relojArena');
    var estatus   = document.getElementById('estaus_reloj');
    var call_to   = document.getElementById('call_to');
    var text      = document.getElementById('text');
    var encolar   = document.getElementById('encolar');
    var messages  = document.getElementById('messages').value;
    var trunk     = document.getElementsByName('trunk')[0].value;

    if (messages > 1) {
    	var r = confirm(SMS["warning send more than one sms per message"].replace("%messages%",messages));
        if (!r) {
            return r;
        }
    }

    if(estatus.value=='apagado'){
         estatus.value='prendido';
         nodoReloj.innerHTML = "<img src='images/hourglass.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>"+SMS["sending"]+"</font>";
         xajax_sendSMS(call_to.value,text.value,encolar.value,trunk);
     }
}

function save_campaign() {
    var messages  = document.getElementById('messages').value;
    var pause  = document.getElementById('pause').value;

    if (messages > 1) {
    	var r = confirm(SMS["warning campaign more than one sms per message"].replace("%messages%",messages));

        return r;
    }

    if (pause == 'on') {
        alert(SMS["warning campaign is in pause"]);
    }
}
