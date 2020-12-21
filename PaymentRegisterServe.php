<?php

/*

Web Service Call: http://192.168.10.12:81/SoapServerKPLC/Soap_PaymentRegister.php?wsdl
Endpoint Action: https://197.248.29.94:8244/incmsPayments/2.0.1

*/
try {
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

//Load the wsdl file here.
$server = new SoapServer("admin--IncmsPayment1.0.0.wsdl",array('soap_version'=>SOAP_1_2,'STYLE'=>SOAP_RPC,'use'=>SOAP_LITERAL));
$server->addFunction("PaymentRegister");

    $server->handle();
    
}
catch (Exception $e) {

    $server->fault('Sender', $e->getMessage());
}

?>
