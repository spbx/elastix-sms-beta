<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
/* Codificación: UTF-8
   +----------------------------------------------------------------------+
   | Copyright (c) 1997-2003 PaloSanto Solutions S. A.                    |
   +----------------------------------------------------------------------+
   | Cdla. Nueva Kennedy Calle E #222 y 9na. Este                         |
   | Telfs. 2283-268, 2294-440, 2284-356                                  |
   | Guayaquil - Ecuador                                                  |
   +----------------------------------------------------------------------+
   | Este archivo fuente esta sujeto a las politicas de licenciamiento    |
   | de PaloSanto Solutions S. A. y no esta disponible publicamente.      |
   | El acceso a este documento esta restringido segun lo estipulado      |
   | en los acuerdos de confidencialidad los cuales son parte de las      |
   | politicas internas de PaloSanto Solutions S. A.                      |
   | Si Ud. esta viendo este archivo y no tiene autorizacion explicita    |
   | de hacerlo comuniquese con nosotros, podria estar infringiendo       |
   | la ley sin saberlo.                                                  |
   +----------------------------------------------------------------------+
   | Autores: Alex Villacís Lasso <a_villacis@palosanto.com>              |
   +----------------------------------------------------------------------+
  
   $Id: AppLogger.class.php,v 1.1 2007/08/06 17:00:07 alex Exp $
*/
class AppLogger
{
    private $LOGHANDLE;
    private $PREFIJO;
    
    // Crear una nueva instancia de AppLogger
    function AppLogger()
    {
        $this->LOGHANDLE = NULL;
        $this->PREFIJO = NULL;
    }
    
    // Abrir una bitácora, dado el nombre de archivo
    function open($sNombreArchivo)
    {
        // Intentar la apertura del archivo de bitácora
        if (is_null($this->LOGHANDLE)) {
            $hLogHandle = fopen($sNombreArchivo, 'at');
            if (!$hLogHandle) {
                $e = error_get_last();
                throw new Exception("AppLogger::open() - No se puede abrir archivo de log '$sNombreArchivo' - $e[message]");
            }
            stream_set_write_buffer($hLogHandle, 0);
            $this->LOGHANDLE = $hLogHandle;
        }
    }

    // Definir el prefijo a mostrar en cada mensaje
    function prefijo($sNuevoPrefijo = false)
    {
        if ($sNuevoPrefijo !== false) $this->PREFIJO = "$sNuevoPrefijo";
        return $this->PREFIJO;
    }

    // Escribir una cadena en la bitácora, precedida por la fecha del sistema en
    // formato YYYY-MM-DD hh:mm
    function output($sCadena)
    {
        fwrite($this->LOGHANDLE, date('Y/m/d H:i')." : ".(is_null($this->PREFIJO) ? '' : "($this->PREFIJO) ").$sCadena."\n");
    }

    // Cerrar la bitácora del programa
    function close()
    {
        // Mandar a cerrar el archivo de bitácora
        if (!is_null($this->LOGHANDLE)) {
            fclose ($this->LOGHANDLE);
            $this->LOGHANDLE = NULL;
        }
    }
}
?>
