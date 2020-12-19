# Kenya Power API
This is an unofficial API Wrapper for Kenya Power Eazzy Pay Customers. Customers who seek to Integrate with kenya power for real time Payments via their Secure API.
The API consists of 2 endpoints;

1. Account Balance API Endpoint. ( Endpoint to Generate Access Token <link> http://197.248.29.94:8281/token </link> )
2. Payment Registration API Endpoint.( Endpoint to Pass Payment Data via SoapClient <link> https://197.248.29.94:8244/incmsPayments/2.0.1/ </link>

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
  PaymentCenterCode    = Supplied by KPLC
  PaymentSubAgencyCode = 0
  CheckNumber          = 0
  RecordType           = C
  Date Format          = Y-m-d\Th:i:s
  
  SAMPLE PAYMENT REGISTRATION REQUEST 




