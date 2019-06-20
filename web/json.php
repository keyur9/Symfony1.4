<?php

// echo 'Inside Json php resp';

require_once(dirname(__FILE__).'/../config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'prod', false);
// sfContext::createInstance($configuration)->dispatch();

$context = sfContext::createInstance($configuration);

$response = new sfWebResponse(sfContext::getInstance()->getEventDispatcher());
sfContext::getInstance()->setResponse($response);

$response->setStatusCode(201);

$response->sendHttpHeaders();

// $response->sendContent('This is the response');

$response->setContent(json_encode([
    'data' => 123,
]));

$response->sendContent();

// Echo 'Action Name';

$context->dispatch();

// $jsonAnswer = array('test' => 'true');
// echo json_encode($jsonAnswer);