<?php

/*
-------------------------Change Log:----------------------------------

===============
02 July 2017:- 

 KPLC Included a transactionID to uniquely sign all transactions from the system as Accepted.
 What this change means is that any transaction from KPLC without a transactionID should be 
 resent or money refunded back to the customer.
===============
19 September 2017:-

KPLC Made changes to their internal system. All 3rd Party Agents are hereby required to generate their own 
access token and pass the parameter as an Authorization Bearer in all transactions to the Endpoint.
This was implemented on the said date. Details of implementation are at classfunctions.php



*/
include('classfunctions.php');

try{

$token = GenerateToken();

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
