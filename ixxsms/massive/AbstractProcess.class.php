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
  
   $Id: AbstractProcess.class.php,v 1.1 2007/08/06 17:00:07 alex Exp $
*/

class AbstractProcess
{
    function inicioPostDemonio()
    {
        throw new Exception("AbstractProcess::inicioPostDemonio() llamado sin sobrecarga");
    }

    function procedimientoDemonio()
    {
        throw new Exception("AbstractProcess::procedimientoDemonio() llamado sin sobrecarga");
    }

    function limpiezaDemonio()
    {
        throw new Exception("AbstractProcess::limpiezaDemonio() llamado sin sobrecarga");
    }

    function demonioSoportaReconfig()
    {
        return FALSE;
    }

    function reinicioDemonio($param)
    {
        throw new Exception("AbstractProcess::reinicioDemonio() llamado sin sobrecarga");
    }
}
?>
