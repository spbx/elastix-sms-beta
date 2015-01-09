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

class IXXSMSSMPPProvider {
    var $OK = 0;
	var $CONNECTION_ERROR = -2; //Connection error
	var $ESME_ROK = 0x00000000; //No Error
	var $ESME_RINVMSGLEN = 0x00000001; //Message too long
	var $ESME_RINVCMDLEN = 0x00000002; //Command length is invalid
	var $ESME_RINVCMDID = 0x00000003; //Command ID is invalid or not supported
	var $ESME_RINVBNDSTS = 0x00000004; //Incorrect bind status for given command
	var $ESME_RALYBND = 0x00000005; //Already bound
	var $ESME_RINVPRTFLG = 0x00000006; //Invalid Priority Flag
	var $ESME_RINVREGDLVFLG = 0x00000007; //Invalid registered delivery flag
	var $ESME_RSYSERR = 0x00000008; //System error
	var $ESME_RINVSRCADR = 0x0000000A; //Invalid source address
	var $ESME_RINVDSTADR = 0x0000000B; //Invalid destination address
	var $ESME_RINVMSGID = 0x0000000C; //Message ID is invalid
	var $ESME_RBINDFAIL = 0x0000000D; //Bind failed
	var $ESME_RINVPASWD = 0x0000000E; //Invalid password
	var $ESME_RINVSYSID = 0x0000000F; //Invalid System ID
	var $ESME_RCANCELFAIL = 0x00000011; //Cancelling message failed
	var $ESME_RREPLACEFAIL = 0x00000013; //Message recplacement failed
	var $ESME_RMSSQFUL = 0x00000014; //Message queue full
	var $ESME_RINVSERTYP = 0x00000015; //Invalid service type
	var $ESME_RINVNUMDESTS = 0x00000033; //Invalid number of destinations
	var $ESME_RINVDLNAME = 0x00000034; //Invalid distribution list name
	var $ESME_RINVDESTFLAG = 0x00000040; //Invalid destination flag
	var $ESME_RINVSUBREP = 0x00000042; //Invalid submit with replace request
	var $ESME_RINVESMCLASS = 0x00000043; //Invalid esm class set
	var $ESME_RCNTSUBDL = 0x00000044; //Invalid submit to ditribution list
	var $ESME_RSUBMITFAIL = 0x00000045; //Submitting message has failed
	var $ESME_RINVSRCTON = 0x00000048; //Invalid source address type of number ( TON )
	var $ESME_RINVSRCNPI = 0x00000049; //Invalid source address numbering plan ( NPI )
	var $ESME_RINVDSTTON = 0x00000050; //Invalid destination address type of number ( TON )
	var $ESME_RINVDSTNPI = 0x00000051; //Invalid destination address numbering plan ( NPI )
	var $ESME_RINVSYSTYP = 0x00000053; //Invalid system type
	var $ESME_RINVREPFLAG = 0x00000054; //Invalid replace_if_present flag
	var $ESME_RINVNUMMSGS = 0x00000055; //Invalid number of messages
	var $ESME_RTHROTTLED = 0x00000058; //Throttling error
	var $ESME_RINVSCHED = 0x00000061; //Invalid scheduled delivery time
	var $ESME_RINVEXPIRY = 0x00000062; //Invalid Validty Period value
	var $ESME_RINVDFTMSGID = 0x00000063; //Predefined message not found
	var $ESME_RX_T_APPN = 0x00000064; //ESME Receiver temporary error
	var $ESME_RX_P_APPN = 0x00000065; //ESME Receiver permanent error
	var $ESME_RX_R_APPN = 0x00000066; //ESME Receiver reject message error
	var $ESME_RQUERYFAIL = 0x00000067; //Message query request failed
	var $ESME_RINVTLVSTREAM = 0x000000C0; //Error in the optional part of the PDU body
	var $ESME_RTLVNOTALLWD = 0x000000C1; //TLV not allowed
	var $ESME_RINVTLVLEN = 0x000000C2; //Invalid parameter length
	var $ESME_RMISSINGTLV = 0x000000C3; //Expected TLV missing
	var $ESME_RINVTLVVAL = 0x000000C4; //Invalid TLV value
	var $ESME_RDELIVERYFAILURE = 0x000000FE; //Transaction delivery failure
	var $ESME_RUNKNOWNERR = 0x000000FF; //Unknown error
	var $ESME_RSERTYPUNAUTH = 0x00000100; //ESME not authorised to use specified servicetype
	var $ESME_RPROHIBITED = 0x00000101; //ESME prohibited from using specified operation
	var $ESME_RSERTYPUNAVAIL = 0x00000102; //Specified servicetype is unavailable
	var $ESME_RSERTYPDENIED = 0x00000103; //Specified servicetype is denied
	var $ESME_RINVDCS = 0x00000104; //Invalid data coding scheme
	var $ESME_RINVSRCADDRSUBUNIT = 0x00000105; //Invalid source address subunit
	var $ESME_RINVSTDADDRSUBUNIR = 0x00000106; //Invalid destination address subunit
	var $ESME_RINVBALANCE = 0x0000040B; //Insufficient credits to send message
	var $ESME_RUNESME_SPRTDDESTADDR = 0x0000040C; //Destination address blocked by the ActiveXperts SMPP Demo Server

