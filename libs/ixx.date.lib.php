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

function formatoFecha($comodin) {
    switch (get_language()."_".strtoupper(get_language())) {
        case "en_EN":
            return "{$comodin}Y-{$comodin}m-{$comodin}d";
    }

    return "{$comodin}d/{$comodin}m/{$comodin}Y";
}

function traducirFechaHoraCorta($fecha) {
    return date("d/m - H:i:s",strtotime($fecha));
} 

function traducirFechaHoraLarga($fecha) {
    return date(formatoFecha("")." H:i:s",strtotime($fecha));
} 

function traducirFecha_a_Hora($fecha) {
    return date("H:i:s",strtotime($fecha));
}

function traducirFecha_a_NumeroDia($fecha) {
    return date("d",strtotime($fecha));
}

function traducirFechaLetrasEn($fecha) {
    $mesesT = array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
    $mesesI = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

    return str_ireplace($mesesT,$mesesI,$fecha);
}

function traducirFechaLetras($fecha) {
    setlocale(LC_TIME, get_language()."_".strtoupper(get_language()));

    if (is_integer($fecha)) {
        $trad = strftime('%d %b %Y',$fecha);
    } else {
        $fnum = strtotime($fecha);
        if (($fnum == 1) || ($fnum == "")) {
           $fnum = strtotime(traducirFechaLetrasEn($fecha));
        }

        $trad = strftime('%d %b %Y',$fnum);
    }

    $mesesT = array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
    return str_ireplace($mesesT,$mesesT,$trad);
}

function traducirFechaNumerica($fecha) {
    if ($fecha != "") {
        setlocale(LC_TIME, get_language()."_".strtoupper(get_language()));

        return strftime(formatoFecha("%"),strtotime($fecha));
    } else {
        return "";
    }
}

function traducirFechaDiaFecha($fecha) {
    if ($fecha != "") {
        setlocale(LC_TIME, get_language()."_".strtoupper(get_language()));

        return utf8_encode(ucfirst(strftime("%a, ".formatoFecha("%"),strtotime($fecha))));
    } else {
        return "";
    }
}

function traducirSegundos_a_HorasMinutosSegundos($segundos) {
    $hours = intval(intval($segundos) / 3600);
    $minutes = ($segundos / 60)%60;
    $seconds = $segundos%60;

    if ($hours < 10) {
        $hours = "0".$hours;
    }

    if ($minutes < 10) {
        $minutes = "0".$minutes;
    }

    if ($seconds < 10) {
        $seconds = "0".$seconds ;
    }

    return "$hours:$minutes:$seconds";
}

function traducirSegundos_a_Minutos($segundos) {
    $hours = intval(intval($segundos) / 3600);
    $seconds = $segundos%60;

    $minutes = ($segundos / 60)%60 + $hours * 60;

    if ($minutes < 10) {
        $minutes = "0".$minutes;
    }

    return number_format($minutes,0,",",".");
}
?>
