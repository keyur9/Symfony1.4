<?php

// Application Id
// define(applicationId, 'eyJhcHAiOiI2NjA6NjUiLCJ2ZXIiOiIyLjAiLCJvcmciOiIyMDc6MTE2IiwiaWF0IjoxNTYwMzg0MDAwfQ.vvlpq-Fg2xeBT-1pJNhQO4-Fg0_7zcdoV1D6a62ZI-8');
 
// /**
//  * returns metadata to be added by the events.
//  *
//  * @return mixed
//  */
function getMetadata($request, $response) {
    return array("foo"=>"Symfony example", "boo"=>"custom data", "uri" => $request->getUri());
  };

/**
 * Use this function to find tokenId . If your app differs from standard lararvel for tokenIds.
 *
 * @return string
 */
function identifySessionId($request, $response) {
    // if ($request->hasSession()) {
    //     return $request->session()->getId();
    // } else {
    //     return null;
    // }
    return '23jdf0owekfmcn4u3qypxg09w4d8ayrcdx8nu2ngs98y18cx98q3yhwmnhcfx43f';
};

/**
 * users the userId. If your app differs from standard lararvel for identify users.
 *
 * @return string
 */

function identifyUserId($request, $response) {
    // if (is_null($request->user())) {
    //     return null;
    // } else {
    //     $user = $request->user();
    //     return $user['id'];
    // }
    return '12345';
};


/**
 * users the userId. If your app differs from standard lararvel for identify company.
 *
 * @return string
 */

function identifyCompanyId($request, $response) {
    // if (is_null($request->user())) {
    //     return null;
    // } else {
    //     $user = $request->user();
    //     return $user['id'];
    // }
    return '5678';
};


/**
 * Remove any fields from body that you don't want to send to Moesif.
 *
 * @return body
 */
function maskResponseBody($body) {
    return $body;
};


/**
 * Remove any fields from body that you don't want to send to Moesif.
 *
 * @return body
 */
function maskRequestBody($body) {
    return $body;
};

// sfConfig::set($test, 'test');

// define(getMetadata, function($request, $response) {
//     return array("foo"=>"Symfony example", "boo"=>"custom data");
//   });

// return [
//     'applicationId' => 'your application id',
//     'maskRequestHeaders' => $maskRequestHeaders,
//     'maksRequestBody' => $maskRequestBody,
//     'maskResponseHeaders' => $maskResponseHeaders,
//     'maskResponseBody' => $maskResponseBody,
//     'identifyUserId' => $identifyUserId,
//     'identifySessionId' => $identifySessionId,
//     'apiVersion' => '1.2.2',
//     'debug' => true,
//     'addTags' => $addTags
// ];