    function getMessage($code) {
        $msg = array(
                        $this->OK  => 'Message delivered for sending',
                        $this->CONNECTION_ERROR=>"Connection socket error",
                        $this->ESME_RINVMSGLEN=>"Message too long",
                        $this->ESME_RINVCMDLEN=>"Command length is invalid",
                        $this->ESME_RINVCMDID=>"Command ID is invalid or not supported",
                        $this->ESME_RINVBNDSTS=>"Incorrect bind status for given command",
                        $this->ESME_RALYBND=>"Already bound",
                        $this->ESME_RINVPRTFLG =>"Invalid Priority Flag",
                        $this->ESME_RINVREGDLVFLG=>"Invalid registered delivery flag",
                        $this->ESME_RSYSERR=>"System error",
                        $this->ESME_RINVSRCADR=>"Invalid source address",
                        $this->ESME_RINVDSTADR=>"Invalid destination address",
                        $this->ESME_RINVMSGID=>"Message ID is invalid",
                        $this->ESME_RBINDFAIL=>"Bind failed",
                        $this->ESME_RINVPASWD=>"Invalid password",
                        $this->ESME_RINVSYSID=>"Invalid System ID",
                        $this->ESME_RCANCELFAIL=>"Cancelling message failed",
                        $this->ESME_RREPLACEFAIL=>"Message recplacement failed",
                        $this->ESME_RMSSQFUL=>"Message queue full",
                        $this->ESME_RINVSERTYP=>"Invalid service type",
                        $this->ESME_RINVNUMDESTS=>"Invalid number of destinations",
                        $this->ESME_RINVDLNAME=>"Invalid distribution list name",
                        $this->ESME_RINVDESTFLAG=>"Invalid destination flag",
                        $this->ESME_RINVSUBREP=>"Invalid submit with replace request",
                        $this->ESME_RINVESMCLASS=>"Invalid esm class set",
                        $this->ESME_RCNTSUBDL=>"Invalid submit to ditribution list",
                        $this->ESME_RSUBMITFAIL=>"Submitting message has failed",
                        $this->ESME_RINVSRCTON=>"Invalid source address type of number ( TON )",
                        $this->ESME_RINVSRCNPI=>"Invalid source address numbering plan ( NPI )",
                        $this->ESME_RINVDSTTON=>"Invalid destination address type of number ( TON )",
                        $this->ESME_RINVDSTNPI=>"Invalid destination address numbering plan ( NPI )",
                        $this->ESME_RINVSYSTYP=>"Invalid system type",
                        $this->ESME_RINVREPFLAG=>"Invalid replace_if_present flag",
                        $this->ESME_RINVNUMMSGS=>"Invalid number of messages",
                        $this->ESME_RTHROTTLED=>"Throttling error",
                        $this->ESME_RINVSCHED=>"Invalid scheduled delivery time",
                        $this->ESME_RINVEXPIRY=>"Invalid Validty Period value",
                        $this->ESME_RINVDFTMSGID=>"Predefined message not found",
                        $this->ESME_RX_T_APPN=>"ESME Receiver temporary error",
                        $this->ESME_RX_P_APPN=>"ESME Receiver permanent error",
                        $this->ESME_RX_R_APPN=>"ESME Receiver reject message error",
                        $this->ESME_RQUERYFAIL=>"Message query request failed",
                        $this->ESME_RINVTLVSTREAM=>"Error in the optional part of the PDU body",
                        $this->ESME_RTLVNOTALLWD=>"TLV not allowed",
                        $this->ESME_RINVTLVLEN=>"Invalid parameter length",
                        $this->ESME_RMISSINGTLV=>"Expected TLV missing",
                        $this->ESME_RINVTLVVAL=>"Invalid TLV value",
                        $this->ESME_RDELIVERYFAILURE=>"Transaction delivery failure",
                        $this->ESME_RUNKNOWNERR=>"Unknown error",
                        $this->ESME_RSERTYPUNAUTH=>"ESME not authorised to use specified servicetype",
                        $this->ESME_RPROHIBITED=>"ESME prohibited from using specified operation",
                        $this->ESME_RSERTYPUNAVAIL=>"Specified servicetype is unavailable",
                        $this->ESME_RSERTYPDENIED=>"Specified servicetype is denied",
                        $this->ESME_RINVDCS=>"Invalid data coding scheme",
                        $this->ESME_RINVSRCADDRSUBUNIT=>"Invalid source address subunit",
                        $this->ESME_RINVSTDADDRSUBUNIR=>"Invalid destination address subunit",
                        $this->ESME_RINVBALANCE=>"Insufficient credits to send message",
                        $this->ESME_RUNESME_SPRTDDESTADDR=>"Destination address blocked by the ActiveXperts SMPP Demo Server",
                );

        return $msg[$code];
    }

