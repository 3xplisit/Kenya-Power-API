<?php

/*

Web Service Call: http://<you_server_name/IP Address>/AccountBalanceServe.php?wsdl
Endpoint Action: https://197.248.29.94:8244/incmsAccountBalance/2.0.1

*/
try {
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

$wsdl = 'admin--incmsAccountBalance2.0.1.wsdl';
$options = array('trace'=>1,
				 'cache_wsdl'=>WSDL_CACHE_NONE,
				 'exception'=>1);


$server = new soapserver($wsdl,$options);
$server->handle();

}
catch (Exception $e) {

    $server->fault('Sender', $e->getMessage());
}



