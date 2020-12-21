<?php
error_reporting(0);
date_default_timezone_set("Africa/Nairobi");


class MySoapClient extends SoapClient {

    
    
    //one way = 0 will turn off the error
    //Declaration of MySoapClient::__doRequest($request, $location, $action, $version) should be compatible with SoapClient::__doRequest($request, $location, $action, $version, $one_way = NULL)

    public function __doRequest($request, $location, $action, $version, $one_way = 0) 

    { 

        $result = parent::__doRequest($request, $location, $action, $version, $one_way = 0);

        return $result; 
    } 

  function PaymentRegister($array) { 
           

        $request  = $array;
        $location = 'https://197.248.29.94:8244/incmsPayments/2.0.1';   //set the endpoint for the JAVA JAX-WS
        $action   = 'http://incms.indra.es/IncmsPayment/PaymentRegister'; //Set the namepace here
        $version  = '1.1';
        $result   = $this->__doRequest($request, $location, $action, $version, $one_way = 0);

        return $result;

   }


}



/*-------------------------Generate  Access Token--------------------------------------------*/

function GenerateToken()

  {

    $url    = "http://197.248.29.94:8281/token"; //endpoint to generate Token

    $cs_key = ''; //Supplied by Kenya Power
    $cs_sec = ''; //Supplied by Kenya Power


    $basicAuthHeader   = base64_encode($cs_key.':'.$cs_sec);

    $params = array('grant_type' => 'password',
               'username'   => '', //supplied by Kenya Power
               'password'   => '', //Supplied by Kenya Power
               'scope'      =>'apim:subscribe'

                     );

    $curl = curl_init();

    curl_setopt($curl,CURLOPT_URL, $url);
    curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-type: application/x-www-form-urlencoded','Authorization: Basic'.' '.$basicAuthHeader));
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($params));

    $response = curl_exec($curl);

    curl_close($curl);

    $resp = json_decode($response);

    if($resp->access_token == null){

        return 'Error:'.' '.$resp->error;
        exit;

    }else{

        return $resp->access_token;
        
    }
    
}

/*---------------------Function to Capture XML and strip of whitespaces----*/
 
function load_invalid_xml($xml)
{
    $use_internal_errors = libxml_use_internal_errors(true);
    libxml_clear_errors(true);

    $sxe = simplexml_load_string($xml);

    if ($sxe)
    {
        return $sxe;
    }

    $fixed_xml = '';
    $last_pos  = 0;

    foreach (libxml_get_errors() as $error)
    {
        // $pos is the position of the faulty character,
        // you have to compute it yourself
        $pos = compute_position($error->line, $error->column);
        $fixed_xml .= substr($xml, $last_pos, $pos - $last_pos) . htmlspecialchars($xml[$pos]);
        $last_pos = $pos + 1;
    }
    $fixed_xml .= substr($xml, $last_pos);

    libxml_use_internal_errors($use_internal_errors);

    return simplexml_load_string($fixed_xml);
}




?>