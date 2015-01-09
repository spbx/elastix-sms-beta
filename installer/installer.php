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
$DocumentRoot = "/var/www/html";

require_once "$DocumentRoot/libs/paloSantoInstaller.class.php";
include_once "$DocumentRoot/libs/paloSantoDB.class.php";
include_once "$DocumentRoot/libs/paloSantoConfig.class.php";

$tmpDir = '/tmp/new_module';  # in this folder the load module extract the package content

#generar el archivo db de campañas
$return=1;
$path_script_db="$tmpDir/installer/massive_sms.sql";

$pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
$arrConfig = $pConfig->leer_configuracion(false);

$datos_conexion['user'] = $arrConfig['AMPDBUSER']['valor'];
$datos_conexion['password'] = $arrConfig['AMPDBPASS']['valor'];

$datos_conexion['locate'] = "";

$oInstaller = new Installer();

if (file_exists($path_script_db))
{
    $return=0;

    //STEP 1: Create database call_center
    $pDB = new paloDB ('mysql://root:'.MYSQL_ROOT_PASSWORD.'@localhost/mysql');
    $sPeticionSQL = "SELECT count(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array('massive_sms'));
    if ($r[0] > 0) {
        fputs(STDERR, "INFO: La base de datos massive_sms ya está creada:\n");
    } else {
        $return=$oInstaller->createNewDatabaseMySQL($path_script_db,"massive_sms",$datos_conexion);
    }

    $pDB = new paloDB ('mysql://root:'.MYSQL_ROOT_PASSWORD.'@localhost/massive_sms');

    crearColumnaSiNoExiste($pDB, "massive_sms", "sms_trunk", "script", "ADD COLUMN script text");

    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "listo", "ADD COLUMN listo TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_server", "ADD COLUMN has_server TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_user", "ADD COLUMN has_user TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_password", "ADD COLUMN has_password TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_port", "ADD COLUMN has_port TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_system_type", "ADD COLUMN has_system_type TINYINT(4) DEFAULT 1");
    crearColumnaSiNoExiste($pDB, "massive_sms", "service_types", "has_script", "ADD COLUMN has_script TINYINT(4) DEFAULT 0");

    $pDB->genQuery("update service_types set listo = 1 where type = 'SMPP'");
    $pDB->genQuery("update service_types set listo = 2 where type = 'InfoBip'");
    $pDB->genQuery("update service_types set listo = 3 where type = 'SIPTraffic'");

    $pDB->genQuery("insert into service_types(type,name,active,listo,has_server,has_user,has_password,has_port,has_system_type,has_script) values('BASH','Bash Script',1,4,0,0,0,0,0,1)");

    //$pDB = new paloDB ('mysql://{$datos_conexion['user']}:{$datos_conexion['password']}@localhost/massive_sms');
    //$pDB->disconnect();

    //STEP 2: Libs
    exec("sudo -u root chmod 777 $DocumentRoot/libs/",$arrConsole,$flagStatus);
    exec("cp -f -r $tmpDir/libs/* $DocumentRoot/libs",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 775 $DocumentRoot/libs/",$arrConsole,$flagStatus);

    //STEP 3: Dialer process
    exec("sudo -u root chmod 777 /opt/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 777 /opt/elastix/",$arrConsole,$flagStatus);

    exec("mkdir -p /opt/elastix/ixxsms/",$arrConsole,$flagStatus);

    exec("mv -f $tmpDir/ixxsms/massive/* /opt/elastix/ixxsms/",$arrConsole,$flagStatus);

    exec("sudo -u root chmod 755 /opt/elastix/ixxsms/*",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /opt/elastix/ixxsms/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /opt/elastix/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /opt/",$arrConsole,$flagStatus);

    // STEP 4: logrotate configuration
    exec("sudo -u root chmod 777 /etc/logrotate.d/",$arrConsole,$flagStatus);
    exec("mv -f $tmpDir/installer/ixxsms.logrotate /etc/logrotate.d/ixxsms",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/logrotate.d/",$arrConsole,$flagStatus);

    // STEP 5: init script
    exec("sudo -u root chmod 777 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("mv $tmpDir/ixx_sms_process/ixxmassivesms /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/rc.d/init.d/ixxmassivesms",$arrConsole,$flagStatus);
    exec("sudo -u root chmod 755 /etc/rc.d/init.d/",$arrConsole,$flagStatus);
    $return = ($flagStatus)?2:0;
}

exit($return);

function quitarColumnaSiExiste($pDB, $sDatabase, $sTabla, $sColumna)
{
    $sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
    	fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] > 0) {
        fputs(STDERR, "INFO: Se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla DROP COLUMN $sColumna";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);
        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
        fputs(STDERR, "INFO: No existe $sTabla.$sColumna en base de datos $sDatabase. No se hace nada.\n");
    }
}

function crearColumnaSiNoExiste($pDB, $sDatabase, $sTabla, $sColumna, $sColumnaDef)
{
	$sPeticionSQL = <<<EXISTE_COLUMNA
SELECT COUNT(*) 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
EXISTE_COLUMNA;
    $r = $pDB->getFirstRowQuery($sPeticionSQL, FALSE, array($sDatabase, $sTabla, $sColumna));
    if (!is_array($r)) {
        fputs(STDERR, "ERR: al verificar tabla $sTabla.$sColumna - ".$pDB->errMsg."\n");
        return;
    }
    if ($r[0] <= 0) {
    	fputs(STDERR, "INFO: No se encuentra $sTabla.$sColumna en base de datos $sDatabase, se ejecuta:\n");
        $sql = "ALTER TABLE $sTabla $sColumnaDef";
        fputs(STDERR, "\t$sql\n");
        $r = $pDB->genQuery($sql);

        if (!$r) fputs(STDERR, "ERR: ".$pDB->errMsg."\n");
    } else {
    	fputs(STDERR, "INFO: Ya existe $sTabla.$sColumna en base de datos $sDatabase.\n");
    }
}
?>
