<?php


// Metadata / Mask options
// Send response headers back to the client
// Remove unnecssary files and content from the files - Clean up
// Create a sample endpoing returning Json
// Add log message --
// Delete Logger / config / controller files

use DateTime;
use DateTimeZone;
require_once __DIR__."/Moesif/Sender/MoesifApi.php";
// include (__DIR__ ."/../apps/frontend/config/config.php");
use Moesif\Sender\MoesifApi;
// use Psr\Log\LoggerInterface;
// require_once "Logger.php";

// class SingleClass extends sfWebResponse
// {
//   //   public function setHttpHeader($name, $value, $replace = true) {
//   //     error_log('Inside setHttpHeader');
//   //     error_log(print_r($name, TRUE));
//   //     error_log(print_r($value, TRUE));
//   //     // parent::setHttpHeader($name, $value, $replace);
//   //     $this->setHttpHeader($name, $value, $replace);
//   //     // parent::setParameter($name, $value);
//   // }
// }

// class MultipleClass extends sfFilter
// // class sfFilter extends SingleClass
// {
//     public function setHttpHeader($name, $value, $replace = true) {
//       error_log('Inside setHttpHeader');
//       error_log(print_r($name, TRUE));
//       error_log(print_r($value, TRUE));
//       parent::setHttpHeader($name, $value, $replace);
//       // $this->setHttpHeader($name, $value, $replace);
//       // parent::setParameter($name, $value);
//   }
// }

class MoesifFilter extends sfFilter {

  // private $log;

  // public function __constructor(\Psr\Log\LoggerInterface $logger)
  //  {
  //      $this->$log = $logger;
  //  }