    //Determina si el error cometido es suficiento como para parar una
    //campaña en ejecución
    function stopTrunk($error) {
        return in_array($error, array(  $this->CONNECTION_ERROR,
                                        $this->ESME_RINVPASWD,
                						$this->ESME_RINVSYSID,
	    	        					$this->ESME_RINVSERTYP,
	    	        					$this->ESME_RINVSYSTYP,
	    	        					$this->ESME_RSERTYPUNAVAIL,
	    	        					$this->ESME_RSERTYPDENIED,
	    	        					$this->ESME_RINVDCS,
	    	        					$this->ESME_RTHROTTLED));
    }

    //Determina si el error cometido es suficiento como para descartar
    //un número en una campaña en ejecución
    function stopMessage($error) {
        return in_array($error, array(  $this->ESME_RINVSRCTON,
                						$this->ESME_RINVSRCNPI,
                						$this->ESME_RINVDSTTON,
                						$this->ESME_RINVDSTNPI,
                						$this->ESME_RINVSRCADDRSUBUNIT,
                						$this->ESME_RINVSTDADDRSUBUNIR));
    }

	function sendToCarrier($user,$password,$server,$port,$system_type,$clid,$destination,$text,$unicode) {
		require_once("/var/www/html/libs/sms/smppclass.php");

		$smpp = new SMPPClass();
		$smpp->_debug = false;

        if ($clid == "") {
            $clid = " ";
        }

		$smpp->SetSender($clid);
		
        $result = $smpp->Start($server, $port, $user, $password,$system_type);
        if ($result == "") {
                return $this->CONNECTION_ERROR;
        } else {
    		if (!$result) {
	    		return $smpp->commandStatus;
	    	}
        }

		$result = $smpp->TestLink();
   		if (!$result) {
    		return $smpp->commandStatus;
    	}

		$result = $smpp->Send($destination, $text, $unicode);
   		if (!$result) {
    		return $smpp->commandStatus;
    	}

		$smpp->End();

		return 0;
	}	

	function send($clid,$destination,$text,$trunk,$unicode) {
		$ok = $this->sendToCarrier($trunk['user'],$trunk['password'],$trunk['server'],$trunk['port'],$trunk['system_type'],$clid,$destination,$text,$unicode);

		return array($ok,$this->getMessage($ok),0,$this->stopTrunk($ok),$this->stopMessage($ok));
	}
}

?>
