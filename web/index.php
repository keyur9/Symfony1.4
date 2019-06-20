<?php

// Echo "Hello, World!";

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
// sfContext::createInstance($configuration)->dispatch();


$context = sfContext::createInstance($configuration);



// Clean up request headers --
// Move time before executing filter --
// Ip Address with GetIP function --
// How to add function parameters to the filter / Set Response header other than Content-Type
// Add Response header in index.php --
// Configuration options and update the code accordingly
// Figure out why the json.php not working --
// Transcation Id / Debug and update user/company
// Test Get IP method with different ip address --
// Create new route with json or remove the html response from the index.php / json.php
// How to remove the create symfony message from the response
// Create new controller / route


// Things to do -

// How to set options,
// Initialize client only once
// Test Req / Response Mask body
// Add Mask headers 
// Create a new route with json response

// Fix Request headers 
// NEed to place LogFilter.php in filter folder
// Need to create example with POST request
// How to get all the configuration options
// How to get the request / response body/headers -- Check if we need to have the base encoding for empty string
// Request / Response time --
// Figure out 
// Create new route and return json response
// Controller 
// Send Event should be done before/after the filterchain execute method?


// header('Content-Type: application/xhtml+xml');
// header('X-Custom: Moesif');

// $jsonAnswer = array('test' => 'true');
// echo json_encode($jsonAnswer);

// return sfView::NONE;

// Echo $context->setResponse();

$response = new sfWebResponse(sfContext::getInstance()->getEventDispatcher());

sfContext::getInstance()->setResponse($response);

$response->setStatusCode(201);
// $response->setHttpHeader('Content-Type', 'application/json');

// $response->clearHttpHeaders();

// $response->setHttpHeader('X-Moesif', 'XXXX');

// $response->getHttpHeaders();

// header("X-Moesif-Transaction-Id: XXXXXX");

$response->sendHttpHeaders();

// $response->sendContent('This is the response');

$response->setContent(json_encode([
    'data' => 123,
]));

// $response->setHttpHeader('Content-Type', 'application/json');

// $response->sendHttpHeaders();

$response->sendContent();


// $response->send();

// Echo 'Action Name';

$context->dispatch();

// $response->sendHttpHeaders();

// return sfView::NONE;

// $resp = sfResponse::send('This is the response');

// $context->setResponse($resp);

// use Symfony\Component\HttpFoundation\Response;

// $response = new Response();
// $response->setContent(json_encode([
//     'data' => 123,
// ]));
// $response->headers->set('Content-Type', 'application/json');