  /**
   * Get Client Ip Address.
   */
  function getIp(){
    foreach (array('HTTP_X_CLIENT_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_TRUE_CLIENT_IP', 
    'HTTP_X_REAL_IP', 'HTTP_X_REAL_IP',  'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe
                if (strpos($ip, ':') !== false) {
                    $ip = array_values(explode(':', $ip))[0];
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
}

/**
 * Generate GUID.
 */
function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Function for basic field validation (present and neither empty nor only white space.
 */
function IsNullOrEmptyString($str){
  $isNullOrEmpty = false;
  if (!isset($str) || trim($str) === '') {
      $isNullOrEmpty = true;
  } 
  return $isNullOrEmpty;
}

protected function ensureString($item) {
  if (is_null($item)) {
    return $item;
  }
  if (is_string($item)) {
    return $item;
  }
  return strval($item);
}

  public function setHttpHeader($name, $value, $replace = true) {
      error_log('Inside setHttpHeader');
      error_log(print_r($name, TRUE));
      error_log(print_r($value, TRUE));
      parent::setHttpHeader($name, $value, $replace);
      // parent::setParameter($name, $value);
  }

 public function execute($filterChain){

    // Perform action before response
    $startTime = microTime(true);
    $micro = sprintf("%06d",($startTime - floor($startTime)) * 1000000);
    $startDateTime = new DateTime( date('Y-m-d H:i:s.'.$micro, $startTime) );
    $startDateTime->setTimezone(new DateTimeZone("UTC"));

    // Request
    $request = $this->context->getRequest();

    // execute next filter
    $filterChain->execute(); // $filterChain->execute($filterChain); 
      
    // Response
    $response = $this->context->getResponse();

    // TODO: Patch
    $response->setHttpHeader = $this->setHttpHeader;

    // Configuration Options
    $applicationId = $this->getParameter('applicationId');
    $debug = $this->getParameter('debug');
    $disableTransactionId = $this->getParameter('disableTransactionId') ?: false;

    if (is_null($debug)) {
        $debug = false;
    }

    if (is_null($applicationId)) {
        throw new Exception('ApplicationId is missing. Please provide applicationId in moesif.php in config folder.');
    }

    // Request object
    $requestData = [
      'time' => $startDateTime->format('Y-m-d\TH:i:s.uP'),
      'verb' => $request->getRequestContext()['method'],
      'uri' => $request->getUri(),
      'ip_address' => $this->getIp()
    ];

    // Request Headers
    $requestHeaders = [];
    foreach ($request->getPathInfoArray() as $key => $value) {
      if( strpos( $key, 'HTTP' ) !== false) {
        $key = str_replace('HTTP_','', $key);
        $key = str_replace('_','-', $key);
        $requestHeaders[$key] = (string) $value;
      }
    }

    // Add Transaction Id to the request headers
    if (!$disableTransactionId) {
      if (!is_null((string) $requestHeaders['X-MOESIF-TRANSACTION-ID'] ?? null)) {
          $reqTransId = (string) $requestHeaders['X-MOESIF-TRANSACTION-ID'];
          if (!is_null($reqTransId)) {
              $transactionId = $reqTransId;
          }
          if ($this->IsNullOrEmptyString($transactionId)) {
              $transactionId = $this->guidv4(openssl_random_pseudo_bytes(16));
          }
      }
      else {
          $transactionId = $this->guidv4(openssl_random_pseudo_bytes(16));
      }
      // Add Transaction Id to the request headers
      $requestHeaders['X-Moesif-Transaction-Id'] = $transactionId;
  }

  // can't use headers->all() because it is an array of arrays.
  // $request->headers->all();
  // if (function_exists('maskRequestHeaders')) {
  //   $requestData['headers'] = maskRequestHeaders($requestHeaders);
  // } else {
  //   $requestData['headers'] = $requestHeaders;
  // }

  // Set Request headers
  $requestData['headers'] = $requestHeaders;

    // Request Body
    $requestContent = $request->getContent();
    if(!is_null($requestContent)) {
        $requestBody = json_decode($requestContent, true);
        if (is_null($requestBody)) {
          if ($debug) {
            error_log('[Moesif] : request body is empty or not json, base 64 encode');
            $this->customLog('[moesif] : request body is empty or not json, base 64 encode');
          }
          $requestData['body'] = base64_encode($requestContent);
          $requestData['transfer_encoding'] = 'base64';
        } else {
          
          // Set Request body
          $requestData['body'] = $requestBody;
            //   if (function_exists('maskRequestBody')) {
            //     $requestData['body'] = maskRequestBody($requestBody);
            // } else {
            //     $requestData['body'] = $requestBody;
            // }
        }
    }

    error_log(print_r($requestData, TRUE));
    $this->customLog('[moesif] : Request Data - ');
    $this->customLog(json_encode($requestData));
    
    // Response object
    $endTime = microTime(true);
    $micro = sprintf("%06d",($endTime - floor($endTime)) * 1000000);
    $endDateTime = new DateTime( date('Y-m-d H:i:s.'.$micro, $endTime) );
    $endDateTime->setTimezone(new DateTimeZone("UTC"));

    // Response Object
    $responseData = [
      'time' => $endDateTime->format('Y-m-d\TH:i:s.uP'),
      'status' => $response->getStatusCode()
    ];

    // Response Headers
    $responseHeaders = [];
    foreach ($response->getHttpHeaders() as $key => $value) {
      if (!is_null($key) && $key != '') {
        $responseHeaders[$key] = (string) $value;
      }
    }

    // Add Transaction Id to the response headers
    if (!is_null($transactionId)) {
      $responseHeaders['X-Moesif-Transaction-Id'] = $transactionId;
    }

    // // Mask Response Body
    // if (function_exists('maskResponseHeaders')) {
    //     $responseData['headers'] = maskResponseHeaders($responseHeaders);
    // } else {
    //     $responseData['headers'] = $responseHeaders;
    // }

    // Set Response Headers
    $responseData['headers'] = $responseHeaders;

    // Response Body
    $responseContent = $response->getContent();
    if (!is_null($responseContent)) {
      $jsonBody = json_decode($response->getContent(), true);

      if(!is_null($jsonBody)) {
          //   if (function_exists('maskResponseBody')) {
          //     $responseData['body'] = maskResponseBody($jsonBody);
          // } else {
          //     $responseData['body'] = $jsonBody;
          // }
          
          // Set Response Body
          $responseData['body'] = $jsonBody;
      } else {
          if (!empty($responseContent)) {
              if ($debug) {
                error_log('[moesif] : response body not be empty and not json, base 64 encode');
                $this->customLog('[moesif] : response body not be empty and not json, base 64 encode');
              }
              $responseData['body'] = base64_encode($responseContent);
              $responseData['transfer_encoding'] = 'base64';
          }
      }
    }

    error_log(print_r($responseData, TRUE));
    $this->customLog('[moesif] : Response Data - ');
    $this->customLog(json_encode($responseData));

    // Prepare Moesif Event Model
    $data = [
        'request' => $requestData,
        'response' => $responseData
    ];

    // Metadata
    // $data['metadata'] = getMetadata($request, $response);
    

    // Session Token
    // $data['session_token'] = identifySessionId($request, $response);
    if(!is_null($requestHeaders['SESSIONTOKENHEADER'])) {
      $data['session_token'] = $this->ensureString($requestHeaders['SESSIONTOKENHEADER']);
    }

    // UserId
    // $data['user_id'] = identifyUserId($request, $response);
    if(!is_null($requestHeaders['USERIDHEADER'])) {
      $data['user_id'] = $this->ensureString($requestHeaders['USERIDHEADER']); //identifyUserId($request, $response);
    }

    // CompanyId
    // $data['company_id'] = identifyCompanyId($request, $response);
    if(!is_null($requestHeaders['COMPANYIDHEADER'])) {
      $data['company_id'] = $this->ensureString($requestHeaders['COMPANYIDHEADER']);
    }

    // Add transaction Id to the response send to the client
    if (!is_null($transactionId)) {
      // $response->addHttpMeta('X-MOESIF-TRANSACTION-ID', $transactionId);
      error_log('Before adding');
      error_log(print_r($response->getHttpHeaders(), TRUE));
      // $response->clearHttpHeaders();
      // $name = $response->normalizeHeaderName('X-Moesif-Transaction-Id');
      $response->setHttpHeader('X-Moesif-Transaction-Id', $transactionId); //X-Moesif-Transaction-Id
      // $this->setHttpHeader('X-Moesif-Transaction-Id', $transactionId); //X-Moesif-Transaction-Id
      // $response->setHttpHeader('X-Moesif-Transaction-Id', $transactionId);

      // error_log('Normalized header name ');
      // error_log(print_r($this->normalizeHeaderName('X-Moesif-Transaction-Id'), TRUE));
      // $this -> newsetHttpHeader('X-Moesif-Transaction-Id', $transactionId);
      // $this -> getContext() -> getResponse() -> setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', false);
      
      // $this->getHttpHeaders();
      
      // foreach ($this->newgetHttpHeaders() as $key => $value) {
      //   error_log(print_r($key, TRUE));
      //   error_log(print_r($value, TRUE));
      //   $this->setHttpHeader($key, $value);
      //   // $response->setHttpHeader($key, $value);
      // }

      
      // $this->getHttpHeaders = $response->getHttpHeaders;
      // $headers = $this->getHttpHeaders();

      // $response->setHttpHeader = $this->setHttpHeader;
      // $response->getHttpHeaders = $this->newgetHttpHeaders;

      // error_log('After adding this ');
      // error_log(print_r($this->getHttpHeaders(), TRUE));

      error_log('After adding response');
      error_log(print_r($response->getHttpHeaders(), TRUE));
      
      // $this->sendHttpHeaders();
      // $response->sendHttpHeaders();
      // return sfView::HEADER_ONLY;

    }

    // Need to initialize it once only
    // $moesifApi =  new MoesifApi($applicationId, ['fork'=>true, 'debug'=>$debug]);
    // $moesifApi->track($data);


    // sfContext::getInstance()->getLogger()->crit('Debyug');
    error_log('Event Sent to Moesif');
    $this->customLog('[moesif] : Event Sent to Moesif');
    // sfLogger::getInstance()->err('Ex');

  //   $logger = new sfFileLogger(
  //     new sfEventDispatcher(), 
  //     array('file'=> $file)
  // );

  // $logger->log( sprintf("#%s# (%s) %s ", $seed, $url, $content),
  //           0, 
  //           "info"
  // );

    // $log = new MyDependency();
    // debug('Debug');
    // $logger = $this->getContainer()->get('logger');
    // error_log(print_r($this->log, TRUE));
    // $this->$log->warning('some warning');

  //   $logger = new sfVarLogger(
  //     new sfEventDispatcher(), 
  //     array()
  // );

  // $logger->doLog('Send', 0);
    // sfContext::getInstance()->getLogger()->info('qmchenry was here');
    // $this->logMessage('errortest1', 'err');
  }

  /**
 * just log
 *  @param integer $seed: a random number that will be identical across request and response.
 *  @param string $content: the content of the message to be logged
 *  @param type: the type of the message (RQ = request, RS = response)
 */
  private function customLog($content)
  {
      // get the current action information
      $moduleName = $this->context->getModuleName();
      $actionName = $this->context->getActionName();
      $message = $moduleName."-".$actionName;
      $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

      $file =  sprintf('%s/%s.log', sfConfig::get('sf_log_dir', "no_log_dir")."/api-in", $message);
      $logger = new sfFileLogger(
                  new sfEventDispatcher(), 
                  array('file'=> $file)
              );

      $logger->log( sprintf("# (%s) %s ", $url, $content), 0, "info");
  }
}