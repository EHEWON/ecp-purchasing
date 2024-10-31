<?php

$router->get('/', ['uses' => 'IndexController@index']);
$router->group(['prefix' => 'frontend'], function () use ($router) {
    $router->get('list', ['uses' => 'IndexController@getList']);
    $router->get('inquiry', ['uses' => 'InquiryController@index']);
    $router->get('inquiry/{id:[0-9]+}', ['uses' => 'InquiryController@info']);
    $router->get('bidding', ['uses' => 'BiddingController@index']);
    $router->get('bidding/{id:[0-9]+}', ['uses' => 'BiddingController@info']);
    $router->get('tendering', ['uses' => 'TenderingController@index']);
    $router->get('tendering/{id:[0-9]+}', ['uses' => 'TenderingController@info']);
});

