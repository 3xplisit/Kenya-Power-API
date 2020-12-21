# Kenya Power API
This is an unofficial API Wrapper for Kenya Power Eazzy Pay Customers. Customers who seek to Integrate with kenya power for real time Payments via their Secure API.
The API consists of 3 endpoints;

NB: <b> Please Note that to use this API your IP address needs to be whitelisted by Kenya Power to make all requests to the Below endpoints </b>
1. Access Token Generation Endpoint. ( Endpoint to Generate Access Token <link> http://197.248.29.94:8281/token </link> )
2. Payment Registration API Endpoint.( This Endpoint action is referrenced on the WSDL file and allows Developers to pass Payment information to register payment to Customers account) <link> https://197.248.29.94:8244/incmsPayments/2.0.1/ </link>
3. Account Balance API Endpoint. ( This Endpoint action is referrenced on the WSDL file and accepts Account Number to Fetch Customers Account Balance Details) <link>https://197.248.29.94:8244/incmsAccountBalance/2.0.1</link>
# Setting Up Soap Server to serve the wsdl request to KPLC
Sample Payment Register Soap Server. We will create a file called <b>PaymentRegisterServe.php</b> to handle our Payment Register Soap Server

```
/*

WSDL Web Service Call: http://<you_server_name/IP Address>/PaymentRegisterServe.php?wsdl
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
```
Your SoapServer URL will be as below. You will now need this inorder to pass the WebService Parameter Request to KPLC when calling your SoapClient Class.
```
https://<your_server_name/IP address>/PaymentRegisterServe.php?wsdl
```
# API FLow
<h3> 1. Generating the Access Token </h3>
For you to use the API and make requests to the Kenya Power API Gateway, you are provided with a Consumer Key and Consumer Secret to Generate and pass a Basic Auth Credentials to the request header to <b>Generate the Access Token</b> to be passed on all Requests to the below Endpoints and a username and password to pass as the body of the parameters as shown on the code snippet below.
<code>

    $cs_key = ''; //Supplied by Kenya Power
    $cs_sec = ''; //Supplied by Kenya Power
    $basicAuthHeader   = base64_encode($cs_key.':'.$cs_sec);
    $params = array('grant_type' => 'password',
               'username'   => '', //supplied by Kenya Power
               'password'   => '', //Supplied by Kenya Power
               'scope'      =>'apim:subscribe'

      );
  
  </code>

1. Account Balance API Endpoint.
2. Payment Registration API Endpoint.

 <h3> 2. Making Requests to the Payment Registration Endpoint.</h3>
 At this point the developer is required to take note of the following constants
  <ol>
    <li>PaymentCenterCode    = Supplied by KPLC </li>
    <li>PaymentSubAgencyCode = 0 </li>
    <li>CheckNumber          = 0 </li>
    <li>RecordType           = C </li>
    <li>Date Format          = Y-m-d\Th:i:s</li>
  
  SAMPLE PAYMENT REGISTRATION REQUEST 
 ```
 <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:inc='http://incms.indra.es/IncmsPayment/'>
   <soapenv:Header/>
   <soapenv:Body>
      <inc:PaymentRegister>
         <PaymentCenterCode>0075</PaymentCenterCode>
         <PaymentSubAgencyCode>0</PaymentSubAgencyCode>
         <ReferenceNumber>$accountno</ReferenceNumber>
         <PaymentDate>$date</PaymentDate>
         <PaymentAmount>$amount</PaymentAmount>
         <CheckNumber>0</CheckNumber>
         <AuxData>$payment_ref</AuxData>
         <PaymentEmail>0</PaymentEmail>
         <RecordType>C</RecordType>
         <ExternalData>$msisdn</ExternalData>
         <ListData>
            <Data>
               <Code>Payment_type</Code>
               <Value>$payment_type</Value>
            </Data>
         </ListData>
      </inc:PaymentRegister>
   </soapenv:Body>
</soapenv:Envelope>
```
<b> Sending the Above request XML via SoapClient </b> <br>

```
//New instance of your Extended Soap Client class..
$soapClient = new MySoapClient("http://<path_to_your_soapserver/servewsdlkplc/PaymentRegisterServe.php?WSDL", $params);
$PostTransaction = $soapClient->PaymentRegister($orderRequest);
if($PostTransaction==''){

  echo 'No Response From Host..Please Check with your Admin';
  exit;
}

//Store the data on an XML file to be used to read later
file_put_contents('data.xml', $PostTransaction); //capture the response from Kenya Power in a file then use it to parse 

$data = file_get_contents('data.xml');

//load the stored XML
$xml = load_invalid_xml($data);

$ns                = $xml->getNamespaces(true);          
$soap              = $xml->children($ns['soapenv']);
$response_Body     = $soap->children($ns['ns1']);
```
<b>Response gotten from a Successful SoapClient call to the webservice call to KPLC</b>
    
    
The Below response shows the request is a duplicate Transaction.

```
<?xml version='1.0' encoding='utf-8'?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:inc="http://incms.indra.es/IncmsPayment/">
	<soapenv:Body>
		<ns1:RegistryPaymentResponse xmlns:ns1="http://incms.indra.es/IncmsPayment/">
			<ns1:code>-1</ns1:code>
			<ns1:message>Error registering payment</ns1:message>
			<ns1:description>Duplicated payment</ns1:description>
			<ns1:system>INCMS</ns1:system>
			<ns1:transactionId>12300989</ns1:transactionId>
			<ns1:aux>S10400-54504761312</ns1:aux>
			<ns1:accountNumber>41307543</ns1:accountNumber>
			<ns1:amount>2000</ns1:amount>
			<ns1:paymentDate>2018-11-27 11:10:13.0</ns1:paymentDate>
		</ns1:RegistryPaymentResponse>
	</soapenv:Body>
</soapenv:Envelope>
```

<h2>Full Code</h2>

```
//Include the functions class below.
include('classfunctions.php');

try{
//function that generates the token
$token = GenerateToken();

//pass headers to the web service call
$httpheaders = array('http'=>array('protocol_version'=>1.1));
$context = stream_context_create($httpheaders);
$params = array('stream_context'=>$context,'trace'=>true,'exceptions'=>true,"stream_context" => stream_context_create(
            array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                ),'http' => [
                   'header' => "Authorization: Bearer ".$token,
    ],
            )));


/*-------------------------XML Request to Be Sent.--------------------------------

Some values in the XML are constant eg
PaymentCenterCode    = Supplied by KPLC
PaymentSubAgencyCode = 0
CheckNumber          = 0
RecordType           = C
date format should be Sent in the below format Y-m-d\Th:i:s

---------------------------------------------------------------------------------*/
//payment register sample request
$orderRequest ="<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:inc='http://incms.indra.es/IncmsPayment/'>
   <soapenv:Header/>
   <soapenv:Body>
      <inc:PaymentRegister>
         <PaymentCenterCode>0075</PaymentCenterCode>
         <PaymentSubAgencyCode>0</PaymentSubAgencyCode>
         <ReferenceNumber>$accountno</ReferenceNumber>
         <PaymentDate>$date</PaymentDate>
         <PaymentAmount>$amount</PaymentAmount>
         <CheckNumber>0</CheckNumber>
         <AuxData>$payment_ref</AuxData>
         <PaymentEmail>0</PaymentEmail>
         <RecordType>C</RecordType>
         <ExternalData>$cleanMSISDN</ExternalData>
         <ListData>
            <Data>
               <Code>Payment_type</Code>
               <Value>$payment_type</Value>
            </Data>
         </ListData>
      </inc:PaymentRegister>
   </soapenv:Body>
</soapenv:Envelope>";

//New instance of your Extended Soap Client class..
$soapClient = new MySoapClient("http://<path_to_your_soapserver/servewsdlkplc/PaymentRegisterServe.php?WSDL", $params);
$PostTransaction = $soapClient->PaymentRegister($orderRequest);
if($PostTransaction==''){

  echo 'No Response From Host..Please Check with your Admin';
  exit;
}

//Store the data on an XML file to be used to read later
file_put_contents('data.xml', $PostTransaction); //capture the response from Kenya Power in a file then use it to parse 

$data = file_get_contents('data.xml');

//load the stored XML
$xml = load_invalid_xml($data);

$ns                = $xml->getNamespaces(true);          
$soap              = $xml->children($ns['soapenv']);
$response_Body     = $soap->children($ns['ns1']);
//Transaction ID Generated from KenyaPower

$transactionID  = $response_Body->RegistryPaymentResponse->transactionId;

/*---------------------------------------------------------------------

      SUCCESSFULL TRANSACTIONS WILL BE VALIDATED HERE BEFORE DATABASE 

-----------------------------------------------------------------------*/
  if($response_Body->RegistryPaymentResponse->code =='0'){

      //store the payment here on a database and do your logic here

  }else{
  
/*-----------------------------------------------------------------------------------------

DUPLICATED TRANSACTIONS WILL BE VALIDATED HERE AND DATABASE UPDATING WILL BE DONE HERE ALSO. 
      
-------------------------------------------------------------------------------------------*/
    if($response_Body->RegistryPaymentResponse->description=='Duplicated payment' || $response_Body->RegistryPaymentResponse->message=='Error registering payment'){

      //Duplicate Transactions. A duplicate transaction is one where the payment ref has been repeated..

    }else{

      if($response_Body->RegistryPaymentResponse->message=='Account number doesn´t exist' || $response_Body->RegistryPaymentResponse->description=='Please review the data provided'){

      //Account Number doest exist here..
    
      }else{

        if($response_Body->RegistryPaymentResponse->description=='The resource is not available'){

      //Resource is unavailable.

        }else{


           /*----Aged transactions 3 months transactionscannot be captured in the system---*/

          if($response_Body->RegistryPaymentResponse->description=='Payment was made more than 3 months ago'){


      //Aged transactions. Those are payments whose date captured were more than 3 months ago
          }else{

            if($response_Body->RegistryPaymentResponse->message=="Payment date can not be greater than today's date"){

      //Doesnt allow future dates..
              
            }

          }
          /*------------------------------------------------------------------------------------*/
        }
      }
    }
  }
}catch(SoapFault $fault){

//capture your soap faults here..
$faultcode = $fault->faultcode;
$errormsg = $fault->faultstring;

}
```



